<?php
/**
 * Plugin Name: WebPlatform Messaging Connector
 * Description: Send WhatsApp notifications from WordPress and WooCommerce through WebPlatform.
 * Version: 0.3.2
 * Requires at least: 6.2
 * Requires PHP: 7.4
 * Author: WebPlatform
 * Author URI: https://webplatform.co.in/
 * License: GPL-2.0-or-later
 * Text Domain: webplatform-messaging-connector
 */

if (!defined('ABSPATH')) {
    exit;
}

define('WPWA_VERSION', '0.3.2');
define('WPWA_FILE', __FILE__);
define('WPWA_DIR', plugin_dir_path(__FILE__));
define('WPWA_URL', plugin_dir_url(__FILE__));

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

/**
 * Display the current WebPlatform brand on this plugin's settings screen.
 */
function wpwa_admin_brand()
{
    if (!current_user_can('manage_options') || !isset($_GET['page'])) {
        return;
    }

    $page = sanitize_key(wp_unslash($_GET['page']));
    if ('webplatform-messaging' !== $page) {
        return;
    }

    ?>
    <div class="webplatform-plugin-brand" style="display:flex;align-items:center;gap:12px;margin:16px 0 8px;padding:12px 16px;background:#fff;border:1px solid #dcdcde;border-radius:8px;box-sizing:border-box;max-width:1100px">
        <img src="<?php echo esc_url(WPWA_URL . 'assets/brand-icon.png'); ?>" width="48" height="48" alt="" aria-hidden="true" style="display:block;width:48px;height:48px;object-fit:contain">
        <img src="<?php echo esc_url(WPWA_URL . 'assets/brand-wordmark.png'); ?>" width="180" height="35" alt="<?php echo esc_attr__('WebPlatform', 'webplatform-messaging-connector'); ?>" style="display:block;width:180px;max-width:45vw;height:auto">
    </div>
    <?php
}
add_action('admin_notices', 'wpwa_admin_brand');
