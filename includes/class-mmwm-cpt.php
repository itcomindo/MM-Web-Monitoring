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
            'edit_item'             => __('Edit Website', 'mm-web-monitoring'),
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
        add_meta_box(
            'mmwm_website_details',
            __('Monitoring Settings', 'mm-web-monitoring'),
            array($this, 'render_meta_box'),
            'mmwm_website',
            'normal',
            'high'
        );
    }

    public function render_meta_box($post)
    {
        wp_nonce_field('mmwm_save_meta_box_data', 'mmwm_meta_box_nonce');

        $target_url             = get_post_meta($post->ID, '_mmwm_target_url', true);
        $interval               = get_post_meta($post->ID, '_mmwm_interval', true);
        $check_type             = get_post_meta($post->ID, '_mmwm_check_type', true);
        $html_selector          = get_post_meta($post->ID, '_mmwm_html_selector', true);
        $notification_email     = get_post_meta($post->ID, '_mmwm_notification_email', true);
        $notification_trigger   = get_post_meta($post->ID, '_mmwm_notification_trigger', true) ?: 'always'; // Default 'always'
        $last_status            = get_post_meta($post->ID, '_mmwm_status', true);
        $monitoring_status      = get_post_meta($post->ID, '_mmwm_monitoring_status', true) ?: 'stopped';

        $headline_style = 'padding: 10px; color: white; margin-bottom: 15px; font-weight: bold;';
        if ($monitoring_status === 'active') {
            $headline_text = __('Monitoring is Active', 'mm-web-monitoring');
            $headline_style .= 'background-color: #2271b1;'; // Blue
        } elseif ($monitoring_status === 'paused') {
            $headline_text = __('Monitoring is Paused', 'mm-web-monitoring');
            $headline_style .= 'background-color: #d63638;'; // Red
        } else { // stopped
            $headline_text = __('Monitoring is Stopped', 'mm-web-monitoring');
            $headline_style .= 'background-color: #50575e;'; // Gray
        }
?>

        <div id="mmwm-status-headline" style="<?php echo esc_attr($headline_style); ?>"><?php echo esc_html($headline_text); ?></div>

        <?php if ($monitoring_status === 'stopped' && $post->post_status === 'publish') : ?>
            <div class="notice notice-warning inline">
                <p><strong><?php _e('Action Required:', 'mm-web-monitoring'); ?></strong> <?php _e('This website is not being monitored. Click the "Start Monitoring" button below to begin checks.', 'mm-web-monitoring'); ?></p>
            </div>
        <?php endif; ?>

        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="mmwm_target_url"><?php _e('Target URL', 'mm-web-monitoring'); ?></label></th>
                <td><input type="url" id="mmwm_target_url" name="mmwm_target_url" value="<?php echo esc_url($target_url); ?>" class="large-text" required placeholder="https://example.com/page-to-check" /></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="mmwm_interval"><?php _e('Check Interval', 'mm-web-monitoring'); ?></label></th>
                <td>
                    <select name="mmwm_interval" id="mmwm_interval">
                        <?php
                        $intervals = [5, 10, 15, 20, 25, 30, 45, 60];
                        foreach ($intervals as $value) {
                            echo '<option value="' . esc_attr($value) . '" ' . selected($interval, $value, false) . '>' . sprintf(__('%d minutes', 'mm-web-monitoring'), $value) . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php _e('Check Type', 'mm-web-monitoring'); ?></label></th>
                <td>
                    <label><input type="radio" name="mmwm_check_type" value="response_code" <?php checked($check_type, 'response_code'); ?>> <?php _e('Response Code Only', 'mm-web-monitoring'); ?></label><br>
                    <label><input type="radio" name="mmwm_check_type" value="fetch_html" <?php checked($check_type, 'fetch_html'); ?>> <?php _e('Fetch HTML Content', 'mm-web-monitoring'); ?></label>
                </td>
            </tr>
            <tr valign="top" id="mmwm_html_selector_row" style="display: <?php echo ($check_type === 'fetch_html') ? 'table-row' : 'none'; ?>;">
                <th scope="row"><label for="mmwm_html_selector"><?php _e('HTML Element to Find', 'mm-web-monitoring'); ?></label></th>
                <td><textarea name="mmwm_html_selector" id="mmwm_html_selector" rows="3" class="large-text"><?php echo esc_textarea($html_selector); ?></textarea></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label for="mmwm_notification_email"><?php _e('Notification Email', 'mm-web-monitoring'); ?></label></th>
                <td><input type="email" id="mmwm_notification_email" name="mmwm_notification_email" value="<?php echo esc_attr($notification_email ?: get_option('admin_email')); ?>" class="regular-text" /></td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label><?php _e('Send Email', 'mm-web-monitoring'); ?></label>
                </th>
                <td>
                    <fieldset>
                        <label>
                            <input type="radio" name="mmwm_notification_trigger" value="always" <?php checked($notification_trigger, 'always'); ?>>
                            <?php _e('Always (on any status change)', 'mm-web-monitoring'); ?>
                        </label>
                        <p class="description" style="margin-left: 20px;"><?php _e('Sends an email when status changes from UP to DOWN, or from DOWN to UP.', 'mm-web-monitoring'); ?></p>
                        <br>
                        <label>
                            <input type="radio" name="mmwm_notification_trigger" value="when_error_only" <?php checked($notification_trigger, 'when_error_only'); ?>>
                            <?php _e('On Error Only', 'mm-web-monitoring'); ?>
                        </label>
                        <p class="description" style="margin-left: 20px;"><?php _e('Only sends an email when the status changes to DOWN or CONTENT_ERROR.', 'mm-web-monitoring'); ?></p>
                    </fieldset>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php _e('Last Check Result', 'mm-web-monitoring'); ?></label></th>
                <td><strong id="mmwm-last-status-text"><?php echo esc_html($last_status ?: 'Not yet checked'); ?></strong></td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php _e('Actions', 'mm-web-monitoring'); ?></label></th>
                <td>
                    <button type="button" class="button button-secondary" id="mmwm-run-check-now" data-postid="<?php echo $post->ID; ?>">
                        <?php _e('Run Check Now', 'mm-web-monitoring'); ?>
                    </button>
                    <span id="mmwm-check-now-progress" style="display:none; vertical-align: middle; margin-left: 10px;"><?php _e('Please wait...', 'mm-web-monitoring'); ?></span>
                    <hr style="margin: 15px 0;">

                    <?php if ($monitoring_status === 'active'): ?>
                        <button type="button" class="button" id="mmwm-toggle-monitoring" data-postid="<?php echo $post->ID; ?>" data-action="paused"><?php _e('Pause Monitoring', 'mm-web-monitoring'); ?></button>
                    <?php else: ?>
                        <button type="button" class="button button-primary" id="mmwm-toggle-monitoring" data-postid="<?php echo $post->ID; ?>" data-action="active"><?php _e('Start Monitoring', 'mm-web-monitoring'); ?></button>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    <?php
    }

    public function add_meta_box_script()
    {
        global $post_type;
        if ('mmwm_website' != $post_type) return;

        $ajax_nonce = wp_create_nonce('mmwm_ajax_nonce');
    ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                var ajax_nonce = '<?php echo $ajax_nonce; ?>';

                // Toggle HTML selector field
                function toggleHtmlSelector() {
                    $('#mmwm_html_selector_row').toggle($('input[name="mmwm_check_type"]:checked').val() === 'fetch_html');
                }
                toggleHtmlSelector();
                $('input[name="mmwm_check_type"]').on('change', toggleHtmlSelector);

                // Run Check Now
                $('#mmwm-run-check-now').on('click', function() {
                    var button = $(this);
                    var post_id = button.data('postid');
                    var progress = $('#mmwm-check-now-progress');

                    button.prop('disabled', true);
                    progress.show();

                    $.post(ajaxurl, {
                            action: 'mmwm_run_check_now',
                            post_id: post_id,
                            _ajax_nonce: ajax_nonce
                        })
                        .done(function(response) {
                            if (response.success) {
                                $('#mmwm-last-status-text').text(response.data.status);
                            } else {
                                alert('Error: ' + response.data.message);
                            }
                        }).always(function() {
                            button.prop('disabled', false);
                            progress.hide();
                        });
                });

                // Start/Pause Monitoring
                $('#mmwm-toggle-monitoring').on('click', function() {
                    var button = $(this);
                    var post_id = button.data('postid');
                    var new_status = button.data('action');

                    button.prop('disabled', true);
                    $.post(ajaxurl, {
                            action: 'mmwm_update_monitoring_status',
                            post_id: post_id,
                            new_status: new_status,
                            _ajax_nonce: ajax_nonce
                        })
                        .done(function(response) {
                            if (response.success) {
                                location.reload();
                            } else {
                                alert('Error: ' + response.data.message);
                                button.prop('disabled', false);
                            }
                        });
                });
            });
        </script>
