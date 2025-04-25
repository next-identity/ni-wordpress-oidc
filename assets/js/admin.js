/**
 * Admin JavaScript for Next Identity OIDC
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Copy to clipboard functionality for callback URL
        if (document.querySelector('button.button[onclick*="clipboard"]')) {
            document.querySelector('button.button[onclick*="clipboard"]').addEventListener('click', function() {
                // Show a success message after copying
                var originalText = this.innerText;
                var button = this;
                
                button.innerText = 'Copied!';
                setTimeout(function() {
                    button.innerText = originalText;
                }, 2000);
            });
        }

        // Provide visual feedback when settings are saved
        var urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('settings-updated') === 'true') {
            var notice = $('<div class="notice notice-success is-dismissible"><p>Settings saved successfully.</p></div>');
            $('.wrap h1').after(notice);
        }
    });

})(jQuery); 