<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/akashSinghtkd
 * @since             1.0.0
 * @package           Akwsc
 *
 * @wordpress-plugin
 * Plugin Name:       AK WooCommerce Stock Checker
 * Plugin URI:        https://github.com/akashSinghtkd
 * Description:       Enhance your WooCommerce store with the Stock Checker plugin. This intuitive plugin adds a dynamic stock checking feature directly on your product pages
 * Version:           1.0.0
 * Author:            Akash Singh
 * Author URI:        https://github.com/akashSinghtkd/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       akwsc
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 */
define( 'AKWSC__VERSION', '1.0.0' );

/**
 * Currently plugin name.
 * Start at version 1.0.0
 */
define( 'PLUGIN_NAME', 'AK WooCommerce Stock Checker' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-akwsc-activator.php
 */
function activate_akwsc() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-akwsc-activator.php';
	Akwsc_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-akwsc-deactivator.php
 */
function deactivate_akwsc() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-akwsc-deactivator.php';
	Akwsc_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_akwsc' );
register_deactivation_hook( __FILE__, 'deactivate_akwsc' );

/**
 * Check if WooCommerce is active.
 *
 * This function checks if the WooCommerce plugin is installed and active.
 * If WooCommerce is active, it adds functionality hooks.
 * If not, it shows an admin notice to inform the user.
 */
function akwsc_is_woocommerce_active() {
    // Check if the WooCommerce class exists, indicating that WooCommerce is active
    if ( !class_exists( 'WooCommerce' ) ) {
        // WooCommerce is not active, show an admin notice and deactivate the plugin
		deactivate_plugins( plugin_basename( __FILE__ ) );
        add_action( 'admin_notices', 'akwsc_woocommerce_not_available_admin_notice' );
		if( isset($_GET['activate']) ) {
			unset($_GET['activate']);
		}
    }
}
add_action( 'admin_init', 'akwsc_is_woocommerce_active' );

/**
 * Alert the admin if plugin activation fails due to the WooCommerce plugin being missing or not activated
 *
 * This function shows an admin notice in the WordPress admin area
 * if WooCommerce is not installed or activated.
 * 
 * @since      1.0.0
 * @author     Akash Singh <akashsinghtkd01@gmail.com>
 */
function akwsc_woocommerce_not_available_admin_notice() {
    // Check if WooCommerce is installed by verifying the plugin file exists
    $woocommerce_installed = file_exists( WP_PLUGIN_DIR . '/woocommerce/woocommerce.php' );
    
    // Determine the message to display based on the status of WooCommerce
    if ( ! $woocommerce_installed ) {
        $message = Plugin_Messages::woocommerce_not_installed();
    } else {
        $message = Plugin_Messages::woocommerce_not_activated();
    }

    // Display the admin notice
    ?>
    <div class="notice notice-error is-dismissible">
        <p><strong><?php echo $message; ?></strong></p>
    </div>
    <?php
}

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-akwsc.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_akwsc() {

	$plugin = new Akwsc();
	$plugin->run();

}
run_akwsc();
