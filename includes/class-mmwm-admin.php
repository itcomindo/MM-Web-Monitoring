<?php

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

class MMWM_Admin
{

    public function add_global_options_page()
    {
        add_submenu_page(
            'edit.php?post_type=mmwm_website',
            __('Global Options', 'mm-web-monitoring'),
            __('Global Options', 'mm-web-monitoring'),
            'manage_options',
            'mmwm-global-options',
            array($this, 'render_global_options_page')
        );
        add_action('admin_init', array($this, 'register_settings'));
    }

    public function register_settings()
    {
        register_setting('mmwm_global_options', 'mmwm_default_email', array('sanitize_callback' => 'sanitize_email'));
        register_setting('mmwm_global_options', 'mmwm_auto_reload_interval', array('sanitize_callback' => 'intval'));
        register_setting('mmwm_global_options', 'mmwm_global_cron_hour', array('sanitize_callback' => 'intval'));
        register_setting('mmwm_global_options', 'mmwm_global_check_enabled', array('sanitize_callback' => 'intval'));
        register_setting('mmwm_global_options', 'mmwm_global_check_frequency', array('sanitize_callback' => 'intval'));
        register_setting('mmwm_global_options', 'mmwm_custom_user_agent_enabled', array('sanitize_callback' => 'intval'));

        add_settings_section(
            'mmwm_main_settings_section',
            __('Email Settings', 'mm-web-monitoring'),
            null,
            'mmwm-global-options'
        );
        add_settings_field(
            'mmwm_default_email',
            __('Default Notification Email', 'mm-web-monitoring'),
            array($this, 'render_default_email_field'),
            'mmwm-global-options',
            'mmwm_main_settings_section'
        );

        add_settings_section(
            'mmwm_ui_settings_section',
            __('User Interface Settings', 'mm-web-monitoring'),
            null,
            'mmwm-global-options'
        );
        add_settings_field(
            'mmwm_auto_reload_interval',
            __('Auto-Reload Interval (seconds)', 'mm-web-monitoring'),
            array($this, 'render_auto_reload_interval_field'),
            'mmwm-global-options',
            'mmwm_ui_settings_section'
        );

        add_settings_section(
            'mmwm_cron_settings_section',
            __('Global Monitoring Schedule', 'mm-web-monitoring'),
            null,
            'mmwm-global-options'
        );
        add_settings_field(
            'mmwm_global_cron_hour',
            __('Daily Global Check Time', 'mm-web-monitoring'),
            array($this, 'render_global_cron_hour_field'),
            'mmwm-global-options',
            'mmwm_cron_settings_section'
        );
    }

    public function render_auto_reload_interval_field()
    {
        $interval = get_option('mmwm_auto_reload_interval', 30);
        echo '<input type="number" name="mmwm_auto_reload_interval" value="' . esc_attr($interval) . '" min="10" max="300" class="small-text" />';
        echo '<p class="description">' . __('How often (in seconds) the All Websites page should auto-reload. Set between 10-300 seconds. Default: 30 seconds.', 'mm-web-monitoring') . '</p>';
    }

    public function render_global_cron_hour_field()
    {
        $hour = get_option('mmwm_global_cron_hour', 2);
        echo '<select name="mmwm_global_cron_hour">';
        for ($i = 0; $i <= 23; $i++) {
            $display_hour = sprintf('%02d:00', $i);
            echo '<option value="' . esc_attr($i) . '"' . selected($hour, $i, false) . '>' . esc_html($display_hour) . '</option>';
        }
        echo '</select>';
        echo '<p class="description">' . __('What time each day should the global monitoring check run? This will check SSL certificates, domain expiration, and other scheduled tasks. Default: 02:00 (2 AM).', 'mm-web-monitoring') . '</p>';
    }

    public function render_default_email_field()
    {
        $email = get_option('mmwm_default_email', get_option('admin_email'));
        echo '<input type="email" name="mmwm_default_email" value="' . esc_attr($email) . '" class="regular-text" />';
        echo '<p class="description">' . __('This email will be used as a default if a monitoring website does not have a specific email address set.', 'mm-web-monitoring') . '</p>';
    }

    public function render_global_options_page()
    {
        // Enqueue modern styles and scripts
        wp_enqueue_script('mmwm-admin-modern', plugin_dir_url(__FILE__) . '../assets/admin-modern.js', array('jquery'), '1.0.8', true);
        wp_localize_script('mmwm-admin-modern', 'mmwm_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mmwm_admin_nonce')
        ));

        // Get current server info
        $server_ip = $this->get_server_ip();
        $ssl_info = $this->get_site_ssl_info();

