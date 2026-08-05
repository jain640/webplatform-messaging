<?php

if (!defined('ABSPATH')) {
    exit;
}

class WPWA_Admin
{
    private $client;

    public function __construct(WPWA_API_Client $client)
    {
        $this->client = $client;
        add_action('admin_menu', array($this, 'menu'));
        add_action('admin_init', array($this, 'register_settings'));
        add_action('admin_post_wpwa_test_connection', array($this, 'test_connection'));
        add_action('admin_post_wpwa_send_test', array($this, 'send_test'));
        add_action('admin_post_wpwa_sync_wordpress', array($this, 'sync_wordpress'));
    }

    public function menu()
    {
        add_options_page(
            __('WebPlatform Messaging', 'webplatform-messaging-connector'),
            __('WebPlatform Messaging', 'webplatform-messaging-connector'),
            'manage_options',
            'webplatform-messaging',
            array($this, 'render')
        );
    }

    public function register_settings()
    {
        register_setting('wpwa_settings_group', WPWA_API_Client::OPTION_KEY, array(
            'type' => 'array',
            'sanitize_callback' => array($this, 'sanitize'),
            'default' => array(),
        ));
    }

    public function sanitize($input)
    {
        $old = $this->client->settings();
        $token = isset($input['access_token']) ? trim(sanitize_text_field(wp_unslash($input['access_token']))) : '';

        return array(
            'base_url' => isset($input['base_url']) ? untrailingslashit(esc_url_raw($input['base_url'])) : '',
            'access_token' => '' !== $token ? $token : $old['access_token'],
            'timeout' => isset($input['timeout']) ? max(5, min(30, absint($input['timeout']))) : 15,
            'enable_woocommerce' => !empty($input['enable_woocommerce']) ? 1 : 0,
            'template_processing' => isset($input['template_processing']) ? sanitize_key($input['template_processing']) : '',
            'template_completed' => isset($input['template_completed']) ? sanitize_key($input['template_completed']) : '',
            'template_cancelled' => isset($input['template_cancelled']) ? sanitize_key($input['template_cancelled']) : '',
            'template_language' => isset($input['template_language']) ? sanitize_text_field($input['template_language']) : 'en',
        );
    }

    public function render()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $settings = $this->client->settings();
        $sync_status = $this->client->is_configured() ? $this->client->connector_status() : null;
        $sync_data = !is_wp_error($sync_status) ? (array) ($sync_status['data'] ?? array()) : array();
        ?>
        <div class="wrap">
            <h1><?php esc_html_e('WebPlatform Messaging', 'webplatform-messaging-connector'); ?></h1>
            <?php settings_errors('wpwa'); ?>
            <p><?php esc_html_e('Connect WordPress to your WebPlatform merchant account. Your Meta access token is never stored in WordPress.', 'webplatform-messaging-connector'); ?></p>

            <h2><?php esc_html_e('Audience synchronization', 'webplatform-messaging-connector'); ?></h2>
            <p>
                <?php
                printf(
                    /* translators: 1: synchronized contacts, 2: synchronized orders. */
                    esc_html__('%1$d contacts and %2$d orders synchronized.', 'webplatform-messaging-connector'),
                    absint($sync_data['contacts'] ?? 0),
                    absint($sync_data['orders'] ?? 0)
                );
                ?>
            </p>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-block;margin-right:8px">
                <input type="hidden" name="action" value="wpwa_sync_wordpress">
                <?php wp_nonce_field('wpwa_sync_wordpress'); ?>
                <?php submit_button(__('Sync WordPress data', 'webplatform-messaging-connector'), 'primary', 'submit', false); ?>
            </form>
            <?php if (!empty($sync_data['dashboards']['messaging'])) : ?>
                <a class="button button-secondary" href="<?php echo esc_url($sync_data['dashboards']['messaging']); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e('Open Messaging Campaigns', 'webplatform-messaging-connector'); ?></a>
            <?php endif; ?>

