=== WebPlatform Messaging Connector ===
Contributors: webplatform
Tags: whatsapp, woocommerce, notifications
Requires at least: 6.2
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 0.2.0
License: GPLv2 or later

Send WordPress and WooCommerce WhatsApp notifications through your WebPlatform merchant account.

== Description ==

WebPlatform WhatsApp connects WordPress to WebPlatform's merchant WhatsApp API.
Meta credentials remain in WebPlatform. WordPress stores only the WebPlatform base URL
and a dedicated merchant API token.

Version 0.1 includes:

* Connection and approved-template checks.
* Revocable, site-bound WebPlatform license activation.
* Manual free-form test messages for open customer-service windows.
* WooCommerce checkout opt-in.
* Template notifications for processing, completed, and cancelled orders.
* Order notes for delivery submission success or failure.

The plugin is free to install. API entitlement, usage limits, and future paid plans are
controlled by the merchant's WebPlatform account.

== Installation ==

1. Upload and activate the plugin.
2. Open Settings > WebPlatform WhatsApp.
3. Enter the WebPlatform URL and dedicated merchant API token.
4. Save, then select Test connection.
5. If using WooCommerce, enter approved template names and enable notifications.

Automated WhatsApp messages normally require approved Meta templates. Customers must
explicitly opt in; the plugin adds an unchecked checkout consent field.

== Changelog ==

= 0.2.0 =
* Added a multi-plugin license catalog and product selector.
* Added support for separate WebPlatform Messaging and wc-sodexo activations.

= 0.1.0 =
* Initial MVP.
