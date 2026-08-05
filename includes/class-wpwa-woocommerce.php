<?php

if (!defined('ABSPATH')) {
    exit;
}

class WPWA_WooCommerce
{
    private $client;

    public function __construct(WPWA_API_Client $client)
    {
        $this->client = $client;
        add_action('woocommerce_after_order_notes', array($this, 'opt_in_field'));
        add_action('woocommerce_checkout_update_order_meta', array($this, 'save_opt_in'));
        add_action('woocommerce_order_status_changed', array($this, 'status_changed'), 10, 4);
    }

    public function opt_in_field($checkout)
    {
        woocommerce_form_field('wpwa_whatsapp_opt_in', array(
            'type' => 'checkbox',
            'class' => array('form-row-wide'),
            'label' => __('Send me order updates on WhatsApp', 'webplatform-messaging-connector'),
            'required' => false,
        ), $checkout->get_value('wpwa_whatsapp_opt_in'));
    }

    public function save_opt_in($order_id)
    {
        $nonce = isset($_POST['woocommerce-process-checkout-nonce'])
            ? sanitize_text_field(wp_unslash($_POST['woocommerce-process-checkout-nonce']))
            : '';

        if (!$nonce || !wp_verify_nonce($nonce, 'woocommerce-process_checkout')) {
            return;
        }

        update_post_meta($order_id, '_wpwa_whatsapp_opt_in', !empty($_POST['wpwa_whatsapp_opt_in']) ? 'yes' : 'no');
        if (!empty($_POST['wpwa_whatsapp_opt_in']) && get_current_user_id()) {
            update_user_meta(get_current_user_id(), 'wpwa_whatsapp_opt_in', 1);
        }
    }

    public function status_changed($order_id, $old_status, $new_status, $order)
    {
        $settings = $this->client->settings();
        if (empty($settings['enable_woocommerce']) || 'yes' !== $order->get_meta('_wpwa_whatsapp_opt_in')) {
            return;
        }

        $template = isset($settings['template_' . $new_status]) ? trim($settings['template_' . $new_status]) : '';
        $phone = $this->normalize_phone($order->get_billing_phone(), $order->get_billing_country());
        if ('' === $template || '' === $phone) {
            return;
        }

        $result = $this->client->send_template($phone, $template, $settings['template_language']);
        if (is_wp_error($result)) {
            $order->add_order_note(sprintf(
                /* translators: %s: Error returned by the WebPlatform messaging API. */
                __('WebPlatform WhatsApp notification failed: %s', 'webplatform-messaging-connector'),
                $result->get_error_message()
            ));
            return;
        }

        $order->add_order_note(sprintf(
            /* translators: %s: Approved messaging template name. */
            __('WebPlatform WhatsApp template “%s” sent.', 'webplatform-messaging-connector'),
            $template
        ));
    }

    private function normalize_phone($phone, $country)
    {
        $phone = preg_replace('/[^0-9+]/', '', (string) $phone);
        if (0 === strpos($phone, '+')) {
            return $phone;
        }

        $calling_code = WC()->countries->get_country_calling_code($country);
        if (is_array($calling_code)) {
            $calling_code = reset($calling_code);
        }
        $calling_code = preg_replace('/[^0-9]/', '', (string) $calling_code);

        return $calling_code ? '+' . $calling_code . ltrim($phone, '0') : $phone;
    }
}
