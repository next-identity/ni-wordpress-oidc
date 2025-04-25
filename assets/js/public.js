/**
 * Public JavaScript for Next Identity OIDC
 */
(function() {
    'use strict';

    document.addEventListener('DOMContentLoaded', function() {
        // If there's an error parameter in the URL, display it
        var urlParams = new URLSearchParams(window.location.search);
        var error = urlParams.get('login_error');
        
        if (error) {
            // Find the login form if we're on the login page
            var loginForm = document.getElementById('loginform');
            
            if (loginForm) {
                // Create an error message
                var errorDiv = document.createElement('div');
                errorDiv.className = 'login-error';
                errorDiv.style.color = 'red';
                errorDiv.style.marginBottom = '15px';
                
                // Set the error message based on the error code
                switch (error) {
                    case 'no_code':
                        errorDiv.textContent = 'Authentication failed: No authorization code received.';
                        break;
                    case 'no_state':
                        errorDiv.textContent = 'Authentication failed: No state parameter received.';
                        break;
                    case 'invalid_state':
                        errorDiv.textContent = 'Authentication failed: Invalid state parameter.';
                        break;
                    case 'token_exchange_failed':
                        errorDiv.textContent = 'Authentication failed: Token exchange failed.';
                        break;
                    case 'userinfo_failed':
                        errorDiv.textContent = 'Authentication failed: Could not retrieve user information.';
                        break;
                    case 'no_subject':
                        errorDiv.textContent = 'Authentication failed: No user identifier provided.';
                        break;
                    case 'registration_disabled':
                        errorDiv.textContent = 'Registration is disabled. Please contact the administrator.';
                        break;
                    case 'no_email':
                        errorDiv.textContent = 'No email address provided. Email is required for registration.';
                        break;
                    default:
                        errorDiv.textContent = 'Authentication failed: ' + error;
                }
                
                // Insert the error message at the top of the form
                loginForm.insertBefore(errorDiv, loginForm.firstChild);
            }
        }
    });
})(); 