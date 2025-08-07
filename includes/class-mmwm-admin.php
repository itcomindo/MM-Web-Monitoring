<?php

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

class MMWM_Admin
{

    // #FITUR BARU: Halaman Global Options
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

    // #FITUR BARU: Halaman Bulk Add
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
        // ... (kode ini tetap sama dari versi sebelumnya) ...
    ?>
        <div class="wrap">
            <h1><?php _e('Add Websites in Bulk', 'mm-web-monitoring'); ?></h1>
            <p><?php _e('Enter one website URL per line. The plugin will automatically use the domain name as the title.', 'mm-web-monitoring'); ?></p>

            <textarea id="mmwm-bulk-urls" rows="15" class="large-text" placeholder="https://example.com/&#10;https://wordpress.org/&#10;https://another-site.net/path"></textarea>

            <p>
                <button type="button" class="button button-primary" id="mmwm-start-bulk-add"><?php _e('Add Bulk Monitoring', 'mm-web-monitoring'); ?></button>
            </p>

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

    // #MODIFIKASI: Kolom Kustom di daftar CPT
    public function add_custom_columns($columns)
    {
        $new_columns = [];
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = __('Website', 'mm-web-monitoring');
        $new_columns['check_result'] = __('Check Result', 'mm-web-monitoring');
        $new_columns['last_check'] = __('Last Check', 'mm-web-monitoring');
        $new_columns['next_check'] = __('Next Check', 'mm-web-monitoring');
        $new_columns['monitoring_status'] = __('Monitoring Status', 'mm-web-monitoring');
        $new_columns['interval'] = __('Interval', 'mm-web-monitoring');
        $new_columns['email_report'] = __('Email Report', 'mm-web-monitoring');
        $new_columns['email_notification'] = __('Email Notification', 'mm-web-monitoring');
        $new_columns['website_link'] = __('Website Link', 'mm-web-monitoring');
        $new_columns['actions'] = __('Actions', 'mm-web-monitoring');
        return $new_columns;
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

            case 'last_check':
                $last_check_timestamp = get_post_meta($post_id, '_mmwm_last_check', true);
                if ($last_check_timestamp) {
                    echo sprintf('<span title="%s">%s</span>', esc_attr(wp_date('Y-m-d H:i:s', $last_check_timestamp)), esc_html(human_time_diff($last_check_timestamp) . ' ago'));
                } else {
                    echo 'N/A';
                }
                break;

            case 'next_check':
                $last_check_timestamp = get_post_meta($post_id, '_mmwm_last_check', true);
                $interval = get_post_meta($post_id, '_mmwm_interval', true) ?: 15;
                if ($last_check_timestamp) {
                    $next_check_timestamp = $last_check_timestamp + ($interval * 60);
                    echo '<span class="mmwm-next-check" data-timestamp="' . esc_attr($next_check_timestamp) . '">Calculating...</span>';
                } else {
                    echo 'N/A';
                }
                break;

            case 'monitoring_status':
                $monitoring_status = get_post_meta($post_id, '_mmwm_monitoring_status', true) ?: 'stopped';
                $bg_color = '#646970'; // Stopped
                if ($monitoring_status === 'active') $bg_color = '#008a20'; // Active (darker green)
                if ($monitoring_status === 'paused') $bg_color = '#d98500'; // Paused (darker orange)
                echo '<span style="background-color:' . esc_attr($bg_color) . '; color:white; padding: 4px 8px; border-radius: 4px; font-weight: bold; font-size: 12px;">' . esc_html(ucfirst($monitoring_status)) . '</span>';
                break;

            case 'interval':
                $interval = get_post_meta($post_id, '_mmwm_interval', true) ?: 15;
                echo '<span class="mmwm-interval-val" data-postid="' . $post_id . '" title="Click to change">' . esc_html($interval) . ' min</span>';
                break;

            case 'email_report':
                $trigger = get_post_meta($post_id, '_mmwm_notification_trigger', true) ?: 'always';
                echo '<span class="mmwm-email-trigger-val" data-postid="' . $post_id . '" title="Click to change">' . esc_html(ucfirst($trigger)) . '</span>';
                break;

            case 'email_notification':
                $email = get_post_meta($post_id, '_mmwm_notification_email', true);
                if (!$email) {
                    $email = get_option('mmwm_default_email', get_option('admin_email'));
                }
                echo '<span class="mmwm-email-notification-val" data-postid="' . $post_id . '" title="Click to change">' . esc_html($email) . '</span>';
                break;

            case 'website_link':
                $url = get_post_meta($post_id, '_mmwm_target_url', true);
                if ($url) {
                    echo '<a href="' . esc_url($url) . '" target="_blank" title="' . esc_attr($url) . '">Visit</a>';
                } else {
                    echo 'N/A';
                }
                break;

            case 'actions':
                $monitoring_status = get_post_meta($post_id, '_mmwm_monitoring_status', true) ?: 'stopped';
                echo '<button class="button button-small mmwm-action-btn" data-action="run_check_now" data-postid="' . $post_id . '">Check Now</button>';
                if ($monitoring_status === 'active') {
                    echo '<button class="button button-small mmwm-action-btn" data-action="paused" data-postid="' . $post_id . '">Pause</button>';
                } else {
                    echo '<button class="button button-small button-primary mmwm-action-btn" data-action="active" data-postid="' . $post_id . '">Start</button>';
                }
                if ($monitoring_status !== 'stopped') {
                    echo '<button class="button button-small button-danger mmwm-action-btn" data-action="stopped" data-postid="' . $post_id . '">Stop</button>';
                }
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
        return $columns;
    }

