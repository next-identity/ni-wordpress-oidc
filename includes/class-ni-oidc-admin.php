<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    NI_OIDC
 */

class NI_OIDC_Admin {

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style('ni-oidc-admin', NI_OIDC_PLUGIN_URL . 'assets/css/admin.css', array(), NI_OIDC_VERSION, 'all');
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script('ni-oidc-admin', NI_OIDC_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), NI_OIDC_VERSION, false);
    }

    /**
     * Add options page to the admin menu.
     *
     * @since    1.0.0
     */
    public function add_options_page() {
        add_options_page(
            __('Next Identity OIDC Settings', 'ni-oidc'),
            __('Next Identity OIDC', 'ni-oidc'),
            'manage_options',
            'ni-oidc-settings',
            array($this, 'display_options_page')
        );
    }

    /**
     * Display the options page content.
     *
     * @since    1.0.0
     */
    public function display_options_page() {
        include_once NI_OIDC_PLUGIN_DIR . 'templates/admin-settings.php';
    }

    /**
     * Register the settings for the plugin.
     *
     * @since    1.0.0
     */
    public function register_settings() {
        // Register a setting for the provider URL
        register_setting('ni_oidc_options', 'ni_oidc_provider_url', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ));

        // Register a setting for the client ID
        register_setting('ni_oidc_options', 'ni_oidc_client_id', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ));

        // Register a setting for the client secret
        register_setting('ni_oidc_options', 'ni_oidc_client_secret', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => '',
        ));

        // Register a setting for the required scopes
        register_setting('ni_oidc_options', 'ni_oidc_scopes', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'openid profile email',
        ));

        // Register a setting for skipping userinfo endpoint calls
        register_setting('ni_oidc_options', 'ni_oidc_skip_userinfo', array(
            'type' => 'boolean',
            'sanitize_callback' => array($this, 'sanitize_checkbox'),
            'default' => false,
        ));

        // Register a setting for button text
        register_setting('ni_oidc_options', 'ni_oidc_login_button_text', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => __('Log in with Next Identity', 'ni-oidc'),
        ));

        // Register a setting for register button text
        register_setting('ni_oidc_options', 'ni_oidc_register_button_text', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => __('Register with Next Identity', 'ni-oidc'),
        ));

        // Register a setting for edit profile button text
        register_setting('ni_oidc_options', 'ni_oidc_edit_profile_button_text', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => __('Edit Profile', 'ni-oidc'),
        ));

        // Register a setting for auto-register users
        register_setting('ni_oidc_options', 'ni_oidc_auto_register_users', array(
            'type' => 'boolean',
            'sanitize_callback' => array($this, 'sanitize_checkbox'),
            'default' => true,
        ));

        // Register a setting for user role
        register_setting('ni_oidc_options', 'ni_oidc_default_role', array(
            'type' => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'default' => 'subscriber',
        ));

        // Register a setting for login redirect URL
        register_setting('ni_oidc_options', 'ni_oidc_login_redirect', array(
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => '',
        ));

        // Register a setting for logout redirect URL
        register_setting('ni_oidc_options', 'ni_oidc_logout_redirect', array(
            'type' => 'string',
            'sanitize_callback' => 'esc_url_raw',
            'default' => '',
        ));

        // Add a settings section for provider settings
        add_settings_section(
            'ni_oidc_provider_section',
            __('Next Identity Provider Settings', 'ni-oidc'),
            array($this, 'provider_section_callback'),
            'ni-oidc-settings'
        );

        // Add a settings section for button customization
        add_settings_section(
            'ni_oidc_button_section',
            __('Button Customization', 'ni-oidc'),
            array($this, 'button_section_callback'),
            'ni-oidc-settings'
        );

        // Add a settings section for user settings
        add_settings_section(
            'ni_oidc_user_section',
            __('User Settings', 'ni-oidc'),
            array($this, 'user_section_callback'),
            'ni-oidc-settings'
        );

        // Add a settings section for redirect settings
        add_settings_section(
            'ni_oidc_redirect_section',
            __('Redirect Settings', 'ni-oidc'),
            array($this, 'redirect_section_callback'),
            'ni-oidc-settings'
        );

        // Add fields for each setting
        add_settings_field(
            'ni_oidc_provider_url',
            __('Provider URL', 'ni-oidc'),
            array($this, 'provider_url_callback'),
            'ni-oidc-settings',
            'ni_oidc_provider_section'
        );

        add_settings_field(
            'ni_oidc_client_id',
            __('Client ID', 'ni-oidc'),
            array($this, 'client_id_callback'),
            'ni-oidc-settings',
            'ni_oidc_provider_section'
        );

        add_settings_field(
            'ni_oidc_client_secret',
            __('Client Secret', 'ni-oidc'),
            array($this, 'client_secret_callback'),
            'ni-oidc-settings',
            'ni_oidc_provider_section'
        );

        add_settings_field(
            'ni_oidc_scopes',
            __('Scopes', 'ni-oidc'),
            array($this, 'scopes_callback'),
            'ni-oidc-settings',
            'ni_oidc_provider_section'
        );

        add_settings_field(
            'ni_oidc_skip_userinfo',
            __('Skip UserInfo Endpoint', 'ni-oidc'),
            array($this, 'skip_userinfo_callback'),
            'ni-oidc-settings',
            'ni_oidc_provider_section'
        );

        add_settings_field(
            'ni_oidc_callback_url',
            __('Callback URL', 'ni-oidc'),
            array($this, 'callback_url_callback'),
            'ni-oidc-settings',
            'ni_oidc_provider_section'
        );

        add_settings_field(
            'ni_oidc_login_button_text',
            __('Login Button Text', 'ni-oidc'),
            array($this, 'login_button_text_callback'),
            'ni-oidc-settings',
            'ni_oidc_button_section'
        );

        add_settings_field(
            'ni_oidc_register_button_text',
            __('Register Button Text', 'ni-oidc'),
            array($this, 'register_button_text_callback'),
            'ni-oidc-settings',
            'ni_oidc_button_section'
        );

        add_settings_field(
            'ni_oidc_edit_profile_button_text',
            __('Edit Profile Button Text', 'ni-oidc'),
            array($this, 'edit_profile_button_text_callback'),
            'ni-oidc-settings',
            'ni_oidc_button_section'
        );

        add_settings_field(
            'ni_oidc_auto_register_users',
            __('Auto-register Users', 'ni-oidc'),
            array($this, 'auto_register_users_callback'),
            'ni-oidc-settings',
            'ni_oidc_user_section'
        );

        add_settings_field(
            'ni_oidc_default_role',
            __('Default User Role', 'ni-oidc'),
            array($this, 'default_role_callback'),
            'ni-oidc-settings',
            'ni_oidc_user_section'
        );

        add_settings_field(
            'ni_oidc_login_redirect',
            __('Login Redirect URL', 'ni-oidc'),
            array($this, 'login_redirect_callback'),
            'ni-oidc-settings',
            'ni_oidc_redirect_section'
        );

        add_settings_field(
            'ni_oidc_logout_redirect',
            __('Logout Redirect URL', 'ni-oidc'),
            array($this, 'logout_redirect_callback'),
            'ni-oidc-settings',
            'ni_oidc_redirect_section'
        );
    }

    /**
     * Sanitize checkbox values.
     *
     * @since    1.0.0
     * @param    mixed    $input    The value to sanitize.
     * @return   boolean            The sanitized value.
     */
    public function sanitize_checkbox($input) {
        return (isset($input) && true == $input) ? true : false;
    }

    /**
     * Render the provider section description.
     *
     * @since    1.0.0
     */
    public function provider_section_callback() {
        echo '<p>' . __('Configure your Next Identity provider settings here. You will need to create an application in your Next Identity dashboard to get these values.', 'ni-oidc') . '</p>';
    }

    /**
     * Render the button section description.
     *
     * @since    1.0.0
     */
    public function button_section_callback() {
        echo '<p>' . __('Customize the text that appears on the login, registration, and profile buttons.', 'ni-oidc') . '</p>';
    }

    /**
     * Render the user section description.
     *
     * @since    1.0.0
     */
    public function user_section_callback() {
        echo '<p>' . __('Configure how users are created and managed when logging in with Next Identity.', 'ni-oidc') . '</p>';
    }

    /**
     * Render the redirect section description.
     *
     * @since    1.0.0
     */
    public function redirect_section_callback() {
        echo '<p>' . __('Configure where users are redirected after logging in or out. Leave blank to use WordPress defaults.', 'ni-oidc') . '</p>';
    }

    /**
     * Render the provider URL field.
     *
     * @since    1.0.0
     */
    public function provider_url_callback() {
        $provider_url = get_option('ni_oidc_provider_url', '');
        echo '<input type="text" id="ni_oidc_provider_url" name="ni_oidc_provider_url" value="' . esc_attr($provider_url) . '" class="regular-text" />';
        echo '<p class="description">' . __('Enter the base URL of your Next Identity provider (e.g., https://auth.nextidentity.com)', 'ni-oidc') . '</p>';
    }

    /**
     * Render the client ID field.
     *
     * @since    1.0.0
     */
    public function client_id_callback() {
        $client_id = get_option('ni_oidc_client_id', '');
        echo '<input type="text" id="ni_oidc_client_id" name="ni_oidc_client_id" value="' . esc_attr($client_id) . '" class="regular-text" />';
        echo '<p class="description">' . __('Enter the client ID provided by Next Identity', 'ni-oidc') . '</p>';
    }

    /**
     * Render the client secret field.
     *
     * @since    1.0.0
     */
    public function client_secret_callback() {
        $client_secret = get_option('ni_oidc_client_secret', '');
        echo '<input type="password" id="ni_oidc_client_secret" name="ni_oidc_client_secret" value="' . esc_attr($client_secret) . '" class="regular-text" />';
        echo '<p class="description">' . __('Enter the client secret provided by Next Identity', 'ni-oidc') . '</p>';
    }

    /**
     * Render the scopes field.
     *
     * @since    1.0.0
     */
    public function scopes_callback() {
        $scopes = get_option('ni_oidc_scopes', 'openid profile email');
        echo '<input type="text" id="ni_oidc_scopes" name="ni_oidc_scopes" value="' . esc_attr($scopes) . '" class="regular-text" />';
        echo '<p class="description">' . __('Space-separated list of scopes to request (e.g., openid profile email)', 'ni-oidc') . '</p>';
    }

    /**
     * Render the skip userinfo field.
     *
     * @since    1.0.0
     */
    public function skip_userinfo_callback() {
        $skip_userinfo = get_option('ni_oidc_skip_userinfo', false);
        echo '<input type="checkbox" id="ni_oidc_skip_userinfo" name="ni_oidc_skip_userinfo" value="1" ' . checked(1, $skip_userinfo, false) . ' />';
        echo '<p class="description">' . __('Use ID token claims instead of calling the UserInfo endpoint. This can improve performance but requires that all necessary user information is included in the ID token.', 'ni-oidc') . '</p>';
    }

    /**
     * Render the callback URL field.
     *
     * @since    1.0.0
     */
    public function callback_url_callback() {
        $callback_url = site_url('?ni_oidc=callback');
        echo '<input type="text" id="ni_oidc_callback_url" value="' . esc_url($callback_url) . '" class="regular-text" readonly />';
        echo '<button type="button" class="button" onclick="navigator.clipboard.writeText(\'' . esc_js($callback_url) . '\');">' . __('Copy to Clipboard', 'ni-oidc') . '</button>';
        echo '<p class="description">' . __('Copy this URL and register it as an authorized redirect URI in your Next Identity application settings', 'ni-oidc') . '</p>';
    }

    /**
     * Render the login button text field.
     *
     * @since    1.0.0
     */
    public function login_button_text_callback() {
        $login_button_text = get_option('ni_oidc_login_button_text', __('Log in with Next Identity', 'ni-oidc'));
        echo '<input type="text" id="ni_oidc_login_button_text" name="ni_oidc_login_button_text" value="' . esc_attr($login_button_text) . '" class="regular-text" />';
    }

    /**
     * Render the register button text field.
     *
     * @since    1.0.0
     */
    public function register_button_text_callback() {
        $register_button_text = get_option('ni_oidc_register_button_text', __('Register with Next Identity', 'ni-oidc'));
        echo '<input type="text" id="ni_oidc_register_button_text" name="ni_oidc_register_button_text" value="' . esc_attr($register_button_text) . '" class="regular-text" />';
    }

    /**
     * Render the edit profile button text field.
     *
     * @since    1.0.0
     */
    public function edit_profile_button_text_callback() {
        $edit_profile_button_text = get_option('ni_oidc_edit_profile_button_text', __('Edit Profile', 'ni-oidc'));
        echo '<input type="text" id="ni_oidc_edit_profile_button_text" name="ni_oidc_edit_profile_button_text" value="' . esc_attr($edit_profile_button_text) . '" class="regular-text" />';
    }

    /**
     * Render the auto-register users field.
     *
     * @since    1.0.0
     */
    public function auto_register_users_callback() {
        $auto_register = get_option('ni_oidc_auto_register_users', true);
        echo '<input type="checkbox" id="ni_oidc_auto_register_users" name="ni_oidc_auto_register_users" value="1" ' . checked(1, $auto_register, false) . ' />';
        echo '<p class="description">' . __('Automatically create a WordPress user account when a new user logs in with Next Identity', 'ni-oidc') . '</p>';
    }

    /**
     * Render the default role field.
     *
     * @since    1.0.0
     */
    public function default_role_callback() {
        $default_role = get_option('ni_oidc_default_role', 'subscriber');
        $roles = wp_roles()->get_names();
        echo '<select id="ni_oidc_default_role" name="ni_oidc_default_role">';
        foreach ($roles as $role_value => $role_name) {
            echo '<option value="' . esc_attr($role_value) . '" ' . selected($default_role, $role_value, false) . '>' . esc_html($role_name) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('The role assigned to new users created after logging in with Next Identity', 'ni-oidc') . '</p>';
    }

    /**
     * Render the login redirect field.
     *
     * @since    1.0.0
     */
    public function login_redirect_callback() {
        $login_redirect = get_option('ni_oidc_login_redirect', '');
        echo '<input type="text" id="ni_oidc_login_redirect" name="ni_oidc_login_redirect" value="' . esc_url($login_redirect) . '" class="regular-text" />';
        echo '<p class="description">' . __('Where to redirect users after successful login. Leave blank to use WordPress default.', 'ni-oidc') . '</p>';
    }

    /**
     * Render the logout redirect field.
     *
     * @since    1.0.0
     */
    public function logout_redirect_callback() {
        $logout_redirect = get_option('ni_oidc_logout_redirect', '');
        echo '<input type="text" id="ni_oidc_logout_redirect" name="ni_oidc_logout_redirect" value="' . esc_url($logout_redirect) . '" class="regular-text" />';
        echo '<p class="description">' . __('Where to redirect users after logging out. Leave blank to use WordPress default.', 'ni-oidc') . '</p>';
    }
} 