?>
        <style>
            .mmwm-modern-container {
                max-width: 1200px;
                margin: 20px 0;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            }

            .mmwm-card {
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                margin-bottom: 20px;
                overflow: hidden;
                border: 1px solid #e1e5e9;
            }

            .mmwm-card-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 20px;
                font-size: 18px;
                font-weight: 600;
            }

            .mmwm-card-body {
                padding: 20px;
            }

            .mmwm-toggle-switch {
                position: relative;
                display: inline-block;
                width: 60px;
                height: 34px;
            }

            .mmwm-toggle-switch input {
                opacity: 0;
                width: 0;
                height: 0;
            }

            .mmwm-slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #ccc;
                transition: .4s;
                border-radius: 34px;
            }

            .mmwm-slider:before {
                position: absolute;
                content: "";
                height: 26px;
                width: 26px;
                left: 4px;
                bottom: 4px;
                background-color: white;
                transition: .4s;
                border-radius: 50%;
            }

            input:checked+.mmwm-slider {
                background-color: #2196F3;
            }

            input:checked+.mmwm-slider:before {
                transform: translateX(26px);
            }

            .mmwm-form-row {
                display: flex;
                align-items: center;
                margin-bottom: 15px;
                padding: 15px;
                background: #f8f9fa;
                border-radius: 8px;
                border-left: 4px solid #2196F3;
            }

            .mmwm-form-label {
                flex: 1;
                font-weight: 500;
                color: #495057;
            }

            .mmwm-form-control {
                flex: 2;
                margin-left: 20px;
            }

            .mmwm-btn-modern {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                padding: 12px 24px;
                border-radius: 8px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.3s ease;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .mmwm-btn-modern:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            }

            .mmwm-success-notice {
                background: #d4edda;
                border: 1px solid #c3e6cb;
                color: #155724;
                padding: 12px;
                border-radius: 6px;
                margin: 10px 0;
                display: none;
            }

            .mmwm-error-notice {
                background: #f8d7da;
                border: 1px solid #f5c6cb;
                color: #721c24;
                padding: 12px;
                border-radius: 6px;
                margin: 10px 0;
                display: none;
            }

            .mmwm-info-box {
                background: #e3f2fd;
                border: 1px solid #bbdefb;
                color: #0d47a1;
                padding: 15px;
                border-radius: 8px;
                margin: 15px 0;
            }

            .mmwm-code-box {
                background: #f4f4f4;
                border: 1px solid #ddd;
                padding: 15px;
                border-radius: 6px;
                font-family: monospace;
                font-size: 12px;
                overflow-x: auto;
                margin: 10px 0;
            }
        </style>

        <div class="wrap">
            <h1><?php _e('🌐 Web Monitoring Global Options v1.0.8', 'mm-web-monitoring'); ?></h1>

            <div class="mmwm-modern-container">

                <!-- Server Information Card -->
                <div class="mmwm-card">
                    <div class="mmwm-card-header">
                        <i class="dashicons dashicons-admin-site-alt3"></i> <?php _e('Server Information', 'mm-web-monitoring'); ?>
                    </div>
                    <div class="mmwm-card-body">
                        <div class="mmwm-form-row">
                            <div class="mmwm-form-label"><?php _e('Monitoring Server IP:', 'mm-web-monitoring'); ?></div>
                            <div class="mmwm-form-control">
                                <strong><?php echo esc_html($server_ip); ?></strong>
                            </div>
                        </div>

                        <div class="mmwm-form-row">
                            <div class="mmwm-form-label"><?php _e('Monitoring Site SSL Status:', 'mm-web-monitoring'); ?></div>
                            <div class="mmwm-form-control">
                                <?php if ($ssl_info['valid']): ?>
                                    <span style="color: #28a745; font-weight: bold;">✅ Valid until <?php echo esc_html($ssl_info['expires']); ?></span>
                                <?php else: ?>
                                    <span style="color: #dc3545; font-weight: bold;">❌ <?php echo esc_html($ssl_info['error']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cloudflare Integration Card -->
                <div class="mmwm-card">
                    <div class="mmwm-card-header">
                        <i class="dashicons dashicons-cloud"></i> <?php _e('Cloudflare Integration', 'mm-web-monitoring'); ?>
                    </div>
                    <div class="mmwm-card-body">
                        <div class="mmwm-info-box">
                            <strong><?php _e('Security Rules for Cloudflare WAF (Choose One Method):', 'mm-web-monitoring'); ?></strong>

                            <h4><?php _e('Method 1: IP + User Agent (Recommended)', 'mm-web-monitoring'); ?></h4>
                            <div class="mmwm-code-box">
                                (ip.src eq <?php echo esc_html($server_ip); ?>) or (http.user_agent contains "MM-Web-Monitoring")
                            </div>

                            <h4><?php _e('Method 2: IP Only', 'mm-web-monitoring'); ?></h4>
                            <div class="mmwm-code-box">
                                ip.src eq <?php echo esc_html($server_ip); ?>
                            </div>

                            <h4><?php _e('Method 3: User Agent Only', 'mm-web-monitoring'); ?></h4>
                            <div class="mmwm-code-box">
                                http.user_agent contains "MM-Web-Monitoring"
                            </div>

                            <h4><?php _e('Method 4: Country + User Agent', 'mm-web-monitoring'); ?></h4>
                            <div class="mmwm-code-box">
                                (ip.geoip.country eq "ID") and (http.user_agent contains "MM-Web-Monitoring")
                            </div>

                            <p><strong><?php _e('How to apply:', 'mm-web-monitoring'); ?></strong></p>
                            <ol>
                                <li><?php _e('Login to Cloudflare Dashboard', 'mm-web-monitoring'); ?></li>
                                <li><?php _e('Go to Security → WAF → Custom Rules', 'mm-web-monitoring'); ?></li>
                                <li><?php _e('Create new rule with one of the expressions above', 'mm-web-monitoring'); ?></li>
                                <li><?php _e('Set Action to "Allow" and Priority to "1"', 'mm-web-monitoring'); ?></li>
                                <li><?php _e('Add descriptive name like "MM Web Monitoring Allow"', 'mm-web-monitoring'); ?></li>
                            </ol>

                            <p><strong><?php _e('Security Notes:', 'mm-web-monitoring'); ?></strong></p>
                            <ul>
                                <li><?php _e('Method 1 is most secure (combines IP and User Agent)', 'mm-web-monitoring'); ?></li>
                                <li><?php _e('Method 2 is simplest but IP-dependent', 'mm-web-monitoring'); ?></li>
                                <li><?php _e('Method 3 is flexible but relies only on User Agent', 'mm-web-monitoring'); ?></li>
                                <li><?php _e('Method 4 restricts by country (change "ID" to your country code)', 'mm-web-monitoring'); ?></li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Configuration Card -->
                <div class="mmwm-card">
                    <div class="mmwm-card-header">
                        <i class="dashicons dashicons-admin-settings"></i> <?php _e('Configuration Settings', 'mm-web-monitoring'); ?>
                    </div>
                    <div class="mmwm-card-body">
                        <form method="post" action="options.php" id="mmwm-global-form">
                            <?php settings_fields('mmwm_global_options'); ?>

                            <div class="mmwm-form-row">
                                <div class="mmwm-form-label">
                                    <label for="mmwm_default_email"><?php _e('Default Notification Email', 'mm-web-monitoring'); ?></label>
                                </div>
                                <div class="mmwm-form-control">
                                    <input type="email" id="mmwm_default_email" name="mmwm_default_email" value="<?php echo esc_attr(get_option('mmwm_default_email', get_option('admin_email'))); ?>" class="regular-text" />
                                </div>
                            </div>

                            <div class="mmwm-form-row">
                                <div class="mmwm-form-label">
                                    <label for="mmwm_auto_reload_interval"><?php _e('Auto-Reload Interval (seconds)', 'mm-web-monitoring'); ?></label>
                                </div>
                                <div class="mmwm-form-control">
                                    <input type="number" id="mmwm_auto_reload_interval" name="mmwm_auto_reload_interval" value="<?php echo esc_attr(get_option('mmwm_auto_reload_interval', 30)); ?>" min="10" max="300" class="small-text" />
                                </div>
                            </div>

                            <!-- Global Check Enable/Disable -->
                            <div class="mmwm-form-row">
                                <div class="mmwm-form-label">
                                    <label><?php _e('Enable Global Daily Check', 'mm-web-monitoring'); ?></label>
                                    <p class="description"><?php _e('Run comprehensive SSL and domain checks once daily', 'mm-web-monitoring'); ?></p>
                                </div>
                                <div class="mmwm-form-control">
                                    <label class="mmwm-toggle-switch">
                                        <input type="checkbox" id="mmwm_global_check_enabled" name="mmwm_global_check_enabled" value="1" <?php checked(get_option('mmwm_global_check_enabled', 0), 1); ?> onchange="toggleGlobalCheckOptions(this.checked)">
                                        <span class="mmwm-slider"></span>
                                    </label>
                                </div>
                            </div>

                            <div id="global-check-options" style="<?php echo get_option('mmwm_global_check_enabled', 0) ? '' : 'display:none;'; ?>">
                                <div class="mmwm-form-row">
                                    <div class="mmwm-form-label">
                                        <label for="mmwm_global_check_frequency"><?php _e('Global Check Frequency', 'mm-web-monitoring'); ?></label>
                                    </div>
                                    <div class="mmwm-form-control">
                                        <select id="mmwm_global_check_frequency" name="mmwm_global_check_frequency">
                                            <option value="3" <?php selected(get_option('mmwm_global_check_frequency', 3), 3); ?>><?php _e('Every 3 days', 'mm-web-monitoring'); ?></option>
                                            <option value="7" <?php selected(get_option('mmwm_global_check_frequency', 3), 7); ?>><?php _e('Every 7 days', 'mm-web-monitoring'); ?></option>
                                            <option value="14" <?php selected(get_option('mmwm_global_check_frequency', 3), 14); ?>><?php _e('Every 14 days', 'mm-web-monitoring'); ?></option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mmwm-form-row">
                                    <div class="mmwm-form-label">
                                        <label for="mmwm_global_cron_hour"><?php _e('Daily Check Time', 'mm-web-monitoring'); ?></label>
                                        <p class="description"><?php _e('What time each day should the global monitoring check run?', 'mm-web-monitoring'); ?></p>
                                    </div>
                                    <div class="mmwm-form-control">
                                        <select id="mmwm_global_cron_hour" name="mmwm_global_cron_hour">
                                            <?php for ($hour = 0; $hour < 24; $hour++): ?>
                                                <option value="<?php echo $hour; ?>" <?php selected(get_option('mmwm_global_cron_hour', 2), $hour); ?>><?php echo sprintf('%02d:00', $hour); ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Custom User Agent -->
                            <div class="mmwm-form-row">
                                <div class="mmwm-form-label">
                                    <label><?php _e('Auto-Configure Custom User Agent', 'mm-web-monitoring'); ?></label>
                                    <p class="description"><?php _e('Automatically add custom user agent to wp-config.php', 'mm-web-monitoring'); ?></p>
                                </div>
                                <div class="mmwm-form-control">
                                    <label class="mmwm-toggle-switch">
                                        <input type="checkbox" id="mmwm_custom_user_agent_enabled" name="mmwm_custom_user_agent_enabled" value="1" <?php checked(get_option('mmwm_custom_user_agent_enabled', 0), 1); ?> onchange="handleUserAgentToggle(this.checked)">
                                        <span class="mmwm-slider"></span>
                                    </label>
                                    <div id="user-agent-status" style="margin-top: 10px;"></div>
                                </div>
                            </div>

                            <div class="mmwm-success-notice" id="success-notice"></div>
                            <div class="mmwm-error-notice" id="error-notice"></div>

                            <?php submit_button(__('Save Changes', 'mm-web-monitoring'), 'primary mmwm-btn-modern'); ?>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <script>
            function toggleGlobalCheckOptions(enabled) {
                document.getElementById('global-check-options').style.display = enabled ? 'block' : 'none';
            }

            function showNotice(message, type) {
                const notice = document.getElementById(type + '-notice');
                notice.textContent = message;
                notice.style.display = 'block';
                setTimeout(() => notice.style.display = 'none', 3000);
            }

            function handleUserAgentToggle(enabled) {
                const statusDiv = document.getElementById('user-agent-status');
                statusDiv.innerHTML = '<span style="color: #666;">Processing...</span>';

                jQuery.ajax({
                    url: mmwm_ajax.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'mmwm_toggle_user_agent',
                        enabled: enabled ? 1 : 0,
                        nonce: mmwm_ajax.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            statusDiv.innerHTML = '<span style="color: #28a745;">✅ ' + response.data + '</span>';
                            showNotice(response.data, 'success');
                        } else {
                            statusDiv.innerHTML = '<span style="color: #dc3545;">❌ ' + response.data + '</span>';
                            showNotice(response.data, 'error');
                            // Revert toggle if failed
                            document.getElementById('mmwm_custom_user_agent_enabled').checked = !enabled;
                        }
                    },
                    error: function() {
                        statusDiv.innerHTML = '<span style="color: #dc3545;">❌ Ajax error</span>';
                        showNotice('Ajax request failed', 'error');
                        document.getElementById('mmwm_custom_user_agent_enabled').checked = !enabled;
                    }
                });
            }
        </script>
    <?php
    }

    /**
     * Get server IP address
     */
    private function get_server_ip()
    {
        // Try multiple methods to get server IP
        $ip_sources = array(
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_FORWARDED',
            'HTTP_X_CLUSTER_CLIENT_IP',
            'HTTP_FORWARDED_FOR',
            'HTTP_FORWARDED',
            'REMOTE_ADDR'
        );

        foreach ($ip_sources as $source) {
            if (!empty($_SERVER[$source])) {
                $ip = $_SERVER[$source];
                // Handle comma-separated IPs
                if (strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
                if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return $ip;
                }
            }
        }

        // Fallback: try to get external IP
        $external_ip = wp_remote_get('https://icanhazip.com');
        if (!is_wp_error($external_ip) && wp_remote_retrieve_response_code($external_ip) === 200) {
            return trim(wp_remote_retrieve_body($external_ip));
        }

        return __('Unable to detect', 'mm-web-monitoring');
    }

    /**
     * Get SSL information for current site
     */
    private function get_site_ssl_info()
    {
        $site_url = get_site_url();
        if (strpos($site_url, 'https://') !== 0) {
            return array(
                'valid' => false,
                'error' => __('Site is not using HTTPS', 'mm-web-monitoring')
            );
        }

        $parsed_url = parse_url($site_url);
        $hostname = $parsed_url['host'];
        $port = isset($parsed_url['port']) ? $parsed_url['port'] : 443;

        $context = stream_context_create([
            'ssl' => [
                'capture_peer_cert' => true,
                'verify_peer' => false,
                'verify_peer_name' => false,
            ],
        ]);

        $socket = @stream_socket_client(
            "ssl://{$hostname}:{$port}",
            $errno,
            $errstr,
            30,
            STREAM_CLIENT_CONNECT,
            $context
        );

        if (!$socket) {
            return array(
                'valid' => false,
                'error' => __('Unable to connect to SSL', 'mm-web-monitoring')
            );
        }

        $cert = stream_context_get_params($socket);
        fclose($socket);

        if (!isset($cert['options']['ssl']['peer_certificate'])) {
            return array(
                'valid' => false,
                'error' => __('No SSL certificate found', 'mm-web-monitoring')
            );
        }

        $cert_info = openssl_x509_parse($cert['options']['ssl']['peer_certificate']);
        if (!$cert_info) {
            return array(
                'valid' => false,
                'error' => __('Unable to parse SSL certificate', 'mm-web-monitoring')
            );
        }

        $valid_to = $cert_info['validTo_time_t'];
        $current_time = time();

        if ($valid_to < $current_time) {
            return array(
                'valid' => false,
                'error' => __('SSL certificate has expired', 'mm-web-monitoring')
            );
        }

        return array(
            'valid' => true,
            'expires' => date('Y-m-d H:i:s', $valid_to),
            'days_remaining' => ceil(($valid_to - $current_time) / 86400)
        );
    }

    /**
     * Handle AJAX toggle for custom user agent
     */
    public function handle_ajax_toggle_user_agent()
    {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'mmwm_admin_nonce')) {
            wp_die(__('Security check failed', 'mm-web-monitoring'));
        }

        // Check user permissions
        if (!current_user_can('manage_options')) {
            wp_die(__('Insufficient permissions', 'mm-web-monitoring'));
        }

        $enabled = intval($_POST['enabled']);
        $wp_config_path = ABSPATH . 'wp-config.php';

        // Check if wp-config.php is writable
        if (!is_writable($wp_config_path)) {
            wp_send_json_error(__('wp-config.php is not writable. Please set file permissions to 644 or contact your hosting provider.', 'mm-web-monitoring'));
            return;
        }

        $wp_config_content = file_get_contents($wp_config_path);
        if ($wp_config_content === false) {
            wp_send_json_error(__('Unable to read wp-config.php file.', 'mm-web-monitoring'));
            return;
        }

        // Create user agent string that's safe for wp-config.php (no WordPress functions)
        // Use a static version string since wp-config.php loads before WordPress core
        $user_agent_define = "define('MMWM_USER_AGENT', 'MM-Web-Monitoring/1.0.8');";
        $user_agent_pattern = "/\/\/\s*MM Web Monitoring Custom User Agent\s*\n.*?define\s*\(\s*['\"]MMWM_USER_AGENT['\"]\s*,.*?\)\s*;\s*\n?/s";
        $simple_pattern = "/define\s*\(\s*['\"]MMWM_USER_AGENT['\"]\s*,.*?\)\s*;/";

        if ($enabled) {
            // Add the define if it doesn't exist
            if (!preg_match($simple_pattern, $wp_config_content)) {
                // Find the right place to insert (before the "That's all" comment)
                $insert_pattern = "/\/\*.*?That's all.*?\*\//";
                if (preg_match($insert_pattern, $wp_config_content)) {
                    $wp_config_content = preg_replace(
                        $insert_pattern,
                        "\n// MM Web Monitoring Custom User Agent\n" . $user_agent_define . "\n\n$0",
                        $wp_config_content
                    );
                } else {
                    // Fallback: add before the closing PHP tag or at the end
                    if (strpos($wp_config_content, '?>') !== false) {
                        $wp_config_content = str_replace('?>', "\n// MM Web Monitoring Custom User Agent\n" . $user_agent_define . "\n\n?>", $wp_config_content);
                    } else {
                        $wp_config_content .= "\n\n// MM Web Monitoring Custom User Agent\n" . $user_agent_define . "\n";
                    }
                }

                if (file_put_contents($wp_config_path, $wp_config_content) === false) {
                    wp_send_json_error(__('Failed to write to wp-config.php. Please check file permissions.', 'mm-web-monitoring'));
                    return;
                }

                update_option('mmwm_custom_user_agent_enabled', 1);
                wp_send_json_success(__('Custom user agent successfully added to wp-config.php', 'mm-web-monitoring'));
            } else {
                update_option('mmwm_custom_user_agent_enabled', 1);
                wp_send_json_success(__('Custom user agent is already configured in wp-config.php', 'mm-web-monitoring'));
            }
        } else {
            // Remove the define if it exists - use comprehensive pattern first
            if (preg_match($user_agent_pattern, $wp_config_content)) {
                $wp_config_content = preg_replace($user_agent_pattern, '', $wp_config_content);
            } elseif (preg_match($simple_pattern, $wp_config_content)) {
                // Fallback to simple pattern and clean up manually
                $wp_config_content = preg_replace($simple_pattern, '', $wp_config_content);
                // Remove the comment line if it exists
                $wp_config_content = preg_replace("/\/\/\s*MM Web Monitoring Custom User Agent\s*\n/", '', $wp_config_content);
            }

            // Clean up any extra newlines
            $wp_config_content = preg_replace("/\n{3,}/", "\n\n", $wp_config_content);

            if (file_put_contents($wp_config_path, $wp_config_content) === false) {
                wp_send_json_error(__('Failed to write to wp-config.php. Please check file permissions.', 'mm-web-monitoring'));
                return;
            }

            update_option('mmwm_custom_user_agent_enabled', 0);
            wp_send_json_success(__('Custom user agent successfully removed from wp-config.php', 'mm-web-monitoring'));
        }
    }

    public function add_bulk_add_menu()
    {
        add_submenu_page(
            'edit.php?post_type=mmwm_website',
            __('Add Websites in Bulk', 'mm-web-monitoring'),
            __('Bulk Add', 'mm-web-monitoring'),
            'manage_options',
            'mmwm-bulk-add',
            array($this, 'render_bulk_add_page')
        );
    }

    public function render_bulk_add_page()
    {
    ?>
        <style>
            .mmwm-modern-container {
                max-width: 1200px;
                margin: 20px 0;
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            }

            .mmwm-card {
                background: #fff;
                border-radius: 12px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                margin-bottom: 20px;
                overflow: hidden;
                border: 1px solid #e1e5e9;
            }

            .mmwm-card-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 20px;
                font-size: 18px;
                font-weight: 600;
            }

            .mmwm-card-body {
                padding: 20px;
            }

            .mmwm-btn-modern {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border: none;
                padding: 12px 24px;
                border-radius: 8px;
                font-weight: 500;
                cursor: pointer;
                transition: all 0.3s ease;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                text-decoration: none;
                display: inline-block;
            }

            .mmwm-btn-modern:hover {
                transform: translateY(-2px);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
                color: white;
            }

            .mmwm-textarea-modern {
                width: 100%;
                min-height: 300px;
                padding: 15px;
                border: 2px solid #e1e5e9;
                border-radius: 8px;
                font-family: monospace;
                font-size: 14px;
                line-height: 1.5;
                resize: vertical;
                transition: border-color 0.3s ease;
            }

            .mmwm-textarea-modern:focus {
                border-color: #667eea;
                outline: none;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
            }

            .mmwm-progress-container {
                background: #f8f9fa;
                border-radius: 8px;
                padding: 20px;
                margin-top: 20px;
                border-left: 4px solid #667eea;
            }

            #mmwm-progress-bar-container {
                background-color: #e9ecef;
                border-radius: 8px;
                padding: 4px;
                box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
                margin: 15px 0;
            }

            #mmwm-progress-bar {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                height: 30px;
                border-radius: 6px;
                text-align: center;
                color: white;
                line-height: 30px;
                font-weight: 500;
                transition: width 0.4s ease;
                width: 0%;
            }

            #mmwm-bulk-log {
                background-color: #f8f9fa;
                border: 1px solid #dee2e6;
                border-radius: 6px;
                padding: 15px;
                margin-top: 15px;
                height: 250px;
                overflow-y: auto;
                font-family: monospace;
                font-size: 13px;
                line-height: 1.4;
            }

            .mmwm-info-box {
                background: #e3f2fd;
                border: 1px solid #bbdefb;
                color: #0d47a1;
                padding: 15px;
                border-radius: 8px;
                margin: 15px 0;
            }
        </style>

        <div class="wrap">
            <h1><?php _e('📝 Add Websites in Bulk', 'mm-web-monitoring'); ?></h1>

            <div class="mmwm-modern-container">
                <div class="mmwm-card">
                    <div class="mmwm-card-header">
                        <i class="dashicons dashicons-plus-alt2"></i> <?php _e('Bulk Website Addition', 'mm-web-monitoring'); ?>
                    </div>
                    <div class="mmwm-card-body">
                        <div class="mmwm-info-box">
                            <strong><?php _e('Instructions:', 'mm-web-monitoring'); ?></strong><br>
                            <?php _e('Enter one website URL per line. The plugin will automatically use the domain name as the title.', 'mm-web-monitoring'); ?>
                            <br><br>
                            <strong><?php _e('Example:', 'mm-web-monitoring'); ?></strong><br>
                            <code>https://example.com/<br>https://wordpress.org/<br>https://another-site.net/path</code>
                        </div>

                        <label for="mmwm-bulk-urls" style="font-weight: 500; margin-bottom: 10px; display: block;">
                            <?php _e('Website URLs (one per line):', 'mm-web-monitoring'); ?>
                        </label>
                        <textarea id="mmwm-bulk-urls" class="mmwm-textarea-modern" placeholder="https://example.com/&#10;https://wordpress.org/&#10;https://another-site.net/path"></textarea>

                        <p style="margin-top: 20px;">
                            <button type="button" class="mmwm-btn-modern" id="mmwm-start-bulk-add">
                                <i class="dashicons dashicons-upload" style="vertical-align: middle; margin-right: 5px;"></i>
                                <?php _e('Start Bulk Addition', 'mm-web-monitoring'); ?>
                            </button>
                        </p>

                        <div id="mmwm-bulk-progress-wrapper" class="mmwm-progress-container" style="display:none;">
                            <h3><?php _e('🔄 Processing Progress', 'mm-web-monitoring'); ?></h3>
                            <div id="mmwm-progress-bar-container">
                                <div id="mmwm-progress-bar">0%</div>
                            </div>
                            <h4><?php _e('📋 Activity Log:', 'mm-web-monitoring'); ?></h4>
                            <div id="mmwm-bulk-log"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }

    public function add_custom_columns($columns)
    {
        // Unset the default 'date' column
        unset($columns['date']);

        // Add our custom columns
        $columns['check_result'] = __('Check Result', 'mm-web-monitoring');
        $columns['ssl_info'] = __('SSL Certificate', 'mm-web-monitoring');
        $columns['domain_expiry'] = __('Domain Expiry', 'mm-web-monitoring');
        $columns['last_check'] = __('Last Check', 'mm-web-monitoring');
        $columns['next_check'] = __('Next Check', 'mm-web-monitoring');
        $columns['monitoring_status'] = __('Status', 'mm-web-monitoring');
        $columns['interval'] = __('Interval', 'mm-web-monitoring');
        $columns['host_in'] = __('Host In', 'mm-web-monitoring');
        $columns['email_report'] = __('Email Report', 'mm-web-monitoring');
        $columns['email_notification'] = __('Email To', 'mm-web-monitoring');
        $columns['website_link'] = __('Link', 'mm-web-monitoring');
        $columns['actions'] = __('Actions', 'mm-web-monitoring');

        return $columns;
    }

    /**
     * Add custom bulk actions
     */
    public function add_bulk_actions($bulk_actions)
    {
        $bulk_actions['mmwm_check_now'] = __('Check Now', 'mm-web-monitoring');
        $bulk_actions['mmwm_start_monitoring'] = __('Start Monitoring', 'mm-web-monitoring');
        $bulk_actions['mmwm_pause_monitoring'] = __('Pause Monitoring', 'mm-web-monitoring');
        $bulk_actions['mmwm_stop_monitoring'] = __('Stop Monitoring', 'mm-web-monitoring');
        return $bulk_actions;
    }

    /**
     * Handle custom bulk actions
     */
    public function handle_bulk_actions($redirect_to, $doaction, $post_ids)
    {
        if (!in_array($doaction, ['mmwm_check_now', 'mmwm_start_monitoring', 'mmwm_pause_monitoring', 'mmwm_stop_monitoring'])) {
            return $redirect_to;
        }

        $processed = 0;
        $cron = new MMWM_Cron();

        foreach ($post_ids as $post_id) {
            switch ($doaction) {
                case 'mmwm_check_now':
                    $cron->perform_check($post_id);
                    $processed++;
                    break;
                case 'mmwm_start_monitoring':
                    update_post_meta($post_id, '_mmwm_monitoring_status', 'active');
                    $processed++;
                    break;
                case 'mmwm_pause_monitoring':
                    update_post_meta($post_id, '_mmwm_monitoring_status', 'paused');
                    $processed++;
                    break;
                case 'mmwm_stop_monitoring':
                    update_post_meta($post_id, '_mmwm_monitoring_status', 'stopped');
                    $processed++;
                    break;
            }
        }

        $redirect_to = add_query_arg('mmwm_processed', $processed, $redirect_to);
        return $redirect_to;
    }

    /**
     * Show admin notice for bulk actions
     */
    public function show_bulk_action_admin_notice()
    {
        if (!empty($_REQUEST['mmwm_processed'])) {
            $processed = intval($_REQUEST['mmwm_processed']);
            printf('<div id="message" class="updated notice is-dismissible"><p>' .
                _n('Processed %d website.', 'Processed %d websites.', $processed, 'mm-web-monitoring') .
                '</p></div>', $processed);
        }
    }

    public function render_custom_columns($column, $post_id)
    {
        switch ($column) {
            case 'check_result':
                $status = get_post_meta($post_id, '_mmwm_status', true);
                $status_color = '#777';
                if ($status === 'UP') $status_color = '#28a745';
                if ($status === 'DOWN') $status_color = '#dc3545';
                if ($status === 'CONTENT_ERROR') $status_color = '#ffc107';
                echo '<span style="font-weight: bold; color: ' . esc_attr($status_color) . ';">' . esc_html($status ?: 'Not Checked') . '</span>';
                break;
            case 'ssl_info':
                $ssl_is_active = get_post_meta($post_id, '_mmwm_ssl_is_active', true);
                $ssl_error = get_post_meta($post_id, '_mmwm_ssl_error', true);
                $ssl_days_until_expiry = get_post_meta($post_id, '_mmwm_ssl_days_until_expiry', true);
                $ssl_expiry_date = get_post_meta($post_id, '_mmwm_ssl_expiry_date', true);

                if ($ssl_is_active === '1') {
                    $color = '#28a745';
                    $status_text = 'Active';

                    if ($ssl_days_until_expiry !== '' && $ssl_days_until_expiry < 0) {
                        $color = '#dc3545';
                        $status_text = 'EXPIRED';
                    } elseif ($ssl_days_until_expiry !== '' && $ssl_days_until_expiry <= 10) {
                        $color = '#ffc107';
                        $status_text = 'Expiring Soon';
                    }

                    echo '<span style="color: ' . esc_attr($color) . '; font-weight: bold;">' . esc_html($status_text) . '</span>';

                    if ($ssl_expiry_date) {
                        $formatted_date = date('M j, Y', strtotime($ssl_expiry_date));
                        echo '<br><small style="color: #666;">' . esc_html($formatted_date) . '</small>';
                    }
                } elseif ($ssl_error) {
                    echo '<span style="color: #dc3545;" title="' . esc_attr($ssl_error) . '">Inactive</span>';
                } else {
                    echo '<span style="color: #777;">Not Checked</span>';
                }
                break;
            case 'domain_expiry':
                $domain_monitoring_enabled = get_post_meta($post_id, '_mmwm_domain_monitoring_enabled', true);

                if ($domain_monitoring_enabled === '1') {
                    $domain_expiry_date = get_post_meta($post_id, '_mmwm_domain_expiry_date', true);
                    $domain_days_until_expiry = get_post_meta($post_id, '_mmwm_domain_days_until_expiry', true);
                    $domain_error = get_post_meta($post_id, '_mmwm_domain_error', true);
                    $manual_override = get_post_meta($post_id, '_mmwm_domain_manual_override', true);

                    if ($domain_expiry_date) {
                        $color = '#28a745'; // Green for healthy
                        if ($domain_days_until_expiry <= 10) {
                            $color = '#dc3545'; // Red for urgent
                        } elseif ($domain_days_until_expiry <= 30) {
                            $color = '#ffc107'; // Yellow for warning
                        }

                        echo '<span style="color: ' . esc_attr($color) . '; font-weight: bold;">';
                        echo esc_html(date('M j, Y', strtotime($domain_expiry_date)));
                        echo '</span>';

                        if ($manual_override) {
                            echo '<br><small style="color: #0073aa;">Manual</small>';
                        }

                        echo '<br><small style="color: #666;">' . esc_html($domain_days_until_expiry) . ' days left</small>';
                    } elseif ($domain_error) {
                        echo '<span style="color: #dc3545;" title="' . esc_attr($domain_error) . '">Check Failed</span>';
                    } else {
                        echo '<span style="color: #777;">Not Checked</span>';
                    }
                } else {
                    echo '<span style="color: #999;">Disabled</span><br>';
                    echo '<small style="color: #666; font-style: italic;">Activate domain expiry check from website management</small>';
                }
                break;
            case 'last_check':
                $last_check_timestamp = get_post_meta($post_id, '_mmwm_last_check', true);
                echo $last_check_timestamp ? sprintf('<span title="%s">%s</span>', esc_attr(wp_date('Y-m-d H:i:s', $last_check_timestamp)), esc_html(human_time_diff($last_check_timestamp) . ' ago')) : 'N/A';
                break;
            case 'next_check':
                $monitoring_status = get_post_meta($post_id, '_mmwm_monitoring_status', true);
                if ($monitoring_status !== 'active') {
                    echo 'Inactive';
                    break;
                }
                $scheduler = new MMWM_Scheduler();
                $next_check_timestamp = $scheduler->get_next_check_time($post_id);

                if ($next_check_timestamp) {
                    $time_diff = $next_check_timestamp - time();
                    if ($time_diff <= 0) {
                        echo '<span class="mmwm-next-check">Due now</span>';
                    } else {
                        $minutes = floor($time_diff / 60);
                        $seconds = $time_diff % 60;
                        echo '<span class="mmwm-next-check-countdown" data-timestamp="' . esc_attr($next_check_timestamp) . '" data-postid="' . esc_attr($post_id) . '">';
                        echo esc_html($minutes . 'm ' . $seconds . 's');
                        echo '</span>';
                    }
                } else {
                    echo '<span class="mmwm-next-check">Soon</span>';
                }
                break;
            case 'monitoring_status':
                $monitoring_status = get_post_meta($post_id, '_mmwm_monitoring_status', true) ?: 'stopped';
                $bg_color = '#646970'; // Stopped
                if ($monitoring_status === 'active') $bg_color = '#008a20';
                if ($monitoring_status === 'paused') $bg_color = '#d98500';
                echo '<span style="background-color:' . esc_attr($bg_color) . '; color:white; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px;">' . esc_html(ucfirst($monitoring_status)) . '</span>';
                break;
            case 'interval':
                $interval = get_post_meta($post_id, '_mmwm_monitoring_interval', true) ?: '15min';

                // Convert interval to display format
                $interval_display = '';
                switch ($interval) {
                    case '5min':
                        $interval_display = '5 min';
                        break;
                    case '15min':
                        $interval_display = '15 min';
                        break;
                    case '30min':
                        $interval_display = '30 min';
                        break;
                    case '1hour':
                        $interval_display = '1 hour';
                        break;
                    case '6hour':
                        $interval_display = '6 hours';
                        break;
                    case '12hour':
                        $interval_display = '12 hours';
                        break;
                    case '24hour':
                        $interval_display = '24 hours';
                        break;
                    default:
                        $interval_display = '15 min';
                }

                echo '<span class="mmwm-editable-text" data-type="interval" data-postid="' . $post_id . '" title="Click to change">' . esc_html($interval_display) . '</span>';
                break;
            case 'host_in':
                $host_in = get_post_meta($post_id, '_mmwm_host_in', true) ?: 'Belum diisi';
                echo '<span class="mmwm-editable-text" data-type="host_in" data-postid="' . $post_id . '" title="Click to change">' . esc_html($host_in) . '</span>';
                break;
            case 'email_report':
                $trigger = get_post_meta($post_id, '_mmwm_notification_trigger', true) ?: 'always';
                $display_trigger = ($trigger === 'when_error_only') ? 'On Error & Recovery' : 'Always';
                echo '<span class="mmwm-editable-text" data-type="notification_trigger" data-postid="' . $post_id . '" title="Click to change">' . esc_html($display_trigger) . '</span>';
                break;
            case 'email_notification':
                $email = get_post_meta($post_id, '_mmwm_notification_email', true) ?: get_option('mmwm_default_email', get_option('admin_email'));
                echo '<span class="mmwm-editable-text" data-type="notification_email" data-postid="' . $post_id . '" title="Click to change">' . esc_html($email) . '</span>';
                break;
            case 'website_link':
                $url = get_post_meta($post_id, '_mmwm_target_url', true);
                echo $url ? '<a href="' . esc_url($url) . '" target="_blank" title="' . esc_attr($url) . '">Visit &raquo;</a>' : 'N/A';
                break;
            case 'actions':
                $monitoring_status = get_post_meta($post_id, '_mmwm_monitoring_status', true) ?: 'stopped';
                echo '<button class="button button-small mmwm-action-btn" data-action="run_check_now" data-postid="' . $post_id . '">Check Now</button>';
                if ($monitoring_status === 'active') echo '<button class="button button-small mmwm-action-btn" data-action="paused" data-postid="' . $post_id . '">Pause</button>';
                else echo '<button class="button button-small button-primary mmwm-action-btn" data-action="active" data-postid="' . $post_id . '">Start</button>';
                if ($monitoring_status !== 'stopped') echo '<button class="button button-small button-danger mmwm-action-btn" data-action="stopped" data-postid="' . $post_id . '">Stop</button>';
                echo '<span class="spinner" style="float:none; vertical-align:middle;"></span>';
                break;
        }
    }

    public function make_columns_sortable($columns)
    {
        $columns['check_result'] = 'check_result';
        $columns['last_check'] = 'last_check';
        $columns['monitoring_status'] = 'monitoring_status';
        $columns['interval'] = 'interval';
        $columns['host_in'] = 'host_in';
        return $columns;
    }

    public function sort_custom_columns($query)
    {
        if (!is_admin() || !$query->is_main_query() || $query->get('post_type') !== 'mmwm_website') return;
        $orderby = $query->get('orderby');
        $meta_keys = [
            'check_result'      => '_mmwm_status',
            'last_check'        => '_mmwm_last_check',
            'monitoring_status' => '_mmwm_monitoring_status',
            'interval'          => '_mmwm_interval',
            'host_in'           => '_mmwm_host_in',
        ];
        if (isset($meta_keys[$orderby])) {
            $query->set('meta_key', $meta_keys[$orderby]);
            $query->set('orderby', ($orderby === 'last_check' || $orderby === 'interval') ? 'meta_value_num' : 'meta_value');
        }
    }

    public function add_list_page_scripts_and_styles()
    {
        global $current_screen;
        if (!$current_screen || !in_array($current_screen->id, ['edit-mmwm_website', 'mmwm_website_page_mmwm-bulk-add'])) {
            return;
        }

        // Enqueue the enhanced admin script
        wp_enqueue_script(
            'mmwm-admin-enhanced',
            MMWM_PLUGIN_URL . 'includes/assets/admin-enhanced.js',
            array('jquery'),
            MMWM_VERSION,
            true
        );

        // Enqueue the enhanced admin styles
        wp_enqueue_style(
            'mmwm-admin-enhanced',
            MMWM_PLUGIN_URL . 'includes/assets/admin-enhanced.css',
            array(),
            MMWM_VERSION
        );

        // Localize script with data
        wp_localize_script('mmwm-admin-enhanced', 'mmwm_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('mmwm_ajax_nonce'),
            'current_screen' => $current_screen->id,
            'auto_reload_interval' => get_option('mmwm_auto_reload_interval', 30)
        ));

        $ajax_nonce = wp_create_nonce('mmwm_ajax_nonce');
    ?>
        <style>
            .mmwm-editable-text {
                cursor: pointer;
                border-bottom: 1px dashed #2271b1;
            }

            .mmwm-notification {
                position: fixed;
                top: 40px;
                right: 20px;
                background-color: #28a745;
                color: white;
                padding: 10px 20px;
                border-radius: 5px;
                z-index: 9999;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
                display: none;
            }

            .mmwm-notification.error {
                background-color: #dc3545;
            }

            #mmwm-progress-bar {
                width: 0%;
                height: 24px;
                background: linear-gradient(45deg, #2a94d6, #2271b1);
                border-radius: 3px;
                text-align: center;
                color: white;
                line-height: 24px;
                transition: width 0.4s ease;
            }

            #mmwm-bulk-log {
                background-color: #f0f0f1;
                border: 1px solid #ccc;
                padding: 10px;
                margin-top: 10px;
                height: 200px;
                overflow-y: scroll;
                font-family: monospace;
                font-size: 13px;
                border-radius: 4px;
            }
        </style>

        <div id="mmwm-notification" class="mmwm-notification"></div>

        <?php if ($current_screen->id === 'edit-mmwm_website') : ?>
            <script type="text/template" id="tmpl-bulk-actions-template">
                <div id="mmwm-bulk-controls" style="margin: 10px 0;">
                    <button class="button" id="mmwm-bulk-check-now">Bulk Check Now</button>
                    <button class="button" id="mmwm-bulk-start">Bulk Start</button>
                    <button class="button" id="mmwm-bulk-pause">Bulk Pause</button>
                    <span id="mmwm-bulk-spinner" class="spinner" style="float: none; vertical-align: middle;"></span>
                </div>
                 <div id="mmwm-bulk-progress-wrapper" style="display:none;">
                    <div id="mmwm-progress-bar-container" style="background-color: #ddd; border-radius: 5px; padding: 3px; margin-top: 10px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);"><div id="mmwm-progress-bar">0%</div></div>
                    <div id="mmwm-bulk-log" style="height: 150px;"></div>
                </div>
            </script>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    var ajax_nonce = '<?php echo $ajax_nonce; ?>';

                    function showNotification(message, isError = false) {
                        var notification = $('#mmwm-notification');
                        notification.text(message).removeClass('error').addClass(isError ? 'error' : 'success').fadeIn();
                        setTimeout(function() {
                            notification.fadeOut();
                        }, 5000);
                    }

                    $('.wrap h1').after($('#tmpl-bulk-actions-template').html());

                    function updateCountdown() {
                        $('.mmwm-next-check').each(function() {
                            var $this = $(this);
                            var timestamp = $this.data('timestamp');
                            if (!timestamp) return;
                            var remainingTime = timestamp - (Date.now() / 1000);
                            if (remainingTime <= 0) {
                                $this.text('Due now');
                            } else {
                                var minutes = Math.floor(remainingTime / 60);
                                var seconds = Math.floor(remainingTime % 60);
                                $this.text(minutes + 'm ' + seconds + 's');
                            }
                        });
                    }
                    setInterval(updateCountdown, 1000);
                    updateCountdown();

                    $('#the-list').on('click', '.mmwm-editable-text', function() {
                        var span = $(this);
                        if (span.find('select, input').length > 0) return;

                        var type = span.data('type'),
                            currentVal = span.text(),
                            postId = span.data('postid'),
                            editor;

                        if (type === 'interval') {
                            var intervals = {
                                '5min': '5 min',
                                '15min': '15 min',
                                '30min': '30 min',
                                '1hour': '1 hour',
                                '6hour': '6 hours',
                                '12hour': '12 hours',
                                '24hour': '24 hours'
                            };
                            editor = $('<select>');
                            for (var key in intervals) {
                                editor.append($('<option>').val(key).text(intervals[key]).prop('selected', intervals[key] === currentVal));
                            }
                        } else if (type === 'notification_trigger') {
                            editor = $('<select>');
                            editor.append($('<option>').val('always').text('Always').prop('selected', currentVal === 'Always'));
                            editor.append($('<option>').val('when_error_only').text('On Error & Recovery').prop('selected', currentVal === 'On Error & Recovery'));
                        } else { // host_in, notification_email
                            editor = $('<input type="text" style="width: 90%;">').val(currentVal === 'Belum diisi' ? '' : currentVal);
                        }

                        span.html(editor);
                        editor.focus();

                        editor.on('blur keypress', function(e) {
                            if (e.type === 'keypress' && e.which !== 13) return;

                            var newVal = $(this).val();
                            var displayVal = newVal;
                            if (type === 'interval') {
                                var intervalMap = {
                                    '5min': '5 min',
                                    '15min': '15 min',
                                    '30min': '30 min',
                                    '1hour': '1 hour',
                                    '6hour': '6 hours',
                                    '12hour': '12 hours',
                                    '24hour': '24 hours'
                                };
                                displayVal = intervalMap[newVal] || '15 min';
                            }
                            if (type === 'notification_trigger') displayVal = newVal === 'always' ? 'Always' : 'On Error & Recovery';

                            span.text(displayVal || (type === 'host_in' ? 'Belum diisi' : ''));

                            var postData = {
                                post_id: postId,
                                _ajax_nonce: ajax_nonce
                            };
                            postData['new_value'] = newVal;
                            postData.action = 'mmwm_update_' + type;

                            $.post(ajaxurl, postData)
                                .done(function() {
                                    showNotification(type.replace(/_/g, ' ') + ' updated!');
                                })
                                .fail(function() {
                                    showNotification('Failed to update ' + type, true);
                                });
                        });
                    });

                    $('#the-list').on('click', '.mmwm-action-btn', function() {
                        var button = $(this),
                            action = button.data('action'),
                            postId = button.data('postid'),
                            spinner = button.siblings('.spinner');
                        if (action === 'stopped' && !confirm('Are you sure?')) return;
                        button.parent().find('.mmwm-action-btn').prop('disabled', true);
                        spinner.addClass('is-active');
                        var request = (action === 'run_check_now') ? $.post(ajaxurl, {
                            action: 'mmwm_run_check_now',
                            post_id: postId,
                            _ajax_nonce: ajax_nonce
                        }) : $.post(ajaxurl, {
                            action: 'mmwm_update_monitoring_status',
                            post_id: postId,
                            new_status: action,
                            _ajax_nonce: ajax_nonce
                        });
                        request.always(function() {
                            location.reload();
                        });
                    });

                    $('#mmwm-bulk-controls button').on('click', function() {
                        /* ... kode sama dari versi sebelumnya ... */
                    });
                });
            </script>
        <?php endif;
        if ($current_screen->id === 'mmwm_website_page_mmwm-bulk-add') : ?>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    var ajax_nonce = '<?php echo $ajax_nonce; ?>';
                    $('#mmwm-start-bulk-add').on('click', function() {
                        var urls = $('#mmwm-bulk-urls').val().split('\n').filter(function(url) {
                            return url.trim() !== '';
                        });
                        if (urls.length === 0) {
                            alert('Please enter at least one URL.');
                            return;
                        }
                        var button = $(this),
                            progressWrapper = $('#mmwm-bulk-progress-wrapper'),
                            progressBar = $('#mmwm-progress-bar'),
                            logBox = $('#mmwm-bulk-log');
                        button.prop('disabled', true);
                        progressWrapper.show();
                        logBox.html('');
                        var processedCount = 0;

                        function processNextUrl() {
                            if (processedCount >= urls.length) {
                                logBox.append('<div><strong>All websites processed! You can now see them in the "All Websites" list.</strong></div>');
                                button.prop('disabled', false);
                                return;
                            }
                            var url = urls[processedCount];
                            logBox.append('<div>Adding: ' + url + '...</div>');
                            $.post(ajaxurl, {
                                    action: 'mmwm_bulk_add_sites',
                                    url: url,
                                    _ajax_nonce: ajax_nonce
                                })
                                .done(function(res) {
                                    logBox.append('<div style="color:green;">&hookrightarrow; ' + res.data.message + '</div>');
                                })
                                .fail(function(res) {
                                    logBox.append('<div style="color:red;">&hookrightarrow; ' + (res.responseJSON ? res.responseJSON.data.message : 'Server error.') + '</div>');
                                })
                                .always(function() {
                                    processedCount++;
                                    var percent = (processedCount / urls.length) * 100;
                                    progressBar.width(percent + '%').text(Math.round(percent) + '%');
                                    logBox.scrollTop(logBox[0].scrollHeight);
                                    setTimeout(processNextUrl, 2000);
                                });
                        }
                        processNextUrl();
                    });
                });
            </script>
