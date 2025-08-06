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
            'archives'              => __('Website Archives', 'mm-web-monitoring'),
            'attributes'            => __('Website Attributes', 'mm-web-monitoring'),
            'parent_item_colon'     => __('Parent Website:', 'mm-web-monitoring'),
            'all_items'             => __('All Websites', 'mm-web-monitoring'),
            'add_new_item'          => __('Add New Website', 'mm-web-monitoring'),
            'add_new'               => __('Add New', 'mm-web-monitoring'),
            'new_item'              => __('New Website', 'mm-web-monitoring'),
            'edit_item'             => __('Edit Monitoring Website', 'mm-web-monitoring'),
            'update_item'           => __('Update Website', 'mm-web-monitoring'),
            'view_item'             => __('View Website', 'mm-web-monitoring'),
            'view_items'            => __('View Websites', 'mm-web-monitoring'),
            'search_items'          => __('Search Website', 'mm-web-monitoring'),
            'not_found'             => __('Not found', 'mm-web-monitoring'),
            'not_found_in_trash'    => __('Not found in Trash', 'mm-web-monitoring'),
            'items_list'            => __('Websites list', 'mm-web-monitoring'),
            'items_list_navigation' => __('Websites list navigation', 'mm-web-monitoring'),
            'filter_items_list'     => __('Filter websites list', 'mm-web-monitoring'),
        );
        $args = array(
            'label'                 => __('Website', 'mm-web-monitoring'),
            'description'           => __('Websites to Monitor', 'mm-web-monitoring'),
            'labels'                => $labels,
            'supports'              => array('title'),
            'hierarchical'          => false,
            'public'                => false,
            'show_ui'               => true,
            'show_in_menu'          => true,
            'menu_position'         => 25,
            'menu_icon'             => 'dashicons-networking',
            'show_in_admin_bar'     => true,
            'show_in_nav_menus'     => false,
            'can_export'            => true,
            'has_archive'           => false,
            'exclude_from_search'   => true,
            'publicly_queryable'    => false,
            'capability_type'       => 'post',
            'show_in_rest'          => false,
        );
        register_post_type('mmwm_website', $args);
    }

    public function add_meta_boxes()
    {
        add_meta_box('mmwm_website_details', 'Monitoring Settings', [$this, 'render_meta_box'], 'mmwm_website', 'normal', 'high');
    }

    public function render_meta_box($post)
    {
        wp_nonce_field('mmwm_save_meta_box_data', 'mmwm_meta_box_nonce');

        $monitoring_status = get_post_meta($post->ID, '_mmwm_monitoring_status', true) ?: 'stopped';
        $email_log = get_post_meta($post->ID, '_mmwm_email_log', true);
        $check_type = get_post_meta($post->ID, '_mmwm_check_type', true) ?: 'response_code';

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
                <th scope="row"><label><?php _e('Check Type', 'mm-web-monitoring'); ?></label></th>
                <td>
                    <label><input type="radio" name="mmwm_check_type" value="response_code" <?php checked($check_type, 'response_code'); ?>> <?php _e('Response Code Only', 'mm-web-monitoring'); ?></label><br>
                    <label><input type="radio" name="mmwm_check_type" value="fetch_html" <?php checked($check_type, 'fetch_html'); ?>> <?php _e('Fetch HTML Content', 'mm-web-monitoring'); ?></label>
                </td>
            </tr>
            <tr valign="top" id="mmwm_html_selector_row">
                <th scope="row"><label for="mmwm_html_selector"><?php _e('HTML Element to Find', 'mm-web-monitoring'); ?></label></th>
                <td><textarea name="mmwm_html_selector" id="mmwm_html_selector" rows="3" class="large-text"><?php echo esc_textarea(get_post_meta($post->ID, '_mmwm_html_selector', true)); ?></textarea></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="mmwm_notification_email"><?php _e('Notification Email', 'mm-web-monitoring'); ?></label></th>
                <td><input type="email" id="mmwm_notification_email" name="mmwm_notification_email" value="<?php echo esc_attr(get_post_meta($post->ID, '_mmwm_notification_email', true) ?: get_option('admin_email')); ?>" class="regular-text" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php _e('Send Email', 'mm-web-monitoring'); ?></label></th>
                <td>
                    <fieldset>
                        <label>
                            <input type="radio" name="mmwm_notification_trigger" value="always" <?php checked(get_post_meta($post->ID, '_mmwm_notification_trigger', true) ?: 'always', 'always'); ?>>
                            <strong><?php _e('Always', 'mm-web-monitoring'); ?></strong>
                        </label>
                        <p class="description" style="margin-left: 20px;"><?php _e('Kirim email setiap kali pengecekan berjalan, apapun hasilnya (UP, DOWN, atau sama seperti sebelumnya).', 'mm-web-monitoring'); ?></p>
                        <br>
                        <label>
                            <input type="radio" name="mmwm_notification_trigger" value="when_error_only" <?php checked(get_post_meta($post->ID, '_mmwm_notification_trigger', true), 'when_error_only'); ?>>
                            <strong><?php _e('On Error & Recovery', 'mm-web-monitoring'); ?></strong>
                        </label>
                        <p class="description" style="margin-left: 20px;"><?php _e('Kirim email hanya jika status UP -> DOWN, DOWN -> DOWN, atau DOWN -> UP (pemulihan). Tidak mengirim jika status stabil UP.', 'mm-web-monitoring'); ?></p>
                    </fieldset>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php _e('Last Check Details', 'mm-web-monitoring'); ?></label></th>
                <td>
                    <div id="mmwm-last-status-text"><strong>Status:</strong> <?php echo esc_html(get_post_meta($post->ID, '_mmwm_status', true) ?: 'Not yet checked'); ?></div>
                    <div id="mmwm-last-email-log"><strong>Email Log:</strong> <?php echo esc_html($email_log ?: 'N/A'); ?></div>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php _e('Actions', 'mm-web-monitoring'); ?></label></th>
                <td>
                    <div id="mmwm-action-buttons">
                        <button type="button" class="button button-primary" data-action="active" style="<?php echo ($monitoring_status !== 'active') ? '' : 'display:none;'; ?>">Start</button>
                        <button type="button" class="button" data-action="paused" style="<?php echo ($monitoring_status === 'active') ? '' : 'display:none;'; ?>">Pause</button>
                        <button type="button" class="button button-danger" data-action="stopped" style="<?php echo ($monitoring_status !== 'stopped') ? '' : 'display:none;'; ?>">Stop</button>
                        <button type="button" class="button button-secondary" id="mmwm-run-check-now">Check Now</button>
                        <span class="spinner" style="float:none;"></span>
                    </div>
                </td>
            </tr>
        </table>
    <?php
    }

    /**
     * Changes the "Edit Post" title to "Edit Monitoring Website" using JavaScript.
     * This is a safer method than using the 'gettext' filter.
     */
    public function change_cpt_edit_title_script()
    {
        global $current_screen;
        if (!$current_screen || 'mmwm_website' != $current_screen->post_type || 'post' != $current_screen->base) {
            return;
        }
    ?>
        <script type="text/javascript">
            document.addEventListener('DOMContentLoaded', function() {
                var h1 = document.querySelector('.wrap h1');
                if (h1 && h1.childNodes[0].nodeValue.trim() === 'Edit Post') {
                    h1.childNodes[0].nodeValue = '<?php echo esc_js(__('Edit Monitoring Website', 'mm-web-monitoring')); ?> ';
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

                // Check Now Button
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
                                $('#mmwm-last-status-text').html('<strong>Status:</strong> ' + res.data.status);
                                $('#mmwm-last-email-log').html('<strong>Email Log:</strong> ' + res.data.email_log);
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

                // Start/Pause/Stop Buttons
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

    public function save_meta_data($post_id)
    {
        if (!isset($_POST['mmwm_meta_box_nonce']) || !wp_verify_nonce($_POST['mmwm_meta_box_nonce'], 'mmwm_save_meta_box_data')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (!current_user_can('edit_post', $post_id)) return;
        if ('mmwm_website' !== get_post_type($post_id)) return;

        if (isset($_POST['mmwm_target_url'])) update_post_meta($post_id, '_mmwm_target_url', esc_url_raw($_POST['mmwm_target_url']));
        if (isset($_POST['mmwm_check_type'])) update_post_meta($post_id, '_mmwm_check_type', sanitize_text_field($_POST['mmwm_check_type']));
        if (isset($_POST['mmwm_html_selector'])) update_post_meta($post_id, '_mmwm_html_selector', sanitize_textarea_field($_POST['mmwm_html_selector']));
        if (isset($_POST['mmwm_notification_email'])) update_post_meta($post_id, '_mmwm_notification_email', sanitize_email($_POST['mmwm_notification_email']));

        if (isset($_POST['mmwm_notification_trigger'])) {
            $trigger = sanitize_text_field($_POST['mmwm_notification_trigger']);
            if (in_array($trigger, ['always', 'when_error_only'])) {
                update_post_meta($post_id, '_mmwm_notification_trigger', $trigger);
            }
        }

        if (empty(get_post_meta($post_id, '_mmwm_monitoring_status', true))) {
            update_post_meta($post_id, '_mmwm_monitoring_status', 'stopped');
        }
    }
}