            <form method="post" action="options.php">
                <?php settings_fields('wpwa_settings_group'); ?>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><label for="wpwa-base-url"><?php esc_html_e('WebPlatform URL', 'webplatform-messaging-connector'); ?></label></th>
                        <td><input id="wpwa-base-url" class="regular-text" type="url" name="wpwa_settings[base_url]" value="<?php echo esc_attr($settings['base_url']); ?>" placeholder="https://app.example.com" required></td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wpwa-token"><?php esc_html_e('Merchant API token', 'webplatform-messaging-connector'); ?></label></th>
                        <td>
                            <input id="wpwa-token" class="regular-text" type="password" name="wpwa_settings[access_token]" value="" autocomplete="new-password" placeholder="<?php echo !empty($settings['access_token']) ? esc_attr__('Saved — enter only to replace', 'webplatform-messaging-connector') : ''; ?>">
                            <p class="description"><?php esc_html_e('Generate this token in your WebPlatform merchant account.', 'webplatform-messaging-connector'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><label for="wpwa-timeout"><?php esc_html_e('Request timeout', 'webplatform-messaging-connector'); ?></label></th>
                        <td><input id="wpwa-timeout" type="number" min="5" max="30" name="wpwa_settings[timeout]" value="<?php echo esc_attr($settings['timeout']); ?>"> <?php esc_html_e('seconds', 'webplatform-messaging-connector'); ?></td>
                    </tr>
                </table>

                <h2><?php esc_html_e('WooCommerce notifications', 'webplatform-messaging-connector'); ?></h2>
                <table class="form-table" role="presentation">
                    <tr>
                        <th scope="row"><?php esc_html_e('Enable', 'webplatform-messaging-connector'); ?></th>
                        <td><label><input type="checkbox" name="wpwa_settings[enable_woocommerce]" value="1" <?php checked(!empty($settings['enable_woocommerce'])); ?>> <?php esc_html_e('Send approved WhatsApp templates when order status changes', 'webplatform-messaging-connector'); ?></label></td>
                    </tr>
                    <?php foreach (array('processing', 'completed', 'cancelled') as $status) : ?>
                        <tr>
                            <th scope="row"><label for="wpwa-<?php echo esc_attr($status); ?>"><?php echo esc_html(ucfirst($status) . ' template'); ?></label></th>
                            <td><input id="wpwa-<?php echo esc_attr($status); ?>" class="regular-text" name="wpwa_settings[template_<?php echo esc_attr($status); ?>]" value="<?php echo esc_attr($settings['template_' . $status]); ?>" placeholder="order_<?php echo esc_attr($status); ?>"></td>
                        </tr>
                    <?php endforeach; ?>
                    <tr>
                        <th scope="row"><label for="wpwa-language"><?php esc_html_e('Template language', 'webplatform-messaging-connector'); ?></label></th>
                        <td><input id="wpwa-language" name="wpwa_settings[template_language]" value="<?php echo esc_attr($settings['template_language']); ?>" placeholder="en"></td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>

            <hr>
            <h2><?php esc_html_e('Diagnostics', 'webplatform-messaging-connector'); ?></h2>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-block;margin-right:12px">
                <input type="hidden" name="action" value="wpwa_test_connection">
                <?php wp_nonce_field('wpwa_test_connection'); ?>
                <?php submit_button(__('Test connection', 'webplatform-messaging-connector'), 'secondary', 'submit', false); ?>
            </form>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="display:inline-block">
                <input type="hidden" name="action" value="wpwa_send_test">
                <?php wp_nonce_field('wpwa_send_test'); ?>
                <input type="tel" name="phone" placeholder="+919999999999" required>
                <input type="text" name="message" placeholder="<?php esc_attr_e('Test message', 'webplatform-messaging-connector'); ?>" required>
                <?php submit_button(__('Send test text', 'webplatform-messaging-connector'), 'secondary', 'submit', false); ?>
            </form>
            <p class="description"><?php esc_html_e('Free-form text is accepted by WhatsApp only inside an open customer-service conversation window. Use approved templates for automated notifications.', 'webplatform-messaging-connector'); ?></p>
        </div>
        <?php
    }

    public function test_connection()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to perform this action.', 'webplatform-messaging-connector'));
        }
        check_admin_referer('wpwa_test_connection');

        $result = $this->client->templates();
        $message = is_wp_error($result)
            ? $result->get_error_message()
            : sprintf(
                /* translators: %d: Number of approved messaging templates. */
                __('Connected. WebPlatform returned %d approved template(s).', 'webplatform-messaging-connector'),
                absint($result['data']['count'] ?? 0)
            );
        $this->redirect_notice($message, !is_wp_error($result));
    }

    public function send_test()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to perform this action.', 'webplatform-messaging-connector'));
        }
        check_admin_referer('wpwa_send_test');

        $phone = isset($_POST['phone']) ? sanitize_text_field(wp_unslash($_POST['phone'])) : '';
        $message = isset($_POST['message']) ? sanitize_textarea_field(wp_unslash($_POST['message'])) : '';
        $result = $this->client->send_text($phone, $message);
        $this->redirect_notice(
            is_wp_error($result) ? $result->get_error_message() : __('Test message was accepted by WebPlatform.', 'webplatform-messaging-connector'),
            !is_wp_error($result)
        );
    }

    public function sync_wordpress()
    {
        if (!current_user_can('manage_options')) {
            wp_die(esc_html__('You are not allowed to perform this action.', 'webplatform-messaging-connector'));
        }
        check_admin_referer('wpwa_sync_wordpress');
        $result = $this->client->sync_wordpress(WPWA_Sync::payload());
        $this->redirect_notice(
            is_wp_error($result) ? $result->get_error_message() : __('WordPress contacts and orders synchronized.', 'webplatform-messaging-connector'),
            !is_wp_error($result)
        );
    }

    private function redirect_notice($message, $success)
    {
        set_transient('wpwa_notice_' . get_current_user_id(), array('message' => $message, 'success' => $success), 60);
        add_filter('wp_redirect', array($this, 'append_notice'));
        wp_safe_redirect(admin_url('options-general.php?page=webplatform-messaging'));
        exit;
    }

    public function append_notice($location)
    {
        $notice = get_transient('wpwa_notice_' . get_current_user_id());
        if (!$notice) {
            return $location;
        }
        delete_transient('wpwa_notice_' . get_current_user_id());
        add_settings_error('wpwa', 'wpwa_result', $notice['message'], $notice['success'] ? 'success' : 'error');
        set_transient('settings_errors', get_settings_errors(), 30);
        return add_query_arg('settings-updated', 'true', $location);
    }
}
