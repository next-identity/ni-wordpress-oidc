<?php
/**
 * Plugin Name: Next Identity OIDC
 * Plugin URI: https://github.com/next-identity/ni-wordpress-oidc
 * Description: WordPress integration with Next Identity via OpenID Connect (OIDC) for seamless authentication, registration, and profile management.
 * Version: 1.0.0
 * Author: Next Identity
 * Author URI: https://nextidentity.com
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain: ni-oidc
 * Domain Path: /languages
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('NI_OIDC_VERSION', '1.0.0');
define('NI_OIDC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('NI_OIDC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NI_OIDC_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Include required files
require_once NI_OIDC_PLUGIN_DIR . 'includes/class-ni-oidc.php';

// Initialize the plugin
function ni_oidc_init() {
    $plugin = new NI_OIDC();
    $plugin->run();
}
ni_oidc_init();

// Register activation hook
register_activation_hook(__FILE__, 'ni_oidc_activate');

// Register deactivation hook
register_deactivation_hook(__FILE__, 'ni_oidc_deactivate');

/**
 * The code that runs during plugin activation.
 */
function ni_oidc_activate() {
    // Add default options
    if (!get_option('ni_oidc_login_button_text')) {
        add_option('ni_oidc_login_button_text', __('Log in with Next Identity', 'ni-oidc'));
    }
    
    if (!get_option('ni_oidc_register_button_text')) {
        add_option('ni_oidc_register_button_text', __('Register with Next Identity', 'ni-oidc'));
    }
    
    if (!get_option('ni_oidc_edit_profile_button_text')) {
        add_option('ni_oidc_edit_profile_button_text', __('Edit Profile', 'ni-oidc'));
    }
    
    if (!get_option('ni_oidc_scopes')) {
        add_option('ni_oidc_scopes', 'openid profile email');
    }
    
    if (!get_option('ni_oidc_auto_register_users')) {
        add_option('ni_oidc_auto_register_users', true);
    }
    
    if (!get_option('ni_oidc_default_role')) {
        add_option('ni_oidc_default_role', 'subscriber');
    }
    
    // Create any necessary database tables or structures
    global $wpdb;
    
    // Make sure the usermeta table exists and has appropriate indexes
    $wpdb->query("CREATE INDEX IF NOT EXISTS ni_oidc_sub_idx ON {$wpdb->usermeta} (meta_key, meta_value(191)) COMMENT 'Next Identity OIDC subject ID index'");
    
    // Clear any cached data
    delete_transient('ni_oidc_endpoints');
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * The code that runs during plugin deactivation.
 */
function ni_oidc_deactivate() {
    // Clear any cached data
    delete_transient('ni_oidc_endpoints');
    
    // Flush rewrite rules
    flush_rewrite_rules();
}

/**
 * The code that runs during plugin uninstallation.
 * This action is documented in uninstall.php
 */
function ni_oidc_uninstall() {
    // More cleanup, if needed, in uninstall.php
}

// Add link to settings on the plugin listing page
function ni_oidc_add_action_links($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=ni-oidc-settings') . '">' . __('Settings', 'ni-oidc') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . NI_OIDC_PLUGIN_BASENAME, 'ni_oidc_add_action_links'); 