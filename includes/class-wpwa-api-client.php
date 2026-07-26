<?php

if (!defined('ABSPATH')) {
    exit;
}

class WPWA_API_Client
{
    const OPTION_KEY = 'wpwa_settings';

    public function settings()
    {
        return wp_parse_args(get_option(self::OPTION_KEY, array()), array(
            'base_url' => 'https://webplatform.co.in',
            'access_token' => '',
            'timeout' => 15,
            'license_instance_id' => '',
            'license_activation_token' => '',
            'license_status' => 'inactive',
            'license_product_key' => 'webplatform-messaging',
            'licenses' => array(),
        ));
    }

    public function is_configured()
    {
        $settings = $this->settings();
        return !empty($settings['base_url']) && !empty($settings['access_token']);
    }

    public function templates()
    {
        return $this->request('GET', '/api/merchant/whatsapp/templates');
    }

    public function send_template($phone, $template_name, $language = 'en', $components = array())
    {
        return $this->request('POST', '/api/merchant/whatsapp/send-template', array(
            'phone' => $phone,
            'template_name' => $template_name,
            'language' => $language,
            'components' => $components,
        ));
    }

    public function send_text($phone, $message)
    {
        return $this->request('POST', '/api/merchant/whatsapp/send-text', array(
            'phone' => $phone,
            'message' => $message,
        ));
    }

    public function connector_status()
    {
        return $this->request('GET', '/api/merchant/wordpress/status');
    }

    public function sync_wordpress($payload)
    {
        return $this->request('POST', '/api/merchant/wordpress/sync', $payload);
    }

    public function license_products()
    {
        return $this->request('GET', '/api/plugin-license/products');
    }

    public function activate_license($product_key, $instance_id)
    {
        return $this->request('POST', '/api/plugin-license/activate', array(
            'product_key' => $product_key,
            'site_url' => home_url('/'),
            'instance_id' => $instance_id,
        ));
    }

    public function validate_license($product_key, $instance_id, $activation_token)
    {
        return $this->request('POST', '/api/plugin-license/validate', array(
            'product_key' => $product_key,
            'site_url' => home_url('/'),
            'instance_id' => $instance_id,
            'activation_token' => $activation_token,
        ));
    }

    public function deactivate_license($product_key, $instance_id, $activation_token)
    {
        return $this->request('POST', '/api/plugin-license/deactivate', array(
            'product_key' => $product_key,
            'site_url' => home_url('/'),
            'instance_id' => $instance_id,
            'activation_token' => $activation_token,
        ));
    }

    private function request($method, $path, $body = null)
    {
        $settings = $this->settings();
        if (!$this->is_configured()) {
            return new WP_Error('wpwa_not_configured', __('WebPlatform WhatsApp is not configured.', 'webplatform-messaging'));
        }

        $args = array(
            'method' => $method,
            'timeout' => max(5, min(30, absint($settings['timeout']))),
            'headers' => array(
                'Authorization' => 'Bearer ' . trim($settings['access_token']),
                'Accept' => 'application/json',
                'Content-Type' => 'application/json',
                'User-Agent' => 'WebPlatform-WhatsApp-WordPress/' . WPWA_VERSION,
            ),
        );

        if (null !== $body) {
            $args['body'] = wp_json_encode($body);
        }

        $response = wp_remote_request(
            untrailingslashit(esc_url_raw($settings['base_url'])) . $path,
            $args
        );

        if (is_wp_error($response)) {
            return $response;
        }

        $status = wp_remote_retrieve_response_code($response);
        $decoded = json_decode(wp_remote_retrieve_body($response), true);
        if (!is_array($decoded)) {
            return new WP_Error('wpwa_invalid_response', __('WebPlatform returned an invalid response.', 'webplatform-messaging'));
        }

        if ($status < 200 || $status >= 300 || empty($decoded['success'])) {
            $message = isset($decoded['message'])
                ? sanitize_text_field($decoded['message'])
                : sprintf(
                    /* translators: %d: HTTP response status code. */
                    __('WebPlatform request failed with HTTP %d.', 'webplatform-messaging'),
                    $status
                );
            return new WP_Error('wpwa_api_error', $message, array('status' => $status, 'response' => $decoded));
        }

        return $decoded;
    }
}