    public function sort_custom_columns($query)
    {
        if (!is_admin() || !$query->is_main_query() || $query->get('post_type') !== 'mmwm_website') {
            return;
        }
        $orderby = $query->get('orderby');
        $meta_keys = [
            'check_result'      => '_mmwm_status',
            'last_check'        => '_mmwm_last_check',
            'monitoring_status' => '_mmwm_monitoring_status',
            'interval'          => '_mmwm_interval',
        ];

        if (isset($meta_keys[$orderby])) {
            $query->set('meta_key', $meta_keys[$orderby]);
            if ($orderby === 'last_check' || $orderby === 'interval') {
                $query->set('orderby', 'meta_value_num');
            } else {
                $query->set('orderby', 'meta_value');
            }
        }
    }

    // #MODIFIKASI: Menambahkan Notifikasi & JavaScript
    public function add_list_page_scripts_and_styles()
    {
        global $current_screen;
        if (!$current_screen || ($current_screen->id !== 'edit-mmwm_website' && $current_screen->id !== 'mmwm_website_page_mmwm-bulk-add' && $current_screen->id !== 'mmwm_website_page_mmwm-global-options')) {
            return;
        }

        $ajax_nonce = wp_create_nonce('mmwm_ajax_nonce');
    ?>
        <style>
            /* UI Styles */
            .column-actions button {
                margin-right: 4px !important;
                margin-bottom: 4px;
            }

            .mmwm-interval-val,
            .mmwm-email-trigger-val,
            .mmwm-email-notification-val {
                cursor: pointer;
                border-bottom: 1px dashed #2271b1;
            }

            .widefat .column-actions {
                width: 250px;
            }

            .widefat .column-monitoring_status {
                width: 130px;
            }

            .widefat .column-interval {
                width: 90px;
            }

            /* Progress Bar */
            #mmwm-progress-bar {
                width: 0%;
                height: 24px;
                background: linear-gradient(45deg, #2a94d6, #2271b1);
                box-shadow: inset 0 -1px 0 rgba(0, 0, 0, 0.15);
                border-radius: 3px;
                text-align: center;
                color: white;
                line-height: 24px;
                transition: width 0.4s ease-in-out;
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

            #mmwm-bulk-log div {
                padding: 5px 3px;
                border-bottom: 1px solid #e0e0e0;
            }

            #mmwm-bulk-log div:last-child {
                border-bottom: none;
            }

