<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://wysylajtaniej.pl
 * @since      1.0.0
 *
 * @package    wysylajtaniej
 * @subpackage wysylajtaniej/includes
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
 * @package    wysylajtaniej
 * @subpackage wysylajtaniej/includes
 * @author     wysylajtaniej.pl <woocommerce@wysylajtaniej.pl>
 */
class wysylajtaniej {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      wysylajtaniej_Loader    $loader    Maintains and registers all hooks for the plugin.
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
		if ( defined( 'wysylajtaniej_VERSION' ) ) {
			$this->version = wysylajtaniej_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		if ( defined( 'wysylajtaniej_PLUGIN_NAME' ) ) {
			$this->plugin_name = wysylajtaniej_PLUGIN_NAME;
		} else {
			$this->plugin_name = 'wysylajtaniej';
		}

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
	 * - wysylajtaniej_Loader. Orchestrates the hooks of the plugin.
	 * - wysylajtaniej_i18n. Defines internationalization functionality.
	 * - wysylajtaniej_Admin. Defines all hooks for the admin area.
	 * - wysylajtaniej_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wysylajtaniej-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-wysylajtaniej-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-wysylajtaniej-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-wysylajtaniej-public.php';

		$this->loader = new wysylajtaniej_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the wysylajtaniej_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new wysylajtaniej_i18n();

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

		$plugin_admin = new wysylajtaniej_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'wysylajtaniej_menu' );
        $this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'wysylajtaniej_meta_boxes' );
        $this->loader->add_action( 'save_post', $plugin_admin, 'delivery_save_postdata' );
        $this->loader->add_action( 'wp_ajax_wysylajtaniej_getPoints', $plugin_admin, 'get_points' );
        $this->loader->add_filter( 'plugin_action_links_wysylajtaniej/wysylajtaniej.php', $plugin_admin, 'plugin_action_links');
        $this->loader->add_filter( 'manage_edit-shop_order_columns', $plugin_admin, 'extra_order_column');
        $this->loader->add_filter( 'manage_shop_order_posts_custom_column', $plugin_admin, 'extra_order_column_content');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new wysylajtaniej_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        $this->loader->add_action( 'woocommerce_review_order_before_submit', $plugin_public, 'totals_after_shipping' );
        $this->loader->add_action( 'woocommerce_after_shipping_rate', $plugin_public, 'after_shipping_rate',10,2 );
        $this->loader->add_action( 'wp_ajax_savePoint', $plugin_public, 'save_point_to_session');
        $this->loader->add_action( 'wp_ajax_nopriv_getPointToPayment', $plugin_public, 'get_point_to_payment');
        $this->loader->add_action( 'wp_ajax_getPointToPayment', $plugin_public, 'get_point_to_payment');
        $this->loader->add_action( 'woocommerce_checkout_create_order', $plugin_public, 'save_point_to_order',20,2);


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
	 * @return    wysylajtaniej_Loader    Orchestrates the hooks of the plugin.
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
