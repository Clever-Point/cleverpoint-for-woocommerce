<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://cleverpoint.gr/
 * @since             1.0.0
 * @package           Clever_Point_For_Woocommerce
 *
 * @wordpress-plugin
 * Plugin Name:       Clever Point for WooCommerce
 * Plugin URI:        https://cleverpoint.gr/woocommerce
 * Description:       Add Clever Point to your checkout and give the option to your customers to select and pick up their parcels from a convenient store near them.
 * Version:           1.0.6
 * Author:            Clever Point
 * Author URI:        https://cleverpoint.gr/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       clever-point-for-woocommerce
 * Domain Path:       /languages
 * WC requires at least: 3.0
 * WC tested up to: 7.6.1
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
define( 'CLEVER_POINT_FOR_WOOCOMMERCE_VERSION', '1.0.6' );
if (get_option('clever_point_test_mode',null)=='yes') {
    define('CLEVER_POINT_API_ENDPOINT','https://test.cleverpoint.gr/api/v1');
}else {
    define('CLEVER_POINT_API_ENDPOINT','https://platform.cleverpoint.gr/api/v1');
}

require plugin_dir_path(__FILE__) . 'includes/update/plugin-update-checker.php';
$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
    'https://github.com/Clever-Point/cleverpoint-for-woocommerce',
    __FILE__,
    'cleverpoint-for-woocommerce'
);
$myUpdateChecker->setBranch('main');

add_action( 'before_woocommerce_init', function() {
    if ( class_exists( '\Automattic\WooCommerce\Utilities\FeaturesUtil' ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
} );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-clever-point-for-woocommerce-activator.php
 */
function activate_clever_point_for_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-clever-point-for-woocommerce-activator.php';
	Clever_Point_For_Woocommerce_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-clever-point-for-woocommerce-deactivator.php
 */
function deactivate_clever_point_for_woocommerce() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-clever-point-for-woocommerce-deactivator.php';
	Clever_Point_For_Woocommerce_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_clever_point_for_woocommerce' );
register_deactivation_hook( __FILE__, 'deactivate_clever_point_for_woocommerce' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-clever-point-for-woocommerce.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_clever_point_for_woocommerce() {

	$plugin = new Clever_Point_For_Woocommerce();
	$plugin->run();

}
run_clever_point_for_woocommerce();
