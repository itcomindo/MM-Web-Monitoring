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
?>
        <div class="wrap">
            <h1><?php _e('Web Monitoring Global Options', 'mm-web-monitoring'); ?></h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('mmwm_global_options');
                do_settings_sections('mmwm-global-options');
                submit_button();
                ?>
            </form>
        </div>
    <?php
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
        <div class="wrap">
            <h1><?php _e('Add Websites in Bulk', 'mm-web-monitoring'); ?></h1>
            <p><?php _e('Enter one website URL per line. The plugin will automatically use the domain name as the title.', 'mm-web-monitoring'); ?></p>
            <textarea id="mmwm-bulk-urls" rows="15" class="large-text" placeholder="https://example.com/&#10;https://wordpress.org/&#10;https://another-site.net/path"></textarea>
            <p><button type="button" class="button button-primary" id="mmwm-start-bulk-add"><?php _e('Add Bulk Monitoring', 'mm-web-monitoring'); ?></button></p>
            <div id="mmwm-bulk-progress-wrapper" style="display:none;">
                <h3><?php _e('Progress:', 'mm-web-monitoring'); ?></h3>
                <div id="mmwm-progress-bar-container" style="background-color: #ddd; border-radius: 5px; padding: 3px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);">
                    <div id="mmwm-progress-bar">0%</div>
                </div>
                <h4><?php _e('Log:', 'mm-web-monitoring'); ?></h4>
                <div id="mmwm-bulk-log"></div>
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
                    echo '<span style="color: #999;">Disabled</span>';
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
                $next_check_timestamp = $scheduler->get_next_check_timestamp($post_id);

                if ($next_check_timestamp) {
                    echo '<span class="mmwm-next-check-countdown" data-timestamp="' . esc_attr($next_check_timestamp) . '" data-postid="' . esc_attr($post_id) . '">';
                    echo esc_html($scheduler->get_next_check_display($post_id));
                    echo '</span>';
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
                            var intervals = [5, 10, 15, 20, 25, 30, 45, 60];
                            editor = $('<select>');
                            intervals.forEach(function(val) {
                                editor.append($('<option>').val(val).text(val + ' min').prop('selected', val === parseInt(currentVal)));
                            });
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
                            if (type === 'interval') displayVal += ' min';
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
