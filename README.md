# WebPlatform Messaging Connector for WordPress

Installable connector for the WebPlatform merchant WhatsApp API.

## Installation

Download `webplatform-messaging-0.2.0.zip`, upload it from WordPress under
Plugins > Add New > Upload Plugin, and activate it.

## Commercial model

The distributed plugin can remain free. WebPlatform owns authentication, add-on
entitlement, metering, and billing, so a merchant can start on a free entitlement and
move to paid access without a plugin update. Do not put Meta credentials or billing
rules in WordPress.

Launch pricing defaults to zero. When paid access begins, configure
`WHATSAPP_API_SETUP_FEE` and `WHATSAPP_API_MONTHLY_FEE` in WebPlatform and clear the
Laravel configuration cache.

## License API

The plugin uses these authenticated endpoints on `https://webplatform.co.in`:

* `POST /api/plugin-license/activate`
* `POST /api/plugin-license/validate`
* `POST /api/plugin-license/deactivate`
* `GET /api/plugin-license/products`

The merchant API bearer token authorizes each request. Activations are bound to the
normalized WordPress home URL and a generated installation UUID. Only a SHA-256 hash
of the returned activation token is stored by WebPlatform.

The product selector is populated from the API catalog. The initial catalog contains
`webplatform-messaging` and the published
[`wc-sodexo`](https://wordpress.org/plugins/wc-sodexo/) plugin. Each product keeps a
separate activation token in WordPress.
