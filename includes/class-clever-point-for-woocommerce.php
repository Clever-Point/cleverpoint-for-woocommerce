<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://cleverpoint.gr/
 * @since      1.0.0
 *
 * @package    Clever_Point_For_Woocommerce
 * @subpackage Clever_Point_For_Woocommerce/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Clever_Point_For_Woocommerce
 * @subpackage Clever_Point_For_Woocommerce/includes
 * @author     Clever Point <info@cleverpoint.gr>
 */
class Clever_Point_For_Woocommerce {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Clever_Point_For_Woocommerce_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'CLEVER_POINT_FOR_WOOCOMMERCE_VERSION' ) ) {
			$this->version = CLEVER_POINT_FOR_WOOCOMMERCE_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'clever-point-for-woocommerce';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Clever_Point_For_Woocommerce_Loader. Orchestrates the hooks of the plugin.
	 * - Clever_Point_For_Woocommerce_i18n. Defines internationalization functionality.
	 * - Clever_Point_For_Woocommerce_Admin. Defines all hooks for the admin area.
	 * - Clever_Point_For_Woocommerce_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
        /**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-clever-point-for-woocommerce-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-clever-point-for-woocommerce-i18n.php';

        /**
         * The class responsible for shipping class
         * of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-clever-point-for-woocommerce-shipping-class.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-clever-point-for-woocommerce-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-clever-point-for-woocommerce-public.php';

		$this->loader = new Clever_Point_For_Woocommerce_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Clever_Point_For_Woocommerce_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Clever_Point_For_Woocommerce_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Clever_Point_For_Woocommerce_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'clever_point_orders_metabox',10,2 );
        $this->loader->add_filter( 'woocommerce_get_sections_shipping', $plugin_admin,'clever_point_add_section' );
        $this->loader->add_filter( 'woocommerce_get_settings_shipping', $plugin_admin,'clever_point_all_settings', 10, 2 );
        $this->loader->add_action( 'wp_ajax_clever_point_create_voucher', $plugin_admin,'clever_point_create_voucher' );
        $this->loader->add_action( 'wp_ajax_clever_point_print_voucher', $plugin_admin,'clever_point_print_voucher' );
        $this->loader->add_action( 'wp_ajax_clever_point_cancel_voucher', $plugin_admin,'clever_point_cancel_voucher' );
        $this->loader->add_action('plugin_action_links', $plugin_admin, 'clever_point_plugin_action_links', 10, 2);

    }

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Clever_Point_For_Woocommerce_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        $this->loader->add_action( 'woocommerce_review_order_before_payment', $plugin_public,'clever_point_shipping_maps_displayed', 20 );
        $this->loader->add_action( 'woocommerce_after_order_notes', $plugin_public, 'clever_point_station_hidden_field', 10, 1 );
        $this->loader->add_action( 'woocommerce_checkout_update_order_meta', $plugin_public, 'save_custom_checkout_hidden_field', 10, 1 );
        $this->loader->add_action( 'woocommerce_after_checkout_validation', $plugin_public, 'validate_clever_point', 10, 2);
        $this->loader->add_filter( 'woocommerce_cart_shipping_method_full_label',$plugin_public,'clever_point_change_cart_shipping_method_full_label', 10, 2 );
        $this->loader->add_filter( 'woocommerce_package_rates', $plugin_public, 'hide_shipping_method_based_on_options', 10, 2);
    }

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Clever_Point_For_Woocommerce_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
