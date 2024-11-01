<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://mehbub.com
 * @since      1.0.0
 *
 * @package    Woolense
 * @subpackage Woolense/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Woolense
 * @subpackage Woolense/public
 * @author     Mehbub Rashid <rashidiam1998@gmail.com>
 */
class Woolense_Public {

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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/woolense-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/woolense-public.js', array( 'jquery' ), $this->version, false );

	}
	

	//Showing woolense
	public function woolense_show(){
		if(is_single()){
			if(metadata_exists('post', get_the_ID(), 'woolense_color_data') && get_post_meta( get_the_ID(), 'woolense_color_data', true ) != '') {
				echo '<h3>'.esc_html__('Similar color products', 'woolense').'</h3>';
				$data = get_post_meta( get_the_ID(), 'woolense_color_data', true );
				$data = str_replace('\\', '', $data);
				$data = json_decode($data, true);
				$color_from = (array) $data[0]['colors'];
				$current_post_id = get_the_ID();
				//Showing product colors
				for($i=0;$i<count((array) $data[0]['colors']);$i++){
					echo '<div class="single-color" style="background:' . $data[0]['colors'][$i] . ';"></div>';
				}

				//Similar products
				echo '<ul class="products woolense-products">';

				//Getting category id array and pushing it in taxonomy array
				$categories = get_the_terms( get_the_ID(), 'product_cat' );
				$tax_array = array('relation' => 'OR');
				for($i=0;$i<count($categories);$i++){
					$categories[$i] = (array) $categories[$i];
					$tax_single = array(
						'taxonomy'      => 'product_cat',
						'field' => 'term_id', //This is optional, as it defaults to 'term_id'
						'terms'         => $categories[$i]['term_id']
					);
					array_push($tax_array, $tax_single);
				}

				//Product query
				$params = array(
					'posts_per_page' => -1,
					'post_type' => 'product',
					'tax_query' => $tax_array
				);
				$wc_query = new WP_Query($params);
				if ( $wc_query->have_posts() ) {
					while ( $wc_query->have_posts() ) {
						$wc_query->the_post();
						
						//Getting the product object so that we can use woocommerce
						//product functions later
						$product = wc_get_product( get_the_ID() );

						//Display the product if it contains woolense meta data
						if(metadata_exists('post', get_the_ID(), 'woolense_color_data') && $current_post_id != get_the_ID() && get_post_meta( get_the_ID(), 'woolense_color_data', true ) != '') {

							$product_data = get_post_meta( get_the_ID(), 'woolense_color_data', true );
							$product_data = str_replace('\\', '', $product_data);
							$product_data = json_decode($product_data, true);
							$thumbnail_url = wp_upload_dir()['baseurl'].'/'.$product_data[0]['imgurl'];
							$color_to = (array) $product_data[0]['colors'];

							//Decide if the colors are similar
							$distance_finder = new Woolense_Distance();
							$similar = 0;
							for($i=0;$i<count($color_from) - 1;$i++){
								for($j=0;$j<count($color_to) - 1;$j++) {
									$color_distance = $distance_finder->getDistanceBetweenColors($distance_finder->hex_to_rgb($color_from[$i]), $distance_finder->hex_to_rgb($color_to[$j]));
									if($color_distance <= 15) {
										$similar += 1;
										if($similar == 2){
											break;
										}
									}
								}
								if($similar == 2){
									break;
								}
							}
							

							//Display the product if images are of similar colors.
							if($similar == 2){
								echo '<li class="product type-product post-'.get_the_ID().' status-publish">
									<a href="'.get_the_permalink().'" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
										<img src="'.$thumbnail_url.'" class="attachment-woocommerce_thumbnail size-woocommerce_thumbnail" alt="">
										<h2 class="woocommerce-loop-product__title">'.esc_html__(get_the_title(), 'woolense').'</h2>
										<span class="price">
											<span class="woocommerce-Price-amount amount">
												<span class="woocommerce-Price-currencySymbol">'.get_woocommerce_currency_symbol().'</span>
												'.esc_html__($product->get_price(), 'woolense').'
											</span>
										</span>
									</a>
									<a href="/woo/shop/?add-to-cart='.get_the_ID().'" data-quantity="1" class="button product_type_simple add_to_cart_button ajax_add_to_cart"
									data-product_id="'.get_the_ID().'" data-product_sku="'.$product->get_sku().'" aria-label="'.esc_html__('Add “'.get_the_title().'” to your cart', 'woolense').'" rel="nofollow">
										'.esc_html__('Add to cart', 'woolense').'
									</a>
								</li>';
							}
						}
					}
					wp_reset_postdata(); 
				}
				echo '</ul>';
				
			}
		}
	}
}