<?php
    }

    public function save_meta_data($post_id)
    {
        if (! isset($_POST['mmwm_meta_box_nonce']) || ! wp_verify_nonce($_POST['mmwm_meta_box_nonce'], 'mmwm_save_meta_box_data')) return;
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
        if (! current_user_can('edit_post', $post_id)) return;
        if ('mmwm_website' !== get_post_type($post_id)) return;

        if (isset($_POST['mmwm_target_url'])) {
            update_post_meta($post_id, '_mmwm_target_url', esc_url_raw($_POST['mmwm_target_url']));
        }
        if (isset($_POST['mmwm_interval'])) {
            update_post_meta($post_id, '_mmwm_interval', intval($_POST['mmwm_interval']));
        }
        if (isset($_POST['mmwm_check_type'])) {
            update_post_meta($post_id, '_mmwm_check_type', sanitize_text_field($_POST['mmwm_check_type']));
        }
        if (isset($_POST['mmwm_html_selector'])) {
            update_post_meta($post_id, '_mmwm_html_selector', sanitize_textarea_field($_POST['mmwm_html_selector']));
        }
        if (isset($_POST['mmwm_notification_email'])) {
            update_post_meta($post_id, '_mmwm_notification_email', sanitize_email($_POST['mmwm_notification_email']));
        }

        // Save Notification Trigger
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
