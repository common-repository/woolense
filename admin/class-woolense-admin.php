<?php
//Include color thief
require_once 'vendor/autoload.php';
use ColorThief\ColorThief;


/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://mehbub.com
 * @since      1.0.0
 *
 * @package    Woolense
 * @subpackage Woolense/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Woolense
 * @subpackage Woolense/admin
 * @author     Mehbub Rashid <rashidiam1998@gmail.com>
 */
class Woolense_Admin {
	

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woolense_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woolense_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */



		//Cropper css
		wp_enqueue_style( 'cropper.css', plugin_dir_url( __FILE__ ) . 'css/cropper.css', array(), $this->version, 'all' );

		
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woolense-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Woolense_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Woolense_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		//Admin js
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woolense-admin.js', array( 'jquery' ), $this->version, false );

	}

	//New tab for product settings
	public function woolense_product_settings_tabs( $tabs ){
		$tabs['woolense'] = array(
			'label'    => 'Woolense',
			'target'   => 'woolense_product_data',
			'class'    => array(),
			'priority' => 10,
		);
		return $tabs;
	
	}

	//Tab content
	public function woolense_product_panels(){
		wp_enqueue_media();
		
		//Dropdowns
		echo '<div id="woolense_product_data" class="panel woocommerce_options_panel hidden">';
		echo '<div class="woolense-dropdown">
				<button class="woolense-dropbtn">'.esc_html__('Select from', 'woolense').'</button>
				<div class="woolense-dropdown-content">
				<a id="woolense-upload">'.esc_html__('Media Gallery', 'woolense').'</a>
				<a class="woolense-denied">'.esc_html__('Product Gallery', 'woolense').'<span class="woolense-label">'.esc_html__('PRO', 'woolense').'</span></a>
				<a class="woolense-denied">'.esc_html__('Product Image', 'woolense').'<span class="woolense-label">'.esc_html__('PRO', 'woolense').'</span></a>
				<a class="woolense-denied">'.esc_html__('Variable Products', 'woolense').'<span class="woolense-label">'.esc_html__('PRO', 'woolense').'</span></a>
				</div>
			</div>';


		echo '<div class="woolense-dropdown">
			<button class="woolense-dropbtn woolense-cancel">'.esc_html__('Cancel', 'woolense').'</button>
		</div>';
		

		//Fetch image url and colors from product meta
		$data = get_post_meta( get_the_ID(), 'woolense_color_data', true );
		$data = str_replace('\\', '', $data);
		$data = json_decode($data, true);
		
		//Show image url if available
		$imgurl = wp_upload_dir()['baseurl'].'/'.$data[0]['imgurl'];
		echo '<div class="woolense-helper">
			<p>'.__('Choose a transparent background image or all image with the same background to make it work perfectly.Pro version coming soon<br>- Where you can pick colors from image manually.<br>- Thus 100% accuracy no matter what the background is.', 'woolense').'</p>
		</div>';

		echo '<div class="woolense-product-preview-box">';
		if(metadata_exists('post', get_the_ID(), 'woolense_color_data') && get_post_meta( get_the_ID(), 'woolense_color_data', true ) != '') {
			echo '<div class="woolense-product-preview"><img id="image" src="'.$imgurl.'" /></div>';
		}
		echo '</div>';

		//Show product colors if available
		echo '<div class="woolense-color-output">';
		if(metadata_exists('post', get_the_ID(), 'woolense_color_data') && get_post_meta( get_the_ID(), 'woolense_color_data', true ) != '') {
			for($i=0;$i<count((array) $data[0]['colors']);$i++){
				echo '<div class="single-color" style="background:' . $data[0]['colors'][$i] . ';"></div>';
			}
		}
		echo '</div>';

		//Display the loader
		echo '<div class="woolense-loader"><img src="'.plugin_dir_url( __FILE__ ).'images/loader.gif" /></div>';
		woocommerce_wp_hidden_input( array(
			'id'                => 'woolense_color_data',
			'value'             => get_post_meta( get_the_ID(), 'woolense_color_data', true )
		));
	 
		echo '</div>';
	 
	}


	//Saving hidden field info
	function woolense_save_custom_field( $post_id ) {
		$product = wc_get_product( $post_id );
		$req_data = sanitize_text_field( $_POST['woolense_color_data'] );
		$title = isset( $req_data ) ? $req_data : '';

		//Validation to check if it is a valid json or empty string
		$data = str_replace('\\', '', $title);
		if (is_array(json_decode($data, true)) || $title=='') 
		{ 
			$product->update_meta_data( 'woolense_color_data', $title  );
			$product->save();
		}
		
	}

	//Converting rgb to hex
	function rgbtohex($r, $g, $b)
	{
		return sprintf("#%02x%02x%02x", $r, $g, $b);
	}

	//Retrieve colors from image
	function woolense_fetch_product_colors(){
		$imgurl_string = filter_var($_POST['imgurl'], FILTER_SANITIZE_URL);
		if (filter_var($imgurl_string, FILTER_VALIDATE_URL)) {
			$color_array = array();
			try {
				$palette = ColorThief::getPalette($imgurl_string, 2, 1);
				for ($i = 0; $i < sizeof($palette); $i++) {
					$colarr = $palette[$i];
			
					$color = $this->rgbtohex($colarr[0], $colarr[1], $colarr[2]);
					array_push($color_array, $color);
				}
				

				//For getting the img path inside wp uploads directory
				
				$upload_dir_url = wp_upload_dir()['baseurl'];
				$upload_dir_name = basename($upload_dir_url);
				$modified_img_url = substr($imgurl_string, strpos( $imgurl_string, $upload_dir_name )+strlen($upload_dir_name)+1); //Output  = 2019/09/ezgif-2-b0324a92f8f9.jpg
				$full_data = array('imgurl'=>$modified_img_url, 'colors'=>$color_array); 
				$full_data = json_encode($full_data);
				echo $full_data;
			}
			catch(Exception $e) {
				echo esc_html('Error');
			}
			wp_die();
		}
	}


	

}
