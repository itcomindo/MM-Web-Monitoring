<?php

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

class MMWM_Admin
{

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

    // #MODIFIKASI 1: Kolom Kustom di daftar CPT
    public function add_custom_columns($columns)
    {
        $new_columns = [];
        $new_columns['cb'] = $columns['cb'];
        $new_columns['title'] = __('Website', 'mm-web-monitoring');
        $new_columns['check_result'] = __('Check Result', 'mm-web-monitoring');
        $new_columns['last_check'] = __('Last Check', 'mm-web-monitoring');
        $new_columns['monitoring_status'] = __('Monitoring Status', 'mm-web-monitoring');
        $new_columns['interval'] = __('Interval', 'mm-web-monitoring');
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

    public function add_list_page_scripts_and_styles()
    {
        global $current_screen;
        // Hanya jalankan jika kita berada di halaman yang tepat
        if ($current_screen->id !== 'edit-mmwm_website' && $current_screen->id !== 'mmwm_website_page_mmwm-bulk-add') {
            return;
        }

        $ajax_nonce = wp_create_nonce('mmwm_ajax_nonce');
    ?>
        <style>
            .column-actions button {
                margin-right: 4px !important;
                margin-bottom: 4px;
            }

            .mmwm-interval-val {
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

            /* Style untuk Progress Bar Cantik */
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
        </style>
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

                    $('.wrap h1').after($('#tmpl-bulk-actions-template').html());

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
                        span.html(select).find('select').focus().on('change blur', function() {
                            var newVal = $(this).val();
                            span.text(newVal + ' min');
                            $.post(ajaxurl, {
                                action: 'mmwm_update_interval',
                                post_id: postId,
                                new_interval: newVal,
                                _ajax_nonce: ajax_nonce
                            });
                        });
                    });

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
                        });
                    });

                    $('#mmwm-bulk-controls button').on('click', function() {
                        var action = $(this).attr('id').replace('mmwm-bulk-', ''),
                            selectedIds = [];
                        $('input[name="post[]"]:checked').each(function() {
                            selectedIds.push($(this).val());
                        });
                        if (selectedIds.length === 0) {
                            alert('Please select at least one website.');
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
                            $.post(ajaxurl, {
                                    action: 'mmwm_bulk_action_handler',
                                    post_id: postId,
                                    bulk_action: action,
                                    _ajax_nonce: ajax_nonce
                                })
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
                                    setTimeout(processNext, action === 'check-now' ? 3000 : 500);
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

        // Cek jika sudah ada
        $existing = get_page_by_title($title, OBJECT, 'mmwm_website');
        if ($existing) {
            wp_send_json_error(['message' => 'Website already exists: ' . esc_html($title)]);
        }

        $post_id = wp_insert_post(['post_title' => $title, 'post_type' => 'mmwm_website', 'post_status' => 'publish']);

        if (is_wp_error($post_id)) {
            wp_send_json_error(['message' => 'Failed to create post for ' . esc_html($title)]);
        } else {
            update_post_meta($post_id, '_mmwm_target_url', $url);
            update_post_meta($post_id, '_mmwm_interval', 15);
            update_post_meta($post_id, '_mmwm_notification_email', 'me@budiharyono.com');
            update_post_meta($post_id, '_mmwm_notification_trigger', 'always');
            update_post_meta($post_id, '_mmwm_check_type', 'response_code');
            update_post_meta($post_id, '_mmwm_monitoring_status', 'stopped');
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
