# WebPlatform Messaging Connector for WordPress

Installable connector for the WebPlatform merchant WhatsApp API.

## Product links

* [Explore WebPlatform plugins](https://webplatform.co.in/plugins)
* [WebPlatform plugin setup guides](https://webplatform.co.in/help/plugins)
* [Compare WhatsApp and email marketing plans](https://webplatform.co.in/pricing)
* [Open WebPlatform](https://webplatform.co.in/)

## Install

Create a release ZIP whose top-level directory is `webplatform-messaging`, containing the plugin PHP files, `includes/`, `readme.txt`, and `uninstall.php`. Do not include Git metadata or repository-only documentation in the WordPress release package.

Upload the ZIP in WordPress under Plugins > Add New > Upload Plugin and activate it. Then open Settings > WebPlatform WhatsApp, enter the WebPlatform URL and dedicated merchant API token, save the settings, and run the connection test.

The old README referenced `./integrations/wordpress/build-webplatform-messaging.sh`; that script is not part of this standalone repository and should not be used from this checkout.

## WordPress 7.1 validation

Before changing the WordPress.org `Tested up to` value to 7.1, complete the compatibility checklist in `docs/wordpress-7.1-validation.md` on WordPress 7.1 RC/final and run WordPress Plugin Check.

## External service

The plugin uses the authenticated WebPlatform merchant API at
`https://webplatform.co.in` to submit WhatsApp messages, retrieve approved templates,
and synchronize contacts and WooCommerce orders. All functionality included in the
plugin is available without a plugin license or entitlement check.

* [Terms of Service](https://webplatform.co.in/terms)
* [Privacy Policy](https://webplatform.co.in/privacy-policy)
