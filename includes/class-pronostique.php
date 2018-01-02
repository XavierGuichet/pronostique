<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://www.xavier-guichet.fr
 * @since      1.0.0
 *
 * @package    Pronostique
 * @subpackage Pronostique/includes
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
 * @package    Pronostique
 * @subpackage Pronostique/includes
 * @author     Xavier Guichet <contact@xavier-guichet.fr>
 */
class Pronostique {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Pronostique_Loader    $loader    Maintains and registers all hooks for the plugin.
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

		$this->plugin_name = 'pronostique';
		$this->version = '1.0.0';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();
        $this->define_shared_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Pronostique_Loader. Orchestrates the hooks of the plugin.
	 * - Pronostique_i18n. Defines internationalization functionality.
	 * - Pronostique_Admin. Defines all hooks for the admin area.
	 * - Pronostique_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-pronostique-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-pronostique-i18n.php';

        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'shared/class-pronostique-shared.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-pronostique-admin.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-pronostique-public.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/widgets/top-tipsters.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/widgets/top-vip.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/widgets/tipster-stats.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/widgets/tipster-last-tips.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/widgets/prono-taxonomy-nav.php';

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'tools/users-group.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'tools/tips-formatter.php';
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'tools/calculator.php';
        if(!class_exists(TemplateEngine)) {
    		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'tools/template-engine.php';
        }

		$this->loader = new Pronostique_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Pronostique_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Pronostique_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_shared_hooks() {
		$plugin_shared = new Pronostique_Shared( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'parse_request', $plugin_shared, 'handle_rewrite_conflit' );
        // $this->loader->add_action( 'parse_query', $plugin_shared, 'handle_rewrite_conflit_query' );
        $this->loader->add_action( 'save_post', $plugin_shared, 'update_comments_meta' );
        $this->loader->add_action( 'pods_api_post_save_pod_item_pronostique', $plugin_shared, 'sync_post_with_prono', 10, 3);
        $this->loader->add_action( 'pods_api_post_save_pod_item_prono-post', $plugin_shared, 'sync_prono_with_post', 10, 3);
        $this->loader->add_filter( 'init', $plugin_shared, 'prono_rewrite_rule', 10, 2 );
        $this->loader->add_filter( 'comments_open', $plugin_shared, 'prono_comment_open', 10, 2 );
        $this->loader->add_filter( 'term_link', $plugin_shared, 'prono_term_permalink', 10, 2 ); //TODO New code for permalink
        $this->loader->add_filter( 'post_type_link', $plugin_shared, 'tips_permalinks', 10, 2 ); //TODO New code for permalink
        $this->loader->add_filter( 'pods_api_pre_save_pod_item_pronostique', $plugin_shared, 'fix_cote_comma_float', 10, 3);
        $this->loader->add_filter( 'pods_api_pre_save_pod_item_pronostique', $plugin_shared, 'validate_form', 10, 3);
        $this->loader->add_filter( 'pre_get_posts', $plugin_shared, 'add_custom_types' );
	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Pronostique_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_admin_menu');
        $this->loader->add_action( 'wp_ajax_quick_edit_pronostique', $plugin_admin, 'ajax_quick_edit_pronostique');
        $this->loader->add_action( 'pods_admin_ui_custom_pronostique', $plugin_admin, 'display_pronostique_ui');
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Pronostique_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );
		$this->loader->add_action( 'widgets_init', $plugin_public, 'register_widgets' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
        $this->loader->add_filter( 'pre_get_posts', $plugin_public, 'hide_vip_post' );

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
	 * @return    Pronostique_Loader    Orchestrates the hooks of the plugin.
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
