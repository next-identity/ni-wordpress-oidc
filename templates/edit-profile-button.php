<div class="ni-oidc-button-container">
    <a href="<?php echo esc_url($edit_profile_url); ?>" class="ni-oidc-button <?php echo isset($atts['class']) ? esc_attr($atts['class']) : 'ni-oidc-edit-profile-button'; ?>">
        <img src="<?php echo esc_url(NI_OIDC_PLUGIN_URL . 'assets/images/Next_Identity_Icon_White.svg'); ?>" alt="Next Identity Icon" class="ni-oidc-icon">
        <span><?php echo isset($atts['text']) ? esc_html($atts['text']) : esc_html($button_text); ?></span>
    </a>
</div> 