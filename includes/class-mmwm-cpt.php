<?php

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

class MMWM_CPT
{

    public function register_cpt()
    {
        $labels = array(
            'name'                  => _x('Websites', 'Post Type General Name', 'mm-web-monitoring'),
            'singular_name'         => _x('Website', 'Post Type Singular Name', 'mm-web-monitoring'),
            'menu_name'             => __('Web Monitoring', 'mm-web-monitoring'),
            'name_admin_bar'        => __('Website', 'mm-web-monitoring'),
            'all_items'             => __('All Websites', 'mm-web-monitoring'),
            'add_new_item'          => __('Add New Website', 'mm-web-monitoring'),
            'add_new'               => __('Add New', 'mm-web-monitoring'),
            'new_item'              => __('New Website', 'mm-web-monitoring'),
            'edit_item'             => __('Edit Monitoring Website', 'mm-web-monitoring'),
            'view_item'             => __('View Website', 'mm-web-monitoring'),
            'update_item'           => __('Update Monitor', 'mm-web-monitoring'),
            'search_items'          => __('Search Websites', 'mm-web-monitoring'),
        );
        $args = array(
            'label'                 => __('Website', 'mm-web-monitoring'),
            'labels'                => $labels,
            'supports'              => array('title'),
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 25,
            'menu_icon'             => 'dashicons-networking',
            'publicly_queryable'    => false,
            'capability_type'       => 'post',
        );
        register_post_type('mmwm_website', $args);
    }

    public function add_meta_boxes()
    {
        add_meta_box('mmwm_website_details', 'Monitoring Settings', [$this, 'render_meta_box'], 'mmwm_website', 'normal', 'high');
        add_meta_box('mmwm_website_status', 'Current Monitoring Status', [$this, 'render_status_metabox'], 'mmwm_website', 'side', 'high');
    }

    public function render_status_metabox($post)
    {
        $last_status = get_post_meta($post->ID, '_mmwm_status', true);
        $last_check_timestamp = get_post_meta($post->ID, '_mmwm_last_check', true);

?>
        <div id="mmwm-status-details">
            <p><strong>Status:</strong> <span id="mmwm-current-status"><?php echo esc_html($last_status ?: 'Not yet checked'); ?></span></p>
            <p><strong>Last Check:</strong> <span id="mmwm-last-check"><?php echo $last_check_timestamp ? human_time_diff($last_check_timestamp) . ' ago' : 'N/A'; ?></span></p>
        </div>
    <?php
    }

