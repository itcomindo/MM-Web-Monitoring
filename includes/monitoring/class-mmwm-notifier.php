<?php

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Notification handler class implementing notifier interface
 */
class MMWM_Notifier implements MMWM_Notifier_Interface
{
    /**
     * Send notification email
     *
     * @param int $post_id Website post ID
     * @param string $old_status Previous status
     * @param string $new_status Current status
     * @param string $reason Check details/reason
     * @return bool True if sent successfully, false otherwise
     */
    public function send_notification($post_id, $old_status, $new_status, $reason)
    {
        $email_to = $this->get_notification_email($post_id);

        if (!MMWM_Validator::validate_email($email_to)) {
            error_log('MMWM: Invalid notification email for post ' . $post_id);
            return false;
        }

        $url = get_post_meta($post_id, '_mmwm_target_url', true);
        $host_in = get_post_meta($post_id, '_mmwm_host_in', true) ?: 'Not specified';
        $website_title = get_the_title($post_id);

        $subject = $this->build_email_subject($website_title, $new_status);

        // Use unified email template
        $email_data = [
            'title' => $website_title,
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

    /**
     * Check if notification should be sent based on trigger settings
     *
     * @param string $trigger Notification trigger setting
     * @param string $old_status Previous status
     * @param string $new_status Current status
     * @return bool True if should send, false otherwise
     */
    public function should_send_notification($trigger, $old_status, $new_status)
    {
        if ($trigger === 'always') {
            return true;
        }

        if ($trigger === 'when_error_only') {
            $error_statuses = ['DOWN', 'CONTENT_ERROR', 'INVALID_URL'];
            $is_new_error = in_array($new_status, $error_statuses, true);
            $was_old_error = in_array($old_status, $error_statuses, true);

            // Send on new error or recovery from error
            return $is_new_error || ($was_old_error && $new_status === 'UP');
        }

        return false;
    }

    /**
     * Get notification email address for a website
     *
     * @param int $post_id Website post ID
     * @return string Email address
     */
    public function get_notification_email($post_id)
    {
        $email = get_post_meta($post_id, '_mmwm_notification_email', true);

        if (empty($email)) {
            $email = get_option('mmwm_default_email', get_option('admin_email'));
        }

        return MMWM_Sanitizer::sanitize_email($email);
    }

    /**
     * Build email subject
     *
     * @param string $website_title Website title
     * @param string $status Current status
     * @return string Email subject
     */
    private function build_email_subject($website_title, $status)
    {
        $status_emoji = $this->get_status_emoji($status);
        return sprintf('%s Monitoring Alert: %s - %s', $status_emoji, $website_title, $status);
    }

    /**
     * Build email body
     *
     * @param string $title Website title
     * @param string $url Website URL
     * @param string $old_status Previous status
     * @param string $new_status Current status
     * @param string $reason Check reason
     * @param string $host_in Hosting provider
     * @return string Email body HTML
     */
    private function build_email_body($title, $url, $old_status, $new_status, $reason, $host_in)
    {
        $status_color = $this->get_status_color($new_status);
        $check_time = wp_date('Y-m-d H:i:s T', time());

        ob_start();
?>
        <html>

        <head>
            <meta charset="UTF-8">
            <style>
                body {
                    font-family: Arial, sans-serif;
                    line-height: 1.6;
                    color: #333;
                }

                .container {
                    max-width: 600px;
                    margin: 0 auto;
                    padding: 20px;
                }

                .header {
                    background: #f8f9fa;
                    padding: 15px;
                    border-radius: 5px;
                    margin-bottom: 20px;
                }

                .status-badge {
                    display: inline-block;
                    padding: 5px 10px;
                    border-radius: 3px;
                    color: white;
                    font-weight: bold;
                }

                .details {
                    background: #f8f9fa;
                    padding: 15px;
                    border-radius: 5px;
                }

                .footer {
                    margin-top: 20px;
                    padding-top: 15px;
                    border-top: 1px solid #ddd;
                    font-size: 12px;
                    color: #666;
                }
            </style>
        </head>

        <body>
            <div class="container">
                <div class="header">
                    <h2>🔍 Website Monitoring Report</h2>
                    <p><strong>Website:</strong> <?php echo esc_html($title); ?></p>
                    <p><strong>URL:</strong> <a href="<?php echo esc_url($url); ?>"><?php echo esc_html($url); ?></a></p>
                </div>

                <div class="details">
                    <p><strong>Status Change:</strong></p>
                    <p>
                        <?php if ($old_status): ?>
                            Previous: <span class="status-badge" style="background-color: <?php echo $this->get_status_color($old_status); ?>;"><?php echo esc_html($old_status); ?></span>
                            →
                        <?php endif; ?>
                        Current: <span class="status-badge" style="background-color: <?php echo esc_attr($status_color); ?>;"><?php echo esc_html($new_status); ?></span>
                    </p>

                    <p><strong>Check Time:</strong> <?php echo esc_html($check_time); ?></p>
                    <p><strong>Details:</strong> <?php echo esc_html($reason); ?></p>
                    <p><strong>Hosting Provider:</strong> <?php echo esc_html($host_in); ?></p>
                </div>

                <div class="footer">
                    <p>This email was sent by MM Web Monitoring plugin on <?php echo esc_html(get_bloginfo('name')); ?>.</p>
                    <p>You can manage your monitoring settings in the WordPress admin panel.</p>
                </div>
            </div>
        </body>

        </html>
<?php
        return ob_get_clean();
    }

    /**
     * Get status color for display
     *
     * @param string $status Status code
     * @return string Hex color code
     */
    private function get_status_color($status)
    {
        $colors = [
            'UP' => '#28a745',
            'DOWN' => '#dc3545',
            'CONTENT_ERROR' => '#fd7e14',
            'INVALID_URL' => '#6c757d',
        ];

        return $colors[$status] ?? '#6c757d';
    }

    /**
     * Get status emoji
     *
     * @param string $status Status code
     * @return string Emoji
     */
    private function get_status_emoji($status)
    {
        $emojis = [
            'UP' => '✅',
            'DOWN' => '❌',
            'CONTENT_ERROR' => '⚠️',
            'INVALID_URL' => '🚫',
        ];

        return $emojis[$status] ?? '📊';
    }
}
