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
        // Use lazy loading to avoid dependency issues
        add_action('mmwm_check_domain_expiry', array($this, 'check_domain_expiry'));
        add_action('wp_ajax_mmwm_run_check_now', array($this, 'handle_ajax_run_check_now'));
        add_action('wp_ajax_mmwm_enable_domain_monitoring', array($this, 'handle_ajax_enable_domain_monitoring'));
        add_action('mmwm_domain_expiry_notification', array($this, 'send_domain_expiring_notification_by_id'));
    }

    /**
     * Get scheduler instance (lazy loading)
     *
     * @return MMWM_Scheduler
     */
    private function get_scheduler()
    {
        if (!$this->scheduler) {
            $this->scheduler = new MMWM_Scheduler();
        }
        return $this->scheduler;
    }

    public function add_cron_intervals($schedules)
    {
        // Add new minute-based intervals
        $schedules['every_one_minute'] = array(
            'interval' => 60,
            'display'  => esc_html__('Every 1 Minute'),
        );
        $schedules['every_three_minutes'] = array(
            'interval' => 180,
            'display'  => esc_html__('Every 3 Minutes'),
        );
        $schedules['every_five_minutes'] = array(
            'interval' => 300,
            'display'  => esc_html__('Every 5 Minutes'),
        );
        $schedules['every_seven_minutes'] = array(
            'interval' => 420,
            'display'  => esc_html__('Every 7 Minutes'),
        );
        $schedules['every_ten_minutes'] = array(
            'interval' => 600,
            'display'  => esc_html__('Every 10 Minutes'),
        );
        $schedules['every_fifteen_minutes'] = array(
            'interval' => 900,
            'display'  => esc_html__('Every 15 Minutes'),
        );
        $schedules['every_twenty_five_minutes'] = array(
            'interval' => 1500,
            'display'  => esc_html__('Every 25 Minutes'),
        );
        $schedules['every_thirty_minutes'] = array(
            'interval' => 1800,
            'display'  => esc_html__('Every 30 Minutes'),
        );
        $schedules['every_forty_five_minutes'] = array(
            'interval' => 2700,
            'display'  => esc_html__('Every 45 Minutes'),
        );
        $schedules['every_sixty_minutes'] = array(
            'interval' => 3600,
            'display'  => esc_html__('Every 60 Minutes'),
        );
        $schedules['daily'] = array(
            'interval' => 86400, // 24 hours
            'display'  => esc_html__('Once Daily'),
        );
        return $schedules;
    }

    /**
     * Schedule daily global monitoring check
     */
    public function schedule_daily_global_check()
    {
        // Only schedule if global check is enabled
        if (!get_option('mmwm_global_check_enabled', 0)) {
            // Unschedule if disabled
            wp_clear_scheduled_hook('mmwm_daily_global_check_event');
            return;
        }

        if (!wp_next_scheduled('mmwm_daily_global_check_event')) {
            $frequency = get_option('mmwm_global_check_frequency', 3);

            if ($frequency === 'daily') {
                $hour = get_option('mmwm_global_cron_hour', 2);
                // Calculate next occurrence at specified hour
                $today = date('Y-m-d');
                $target_time = strtotime($today . ' ' . sprintf('%02d:00:00', $hour));

                // If target time has already passed today, schedule for tomorrow
                if ($target_time <= time()) {
                    $target_time = strtotime('+1 day', $target_time);
                }

                wp_schedule_event($target_time, 'daily', 'mmwm_daily_global_check_event');
            } else {
                // Schedule based on frequency (3, 7, 14 days)
                $interval = intval($frequency) * 86400; // Convert days to seconds
                $next_run = time() + $interval;
                wp_schedule_single_event($next_run, 'mmwm_daily_global_check_event');
            }
        }
    }

    /**
     * Run daily global monitoring check with sequential processing
     */
    public function run_daily_global_check()
    {
        // Check if global check is enabled
        if (!get_option('mmwm_global_check_enabled', 0)) {
            return;
        }

        // Get all websites with monitoring enabled
        $websites = get_posts(array(
            'post_type' => 'mmwm_website',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_mmwm_monitoring_status',
                    'value' => 'active',
                    'compare' => '='
                )
            )
        ));

        if (empty($websites)) {
            return;
        }

        // Process websites sequentially with 15-second delays
        foreach ($websites as $index => $website) {
            // Schedule each check with progressive delay
            $delay = $index * 15; // 15 seconds between each check
            wp_schedule_single_event(time() + $delay, 'mmwm_sequential_global_check', array($website->ID));
        }

        // Reschedule next global check based on frequency
        $this->reschedule_next_global_check();
    }

    /**
     * Handle sequential global check for individual website
     */
    public function handle_sequential_global_check($post_id)
    {
        $this->check_ssl_expiration($post_id);
        $this->check_domain_expiration_global($post_id);

        // Log global check
        update_post_meta($post_id, '_mmwm_last_global_check', time());
    }

    /**
     * Reschedule next global check based on frequency setting
     */
    private function reschedule_next_global_check()
    {
        $frequency = get_option('mmwm_global_check_frequency', 3);

        if ($frequency !== 'daily') {
            // For non-daily frequency, schedule the next occurrence
            $interval = intval($frequency) * 86400; // Convert days to seconds
            $next_run = time() + $interval;
            wp_schedule_single_event($next_run, 'mmwm_daily_global_check_event');
        }
        // For daily frequency, WordPress cron will handle the recurring schedule
    }

    /**
     * Check SSL expiration for daily global check
     */
    private function check_ssl_expiration($post_id)
    {
        $ssl_checker = new MMWM_SSL_Checker();
        $url = get_post_meta($post_id, '_mmwm_target_url', true);

        if (!$url) {
            return;
        }

        $ssl_result = $ssl_checker->check_ssl($url);

        // Update SSL data
        update_post_meta($post_id, '_mmwm_ssl_last_check', time());

        if ($ssl_result['is_active']) {
            update_post_meta($post_id, '_mmwm_ssl_is_active', '1');
            update_post_meta($post_id, '_mmwm_ssl_expiry_date', $ssl_result['expiry_date']);
            update_post_meta($post_id, '_mmwm_ssl_days_until_expiry', $ssl_result['days_until_expiry']);
            update_post_meta($post_id, '_mmwm_ssl_error', '');

            // Send notification if expiring soon (10 days)
            if ($ssl_result['days_until_expiry'] <= 10) {
                $this->send_ssl_expiring_notification($post_id, $ssl_result);
            }
        } else {
            update_post_meta($post_id, '_mmwm_ssl_is_active', '0');
            update_post_meta($post_id, '_mmwm_ssl_error', $ssl_result['error']);
        }
    }

    /**
     * Check domain expiration for daily global check
     */
    private function check_domain_expiration_global($post_id)
    {
        $domain_monitoring_enabled = get_post_meta($post_id, '_mmwm_domain_monitoring_enabled', true);
        $manual_override = get_post_meta($post_id, '_mmwm_domain_manual_override', true);

        if ($domain_monitoring_enabled !== '1') {
            return;
        }

        // If manual override, check if we need to send notification
        if ($manual_override) {
            $domain_expiry_date = get_post_meta($post_id, '_mmwm_domain_expiry_date', true);
            if ($domain_expiry_date) {
                $days_diff = floor((strtotime($domain_expiry_date) - time()) / (60 * 60 * 24));
                update_post_meta($post_id, '_mmwm_domain_days_until_expiry', $days_diff);

                // Send notification if expiring in 10 days
                if ($days_diff <= 10 && $days_diff > 0) {
                    $this->send_domain_expiring_notification($post_id, array(
                        'days_until_expiry' => $days_diff,
                        'expiry_date' => $domain_expiry_date,
                        'root_domain' => parse_url(get_post_meta($post_id, '_mmwm_target_url', true), PHP_URL_HOST)
                    ));
                }
            }
            return;
        }

        // Auto-check domain if not manual override
        $url = get_post_meta($post_id, '_mmwm_target_url', true);
        if (!$url) {
            return;
        }

        $domain_checker = new MMWM_Domain_Checker();
        $result = $domain_checker->check_domain_expiration($url);

        update_post_meta($post_id, '_mmwm_domain_last_check', time());

        if ($result['success']) {
            update_post_meta($post_id, '_mmwm_domain_expiry_date', $result['expiry_date']);
            update_post_meta($post_id, '_mmwm_domain_days_until_expiry', $result['days_until_expiry']);
            update_post_meta($post_id, '_mmwm_domain_error', '');

            // Send notification if expiring soon (10 days)
            if ($result['days_until_expiry'] <= 10) {
                $this->send_domain_expiring_notification($post_id, $result);
            }
        } else {
            update_post_meta($post_id, '_mmwm_domain_error', $result['error']);
        }
    }

    /**
     * Send SSL expiring notification
     */
    private function send_ssl_expiring_notification($post_id, $ssl_result)
    {
        // Check if notification was already sent recently (within 24 hours)
        $last_ssl_notification = get_post_meta($post_id, '_mmwm_ssl_last_notification', true);

        if ($last_ssl_notification && (time() - $last_ssl_notification) < 86400) {
            return; // Don't spam notifications
        }

        $title = get_the_title($post_id);
        $email_to = get_post_meta($post_id, '_mmwm_notification_email', true);

        if (empty($email_to)) {
            $email_to = get_option('mmwm_default_email', get_option('admin_email'));
        }

        $subject = "🔒 SSL Certificate Expiring Soon: {$title}";

        // Use unified email template
        $email_data = [
            'title' => $title,
            'url' => get_post_meta($post_id, '_mmwm_target_url', true),
            'days_left' => $ssl_result['days_until_expiry'],
            'expiry_date' => $ssl_result['expiry_date']
        ];

        $body = MMWM_Email_Template::build_ssl_expiring_notification($email_data);
        $headers = MMWM_Email_Template::get_html_headers();

        if (function_exists('wp_mail')) {
            $sent = wp_mail($email_to, $subject, $body, $headers);
            if ($sent) {
                update_post_meta($post_id, '_mmwm_ssl_last_notification', time());
            }
        }
    }

    /**
     * Send domain expiring notification
     */
    private function send_domain_expiring_notification($post_id, $domain_result)
    {
        // Check if notification was already sent recently (within 24 hours)
        $last_domain_notification = get_post_meta($post_id, '_mmwm_domain_last_notification', true);

        if ($last_domain_notification && (time() - $last_domain_notification) < 86400) {
            return; // Don't spam notifications
        }

        $title = get_the_title($post_id);
        $email_to = get_post_meta($post_id, '_mmwm_notification_email', true);

        if (empty($email_to)) {
            $email_to = get_option('mmwm_default_email', get_option('admin_email'));
        }

        $subject = "🌐 Domain Registration Expiring Soon: {$title}";

        // Use unified email template
        $email_data = [
            'title' => $title,
            'domain' => $domain_result['root_domain'] ?? parse_url(get_post_meta($post_id, '_mmwm_target_url', true), PHP_URL_HOST),
            'days_left' => $domain_result['days_until_expiry'],
            'expiry_date' => $domain_result['expiry_date'],
            'registrar' => $domain_result['registrar'] ?? 'Unknown'
        ];

        $body = MMWM_Email_Template::build_domain_expiring_notification($email_data);
        $headers = MMWM_Email_Template::get_html_headers();

        if (function_exists('wp_mail')) {
            $sent = wp_mail($email_to, $subject, $body, $headers);
            if ($sent) {
                update_post_meta($post_id, '_mmwm_domain_last_notification', time());
            }
        }
    }
    
    /**
     * Send domain expiring notification by post ID (for scheduled notifications)
     */
    public function send_domain_expiring_notification_by_id($post_id)
    {
        // Check if domain monitoring is still enabled
        $domain_monitoring_enabled = get_post_meta($post_id, '_mmwm_domain_monitoring_enabled', true);
        if ($domain_monitoring_enabled !== '1') {
            return;
        }
        
        // Get domain expiry data
        $domain_expiry_date = get_post_meta($post_id, '_mmwm_domain_expiry_date', true);
        $days_until_expiry = get_post_meta($post_id, '_mmwm_domain_days_until_expiry', true);
        $url = get_post_meta($post_id, '_mmwm_target_url', true);
        $root_domain = parse_url($url, PHP_URL_HOST);
        $registrar = get_post_meta($post_id, '_mmwm_domain_registrar', true) ?: 'Unknown';
        
        // Prepare domain result data
        $domain_result = array(
            'root_domain' => $root_domain,
            'days_until_expiry' => $days_until_expiry,
            'expiry_date' => $domain_expiry_date,
            'registrar' => $registrar
        );
        
        // Send notification
        $this->send_domain_expiring_notification($post_id, $domain_result);
    }

    public function run_checks()
    {
        // Use the new scheduler for better performance and modularity
        return $this->get_scheduler()->schedule_checks();
    }

    public function perform_check($post_id)
    {
        // Use the new checker for better performance and modularity
        $checker = new MMWM_Checker();
        $result = $checker->perform_check($post_id);

        // Update status in database (maintain compatibility with existing system)
        if (is_array($result)) {
            $this->update_status($post_id, $result['status'], $result['reason']);
        }

        return $result;
    }

    private function update_status($post_id, $new_status, $reason = '')
    {
        $old_status = get_post_meta($post_id, '_mmwm_status', true);

        update_post_meta($post_id, '_mmwm_last_check', time());
        update_post_meta($post_id, '_mmwm_status', $new_status);
        update_post_meta($post_id, '_mmwm_status_reason', $reason);

        // Skip email notification if WordPress functions not available
        if (!function_exists('get_option')) {
            update_post_meta($post_id, '_mmwm_email_log', 'Email skipped (WordPress functions not available)');
            return;
        }

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
                $email_log = 'Failed to send email report.';
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

        $subject = sprintf('🔍 Monitoring Report for %s: %s', get_the_title($post_id), $new_status);

        // Use unified email template
        $email_data = [
            'title' => get_the_title($post_id),
            'url' => $url,
            'old_status' => $old_status,
            'new_status' => $new_status,
            'reason' => $reason,
            'host_in' => $host_in
        ];

        $body = MMWM_Email_Template::build_monitoring_report($email_data);
        $headers = MMWM_Email_Template::get_html_headers();

        return wp_mail($email_to, $subject, $body, $headers);
    }

    private function find_element_in_html($html, $selector)
    {
        return MMWM_HTML_Parser::find_element($html, $selector);
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
    
    /**
     * Handle AJAX request to enable domain monitoring
     */
    public function handle_ajax_enable_domain_monitoring()
    {
        // Verify nonce
        check_ajax_referer('mmwm_enable_domain_monitoring_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('edit_posts')) {
            wp_send_json_error(['message' => __('Permission denied.', 'mm-web-monitoring')]);
            return;
        }
        
        // Get post ID
        if (!isset($_POST['post_id'])) {
            wp_send_json_error(['message' => __('Invalid Post ID.', 'mm-web-monitoring')]);
            return;
        }
        
        $post_id = intval($_POST['post_id']);
        $url = get_post_meta($post_id, '_mmwm_target_url', true);
        
        if (empty($url)) {
            wp_send_json_error(['message' => __('No URL found for this website.', 'mm-web-monitoring')]);
            return;
        }
        
        // Check domain expiration
        $domain_checker = new MMWM_Domain_Checker();
        $result = $domain_checker->check_domain_expiration($url);
        
        // Update last check time
        update_post_meta($post_id, '_mmwm_domain_last_check', time());
        update_post_meta($post_id, '_mmwm_domain_monitoring_status', 'active');
        
        if ($result['success']) {
            // Save domain expiration data
            update_post_meta($post_id, '_mmwm_domain_expiry_date', $result['expiry_date']);
            update_post_meta($post_id, '_mmwm_domain_days_until_expiry', $result['days_until_expiry']);
            update_post_meta($post_id, '_mmwm_domain_error', '');
            update_post_meta($post_id, '_mmwm_domain_manual_override', '0');
            
            // Schedule cron job for 30 days before expiry
            $expiry_timestamp = strtotime($result['expiry_date']);
            $notification_timestamp = $expiry_timestamp - (30 * 86400); // 30 days before expiry
            
            // Only schedule if notification date is in the future
            if ($notification_timestamp > time()) {
                wp_schedule_single_event($notification_timestamp, 'mmwm_domain_expiry_notification', array($post_id));
            }
            
            // Send success response
            wp_send_json_success([
                'message' => __('Domain expiry monitoring is enabled.', 'mm-web-monitoring'),
                'expiry_date' => date_i18n(get_option('date_format'), strtotime($result['expiry_date'])),
                'days_until_expiry' => $result['days_until_expiry'],
                'need_manual_input' => false
            ]);
        } else {
            // Save error and request manual input
            update_post_meta($post_id, '_mmwm_domain_error', $result['error']);
            
            // Extract root domain for WHOIS link
            $root_domain = isset($result['root_domain']) ? $result['root_domain'] : parse_url($url, PHP_URL_HOST);
            $root_domain = preg_replace('/^www\./', '', $root_domain); // Remove www if present
            
            // Generate WHOIS link
            $whois_link = 'https://www.whois.com/whois/' . $root_domain;
            
            wp_send_json_success([
                'message' => __('Could not automatically detect domain expiry date. Please enter it manually.', 'mm-web-monitoring'),
                'need_manual_input' => true,
                'whois_link' => $whois_link,
                'root_domain' => $root_domain
            ]);
        }
    }
}