    public function render_meta_box($post)
    {
        wp_nonce_field('mmwm_save_meta_box_data', 'mmwm_meta_box_nonce');

        $monitoring_status = get_post_meta($post->ID, '_mmwm_monitoring_status', true) ?: 'stopped';
        $email_log = get_post_meta($post->ID, '_mmwm_email_log', true);
        $check_type = get_post_meta($post->ID, '_mmwm_check_type', true) ?: 'response_code';
        $notification_trigger = get_post_meta($post->ID, '_mmwm_notification_trigger', true) ?: 'always';
        $notification_email = get_post_meta($post->ID, '_mmwm_notification_email', true);
        $host_in = get_post_meta($post->ID, '_mmwm_host_in', true);

        $headline_style = 'padding: 10px; color: white; margin-bottom: 15px; font-weight: bold; text-transform: uppercase;';
        if ($monitoring_status === 'active') {
            $headline_text = __('Monitoring is Active', 'mm-web-monitoring');
            $headline_style .= 'background-color: #008a20;';
        } elseif ($monitoring_status === 'paused') {
            $headline_text = __('Monitoring is Paused', 'mm-web-monitoring');
            $headline_style .= 'background-color: #d98500;';
        } else {
            $headline_text = __('Monitoring is Stopped', 'mm-web-monitoring');
            $headline_style .= 'background-color: #646970;';
        }

    ?>
        <div id="mmwm-status-headline" style="<?php echo esc_attr($headline_style); ?>"><?php echo esc_html($headline_text); ?></div>

        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="mmwm_target_url"><?php _e('Target URL', 'mm-web-monitoring'); ?></label></th>
                <td><input type="url" id="mmwm_target_url" name="mmwm_target_url" value="<?php echo esc_url(get_post_meta($post->ID, '_mmwm_target_url', true)); ?>" class="large-text" required /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="mmwm_host_in"><?php _e('Host In', 'mm-web-monitoring'); ?></label></th>
                <td><input type="text" id="mmwm_host_in" name="mmwm_host_in" value="<?php echo esc_attr($host_in); ?>" class="regular-text" placeholder="e.g., Rumahweb, Niagahoster" />
                    <p class="description"><?php _e('Enter the name of the hosting provider for this website.', 'mm-web-monitoring'); ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php _e('Check Type', 'mm-web-monitoring'); ?></label></th>
                <td>
                    <label><input type="radio" name="mmwm_check_type" value="response_code" <?php checked($check_type, 'response_code'); ?>> <?php _e('Response Code Only', 'mm-web-monitoring'); ?></label><br>
                    <label><input type="radio" name="mmwm_check_type" value="fetch_html" <?php checked($check_type, 'fetch_html'); ?>> <?php _e('Fetch HTML Content', 'mm-web-monitoring'); ?></label>
                </td>
            </tr>
            <tr valign="top" id="mmwm_html_selector_row">
                <th scope="row"><label for="mmwm_html_selector"><?php _e('HTML Element to Find', 'mm-web-monitoring'); ?></label></th>
                <td>
                    <textarea name="mmwm_html_selector" id="mmwm_html_selector" rows="3" class="large-text"><?php echo esc_textarea(get_post_meta($post->ID, '_mmwm_html_selector', true)); ?></textarea>
                    <p class="description"><?php _e('Copy and paste the HTML element ID or class (e.g., #main-title or .logo) to check for existence in the fetched HTML.', 'mm-web-monitoring'); ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="mmwm_notification_email"><?php _e('Notification Email', 'mm-web-monitoring'); ?></label></th>
                <td>
                    <input type="email" id="mmwm_notification_email" name="mmwm_notification_email" value="<?php echo esc_attr($notification_email); ?>" class="regular-text" />
                    <p class="description"><?php printf(__('If not filled, will use the global default email: %s', 'mm-web-monitoring'), get_option('mmwm_default_email', get_option('admin_email'))); ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php _e('Send Email', 'mm-web-monitoring'); ?></label></th>
                <td>
                    <fieldset>
                        <label>
                            <input type="radio" name="mmwm_notification_trigger" value="always" <?php checked($notification_trigger, 'always'); ?>>
                            <strong><?php _e('Always', 'mm-web-monitoring'); ?></strong>
                        </label>
                        <p class="description" style="margin-left: 20px;"><?php _e('Kirim email setiap kali pengecekan berjalan, apapun hasilnya (UP, DOWN, atau sama seperti sebelumnya).', 'mm-web-monitoring'); ?></p>
                        <br>
                        <label>
                            <input type="radio" name="mmwm_notification_trigger" value="when_error_only" <?php checked($notification_trigger, 'when_error_only'); ?>>
                            <strong><?php _e('On Error & Recovery', 'mm-web-monitoring'); ?></strong>
                        </label>
                        <p class="description" style="margin-left: 20px;"><?php _e('Kirim email jika status berubah dari UP -> DOWN atau jika status tetap DOWN. Juga kirim saat pulih (DOWN -> UP).', 'mm-web-monitoring'); ?></p>
                    </fieldset>
                </td>
            </tr>
            <tr valign="top" id="mmwm-email-log-row">
                <th scope="row"><label><?php _e('Email Log', 'mm-web-monitoring'); ?></label></th>
                <td>
                    <div id="mmwm-last-email-log"><?php echo esc_html($email_log ?: 'N/A'); ?></div>
                </td>
            </tr>
        </table>

        <?php if ($post->ID) : ?>
            <div id="mmwm-action-buttons" style="margin-top: 15px;">
                <button type="button" class="button button-primary" data-action="active" style="<?php echo ($monitoring_status !== 'active') ? '' : 'display:none;'; ?>">Start</button>
                <button type="button" class="button" data-action="paused" style="<?php echo ($monitoring_status === 'active') ? '' : 'display:none;'; ?>">Pause</button>
                <button type="button" class="button button-danger" data-action="stopped" style="<?php echo ($monitoring_status !== 'stopped') ? '' : 'display:none;'; ?>">Stop</button>
                <button type="button" class="button button-secondary" id="mmwm-run-check-now">Check Now</button>
                <span class="spinner" style="float:none;"></span>
            </div>
        <?php endif; ?>
    <?php
    }