<?php endif;
    }

    public function handle_ajax_update_interval()
    {
        check_ajax_referer('mmwm_ajax_nonce');
        if (current_user_can('edit_posts') && isset($_POST['post_id']) && isset($_POST['new_value'])) {
            update_post_meta(intval($_POST['post_id']), '_mmwm_interval', intval($_POST['new_value']));
            wp_send_json_success();
        }
        wp_send_json_error();
    }

    public function handle_ajax_update_host_in()
    {
        check_ajax_referer('mmwm_ajax_nonce');
        if (current_user_can('edit_posts') && isset($_POST['post_id']) && isset($_POST['new_value'])) {
            update_post_meta(intval($_POST['post_id']), '_mmwm_host_in', sanitize_text_field($_POST['new_value']));
            wp_send_json_success();
        }
        wp_send_json_error();
    }

    public function handle_ajax_update_notification_email()
    {
        check_ajax_referer('mmwm_ajax_nonce');
        if (current_user_can('edit_posts') && isset($_POST['post_id']) && isset($_POST['new_value'])) {
            $email = sanitize_email($_POST['new_value']);
            update_post_meta(intval($_POST['post_id']), '_mmwm_notification_email', $email);
            wp_send_json_success();
        }
        wp_send_json_error();
    }

    public function handle_ajax_update_notification_trigger()
    {
        check_ajax_referer('mmwm_ajax_nonce');
        if (current_user_can('edit_posts') && isset($_POST['post_id']) && isset($_POST['new_value'])) {
            $trigger = sanitize_text_field($_POST['new_value']);
            if (in_array($trigger, ['always', 'when_error_only'])) {
                update_post_meta(intval($_POST['post_id']), '_mmwm_notification_trigger', $trigger);
                wp_send_json_success();
            }
        }
        wp_send_json_error();
    }

    public function handle_ajax_bulk_add()
    {
        check_ajax_referer('mmwm_ajax_nonce');
        if (!current_user_can('publish_posts') || !isset($_POST['url'])) wp_send_json_error(['message' => 'Permission denied or missing URL.']);

        $url = esc_url_raw(trim($_POST['url']));
        if (!filter_var($url, FILTER_VALIDATE_URL)) wp_send_json_error(['message' => 'Invalid URL format: ' . esc_html($url)]);

        $host = parse_url($url, PHP_URL_HOST);
        $title = $host ? str_replace('www.', '', $host) : 'Untitled-' . time();

        if (get_page_by_title($title, OBJECT, 'mmwm_website')) wp_send_json_error(['message' => 'Website already exists: ' . esc_html($title)]);

        $post_id = wp_insert_post(['post_title' => $title, 'post_type' => 'mmwm_website', 'post_status' => 'publish']);

        if (is_wp_error($post_id)) {
            wp_send_json_error(['message' => 'Failed to create post for ' . esc_html($title)]);
        } else {
            update_post_meta($post_id, '_mmwm_target_url', $url);
            update_post_meta($post_id, '_mmwm_host_in', 'Belum diisi');
            update_post_meta($post_id, '_mmwm_interval', 15);
            update_post_meta($post_id, '_mmwm_notification_email', get_option('mmwm_default_email', get_option('admin_email')));
            update_post_meta($post_id, '_mmwm_notification_trigger', 'always');
            update_post_meta($post_id, '_mmwm_check_type', 'response_code');
            update_post_meta($post_id, '_mmwm_monitoring_status', 'active');
            (new MMWM_Cron())->perform_check($post_id);
            wp_send_json_success(['message' => 'Successfully added & checked: ' . esc_html($title)]);
        }
    }

    public function handle_ajax_bulk_action()
    {
        check_ajax_referer('mmwm_ajax_nonce');
        if (!current_user_can('edit_posts') || !isset($_POST['post_id']) || !isset($_POST['bulk_action'])) wp_send_json_error(['message' => 'Permission denied or missing data.']);

        $post_id = intval($_POST['post_id']);
        $action = sanitize_text_field($_POST['bulk_action']);

        $response_message = 'Action completed.';
        if ($action === 'check-now') {
            (new MMWM_Cron())->perform_check($post_id);
            $response_message = 'Check initiated.';
        } elseif (in_array($action, ['start', 'pause'])) {
            $new_status = ($action === 'start') ? 'active' : 'paused';
            update_post_meta($post_id, '_mmwm_monitoring_status', $new_status);
            $response_message = 'Status set to ' . $new_status;
        } else {
            wp_send_json_error(['message' => 'Invalid action.']);
        }
        wp_send_json_success(['message' => $response_message]);
    }
}
