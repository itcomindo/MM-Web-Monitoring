<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Email template system for MM Web Monitoring
 */
class MMWM_Email_Template
{
    /**
     * Get email headers for HTML emails
     *
     * @return array
     */
    public static function get_html_headers()
    {
        return array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . get_option('admin_email') . '>'
        );
    }

    /**
     * Build monitoring report email
     *
     * @param array $data Email data
     * @return string HTML email content
     */
    public static function build_monitoring_report($data)
    {
        $status_color = self::get_status_color($data['new_status']);
        $status_emoji = self::get_status_emoji($data['new_status']);
        $check_time = wp_date('Y-m-d H:i:s T', time());

        ob_start();
?>
        <?php echo self::get_email_header(); ?>

        <div class="container">
            <div class="header">
                <h2><?php echo $status_emoji; ?> Website Monitoring Report</h2>
                <p><strong>Website:</strong> <?php echo esc_html($data['title']); ?></p>
                <p><strong>URL:</strong> <a href="<?php echo esc_url($data['url']); ?>"><?php echo esc_html($data['url']); ?></a></p>
            </div>

            <div class="details">
                <h3>Status Change:</h3>
                <p>
                    <?php if (!empty($data['old_status'])): ?>
                        Previous: <span class="status-badge" style="background-color: <?php echo self::get_status_color($data['old_status']); ?>;"><?php echo esc_html($data['old_status']); ?></span>
                        →
                    <?php endif; ?>
                    Current: <span class="status-badge" style="background-color: <?php echo esc_attr($status_color); ?>;"><?php echo esc_html($data['new_status']); ?></span>
                </p>

                <p><strong>Check Time:</strong> <?php echo esc_html($check_time); ?></p>
                <p><strong>Details:</strong> <?php echo esc_html($data['reason']); ?></p>
                <?php if (!empty($data['host_in'])): ?>
                    <p><strong>Hosting Provider:</strong> <?php echo esc_html($data['host_in']); ?></p>
                <?php endif; ?>
            </div>

            <?php echo self::get_email_footer(); ?>
        </div>

        <?php echo self::get_email_closing(); ?>
    <?php
        return ob_get_clean();
    }

    /**
     * Build SSL expiring notification email
     *
     * @param array $data SSL data
     * @return string HTML email content
     */
    public static function build_ssl_expiring_notification($data)
    {
        $check_time = wp_date('Y-m-d H:i:s T', time());
        $is_critical = $data['days_left'] <= 3;
        $warning_color = $is_critical ? '#dc3545' : '#fd7e14';

        ob_start();
    ?>
        <?php echo self::get_email_header(); ?>

        <div class="container">
            <div class="header" style="background-color: <?php echo $warning_color; ?>; color: white;">
                <h2>🔒 SSL Certificate Expiring Warning</h2>
                <p><strong>Website:</strong> <?php echo esc_html($data['title']); ?></p>
                <p><strong>URL:</strong> <a href="<?php echo esc_url($data['url']); ?>" style="color: white;"><?php echo esc_html($data['url']); ?></a></p>
            </div>

            <div class="details">
                <h3>SSL Certificate Information:</h3>
                <p><strong>Expires in:</strong> <span style="color: <?php echo $warning_color; ?>; font-weight: bold;"><?php echo esc_html($data['days_left']); ?> days</span></p>
                <p><strong>Expiry Date:</strong> <?php echo esc_html($data['expiry_date']); ?></p>
                <p><strong>Issuer:</strong> <?php echo esc_html($data['issuer'] ?? 'Unknown'); ?></p>
                <p><strong>Check Time:</strong> <?php echo esc_html($check_time); ?></p>

                <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin-top: 15px;">
                    <p><strong>⚠️ Action Required:</strong></p>
                    <p>Please renew your SSL certificate before it expires to avoid website downtime and security warnings for your visitors.</p>
                </div>
            </div>

            <?php echo self::get_email_footer(); ?>
        </div>

        <?php echo self::get_email_closing(); ?>
    <?php
        return ob_get_clean();
    }

    /**
     * Build domain expiring notification email
     *
     * @param array $data Domain data
     * @return string HTML email content
     */
    public static function build_domain_expiring_notification($data)
    {
        $check_time = wp_date('Y-m-d H:i:s T', time());
        $is_critical = $data['days_left'] <= 3;
        $warning_color = $is_critical ? '#dc3545' : '#fd7e14';

        ob_start();
    ?>
        <?php echo self::get_email_header(); ?>

        <div class="container">
            <div class="header" style="background-color: <?php echo $warning_color; ?>; color: white;">
                <h2>🌐 Domain Registration Expiring Warning</h2>
                <p><strong>Website:</strong> <?php echo esc_html($data['title']); ?></p>
                <p><strong>Domain:</strong> <?php echo esc_html($data['domain']); ?></p>
            </div>

            <div class="details">
                <h3>Domain Registration Information:</h3>
                <p><strong>Expires in:</strong> <span style="color: <?php echo $warning_color; ?>; font-weight: bold;"><?php echo esc_html($data['days_left']); ?> days</span></p>
                <p><strong>Expiry Date:</strong> <?php echo esc_html($data['expiry_date']); ?></p>
                <?php if (!empty($data['registrar'])): ?>
                    <p><strong>Registrar:</strong> <?php echo esc_html($data['registrar']); ?></p>
                <?php endif; ?>
                <p><strong>Check Time:</strong> <?php echo esc_html($check_time); ?></p>

                <div style="background-color: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin-top: 15px;">
                    <p><strong>🚨 Critical Action Required:</strong></p>
                    <p>Please renew your domain registration immediately to prevent your website from going offline. Domain expiration can result in complete website inaccessibility.</p>
                </div>
            </div>

            <?php echo self::get_email_footer(); ?>
        </div>

        <?php echo self::get_email_closing(); ?>
    <?php
        return ob_get_clean();
    }

    /**
     * Get email header with styles
     *
     * @return string
     */
    private static function get_email_header()
    {
        ob_start();
    ?>
        <!DOCTYPE html>
        <html>

        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>MM Web Monitoring Report</title>
            <style>
                body {
                    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
                    line-height: 1.6;
                    color: #333;
                    margin: 0;
                    padding: 0;
                    background-color: #f4f4f4;
                }

                .container {
                    max-width: 600px;
                    margin: 20px auto;
                    background: white;
                    border-radius: 8px;
                    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                    overflow: hidden;
                }

                .header {
                    background: #f8f9fa;
                    padding: 20px;
                    border-bottom: 3px solid #0073aa;
                }

                .header h2 {
                    margin: 0 0 10px 0;
                    color: #2c3e50;
                }

                .header p {
                    margin: 5px 0;
                    color: #555;
                }

                .header a {
                    color: #0073aa;
                    text-decoration: none;
                }

                .details {
                    padding: 20px;
                }

                .details h3 {
                    margin-top: 0;
                    color: #2c3e50;
                    border-bottom: 2px solid #eee;
                    padding-bottom: 10px;
                }

                .status-badge {
                    display: inline-block;
                    padding: 6px 12px;
                    border-radius: 4px;
                    color: white;
                    font-weight: bold;
                    font-size: 12px;
                    text-transform: uppercase;
                }

                .footer {
                    background: #f8f9fa;
                    padding: 15px 20px;
                    border-top: 1px solid #dee2e6;
                    font-size: 12px;
                    color: #6c757d;
                }

                .footer a {
                    color: #0073aa;
                    text-decoration: none;
                }

                @media only screen and (max-width: 600px) {
                    .container {
                        margin: 10px;
                        border-radius: 0;
                    }

                    .header,
                    .details,
                    .footer {
                        padding: 15px;
                    }
                }
            </style>
        </head>

        <body>
        <?php
        return ob_get_clean();
    }

    /**
     * Get email footer
     *
     * @return string
     */
    private static function get_email_footer()
    {
        ob_start();
        ?>
            <div class="footer">
                <p><strong>MM Web Monitoring</strong> - Automated website monitoring service</p>
                <p>This email was sent by <strong><?php echo esc_html(get_bloginfo('name')); ?></strong></p>
                <p>You can manage your monitoring settings in the <a href="<?php echo esc_url(admin_url('edit.php?post_type=mmwm_website')); ?>">WordPress admin panel</a>.</p>
            </div>
    <?php
        return ob_get_clean();
    }

    /**
     * Get email closing tags
     *
     * @return string
     */
    private static function get_email_closing()
    {
        return '</body></html>';
    }

    /**
     * Get status color for display
     *
     * @param string $status Status code
     * @return string Hex color code
     */
    public static function get_status_color($status)
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
    public static function get_status_emoji($status)
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
