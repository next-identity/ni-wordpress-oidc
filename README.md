![Next Identity Logo](assets/images/Next_Identity_Logo_White.svg)
# 

# Next Identity WordPress OIDC

Easily integrate Next Identity authentication into your WordPress site using OpenID Connect.

## Overview

The Next Identity WordPress plugin provides seamless integration with Next Identity authentication services via OpenID Connect (OIDC) protocol. This plugin allows your WordPress site to delegate authentication to Next Identity, a powerful Customer Identity and Access Management (CIAM) provider.

Next Identity provides no-code CIAM orchestration and passwordless options such as Passkeys. Next Identity can also orchestrate authentication between any additional auth provider, allowing you to have a fully unified login capability for your site or suite of sites.

## Features

* Simple configuration interface with provider URL, client credentials, and scope settings
* Automatic display of callback URL for easy configuration on the Next Identity side
* Support for login, registration, and profile editing redirects
* User account creation and synchronization with Next Identity profiles
* Customizable login and registration buttons
* WordPress role assignment for users authenticated through Next Identity
* Clean logout process with proper session termination

## Requirements

* WordPress 5.0 or higher
* PHP 7.4 or higher
* A Next Identity account with access to create OIDC applications

## Installation

1. Download the latest release from the [GitHub repository](https://github.com/next-identity/ni-wordpress-oidc/releases)
2. Upload the plugin to your WordPress site
3. Activate the plugin through the WordPress admin interface
4. Configure the plugin at Settings > Next Identity OIDC

Alternatively, you can install the plugin directly from the WordPress Plugin Directory:

1. In your WordPress admin panel, go to Plugins > Add New
2. Search for "Next Identity OIDC"
3. Click "Install Now" and then "Activate"
4. Configure the plugin at Settings > Next Identity OIDC

## Configuration

1. **Provider Settings**
   * **Provider URL**: Enter the base URL of your Next Identity provider (e.g., https://auth.nextidentity.com)
   * **Client ID**: Enter the client ID provided by Next Identity
   * **Client Secret**: Enter the client secret provided by Next Identity
   * **Scopes**: Specify the required OAuth scopes (default: `openid profile email`)
   * **Skip UserInfo Endpoint**: Use ID token claims instead of making an additional call to the UserInfo endpoint
   * **Callback URL**: Copy this URL and register it as an authorized redirect URI in your Next Identity application settings

2. **Button Customization**
   * **Login Button Text**: Customize the text displayed on the login button
   * **Register Button Text**: Customize the text displayed on the register button
   * **Edit Profile Button Text**: Customize the text displayed on the edit profile button

3. **User Settings**
   * **Auto-register Users**: Enable to automatically create user accounts for new Next Identity users
   * **Default User Role**: Select which role should be assigned to users authenticated through Next Identity

4. **Redirect Settings**
   * **Login Redirect URL**: Where to redirect users after successful login
   * **Logout Redirect URL**: Where to redirect users after logging out

## Usage

### Adding Login and Registration Buttons

You can add Next Identity buttons to your site using shortcodes:

```
[ni_oidc_login_button]
[ni_oidc_register_button]
[ni_oidc_edit_profile_button]
[ni_oidc_logout_button]
```

Each shortcode accepts optional parameters:

```
[ni_oidc_login_button text="Sign in with Next Identity" class="my-custom-button"]
```

### Programmatic Usage

Developers can also access Next Identity user information programmatically:

```php
// Check if a user is authenticated with Next Identity
$ni_user = new NI_OIDC_User();
if ($ni_user->is_authenticated()) {
    // Get user info
    $user_info = $ni_user->get_user_info();
    
    // Get access token for API calls
    $access_token = $ni_user->get_access_token();
    
    // Do something with the user info
    echo 'Welcome, ' . $user_info['name'];
}
```

## Hooks and Filters

The plugin provides several hooks that you can use to customize its behavior:

### Actions

* `ni_oidc_user_login` - Fired when a user logs in with Next Identity
* `ni_oidc_user_updated` - Fired when a user's Next Identity profile is updated

### Filters

* `ni_oidc_authorization_parameters` - Filter the parameters sent to the authorization endpoint
* `ni_oidc_userinfo` - Filter the user info received from Next Identity

## Performance Optimization

The plugin provides several options to optimize performance:

### ID Token vs UserInfo Endpoint

By default, the plugin makes two requests during authentication:
1. Token endpoint - To exchange the authorization code for tokens
2. UserInfo endpoint - To get user profile information

You can enable the "Skip UserInfo Endpoint" option to use only the claims from the ID token instead of making an additional call to the UserInfo endpoint. This reduces authentication time by eliminating one HTTP request.

**Requirements for using this feature:**
- Your Next Identity provider must include all required user claims in the ID token
- At minimum, the ID token should contain: `sub`, `email`, and preferably `name`
- You may need to configure your Next Identity provider to include additional claims in the ID token

## Troubleshooting

If you encounter issues with the plugin:

1. **Check Configuration**: Ensure that the Provider URL, Client ID, and Client Secret are correct.
2. **Verify Callback URL**: Confirm that the callback URL is properly registered in your Next Identity application settings.
3. **Enable WordPress Debug Mode**: Check the WordPress logs for any error messages related to the OIDC authentication process.
4. **Check Scopes**: Ensure that the requested scopes are allowed for your Next Identity application.

### Session Management

The plugin uses WordPress transients to securely store authentication state parameters. This approach ensures:

1. **Security**: State parameters are only valid for a limited time (10 minutes by default)
2. **Reliability**: Works correctly for both authenticated and unauthenticated users
3. **State Parameter**: The OIDC state parameter is stored securely for verification to prevent CSRF attacks

If you're experiencing authentication issues, check that:
- WordPress transients are functioning correctly (typically stored in your database)
- Your server has proper cookie settings
- You have not exceeded the maximum transient size for your WordPress installation
- Any caching plugins you use are configured to preserve WordPress transients

## Credit

This plugin uses the OpenID Connect protocol to integrate with Next Identity. It is inspired by other WordPress authentication plugins and Next Identity OIDC libraries.

## License

This project is licensed under the GPL v2 or later.

## Links

* [Next Identity Website](https://nextidentity.com/)
* [GitHub Repository](https://github.com/next-identity/ni-wordpress-oidc)
* [WordPress Plugin Directory](https://wordpress.org/plugins/ni-wordpress-oidc/)
