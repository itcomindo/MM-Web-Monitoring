<?php

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Website checker class implementing checker interface
 */
class MMWM_Checker implements MMWM_Checker_Interface
{
    /**
     * Perform a check on a website
     *
     * @param int $post_id The website post ID
     * @return array Check result with status and details
     */
    public function perform_check($post_id)
    {
        $config = $this->get_check_config($post_id);

        if (!$this->validate_url($config['url'])) {
            return [
                'status' => 'INVALID_URL',
                'reason' => 'Invalid or empty URL',
                'timestamp' => time()
            ];
        }

        $args = [
            'timeout'     => 30,
            'sslverify'   => false,
            'user-agent'  => 'Mozilla/5.0 (compatible; MMWM-Monitor/1.0; WordPress)',
            'headers'     => [
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept-Encoding' => 'gzip, deflate',
                'Cache-Control'   => 'no-cache',
                'Pragma'          => 'no-cache',
                'Referer'         => MMWM_WP_Compat::home_url(),
            ],
        ];

        $response = MMWM_WP_Compat::wp_remote_get($config['url'], $args);

        if (MMWM_WP_Compat::is_wp_error($response)) {
            return [
                'status' => 'DOWN',
                'reason' => 'Request Error: ' . MMWM_WP_Compat::get_error_message($response),
                'timestamp' => time()
            ];
        }

        $response_code = MMWM_WP_Compat::wp_remote_retrieve_response_code($response);
        if ($response_code >= 400) {
            return [
                'status' => 'DOWN',
                'reason' => "HTTP Error: {$response_code}",
                'timestamp' => time()
            ];
        }

        // Check for HTML element if required
        if ($config['check_type'] === 'fetch_html' && !empty($config['html_selector'])) {
            $body = MMWM_WP_Compat::wp_remote_retrieve_body($response);

            if (!MMWM_HTML_Parser::find_element($body, $config['html_selector'])) {
                return [
                    'status' => 'CONTENT_ERROR',
                    'reason' => "HTML Check: Element '{$config['html_selector']}' not found",
                    'timestamp' => time()
                ];
            }
        }

        // Perform SSL check for HTTPS URLs
        $this->check_ssl_status($post_id, $config['url']);

        return [
            'status' => 'UP',
            'reason' => "HTTP {$response_code} OK" . ($config['check_type'] === 'fetch_html' ? ' (HTML element found)' : ''),
            'timestamp' => time()
        ];
    }

    /**
     * Validate URL before checking
     *
     * @param string $url The URL to validate
     * @return bool True if valid, false otherwise
     */
    public function validate_url($url)
    {
        return MMWM_Validator::validate_url($url);
    }

    /**
     * Get check configuration for a website
     *
     * @param int $post_id The website post ID
     * @return array Configuration array
     */
    public function get_check_config($post_id)
    {
        return [
            'url'           => get_post_meta($post_id, '_mmwm_target_url', true),
            'check_type'    => get_post_meta($post_id, '_mmwm_check_type', true) ?: 'response_code',
            'html_selector' => get_post_meta($post_id, '_mmwm_html_selector', true),
            'interval'      => intval(get_post_meta($post_id, '_mmwm_interval', true)) ?: 15,
        ];
    }

    /**
     * Check SSL status and save to meta
     *
     * @param int $post_id The website post ID
     * @param string $url The URL to check
     * @return void
     */
    private function check_ssl_status($post_id, $url)
    {
        // Skip SSL check if not HTTPS
        if (strpos($url, 'https://') !== 0) {
            update_post_meta($post_id, '_mmwm_ssl_is_active', '0');
            update_post_meta($post_id, '_mmwm_ssl_error', 'URL is not HTTPS');
            return;
        }

        $ssl_checker = new MMWM_SSL_Checker();
        $ssl_result = $ssl_checker->check_ssl_status($url);

        // Save SSL status to meta
        update_post_meta($post_id, '_mmwm_ssl_is_active', $ssl_result['is_active'] ? '1' : '0');
        update_post_meta($post_id, '_mmwm_ssl_error', $ssl_result['error'] ?? '');
        update_post_meta($post_id, '_mmwm_ssl_expiry_date', $ssl_result['expiry_date'] ?? '');
        update_post_meta($post_id, '_mmwm_ssl_days_until_expiry', $ssl_result['days_until_expiry'] ?? '');
        update_post_meta($post_id, '_mmwm_ssl_issuer', $ssl_result['issuer'] ?? '');
        update_post_meta($post_id, '_mmwm_ssl_last_check', time());

        // Check if SSL is expiring soon and send notification
        if ($ssl_result['is_active'] && isset($ssl_result['is_expiring_soon']) && $ssl_result['is_expiring_soon']) {
            $this->send_ssl_expiring_notification($post_id, $ssl_result);
        }
    }

    /**
     * Send SSL expiring notification
     *
     * @param int $post_id The website post ID
     * @param array $ssl_result SSL check result
     * @return void
     */
    private function send_ssl_expiring_notification($post_id, $ssl_result)
    {
        // Check if notification was already sent recently (within 24 hours)
        $last_ssl_notification = get_post_meta($post_id, '_mmwm_ssl_last_notification', true);

        if ($last_ssl_notification && (time() - $last_ssl_notification) < 86400) {
            return; // Don't spam notifications
        }

        $url = get_post_meta($post_id, '_mmwm_target_url', true);
        $title = get_the_title($post_id);
        $email_to = get_post_meta($post_id, '_mmwm_notification_email', true);

        if (empty($email_to)) {
            $email_to = get_option('mmwm_default_email', get_option('admin_email'));
        }

        $days_left = $ssl_result['days_until_expiry'];
        $expiry_date = $ssl_result['expiry_date'];

        $subject = "SSL Certificate Expiring Soon: {$title}";

        $body = "SSL Certificate Expiring Warning:\n\n";
        $body .= "Website: {$title} ({$url})\n";
        $body .= "SSL Certificate expires in: {$days_left} days\n";
        $body .= "Expiry Date: {$expiry_date}\n";
        $body .= "Issuer: " . ($ssl_result['issuer'] ?? 'Unknown') . "\n\n";
        $body .= "Please renew your SSL certificate before it expires to avoid website downtime.\n";

        if (function_exists('wp_mail')) {
            $sent = wp_mail($email_to, $subject, $body);
            if ($sent) {
                update_post_meta($post_id, '_mmwm_ssl_last_notification', time());
            }
        }
    }
}
