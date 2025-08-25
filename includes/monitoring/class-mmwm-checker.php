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
                'Referer'         => home_url(),
            ],
        ];

        $response = wp_remote_get($config['url'], $args);

        if (is_wp_error($response)) {
            return [
                'status' => 'DOWN',
                'reason' => 'Request Error: ' . $response->get_error_message(),
                'timestamp' => time()
            ];
        }

        $response_code = wp_remote_retrieve_response_code($response);

        if ($response_code >= 400) {
            return [
                'status' => 'DOWN',
                'reason' => "HTTP Error: {$response_code}",
                'timestamp' => time()
            ];
        }

        // Check for HTML element if required
        if ($config['check_type'] === 'fetch_html' && !empty($config['html_selector'])) {
            $body = wp_remote_retrieve_body($response);

            if (!MMWM_HTML_Parser::find_element($body, $config['html_selector'])) {
                return [
                    'status' => 'CONTENT_ERROR',
                    'reason' => "HTML Check: Element '{$config['html_selector']}' not found",
                    'timestamp' => time()
                ];
            }
        }

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
}
