<?php

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

class MMWM_Cron
{
    /**
     * Scheduler instance
     *
     * @var MMWM_Scheduler
     */
    private $scheduler;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->scheduler = new MMWM_Scheduler();
    }

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
        // Use the new scheduler for better performance and modularity
        return $this->scheduler->schedule_checks();
    }

    public function perform_check($post_id)
    {
        // Use the new checker for better performance and modularity
        $checker = new MMWM_Checker();
        return $checker->perform_check($post_id);
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

        $host_in = get_post_meta($post_id, '_mmwm_host_in', true) ?: 'Not specified';

        $subject = sprintf('Monitoring Report for %s: %s', get_the_title($post_id), $new_status);

        $body  = "Monitoring Report:\n\n";
        $body .= "Website: " . get_the_title($post_id) . " (" . $url . ")\n";
        $body .= "Previous Status: " . ($old_status ?: 'N/A') . "\n";
        $body .= "Current Status: " . $new_status . "\n";
        $body .= "Check Time: " . wp_date('Y-m-d H:i:s', time()) . "\n";
        $body .= "Reason/Details: " . $reason . "\n";
        $body .= "Host in: " . $host_in . "\n\n";

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
