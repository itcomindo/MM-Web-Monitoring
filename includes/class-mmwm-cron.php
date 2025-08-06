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
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            $this->update_status($post_id, 'Invalid URL', 'URL tidak valid.');
            return;
        }

        $check_type = get_post_meta($post_id, '_mmwm_check_type', true);
        $args = [
            'timeout'    => 20,
            'user-agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/108.0.0.0 Safari/537.36',
        ];

        $response = wp_remote_get($url, $args);

        if (is_wp_error($response)) {
            $this->update_status($post_id, 'DOWN', $response->get_error_message());
            return;
        }

        $response_code = wp_remote_retrieve_response_code($response);
        if ($response_code >= 400) {
            $this->update_status($post_id, 'DOWN', "Respon HTTP: {$response_code}");
            return;
        }

        if ($check_type === 'fetch_html') {
            $html_selector = get_post_meta($post_id, '_mmwm_html_selector', true);
            if (! empty($html_selector)) {
                $body = wp_remote_retrieve_body($response);
                if (! $this->find_element_in_html($body, $html_selector)) {
                    $this->update_status($post_id, 'CONTENT_ERROR', "Elemen '{$html_selector}' tidak ditemukan.");
                    return;
                }
            }
        }

        $this->update_status($post_id, 'UP', "Respon HTTP: {$response_code}");
    }

    private function update_status($post_id, $new_status, $reason = '')
    {
        $old_status = get_post_meta($post_id, '_mmwm_status', true);

        update_post_meta($post_id, '_mmwm_last_check', time());
        update_post_meta($post_id, '_mmwm_status', $new_status);
        update_post_meta($post_id, '_mmwm_status_reason', $reason);

        // Kirim email hanya jika statusnya berubah
        if ($old_status !== $new_status && !empty($old_status)) {

            $notification_trigger = get_post_meta($post_id, '_mmwm_notification_trigger', true) ?: 'always';

            $should_send = false;

            if ($notification_trigger === 'always') {
                $should_send = true;
            } elseif ($notification_trigger === 'when_error_only') {
                $error_statuses = ['DOWN', 'CONTENT_ERROR', 'Invalid URL'];
                if (in_array($new_status, $error_statuses)) {
                    $should_send = true;
                }
            }

            if ($should_send) {
                $this->send_notification_email($post_id, $old_status, $new_status, $reason);
            }
        }
    }

    private function send_notification_email($post_id, $old_status, $new_status, $reason)
    {
        $url = get_post_meta($post_id, '_mmwm_target_url', true);
        $email_to = get_post_meta($post_id, '_mmwm_notification_email', true) ?: get_option('admin_email');

        $subject = sprintf(__('Status Website Change for %s: %s', 'mm-web-monitoring'), get_the_title($post_id), $new_status);

        $body  = sprintf(__("Halo,\n\nStatus monitor untuk website %s (%s) telah berubah.\n\n", 'mm-web-monitoring'), get_the_title($post_id), $url);
        $body .= sprintf(__("Status Sebelumnya: %s\n", 'mm-web-monitoring'), $old_status);
        $body .= sprintf(__("Status Baru: %s\n", 'mm-web-monitoring'), $new_status);
        $body .= sprintf(__("Waktu Pengecekan: %s\n", 'mm-web-monitoring'), wp_date('Y-m-d H:i:s', time()));
        if (! empty($reason)) {
            $body .= sprintf(__("Detail: %s\n", 'mm-web-monitoring'), $reason);
        }
        $body .= __("\nTerima kasih,\nMM Web Monitoring Plugin", 'mm-web-monitoring');

        wp_mail($email_to, $subject, $body);
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
        if (! current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied.']);
        }
        if (isset($_POST['post_id'])) {
            $post_id = intval($_POST['post_id']);
            $this->perform_check($post_id);

            $new_status = get_post_meta($post_id, '_mmwm_status', true);
            $last_check_timestamp = get_post_meta($post_id, '_mmwm_last_check', true);

            $status_color = '#777';
            if ($new_status === 'UP') $status_color = '#28a745';
            if ($new_status === 'DOWN') $status_color = '#dc3545';
            if ($new_status === 'CONTENT_ERROR') $status_color = '#ffc107';

            $last_check_html = 'N/A';
            if ($last_check_timestamp) {
                $last_check_html = sprintf(
                    '<span title="%s">%s</span>',
                    esc_attr(wp_date('Y-m-d H:i:s', $last_check_timestamp)),
                    esc_html(human_time_diff($last_check_timestamp) . ' ago')
                );
            }

            wp_send_json_success([
                'status'          => $new_status,
                'status_color'    => $status_color,
                'last_check_html' => $last_check_html,
            ]);
        }
        wp_send_json_error(['message' => 'Invalid Post ID.']);
    }

    public function handle_ajax_update_monitoring_status()
    {
        check_ajax_referer('mmwm_ajax_nonce');
        if (! current_user_can('edit_posts')) {
            wp_send_json_error(['message' => 'Permission denied.']);
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
