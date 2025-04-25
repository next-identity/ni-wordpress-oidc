<?php
/**
 * The user-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    NI_OIDC
 */

class NI_OIDC_User {

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
    }

    /**
     * Handles various actions when a user logs in with Next Identity.
     *
     * @since    1.0.0
     * @param    WP_User    $user        The WordPress user object.
     * @param    array      $userinfo    The user info from Next Identity.
     */
    public function on_user_login($user, $userinfo) {
        // You can perform additional actions here when a user logs in with Next Identity
        do_action('ni_oidc_user_updated', $user, $userinfo);
    }

    /**
     * Filters the avatar to use the Next Identity profile picture if available.
     *
     * @since    1.0.0
     * @param    string    $avatar        The avatar HTML.
     * @param    mixed     $id_or_email   The user ID, email address, or WP_User object.
     * @param    int       $size          The size of the avatar.
     * @param    string    $default       The default avatar type.
     * @param    string    $alt           The alt text for the avatar.
     * @return   string                   The modified avatar HTML.
     */
    public function get_user_avatar($avatar, $id_or_email, $size, $default, $alt) {
        $user_id = 0;

        if (is_numeric($id_or_email)) {
            $user_id = (int) $id_or_email;
        } elseif (is_string($id_or_email)) {
            $user = get_user_by('email', $id_or_email);
            if ($user) {
                $user_id = $user->ID;
            }
        } elseif (is_object($id_or_email)) {
            if (!empty($id_or_email->user_id)) {
                $user_id = (int) $id_or_email->user_id;
            } elseif (!empty($id_or_email->ID)) {
                $user_id = (int) $id_or_email->ID;
            }
        }

        if ($user_id == 0) {
            return $avatar;
        }

        // Check if the user has a Next Identity avatar
        $ni_avatar = get_user_meta($user_id, 'ni_oidc_avatar', true);

        if (!empty($ni_avatar)) {
            $avatar = "<img alt='" . esc_attr($alt) . "' src='" . esc_url($ni_avatar) . "' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
        }

        return $avatar;
    }

    /**
     * Gets the access token for a user.
     *
     * @since    1.0.0
     * @param    int       $user_id    The user ID.
     * @return   string                The access token or an empty string if not available.
     */
    public function get_access_token($user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }

        if ($user_id === 0) {
            return '';
        }

        // Check if the token has expired
        $expiration = (int) get_user_meta($user_id, 'ni_oidc_token_expiration', true);
        
        if (time() > $expiration) {
            // Try to refresh the token
            $refresh_token = get_user_meta($user_id, 'ni_oidc_refresh_token', true);
            
            if (!empty($refresh_token)) {
                $new_tokens = $this->refresh_tokens($refresh_token);
                
                if ($new_tokens && isset($new_tokens['access_token'])) {
                    update_user_meta($user_id, 'ni_oidc_access_token', $new_tokens['access_token']);
                    update_user_meta($user_id, 'ni_oidc_token_expiration', time() + $new_tokens['expires_in']);
                    
                    if (isset($new_tokens['refresh_token'])) {
                        update_user_meta($user_id, 'ni_oidc_refresh_token', $new_tokens['refresh_token']);
                    }
                    
                    return $new_tokens['access_token'];
                }
            }
            
            // Token has expired and we couldn't refresh it
            return '';
        }
        
        return get_user_meta($user_id, 'ni_oidc_access_token', true);
    }

    /**
     * Gets the ID token for a user.
     *
     * @since    1.0.0
     * @param    int       $user_id    The user ID.
     * @return   string                The ID token or an empty string if not available.
     */
    public function get_id_token($user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }

        if ($user_id === 0) {
            return '';
        }

        return get_user_meta($user_id, 'ni_oidc_id_token', true);
    }

    /**
     * Gets the user info for a user.
     *
     * @since    1.0.0
     * @param    int       $user_id    The user ID.
     * @return   array                The user info or an empty array if not available.
     */
    public function get_user_info($user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }

        if ($user_id === 0) {
            return array();
        }

        $user_info = get_user_meta($user_id, 'ni_oidc_user_info', true);
        
        if (empty($user_info) || !is_array($user_info)) {
            return array();
        }
        
        return $user_info;
    }

    /**
     * Refreshes the tokens using a refresh token.
     *
     * @since    1.0.0
     * @param    string    $refresh_token    The refresh token.
     * @return   array|false                The new tokens or false on failure.
     */
    private function refresh_tokens($refresh_token) {
        // Get the provider URL
        $provider_url = get_option('ni_oidc_provider_url', '');
        
        if (empty($provider_url)) {
            return false;
        }
        
        // Add the .well-known/openid-configuration path if not already included
        if (substr($provider_url, -1) !== '/') {
            $provider_url .= '/';
        }
        
        $discovery_url = $provider_url . '.well-known/openid-configuration';
        
        // Get the token endpoint
        $response = wp_remote_get($discovery_url);
        
        if (is_wp_error($response) || wp_remote_retrieve_response_code($response) !== 200) {
            return false;
        }
        
        $body = wp_remote_retrieve_body($response);
        $config = json_decode($body, true);
        
        if (!$config || !isset($config['token_endpoint'])) {
            return false;
        }
        
        $token_endpoint = $config['token_endpoint'];
        
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
                'grant_type' => 'refresh_token',
                'refresh_token' => $refresh_token,
            ),
        );
        
        // Make the request
        $response = wp_remote_post($token_endpoint, $args);
        
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
     * Checks if a user is authenticated with Next Identity.
     *
     * @since    1.0.0
     * @param    int       $user_id    The user ID.
     * @return   boolean               Whether the user is authenticated with Next Identity.
     */
    public function is_authenticated($user_id = null) {
        if ($user_id === null) {
            $user_id = get_current_user_id();
        }

        if ($user_id === 0) {
            return false;
        }

        $sub = get_user_meta($user_id, 'ni_oidc_sub', true);
        
        return !empty($sub);
    }
} 