    public function add_cpt_header_scripts()
    {
        global $current_screen;
        if (!$current_screen || 'mmwm_website' != $current_screen->post_type || 'post' != $current_screen->base) {
            return;
        }
    ?>
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                var publishButton = document.getElementById('publish');
                if (publishButton) {
                    if (document.body.classList.contains('post-new-php')) {
                        publishButton.value = '<?php echo esc_js(__('Start Monitoring', 'mm-web-monitoring')); ?>';
                    } else {
                        publishButton.value = '<?php echo esc_js(__('Update Monitor', 'mm-web-monitoring')); ?>';
                    }
                }
            });
        </script>
    <?php
    }

    public function add_meta_box_script()
    {
        global $current_screen;
        if (!$current_screen || 'mmwm_website' != $current_screen->post_type || 'post' != $current_screen->base) {
            return;
        }
        global $post;

        $ajax_nonce = wp_create_nonce('mmwm_ajax_nonce');
    ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                function toggleHtmlSelector() {
                    $('#mmwm_html_selector_row').toggle($('input[name="mmwm_check_type"]:checked').val() === 'fetch_html');
                }
                toggleHtmlSelector();
                $('input[name="mmwm_check_type"]').on('change', toggleHtmlSelector);

                var spinner = $('#mmwm-action-buttons .spinner');
                var postId = <?php echo $post->ID; ?>;

                if (postId > 0) {
                    $('#mmwm-action-buttons button').prop('disabled', false);
                } else {
                    $('#mmwm-action-buttons').hide();
                }

                $('#mmwm-run-check-now').on('click', function() {
                    spinner.addClass('is-active');
                    $(this).prop('disabled', true);

                    $.post(ajaxurl, {
                            action: 'mmwm_run_check_now',
                            post_id: postId,
                            _ajax_nonce: '<?php echo $ajax_nonce; ?>'
                        })
                        .done(function(res) {
                            if (res.success) {
                                $('#mmwm-current-status').text(res.data.status);
                                $('#mmwm-last-check').text('just now');
                                $('#mmwm-last-email-log').text(res.data.email_log);
                            } else {
                                alert('Error: ' + (res.data.message || 'Unknown error'));
                            }
                        }).fail(function() {
                            alert('A server error occurred.');
                        }).always(function() {
                            spinner.removeClass('is-active');
                            $('#mmwm-run-check-now').prop('disabled', false);
                        });
                });

                $('#mmwm-action-buttons button[data-action]').on('click', function() {
                    var button = $(this);
                    var action = button.data('action');

                    if (action === 'stopped' && !confirm('Are you sure? This will stop all automatic checks for this site.')) return;

                    spinner.addClass('is-active');
                    button.closest('#mmwm-action-buttons').find('button').prop('disabled', true);

                    $.post(ajaxurl, {
                            action: 'mmwm_update_monitoring_status',
                            post_id: postId,
                            new_status: action,
                            _ajax_nonce: '<?php echo $ajax_nonce; ?>'
                        })
                        .always(function() {
                            location.reload();
                        });
                });
            });
        </script>
<?php
    }

    public function save_meta_data($post_id, $post)
    {
        if (!isset($_POST['mmwm_meta_box_nonce']) || !wp_verify_nonce($_POST['mmwm_meta_box_nonce'], 'mmwm_save_meta_box_data')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        if ('mmwm_website' !== get_post_type($post_id)) return;

        if (isset($_POST['mmwm_target_url'])) update_post_meta($post_id, '_mmwm_target_url', esc_url_raw($_POST['mmwm_target_url']));
        if (isset($_POST['mmwm_host_in'])) update_post_meta($post_id, '_mmwm_host_in', sanitize_text_field($_POST['mmwm_host_in']));
        if (isset($_POST['mmwm_check_type'])) update_post_meta($post_id, '_mmwm_check_type', sanitize_text_field($_POST['mmwm_check_type']));
        if (isset($_POST['mmwm_html_selector'])) update_post_meta($post_id, '_mmwm_html_selector', sanitize_textarea_field($_POST['mmwm_html_selector']));
        if (isset($_POST['mmwm_notification_email'])) update_post_meta($post_id, '_mmwm_notification_email', sanitize_email($_POST['mmwm_notification_email']));

        if (isset($_POST['mmwm_notification_trigger'])) {
            $trigger = sanitize_text_field($_POST['mmwm_notification_trigger']);
            if (in_array($trigger, ['always', 'when_error_only'])) {
                update_post_meta($post_id, '_mmwm_notification_trigger', $trigger);
            }
        }

        if ($post->post_status === 'publish' && get_post_meta($post_id, '_mmwm_monitoring_status', true) !== 'active') {
            update_post_meta($post_id, '_mmwm_monitoring_status', 'active');

            // --- PERBAIKAN BUG DI SINI ---
            // Panggil fungsi perform_check secara langsung, ini lebih andal daripada menjadwalkan event.
            if (class_exists('MMWM_Cron')) {
                (new MMWM_Cron())->perform_check($post_id);
            }
        }
    }
}
