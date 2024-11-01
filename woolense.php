<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://mehbub.com
 * @since             1.0.0
 * @package           Woolense
 *
 * @wordpress-plugin
 * Plugin Name:       Woolense - Similar Color Products for WooCommerce
 * Description:       Extract colors from woocommerce products and show similar color products.
 * Version:           1.0.0
 * Author:            ThemeGuardian
 * WC requires at least: 2.2
 * WC tested up to: 3.8
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       woolense
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'WOOLENSE_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-woolense-activator.php
 */
function activate_woolense() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woolense-activator.php';
	

	/* Set transient if woocommerce plugin is not active*/
	if ( !class_exists( 'WooCommerce' ) ) {
		set_transient( 'woolense-woochecker', true, 5 );
	}
	else {
		$activator = new Woolense_Activator;
		$activator -> activate();
	}
	
}

add_action( 'admin_notices', 'woolense_woochecker' );
/* If not woocommerce active,show a message */
function woolense_woochecker(){

    /* Check transient, if available display notice */
    if( get_transient( 'woolense-woochecker' ) ){
        ?>
        <div class="error is-dismissible"><p><?php echo esc_html__( 'WooCommerce  is required to activate Woolense plugin.', 'woolense' ); ?></p></div>
		<?php
		deactivate_plugins( plugin_basename( __FILE__ ) );
        /* Delete transient, only display this notice once. */
        delete_transient( 'woolense-woochecker' );
    }
}



/* Deactivate this plugin when admin deactivates woocommerce */
function woolense_detect_plugin_deactivation( $plugin, $network_activation ) {
    if ($plugin=="woocommerce/woocommerce.php")
    {
        set_transient( 'woolense-woochecker', true, 5 );
    }
}
add_action( 'deactivated_plugin', 'woolense_detect_plugin_deactivation', 10, 2 );


/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-woolense-deactivator.php
 */
function deactivate_woolense() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-woolense-deactivator.php';
	Woolense_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_woolense' );
register_deactivation_hook( __FILE__, 'deactivate_woolense' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-woolense.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_woolense() {

	$plugin = new Woolense();
	$plugin->run();

}
run_woolense();
