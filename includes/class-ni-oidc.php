<?php
/**
 * The core plugin class.
 *
 * @since      1.0.0
 * @package    NI_OIDC
 */

class NI_OIDC {

    /**
     * The loader that's responsible for maintaining and registering all hooks.
     *
     * @since    1.0.0
     * @access   protected
     * @var      NI_OIDC_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * Define the core functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies() {
        // The class responsible for orchestrating the actions and filters of the core plugin.
        require_once NI_OIDC_PLUGIN_DIR . 'includes/class-ni-oidc-loader.php';

        // The class responsible for defining all actions that occur in the admin area.
        require_once NI_OIDC_PLUGIN_DIR . 'includes/class-ni-oidc-admin.php';

        // The class responsible for handling the OIDC authentication.
        require_once NI_OIDC_PLUGIN_DIR . 'includes/class-ni-oidc-auth.php';

        // The class responsible for handling user operations.
        require_once NI_OIDC_PLUGIN_DIR . 'includes/class-ni-oidc-user.php';

        // Initialize the loader
        $this->loader = new NI_OIDC_Loader();
    }

    /**
     * Register all of the hooks related to the admin area functionality.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        $plugin_admin = new NI_OIDC_Admin();
        
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'add_options_page');
        $this->loader->add_action('admin_init', $plugin_admin, 'register_settings');
    }

    /**
     * Register all of the hooks related to the public-facing functionality.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        $plugin_auth = new NI_OIDC_Auth();
        $plugin_user = new NI_OIDC_User();
        
        // Authentication hooks
        $this->loader->add_action('init', $plugin_auth, 'register_auth_endpoints');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_auth, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_auth, 'enqueue_scripts');
        
        // Login and user creation hooks
        $this->loader->add_action('wp_login_url', $plugin_auth, 'get_login_url', 10, 2);
        $this->loader->add_action('login_form', $plugin_auth, 'add_login_form_button');
        $this->loader->add_action('register_form', $plugin_auth, 'add_register_form_button');
        
        // Shortcodes
        $this->loader->add_shortcode('ni_oidc_login_button', $plugin_auth, 'login_button_shortcode');
        $this->loader->add_shortcode('ni_oidc_logout_button', $plugin_auth, 'logout_button_shortcode');
        $this->loader->add_shortcode('ni_oidc_register_button', $plugin_auth, 'register_button_shortcode');
        $this->loader->add_shortcode('ni_oidc_edit_profile_button', $plugin_auth, 'edit_profile_button_shortcode');
        
        // User data and profile handling
        $this->loader->add_action('ni_oidc_user_login', $plugin_user, 'on_user_login', 10, 2);
        $this->loader->add_filter('get_avatar', $plugin_user, 'get_user_avatar', 10, 5);
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run() {
        $this->loader->run();
    }
} 