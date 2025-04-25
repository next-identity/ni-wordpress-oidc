<?php
/**
 * Fired when the plugin is uninstalled.
 *
 * @since      1.0.0
 * @package    NI_OIDC
 */

// If uninstall not called from WordPress, then exit.
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Define whether to keep plugin data on uninstall or not
$keep_data = apply_filters('ni_oidc_keep_data_on_uninstall', false);

if (!$keep_data) {
    // Remove all options
    delete_option('ni_oidc_provider_url');
    delete_option('ni_oidc_client_id');
    delete_option('ni_oidc_client_secret');
    delete_option('ni_oidc_scopes');
    delete_option('ni_oidc_login_button_text');
    delete_option('ni_oidc_register_button_text');
    delete_option('ni_oidc_edit_profile_button_text');
    delete_option('ni_oidc_auto_register_users');
    delete_option('ni_oidc_default_role');
    delete_option('ni_oidc_login_redirect');
    delete_option('ni_oidc_logout_redirect');
    
    // Remove any transients
    delete_transient('ni_oidc_endpoints');
    
    // Remove user meta from all users
    global $wpdb;
    $wpdb->query("DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE 'ni_oidc_%'");
    
    // Remove any additional database tables if created
    
    // Remove the index we created
    $wpdb->query("DROP INDEX IF EXISTS ni_oidc_sub_idx ON {$wpdb->usermeta}");
    
    // Clean up any other stuff (files, etc.)
} 