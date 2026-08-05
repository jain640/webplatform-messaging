=== WebPlatform Messaging Connector ===
Contributors: jain640
Tags: whatsapp, woocommerce, notifications
Requires at least: 6.2
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 0.3.1
License: GPLv2 or later

Send WordPress and WooCommerce WhatsApp notifications through your WebPlatform merchant account.

== Description ==

WebPlatform WhatsApp connects WordPress to WebPlatform's merchant WhatsApp API.
Meta credentials remain in WebPlatform. WordPress stores only the WebPlatform base URL
and a dedicated merchant API token.

The plugin relies on WebPlatform, an external service, to submit WhatsApp messages,
retrieve approved templates, and synchronize contacts and WooCommerce order data.
Using these features sends the configured site URL, contact details, consent status,
order details, message content, and template selections to WebPlatform. A WebPlatform
merchant account and API token are required.

* Service: https://webplatform.co.in/
* Terms of Service: https://webplatform.co.in/terms
* Privacy Policy: https://webplatform.co.in/privacy-policy

The plugin includes:

* Connection and approved-template checks.
* Manual free-form test messages for open customer-service windows.
* WooCommerce checkout opt-in.
* Template notifications for processing, completed, and cancelled orders.
* Order notes for delivery submission success or failure.
* WordPress user and WooCommerce order synchronization.
* Direct access to the WebPlatform Messaging Campaigns dashboard.

== Installation ==

1. Upload and activate the plugin.
2. Open Settings > WebPlatform WhatsApp.
3. Enter the WebPlatform URL and dedicated merchant API token.
4. Save, then select Test connection.
5. If using WooCommerce, enter approved template names and enable notifications.

Automated WhatsApp messages normally require approved Meta templates. Customers must
explicitly opt in; the plugin adds an unchecked checkout consent field.

== Changelog ==

= 0.3.1 =
* Removed the license and entitlement interface; all included functionality is available without a plugin license check.
* Removed the 100-record synchronization cap.
* Documented the external WebPlatform service and corrected the contributor account.

= 0.3.0 =
* Added audience and order synchronization.
* Added a direct link to the WebPlatform Messaging Campaigns dashboard.

= 0.2.0 =
* Improved connection settings and WooCommerce notification controls.

= 0.1.0 =
* Initial MVP.
