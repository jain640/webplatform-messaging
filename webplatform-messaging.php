<?php
/**
 * Plugin Name: WebPlatform Messaging Connector
 * Description: Send WhatsApp notifications from WordPress and WooCommerce through WebPlatform.
 * Version: 0.3.0
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Author: WebPlatform
 * License: GPL-2.0-or-later
 * Text Domain: webplatform-messaging
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WPWA_VERSION', '0.3.0');
define('WPWA_FILE', __FILE__);
define('WPWA_DIR', plugin_dir_path(__FILE__));

require_once WPWA_DIR . 'includes/class-wpwa-api-client.php';
require_once WPWA_DIR . 'includes/class-wpwa-admin.php';
require_once WPWA_DIR . 'includes/class-wpwa-woocommerce.php';
require_once WPWA_DIR . 'includes/class-wpwa-sync.php';

function wpwa_boot_plugin()
{
    $client = new WPWA_API_Client();
    new WPWA_Admin($client);

    if (class_exists('WooCommerce')) {
        new WPWA_WooCommerce($client);
    }
}
add_action('plugins_loaded', 'wpwa_boot_plugin');
