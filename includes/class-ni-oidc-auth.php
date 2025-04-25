<?php
/**
 * The authentication functionality of the plugin.
 *
 * @since      1.0.0
 * @package    NI_OIDC
 */

class NI_OIDC_Auth {

    /**
     * The OIDC endpoints.
     *
     * @since    1.0.0
     * @access   private
     * @var      array    $endpoints    The OIDC endpoints.
     */
    private $endpoints = null;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style('ni-oidc-public', NI_OIDC_PLUGIN_URL . 'assets/css/public.css', array(), NI_OIDC_VERSION, 'all');
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script('ni-oidc-public', NI_OIDC_PLUGIN_URL . 'assets/js/public.js', array('jquery'), NI_OIDC_VERSION, false);
    }

    /**
     * Register the authentication endpoints.
     *
     * @since    1.0.0
     */
    public function register_auth_endpoints() {
        // Handle the authorization callback
        if (isset($_GET['ni_oidc']) && $_GET['ni_oidc'] === 'callback') {
            $this->handle_callback();
            exit;
        }

        // Handle login requests
        if (isset($_GET['ni_oidc']) && $_GET['ni_oidc'] === 'login') {
            $this->redirect_to_login();
            exit;
        }

        // Handle register requests
        if (isset($_GET['ni_oidc']) && $_GET['ni_oidc'] === 'register') {
            $this->redirect_to_register();
            exit;
        }

        // Handle edit profile requests
        if (isset($_GET['ni_oidc']) && $_GET['ni_oidc'] === 'edit_profile') {
            $this->redirect_to_edit_profile();
            exit;
        }

        // Handle logout requests
        if (isset($_GET['ni_oidc']) && $_GET['ni_oidc'] === 'logout') {
            $this->handle_logout();
            exit;
        }
    }

    /**
     * Get the login URL for the Next Identity provider.
     *
     * @since    1.0.0
     * @param    string    $login_url    The login URL.
     * @param    string    $redirect     The redirect URL.
     * @return   string                  The modified login URL.
     */
    public function get_login_url($login_url, $redirect) {
        // Only change the login URL if the plugin is properly configured
        if ($this->is_plugin_configured()) {
            return add_query_arg('ni_oidc', 'login', site_url());
        }
        return $login_url;
    }

    /**
     * Add the Next Identity login button to the WordPress login form.
     *
     * @since    1.0.0
     */
    public function add_login_form_button() {
        if ($this->is_plugin_configured()) {
            $login_url = add_query_arg('ni_oidc', 'login', site_url());
            $button_text = get_option('ni_oidc_login_button_text', __('Log in with Next Identity', 'ni-oidc'));
            
            include NI_OIDC_PLUGIN_DIR . 'templates/login-button.php';
        }
    }

    /**
     * Add the Next Identity register button to the WordPress registration form.
     *
     * @since    1.0.0
     */
    public function add_register_form_button() {
        if ($this->is_plugin_configured()) {
            $register_url = add_query_arg('ni_oidc', 'register', site_url());
            $button_text = get_option('ni_oidc_register_button_text', __('Register with Next Identity', 'ni-oidc'));
            
            include NI_OIDC_PLUGIN_DIR . 'templates/register-button.php';
        }
    }

    /**
     * Shortcode for displaying a login button.
     *
     * @since    1.0.0
     * @param    array    $atts    The shortcode attributes.
     * @return   string            The login button HTML.
     */
    public function login_button_shortcode($atts) {
        if (!$this->is_plugin_configured()) {
            return '';
        }

        $atts = shortcode_atts(array(
            'text' => get_option('ni_oidc_login_button_text', __('Log in with Next Identity', 'ni-oidc')),
            'class' => 'ni-oidc-login-button',
        ), $atts, 'ni_oidc_login_button');

        $login_url = add_query_arg('ni_oidc', 'login', site_url());
        
        ob_start();
        include NI_OIDC_PLUGIN_DIR . 'templates/login-button.php';
        return ob_get_clean();
    }

    /**
     * Shortcode for displaying a logout button.
     *
     * @since    1.0.0
     * @param    array    $atts    The shortcode attributes.
     * @return   string            The logout button HTML.
     */
    public function logout_button_shortcode($atts) {
        if (!is_user_logged_in()) {
            return '';
        }

        $atts = shortcode_atts(array(
            'text' => __('Log out', 'ni-oidc'),
            'class' => 'ni-oidc-logout-button',
        ), $atts, 'ni_oidc_logout_button');

        $logout_url = add_query_arg('ni_oidc', 'logout', site_url());
        
        ob_start();
        include NI_OIDC_PLUGIN_DIR . 'templates/logout-button.php';
        return ob_get_clean();
    }

    /**
     * Shortcode for displaying a register button.
     *
     * @since    1.0.0
     * @param    array    $atts    The shortcode attributes.
     * @return   string            The register button HTML.
     */
    public function register_button_shortcode($atts) {
        if (!$this->is_plugin_configured() || is_user_logged_in()) {
            return '';
        }

        $atts = shortcode_atts(array(
            'text' => get_option('ni_oidc_register_button_text', __('Register with Next Identity', 'ni-oidc')),
            'class' => 'ni-oidc-register-button',
        ), $atts, 'ni_oidc_register_button');

        $register_url = add_query_arg('ni_oidc', 'register', site_url());
        
        ob_start();
        include NI_OIDC_PLUGIN_DIR . 'templates/register-button.php';
        return ob_get_clean();
    }

    /**
     * Shortcode for displaying an edit profile button.
     *
     * @since    1.0.0
     * @param    array    $atts    The shortcode attributes.
     * @return   string            The edit profile button HTML.
     */
    public function edit_profile_button_shortcode($atts) {
        if (!$this->is_plugin_configured() || !is_user_logged_in()) {
            return '';
        }

        $atts = shortcode_atts(array(
            'text' => get_option('ni_oidc_edit_profile_button_text', __('Edit Profile', 'ni-oidc')),
            'class' => 'ni-oidc-edit-profile-button',
        ), $atts, 'ni_oidc_edit_profile_button');

        $edit_profile_url = add_query_arg('ni_oidc', 'edit_profile', site_url());
        
        ob_start();
        include NI_OIDC_PLUGIN_DIR . 'templates/edit-profile-button.php';
        return ob_get_clean();
    }

    /**
     * Redirect the user to the Next Identity login page.
     *
     * @since    1.0.0
     */
    public function redirect_to_login() {
        // If the user is already logged in, redirect to the home page
        if (is_user_logged_in()) {
            wp_redirect(home_url());
            exit;
        }

        // Generate a nonce for the state parameter
        $state = $this->generate_state();
        
        // Store the state in a session for verification
        WP_Session_Tokens::get_instance()->create($state);
        
        // Store the return URL in the session if provided
        if (isset($_GET['redirect_to'])) {
            update_option('ni_oidc_redirect_to_' . $state, esc_url_raw($_GET['redirect_to']), false);
        }
        
        // Redirect to the authorization endpoint
        $auth_url = $this->get_authorization_url($state);
        wp_redirect($auth_url);
        exit;
    }

    /**
     * Redirect the user to the Next Identity registration page.
     *
     * @since    1.0.0
     */
    public function redirect_to_register() {
        // If the user is already logged in, redirect to the home page
        if (is_user_logged_in()) {
            wp_redirect(home_url());
            exit;
        }

        // Generate a nonce for the state parameter
        $state = $this->generate_state();
        
        // Store the state in a session for verification
        WP_Session_Tokens::get_instance()->create($state);
        
        // Store the return URL in the session if provided
        if (isset($_GET['redirect_to'])) {
            update_option('ni_oidc_redirect_to_' . $state, esc_url_raw($_GET['redirect_to']), false);
        }
        
        // Redirect to the authorization endpoint with the register hint
        $auth_url = $this->get_authorization_url($state, 'register');
        wp_redirect($auth_url);
        exit;
    }

    /**
     * Redirect the user to the Next Identity edit profile page.
     *
     * @since    1.0.0
     */
    public function redirect_to_edit_profile() {
        // If the user is not logged in, redirect to the login page
        if (!is_user_logged_in()) {
            wp_redirect(wp_login_url(add_query_arg('ni_oidc', 'edit_profile', site_url())));
            exit;
        }

        // Generate a nonce for the state parameter
        $state = $this->generate_state();
        
        // Store the state in a session for verification
        WP_Session_Tokens::get_instance()->create($state);
        
        // Store the return URL in the session if provided
        if (isset($_GET['redirect_to'])) {
            update_option('ni_oidc_redirect_to_' . $state, esc_url_raw($_GET['redirect_to']), false);
        }
        
        // Redirect to the authorization endpoint with the personal-details hint
        $auth_url = $this->get_authorization_url($state, 'personal-details');
        wp_redirect($auth_url);
        exit;
    }

    /**
     * Handle the logout request.
     *
     * @since    1.0.0
     */
    public function handle_logout() {
        // Get the ID token if available
        $id_token = get_user_meta(get_current_user_id(), 'ni_oidc_id_token', true);
        
        // Logout from WordPress
        wp_logout();
        
        // Get the redirect URL
        $redirect_url = get_option('ni_oidc_logout_redirect', home_url());
        
        // If we have an ID token and the provider URL is configured, try to logout from the provider
        if ($id_token && $this->is_plugin_configured()) {
            $endpoints = $this->get_endpoints();
            
            if (isset($endpoints['end_session_endpoint'])) {
                $logout_url = add_query_arg(array(
                    'id_token_hint' => $id_token,
                    'post_logout_redirect_uri' => $redirect_url,
                ), $endpoints['end_session_endpoint']);
                
                wp_redirect($logout_url);
                exit;
            }
        }
        
        // If we can't logout from the provider, just redirect
        wp_redirect($redirect_url);
        exit;
    }

    /**
     * Handle the authorization callback.
     *
     * @since    1.0.0
     */
    public function handle_callback() {
        // Check if there's an error
        if (isset($_GET['error'])) {
            $error = sanitize_text_field($_GET['error']);
            $error_description = isset($_GET['error_description']) ? sanitize_text_field($_GET['error_description']) : '';
            
            // Log the error
            error_log('Next Identity OIDC Error: ' . $error . ' - ' . $error_description);
            
            // Redirect to the login page with an error message
            wp_redirect(wp_login_url() . '?login_error=' . urlencode($error));
            exit;
        }
        
        // Check for the authorization code
        if (!isset($_GET['code'])) {
            // Redirect to the login page with an error message
            wp_redirect(wp_login_url() . '?login_error=no_code');
            exit;
        }
        
        // Check for the state parameter
        if (!isset($_GET['state'])) {
            // Redirect to the login page with an error message
            wp_redirect(wp_login_url() . '?login_error=no_state');
            exit;
        }
        
        // Verify the state parameter
        $state = sanitize_text_field($_GET['state']);
        $is_valid_state = false;
        
        // Check if the state is valid
        $token_manager = WP_Session_Tokens::get_instance();
        $sessions = $token_manager->get_all();
        
        foreach ($sessions as $token => $session) {
            if ($token === $state) {
                $is_valid_state = true;
                $token_manager->destroy($token);
                break;
            }
        }
        
        if (!$is_valid_state) {
            // Redirect to the login page with an error message
            wp_redirect(wp_login_url() . '?login_error=invalid_state');
            exit;
        }
        
        // Get the code and exchange it for tokens
        $code = sanitize_text_field($_GET['code']);
        $tokens = $this->exchange_code_for_tokens($code);
        
        if (!$tokens || isset($tokens['error'])) {
            // Log the error
            if (isset($tokens['error'])) {
                error_log('Next Identity OIDC Error: ' . $tokens['error'] . ' - ' . (isset($tokens['error_description']) ? $tokens['error_description'] : ''));
            } else {
                error_log('Next Identity OIDC Error: Failed to exchange code for tokens');
            }
            
            // Redirect to the login page with an error message
            wp_redirect(wp_login_url() . '?login_error=token_exchange_failed');
            exit;
        }
        
        // Get the user info
        $userinfo = $this->get_userinfo($tokens['access_token']);
        
        if (!$userinfo || isset($userinfo['error'])) {
            // Log the error
            if (isset($userinfo['error'])) {
                error_log('Next Identity OIDC Error: ' . $userinfo['error'] . ' - ' . (isset($userinfo['error_description']) ? $userinfo['error_description'] : ''));
            } else {
                error_log('Next Identity OIDC Error: Failed to get user info');
            }
            
            // Redirect to the login page with an error message
            wp_redirect(wp_login_url() . '?login_error=userinfo_failed');
            exit;
        }
        
        // Check if we have a subject (sub) identifier
        if (!isset($userinfo['sub'])) {
            // Redirect to the login page with an error message
            wp_redirect(wp_login_url() . '?login_error=no_subject');
            exit;
        }
        
        // Find or create the WordPress user
        $user = $this->find_or_create_user($userinfo, $tokens);
        
        if (is_wp_error($user)) {
            // Redirect to the login page with an error message
            wp_redirect(wp_login_url() . '?login_error=' . $user->get_error_code());
            exit;
        }
        
        // Log the user in
        wp_set_auth_cookie($user->ID);
        
        // Fire the ni_oidc_user_login action
        do_action('ni_oidc_user_login', $user, $userinfo);
        
        // Get the redirect URL
        $redirect_to = get_option('ni_oidc_redirect_to_' . $state, '');
        delete_option('ni_oidc_redirect_to_' . $state);
        
        if (empty($redirect_to)) {
            $redirect_to = get_option('ni_oidc_login_redirect', admin_url());
        }
        
        // Redirect the user
        wp_redirect($redirect_to);
        exit;
    }

    /**
     * Find or create a WordPress user for the authenticated user.
     *
     * @since    1.0.0
     * @param    array    $userinfo    The user info.
     * @param    array    $tokens      The tokens.
     * @return   WP_User|WP_Error      The user object or an error.
     */
    public function find_or_create_user($userinfo, $tokens) {
        global $wpdb;
        
        // Check if we have a user with this subject ID
        $user_id = $wpdb->get_var($wpdb->prepare(
            "SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'ni_oidc_sub' AND meta_value = %s",
            $userinfo['sub']
        ));
        
        if ($user_id) {
            // Get the user
            $user = get_user_by('ID', $user_id);
            
            if ($user) {
                // Update the user's tokens
                $this->update_user_tokens($user_id, $tokens);
                
                // Update user info if needed
                $this->update_user_info($user_id, $userinfo);
                
                return $user;
            }
        }
        
        // Check if auto-registration is enabled
        $auto_register = get_option('ni_oidc_auto_register_users', true);
        
        if (!$auto_register) {
            return new WP_Error('registration_disabled', __('User registration is disabled.', 'ni-oidc'));
        }
        
        // Check if we have an email address
        if (!isset($userinfo['email'])) {
            return new WP_Error('no_email', __('No email address provided.', 'ni-oidc'));
        }
        
        // Check if a user with this email already exists
        $existing_user = get_user_by('email', $userinfo['email']);
        
        if ($existing_user) {
            // Link the existing user to the Next Identity user
            update_user_meta($existing_user->ID, 'ni_oidc_sub', $userinfo['sub']);
            
            // Update the user's tokens
            $this->update_user_tokens($existing_user->ID, $tokens);
            
            // Update user info if needed
            $this->update_user_info($existing_user->ID, $userinfo);
            
            return $existing_user;
        }
        
        // Create a new user
        $username = $this->generate_username($userinfo);
        $password = wp_generate_password(24, true, true);
        $default_role = get_option('ni_oidc_default_role', 'subscriber');
        
        $user_id = wp_create_user($username, $password, $userinfo['email']);
        
        if (is_wp_error($user_id)) {
            return $user_id;
        }
        
        // Set the user's role
        $user = new WP_User($user_id);
        $user->set_role($default_role);
        
        // Set the user's display name
        if (isset($userinfo['name'])) {
            wp_update_user(array(
                'ID' => $user_id,
                'display_name' => $userinfo['name'],
            ));
        }
        
        // Set the user's first and last name
        if (isset($userinfo['given_name'])) {
            update_user_meta($user_id, 'first_name', $userinfo['given_name']);
        }
        
        if (isset($userinfo['family_name'])) {
            update_user_meta($user_id, 'last_name', $userinfo['family_name']);
        }
        
        // Link the user to the Next Identity user
        update_user_meta($user_id, 'ni_oidc_sub', $userinfo['sub']);
        
        // Update the user's tokens
        $this->update_user_tokens($user_id, $tokens);
        
        // Store additional user info
        $this->update_user_info($user_id, $userinfo);
        
        return $user;
    }

    /**
     * Generate a unique username from the user info.
     *
     * @since    1.0.0
     * @param    array     $userinfo    The user info.
     * @return   string                 The generated username.
     */
    private function generate_username($userinfo) {
        // Try to use the preferred_username if available
        if (isset($userinfo['preferred_username'])) {
            $username = $userinfo['preferred_username'];
            
            if (!username_exists($username)) {
                return $username;
            }
        }
        
        // Try to use the email address
        if (isset($userinfo['email'])) {
            $username = strtok($userinfo['email'], '@');
            
            if (!username_exists($username)) {
                return $username;
            }
            
            // Try appending a number
            for ($i = 1; $i < 10; $i++) {
                $new_username = $username . $i;
                
                if (!username_exists($new_username)) {
                    return $new_username;
                }
            }
        }
        
        // Generate a random username
        return 'user_' . wp_generate_password(12, false);
    }

    /**
     * Update the user's tokens.
     *
     * @since    1.0.0
     * @param    int       $user_id    The user ID.
     * @param    array     $tokens     The tokens.
     */
    private function update_user_tokens($user_id, $tokens) {
        // Store the access token
        update_user_meta($user_id, 'ni_oidc_access_token', $tokens['access_token']);
        
        // Store the refresh token if available
        if (isset($tokens['refresh_token'])) {
            update_user_meta($user_id, 'ni_oidc_refresh_token', $tokens['refresh_token']);
        }
        
        // Store the ID token if available
        if (isset($tokens['id_token'])) {
            update_user_meta($user_id, 'ni_oidc_id_token', $tokens['id_token']);
        }
        
        // Store the token expiration time
        update_user_meta($user_id, 'ni_oidc_token_expiration', time() + $tokens['expires_in']);
    }

    /**
     * Update the user's information.
     *
     * @since    1.0.0
     * @param    int       $user_id     The user ID.
     * @param    array     $userinfo    The user info.
     */
    private function update_user_info($user_id, $userinfo) {
        // Store the raw user info
        update_user_meta($user_id, 'ni_oidc_user_info', $userinfo);
        
        // Update the user's avatar if available
        if (isset($userinfo['picture'])) {
            update_user_meta($user_id, 'ni_oidc_avatar', $userinfo['picture']);
        }
    }

    /**
     * Exchange the authorization code for tokens.
     *
     * @since    1.0.0
     * @param    string    $code    The authorization code.
     * @return   array|false        The tokens or false on failure.
     */
    private function exchange_code_for_tokens($code) {
        // Get the token endpoint
        $endpoints = $this->get_endpoints();
        
        if (!isset($endpoints['token_endpoint'])) {
            return false;
        }
        
        // Get the client credentials
        $client_id = get_option('ni_oidc_client_id', '');
        $client_secret = get_option('ni_oidc_client_secret', '');
        
        // Build the request
        $args = array(
            'headers' => array(
                'Content-Type' => 'application/x-www-form-urlencoded',
                'Authorization' => 'Basic ' . base64_encode($client_id . ':' . $client_secret),
            ),
            'body' => array(
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => site_url('?ni_oidc=callback'),
            ),
        );
        
        // Make the request
        $response = wp_remote_post($endpoints['token_endpoint'], $args);
        
        // Check for errors
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }
        
        // Parse the response
        $body = wp_remote_retrieve_body($response);
        $tokens = json_decode($body, true);
        
        if (!$tokens || !isset($tokens['access_token'])) {
            return false;
        }
        
        return $tokens;
    }

    /**
     * Get the user info using the access token.
     *
     * @since    1.0.0
     * @param    string    $access_token    The access token.
     * @return   array|false               The user info or false on failure.
     */
    private function get_userinfo($access_token) {
        // Get the userinfo endpoint
        $endpoints = $this->get_endpoints();
        
        if (!isset($endpoints['userinfo_endpoint'])) {
            return false;
        }
        
        // Build the request
        $args = array(
            'headers' => array(
                'Authorization' => 'Bearer ' . $access_token,
            ),
        );
        
        // Make the request
        $response = wp_remote_get($endpoints['userinfo_endpoint'], $args);
        
        // Check for errors
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }
        
        // Parse the response
        $body = wp_remote_retrieve_body($response);
        $userinfo = json_decode($body, true);
        
        if (!$userinfo) {
            return false;
        }
        
        return $userinfo;
    }

    /**
     * Get the authorization URL.
     *
     * @since    1.0.0
     * @param    string    $state    The state parameter.
     * @param    string    $hint     Optional hint for the authorization endpoint.
     * @return   string              The authorization URL.
     */
    private function get_authorization_url($state, $hint = null) {
        // Get the authorization endpoint
        $endpoints = $this->get_endpoints();
        
        if (!isset($endpoints['authorization_endpoint'])) {
            return '';
        }
        
        // Get the client ID and scopes
        $client_id = get_option('ni_oidc_client_id', '');
        $scopes = get_option('ni_oidc_scopes', 'openid profile email');
        
        // Build the authorization URL
        $args = array(
            'response_type' => 'code',
            'client_id' => $client_id,
            'redirect_uri' => site_url('?ni_oidc=callback'),
            'scope' => $scopes,
            'state' => $state,
        );
        
        // Add a hint if provided
        if ($hint === 'register') {
            $args['prompt'] = 'create';
        } elseif ($hint === 'personal-details') {
            $args['prompt'] = 'edit';
        }
        
        // Build the URL
        $url = add_query_arg($args, $endpoints['authorization_endpoint']);
        
        return $url;
    }

    /**
     * Get the OIDC endpoints from the discovery endpoint.
     *
     * @since    1.0.0
     * @return   array    The OIDC endpoints.
     */
    private function get_endpoints() {
        if ($this->endpoints !== null) {
            return $this->endpoints;
        }
        
        // Get the provider URL
        $provider_url = get_option('ni_oidc_provider_url', '');
        
        if (empty($provider_url)) {
            return array();
        }
        
        // Add the .well-known/openid-configuration path if not already included
        if (substr($provider_url, -1) !== '/') {
            $provider_url .= '/';
        }
        
        $discovery_url = $provider_url . '.well-known/openid-configuration';
        
        // Check if we have cached endpoints
        $cached_endpoints = get_transient('ni_oidc_endpoints');
        
        if ($cached_endpoints) {
            $this->endpoints = $cached_endpoints;
            return $this->endpoints;
        }
        
        // Make the request
        $response = wp_remote_get($discovery_url);
        
        // Check for errors
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return array();
        }
        
        // Parse the response
        $body = wp_remote_retrieve_body($response);
        $config = json_decode($body, true);
        
        if (!$config) {
            return array();
        }
        
        // Cache the endpoints for 1 hour
        set_transient('ni_oidc_endpoints', $config, HOUR_IN_SECONDS);
        
        $this->endpoints = $config;
        return $this->endpoints;
    }

    /**
     * Generate a state parameter for the authorization request.
     *
     * @since    1.0.0
     * @return   string    The state parameter.
     */
    private function generate_state() {
        return wp_generate_password(24, false);
    }

    /**
     * Check if the plugin is configured with the required settings.
     *
     * @since    1.0.0
     * @return   boolean    True if the plugin is configured, false otherwise.
     */
    private function is_plugin_configured() {
        $provider_url = get_option('ni_oidc_provider_url', '');
        $client_id = get_option('ni_oidc_client_id', '');
        $client_secret = get_option('ni_oidc_client_secret', '');
        
        return !empty($provider_url) && !empty($client_id) && !empty($client_secret);
    }
} 