<?php

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

class MMWM_Cron
{

    public function add_cron_intervals($schedules)
    {
        $schedules['every_five_minutes'] = array(
            'interval' => 300,
            'display'  => esc_html__('Every 5 Minutes'),
        );
        return $schedules;
    }

    public function run_checks()
    {
        $args = array(
            'post_type'      => 'mmwm_website',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => array(
                array(
                    'key'     => '_mmwm_monitoring_status',
                    'value'   => 'active',
                    'compare' => '=',
                ),
            ),
        );

        $websites = new WP_Query($args);

        if ($websites->have_posts()) {
            while ($websites->have_posts()) {
                $websites->the_post();
                $post_id = get_the_ID();
                $interval = get_post_meta($post_id, '_mmwm_interval', true);
                $last_check = get_post_meta($post_id, '_mmwm_last_check', true);

                if (empty($last_check) || (time() - $last_check) >= ($interval * 60)) {
                    $this->perform_check($post_id);
                }
            }
        }
        wp_reset_postdata();
    }

    public function perform_check($post_id)
    {
        $url = get_post_meta($post_id, '_mmwm_target_url', true);
        $check_type = get_post_meta($post_id, '_mmwm_check_type', true);

        $args = [
            'timeout'     => 20,
            'sslverify'   => false,
            'user-agent'  => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36',
            'headers'     => [
                'Accept'          => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.7',
                'Accept-Language' => 'en-US,en;q=0.9',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Cache-Control'   => 'no-cache',
                'Pragma'          => 'no-cache',
            ],
        ];

        $response = wp_remote_get($url, $args);
        $new_status = '';
        $reason = '';

        if (is_wp_error($response)) {
            $new_status = 'DOWN';
            $reason = 'Request Error: ' . $response->get_error_message();
            $this->update_status($post_id, $new_status, $reason);
            return;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code >= 400) {
            $new_status = 'DOWN';
            $reason = "HTTP Error: {$response_code}";
            $this->update_status($post_id, $new_status, $reason);
            return;
        }

        if ($check_type === 'fetch_html') {
            $html_selector = get_post_meta($post_id, '_mmwm_html_selector', true);
            if (! empty($html_selector)) {
                $body = wp_remote_retrieve_body($response);
                if (! $this->find_element_in_html($body, $html_selector)) {
                    $new_status = 'CONTENT_ERROR';
                    $reason = "HTML Check: Element '{$html_selector}' not found.";
                    $this->update_status($post_id, $new_status, $reason);
                    return;
                }
            }
        }

        $new_status = 'UP';
        $reason = "HTTP {$response_code} OK";
        if ($check_type === 'fetch_html' && !empty($html_selector)) {
            $reason .= ' (HTML element found)';
        }

        $this->update_status($post_id, $new_status, $reason);
    }

    private function update_status($post_id, $new_status, $reason = '')
    {
        $old_status = get_post_meta($post_id, '_mmwm_status', true);

        update_post_meta($post_id, '_mmwm_last_check', time());
        update_post_meta($post_id, '_mmwm_status', $new_status);
        update_post_meta($post_id, '_mmwm_status_reason', $reason);

        $notification_trigger = get_post_meta($post_id, '_mmwm_notification_trigger', true) ?: 'always';
        $should_send = false;

        if ($notification_trigger === 'always') {
            $should_send = true;
        } elseif ($notification_trigger === 'when_error_only') {
            $is_new_status_error = in_array($new_status, ['DOWN', 'CONTENT_ERROR', 'Invalid URL']);
            $was_old_status_error = in_array($old_status, ['DOWN', 'CONTENT_ERROR', 'Invalid URL']);

            if ($is_new_status_error || ($was_old_status_error && $new_status === 'UP')) {
                $should_send = true;
            }
        }

        $email_log = 'Email not sent (conditions not met).';
        if ($should_send) {
            $email_sent = $this->send_notification_email($post_id, $old_status, $new_status, $reason);

            if ($email_sent) {
                $email_log = 'Email report sent successfully.';
            } else {
                $email_log = 'Error: Failed to send email. Check WP Mail SMTP logs.';
            }
        }

        update_post_meta($post_id, '_mmwm_email_log', $email_log);
    }

    private function send_notification_email($post_id, $old_status, $new_status, $reason)
    {
        $url = get_post_meta($post_id, '_mmwm_target_url', true);
        $email_to = get_post_meta($post_id, '_mmwm_notification_email', true);

        if (empty($email_to)) {
            $email_to = get_option('mmwm_default_email', get_option('admin_email'));
        }

        // --- PERUBAHAN UTAMA DI SINI ---
        $host_in = get_post_meta($post_id, '_mmwm_host_in', true) ?: 'Not specified';
        // --- AKHIR PERUBAHAN ---

        $subject = sprintf('Monitoring Report for %s: %s', get_the_title($post_id), $new_status);

        $body  = "Monitoring Report:\n\n";
        $body .= "Website: " . get_the_title($post_id) . " (" . $url . ")\n";
        $body .= "Previous Status: " . ($old_status ?: 'N/A') . "\n";
        $body .= "Current Status: " . $new_status . "\n";
        $body .= "Check Time: " . wp_date('Y-m-d H:i:s', time()) . "\n";
        $body .= "Reason/Details: " . $reason . "\n";
        // --- PERUBAHAN UTAMA DI SINI ---
        $body .= "Host in: " . $host_in . "\n\n";
        // --- AKHIR PERUBAHAN ---

        return wp_mail($email_to, $subject, $body);
    }

    private function find_element_in_html($html, $selector)
    {
        if (empty($html) || empty($selector)) return false;
        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        @$dom->loadHTML($html);
        libxml_clear_errors();
        $xpath = new DOMXPath($dom);
        $query = '';
        $first_char = substr($selector, 0, 1);
        $element_name = substr($selector, 1);
        if ($first_char === '#') {
            $query = "//*[@id='" . $element_name . "']";
        } elseif ($first_char === '.') {
            $query = "//*[contains(concat(' ', normalize-space(@class), ' '), ' " . $element_name . " ')]";
        } else {
            return strpos($html, $selector) !== false;
        }
        $results = $xpath->query($query);
        return $results->length > 0;
    }

    public function handle_ajax_run_check_now()
    {
        check_ajax_referer('mmwm_ajax_nonce');
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied.']);
            return;
        }

        if (isset($_POST['post_id'])) {
            $post_id = intval($_POST['post_id']);
            $this->perform_check($post_id);

            wp_send_json_success([
                'status' => get_post_meta($post_id, '_mmwm_status', true),
                'email_log' => get_post_meta($post_id, '_mmwm_email_log', true),
            ]);
        }
        wp_send_json_error(['message' => 'Invalid Post ID.']);
    }

    public function handle_ajax_update_monitoring_status()
    {
        check_ajax_referer('mmwm_ajax_nonce');
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied.']);
            return;
        }
        if (isset($_POST['post_id']) && isset($_POST['new_status'])) {
            $post_id = intval($_POST['post_id']);
            $new_status = sanitize_text_field($_POST['new_status']);
            $allowed_statuses = ['active', 'paused', 'stopped'];

            if (in_array($new_status, $allowed_statuses)) {
                update_post_meta($post_id, '_mmwm_monitoring_status', $new_status);
                wp_send_json_success();
            }
        }
        wp_send_json_error(['message' => 'Invalid data.']);
    }
}
