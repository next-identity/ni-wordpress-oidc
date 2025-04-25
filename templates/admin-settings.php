<div class="wrap">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
    
    <div class="ni-oidc-settings-header">
        <div class="ni-oidc-logo">
            <img src="<?php echo esc_url(NI_OIDC_PLUGIN_URL . 'assets/images/Next_Identity_Logo_Black.svg'); ?>" alt="Next Identity Logo" width="250">
        </div>
        <div class="ni-oidc-description">
            <p><?php _e('Next Identity is a powerful Customer Identity and Access Management (CIAM) provider. Configure your settings below to enable Single Sign-On with Next Identity via OpenID Connect.', 'ni-oidc'); ?></p>
        </div>
    </div>

    <form action="options.php" method="post">
        <?php
        settings_fields('ni_oidc_options');
        do_settings_sections('ni-oidc-settings');
        submit_button(__('Save Settings', 'ni-oidc'));
        ?>
    </form>
    
    <div class="ni-oidc-settings-footer">
        <h3><?php _e('Shortcodes', 'ni-oidc'); ?></h3>
        <p><?php _e('You can use the following shortcodes to add Next Identity buttons to your site:', 'ni-oidc'); ?></p>
        <ul>
            <li><code>[ni_oidc_login_button]</code> - <?php _e('Displays a login button', 'ni-oidc'); ?></li>
            <li><code>[ni_oidc_register_button]</code> - <?php _e('Displays a registration button', 'ni-oidc'); ?></li>
            <li><code>[ni_oidc_edit_profile_button]</code> - <?php _e('Displays an edit profile button for logged-in users', 'ni-oidc'); ?></li>
            <li><code>[ni_oidc_logout_button]</code> - <?php _e('Displays a logout button for logged-in users', 'ni-oidc'); ?></li>
        </ul>
        <p><?php _e('Each shortcode accepts optional parameters:', 'ni-oidc'); ?></p>
        <ul>
            <li><code>text</code> - <?php _e('Custom button text', 'ni-oidc'); ?></li>
            <li><code>class</code> - <?php _e('Custom CSS class for styling', 'ni-oidc'); ?></li>
        </ul>
        <p><?php _e('Example:', 'ni-oidc'); ?> <code>[ni_oidc_login_button text="Login with Next Identity" class="my-custom-button"]</code></p>
        
        <h3><?php _e('Need Help?', 'ni-oidc'); ?></h3>
        <p><?php _e('For more information about Next Identity and OpenID Connect integration, visit:', 'ni-oidc'); ?></p>
        <ul>
            <li><a href="https://nextidentity.com" target="_blank"><?php _e('Next Identity Website', 'ni-oidc'); ?></a></li>
            <li><a href="https://github.com/next-identity/ni-wordpress-oidc" target="_blank"><?php _e('Plugin Documentation', 'ni-oidc'); ?></a></li>
        </ul>
    </div>
</div> 