            /* Notifikasi Custom */
            .mmwm-notification {
                position: fixed;
                top: 30px;
                right: 20px;
                background-color: #4CAF50;
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
        </style>

        <div id="mmwm-notification" class="mmwm-notification"></div>

        <?php

        if ($current_screen->id === 'edit-mmwm_website') :
        ?>
            <script type="text/template" id="tmpl-bulk-actions-template">
                <div id="mmwm-bulk-controls" style="margin: 10px 0;">
                    <button class="button" id="mmwm-bulk-check-now">Bulk Check Now</button>
                    <button class="button" id="mmwm-bulk-start">Bulk Start</button>
                    <button class="button" id="mmwm-bulk-pause">Bulk Pause</button>
                    <span id="mmwm-bulk-spinner" class="spinner" style="float: none; vertical-align: middle;"></span>
                </div>
                 <div id="mmwm-bulk-progress-wrapper" style="display:none;">
                    <div id="mmwm-progress-bar-container" style="background-color: #ddd; border-radius: 5px; padding: 3px; margin-top: 10px; box-shadow: inset 0 1px 2px rgba(0,0,0,0.1);">
                        <div id="mmwm-progress-bar">0%</div>
                    </div>
                    <div id="mmwm-bulk-log" style="height: 150px;"></div>
                </div>
            </script>
            <script type="text/javascript">
                jQuery(document).ready(function($) {
                    var ajax_nonce = '<?php echo $ajax_nonce; ?>';

                    // Fungsi untuk menampilkan notifikasi
                    function showNotification(message, isError = false) {
                        var notification = $('#mmwm-notification');
                        notification.text(message).removeClass('error').addClass(isError ? 'error' : 'success').show();
                        setTimeout(function() {
                            notification.fadeOut(500);
                        }, 5000);
                    }

                    // Tambahkan tombol aksi massal di atas tabel
                    $('.wrap h1').after($('#tmpl-bulk-actions-template').html());

                    // --- Next Check Countdown ---
                    function updateCountdown() {
                        $('.mmwm-next-check').each(function() {
                            var $this = $(this);
                            var timestamp = $this.data('timestamp');
                            if (!timestamp) return;

                            var remainingTime = timestamp - (Date.now() / 1000); // Waktu dalam detik

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


                    // --- Inline Edit: Interval ---
                    $('#the-list').on('click', '.mmwm-interval-val', function() {
                        var span = $(this);
                        if (span.find('select').length > 0) return;

                        var currentVal = parseInt(span.text()),
                            postId = span.data('postid'),
                            intervals = [5, 10, 15, 20, 25, 30, 45, 60],
                            select = $('<select>');
                        intervals.forEach(function(val) {
                            select.append($('<option>').val(val).text(val + ' min').prop('selected', val === currentVal));
                        });

                        span.html(select);
                        select.focus();

                        select.on('change blur', function() {
                            var newVal = $(this).val();
                            span.text(newVal + ' min');

                            $.post(ajaxurl, {
                                action: 'mmwm_update_interval',
                                post_id: postId,
                                new_interval: newVal,
                                _ajax_nonce: ajax_nonce
                            }).done(function() {
                                showNotification('Interval updated successfully!');
                            }).fail(function() {
                                showNotification('Failed to update interval.', true);
                            });
                        });
                    });

                    // --- Inline Edit: Email Report Trigger ---
                    $('#the-list').on('click', '.mmwm-email-trigger-val', function() {
                        var span = $(this);
                        if (span.find('select').length > 0) return;
                        var currentVal = span.text().toLowerCase(),
                            postId = span.data('postid'),
                            select = $('<select>');

                        select.append($('<option>').val('always').text('Always').prop('selected', currentVal === 'always'));
                        select.append($('<option>').val('when_error_only').text('On Error Only').prop('selected', currentVal === 'when_error_only'));

                        span.html(select);
                        select.focus();

                        select.on('change blur', function() {
                            var newVal = $(this).val();
                            span.text(newVal.charAt(0).toUpperCase() + newVal.slice(1));

                            $.post(ajaxurl, {
                                action: 'mmwm_update_notification_trigger',
                                post_id: postId,
                                new_trigger: newVal,
                                _ajax_nonce: ajax_nonce
                            }).done(function() {
                                showNotification('Email trigger updated successfully!');
                            }).fail(function() {
                                showNotification('Failed to update email trigger.', true);
                            });
                        });
                    });

                    // --- Inline Edit: Notification Email ---
                    $('#the-list').on('click', '.mmwm-email-notification-val', function() {
                        var span = $(this);
                        if (span.find('input').length > 0) return;

                        var currentVal = span.text(),
                            postId = span.data('postid'),
                            input = $('<input type="email" style="width: 150px;">').val(currentVal);

                        span.html(input);
                        input.focus();

                        input.on('blur', function() {
                            var newVal = $(this).val();
                            span.text(newVal);

                            $.post(ajaxurl, {
                                action: 'mmwm_update_notification_email',
                                post_id: postId,
                                new_email: newVal,
                                _ajax_nonce: ajax_nonce
                            }).done(function() {
                                showNotification('Notification email updated successfully!');
                            }).fail(function() {
                                showNotification('Failed to update notification email.', true);
                            });
                        });
                    });

                    // --- Single Action Button Handler ---
                    $('#the-list').on('click', '.mmwm-action-btn', function() {
                        var button = $(this),
                            action = button.data('action'),
                            postId = button.data('postid'),
                            spinner = button.siblings('.spinner');

                        if (action === 'stopped' && !confirm('Are you sure? This will stop all automatic checks for this site.')) return;

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
                        }); // Reload to update all statuses
                    });

                    // --- Bulk Action Handler ---
                    $('#mmwm-bulk-controls button').on('click', function() {
                        var action = $(this).attr('id').replace('mmwm-bulk-', ''),
                            selectedIds = [];
                        $('input[name="post[]"]:checked').each(function() {
                            selectedIds.push($(this).val());
                        });

                        if (selectedIds.length === 0) {
                            showNotification('Please select at least one website.', true);
                            return;
                        }

                        var progressWrapper = $('#mmwm-bulk-progress-wrapper'),
                            progressBar = $('#mmwm-progress-bar'),
                            logBox = $('#mmwm-bulk-log');
                        progressWrapper.show();
                        logBox.html('');
                        $('#mmwm-bulk-controls button').prop('disabled', true);
                        $('#mmwm-bulk-spinner').addClass('is-active');

                        var processedCount = 0;

                        function processNext() {
                            if (processedCount >= selectedIds.length) {
                                logBox.append('<div><strong>Bulk action completed! Reloading page...</strong></div>');
                                $('#mmwm-bulk-controls button').prop('disabled', false);
                                $('#mmwm-bulk-spinner').removeClass('is-active');
                                setTimeout(function() {
                                    location.reload();
                                }, 2000);
                                return;
                            }
                            var postId = selectedIds[processedCount],
                                postTitle = $('tr#post-' + postId).find('.row-title').text();
                            logBox.append('<div>Processing: ' + postTitle + '...</div>');

                            var ajaxAction = (action === 'check-now') ? 'mmwm_run_check_now' : 'mmwm_update_monitoring_status';
                            var postData = {
                                action: ajaxAction,
                                post_id: postId,
                                _ajax_nonce: ajax_nonce
                            };
                            if (action !== 'check-now') {
                                postData.new_status = action === 'start' ? 'active' : 'paused';
                            }

                            $.post(ajaxurl, postData)
                                .done(function(res) {
                                    logBox.append('<div style="color:green;">' + postTitle + ': ' + res.data.message + '</div>');
                                })
                                .fail(function(res) {
                                    logBox.append('<div style="color:red;">' + postTitle + ': ' + (res.responseJSON ? res.responseJSON.data.message : 'Request failed.') + '</div>');
                                })
                                .always(function() {
                                    processedCount++;
                                    var percent = (processedCount / selectedIds.length) * 100;
                                    progressBar.width(percent + '%').text(Math.round(percent) + '%');
                                    logBox.scrollTop(logBox[0].scrollHeight);

                                    var delay = (action === 'check-now') ? 3000 : 500;
                                    setTimeout(processNext, delay);
                                });
                        }
                        processNext();
                    });
                });
            </script>
        <?php
        endif;

        if ($current_screen->id === 'mmwm_website_page_mmwm-bulk-add') :
        ?>
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
<?php
        endif;
    }

    // AJAX Handlers
    public function handle_ajax_update_interval()
    {
        check_ajax_referer('mmwm_ajax_nonce');
        if (current_user_can('edit_posts') && isset($_POST['post_id']) && isset($_POST['new_interval'])) {
            update_post_meta(intval($_POST['post_id']), '_mmwm_interval', intval($_POST['new_interval']));
            wp_send_json_success();
        }
        wp_send_json_error();
    }

    public function handle_ajax_update_notification_email()
    {
        check_ajax_referer('mmwm_ajax_nonce');
        if (current_user_can('edit_posts') && isset($_POST['post_id']) && isset($_POST['new_email'])) {
            $post_id = intval($_POST['post_id']);
            $email = sanitize_email($_POST['new_email']);
            if (is_email($email)) {
                update_post_meta($post_id, '_mmwm_notification_email', $email);
                wp_send_json_success();
            } else {
                wp_send_json_error(['message' => 'Invalid email format.']);
            }
        }
        wp_send_json_error();
    }

    public function handle_ajax_update_notification_trigger()
    {
        check_ajax_referer('mmwm_ajax_nonce');
        if (current_user_can('edit_posts') && isset($_POST['post_id']) && isset($_POST['new_trigger'])) {
            $post_id = intval($_POST['post_id']);
            $trigger = sanitize_text_field($_POST['new_trigger']);
            if (in_array($trigger, ['always', 'when_error_only'])) {
                update_post_meta($post_id, '_mmwm_notification_trigger', $trigger);
                wp_send_json_success();
            } else {
                wp_send_json_error(['message' => 'Invalid trigger value.']);
            }
        }
        wp_send_json_error();
    }

    public function handle_ajax_bulk_add()
    {
        check_ajax_referer('mmwm_ajax_nonce');
        if (!current_user_can('publish_posts') || !isset($_POST['url'])) {
            wp_send_json_error(['message' => 'Permission denied or missing URL.']);
        }

        $url = esc_url_raw(trim($_POST['url']));
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            wp_send_json_error(['message' => 'Invalid URL format: ' . esc_html($url)]);
        }

        $host = parse_url($url, PHP_URL_HOST);
        $title = $host ? str_replace('www.', '', $host) : 'Untitled-' . time();

        $existing = get_page_by_title($title, OBJECT, 'mmwm_website');
        if ($existing) {
            wp_send_json_error(['message' => 'Website already exists: ' . esc_html($title)]);
        }

        $post_id = wp_insert_post(['post_title' => $title, 'post_type' => 'mmwm_website', 'post_status' => 'publish']);

        if (is_wp_error($post_id)) {
            wp_send_json_error(['message' => 'Failed to create post for ' . esc_html($title)]);
        } else {
            // Get default email from global settings
            $default_email = get_option('mmwm_default_email', get_option('admin_email'));

            update_post_meta($post_id, '_mmwm_target_url', $url);
            update_post_meta($post_id, '_mmwm_interval', 15);
            update_post_meta($post_id, '_mmwm_notification_email', $default_email);
            update_post_meta($post_id, '_mmwm_notification_trigger', 'always');
            update_post_meta($post_id, '_mmwm_check_type', 'response_code');
            update_post_meta($post_id, '_mmwm_monitoring_status', 'active'); // Auto-start on bulk add

            // Run first check immediately
            (new MMWM_Cron())->perform_check($post_id);

            wp_send_json_success(['message' => 'Successfully added: ' . esc_html($title)]);
        }
    }

    public function handle_ajax_bulk_action()
    {
        check_ajax_referer('mmwm_ajax_nonce');
        if (!current_user_can('edit_posts') || !isset($_POST['post_id']) || !isset($_POST['bulk_action'])) {
            wp_send_json_error(['message' => 'Permission denied or missing data.']);
        }

        $post_id = intval($_POST['post_id']);
        $action = sanitize_text_field($_POST['bulk_action']);

        if ($action === 'check-now') {
            (new MMWM_Cron())->perform_check($post_id);
            wp_send_json_success(['message' => 'Check initiated.']);
        } elseif (in_array($action, ['start', 'pause'])) {
            $new_status = ($action === 'start') ? 'active' : 'paused';
            update_post_meta($post_id, '_mmwm_monitoring_status', $new_status);
            wp_send_json_success(['message' => 'Status set to ' . $new_status]);
        } else {
            wp_send_json_error(['message' => 'Invalid action.']);
        }
    }
}
