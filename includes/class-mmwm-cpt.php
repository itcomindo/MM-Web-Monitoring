<?php

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

class MMWM_CPT
{

    public function register_cpt()
    {
        // Kode ini tidak berubah, jadi saya persingkat untuk kejelasan
        $labels = ['name' => 'Websites', 'singular_name' => 'Website', 'menu_name' => 'Web Monitoring', /* ... all other labels ... */];
        $args = ['label' => 'Website', 'labels' => $labels, 'supports' => ['title'], 'public' => false, 'show_ui' => true, 'show_in_menu' => true, 'menu_icon' => 'dashicons-networking', /* ... other args ... */];
        register_post_type('mmwm_website', $args);
    }

    public function add_meta_boxes()
    {
        add_meta_box('mmwm_website_details', 'Monitoring Settings', [$this, 'render_meta_box'], 'mmwm_website', 'normal', 'high');
    }

    public function render_meta_box($post)
    {
        wp_nonce_field('mmwm_save_meta_box_data', 'mmwm_meta_box_nonce');

        $target_url             = get_post_meta($post->ID, '_mmwm_target_url', true);
        $check_type             = get_post_meta($post->ID, '_mmwm_check_type', true);
        $html_selector          = get_post_meta($post->ID, '_mmwm_html_selector', true);
        $notification_email     = get_post_meta($post->ID, '_mmwm_notification_email', true);
        $notification_trigger   = get_post_meta($post->ID, '_mmwm_notification_trigger', true) ?: 'always';

?>
        <p><strong><?php _e('Note:', 'mm-web-monitoring'); ?></strong> <?php _e('Most actions like starting, pausing, and interval changes can be done directly from the All Websites list for faster workflow.', 'mm-web-monitoring'); ?></p>
        <table class="form-table">
            <tr valign="top">
                <th scope="row"><label for="mmwm_target_url"><?php _e('Target URL', 'mm-web-monitoring'); ?></label></th>
                <td><input type="url" id="mmwm_target_url" name="mmwm_target_url" value="<?php echo esc_url($target_url); ?>" class="large-text" required /></td>
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
                <th scope="row"><label><?php _e('Send Email', 'mm-web-monitoring'); ?></label></th>
                <td>
                    <fieldset>
                        <label><input type="radio" name="mmwm_notification_trigger" value="always" <?php checked($notification_trigger, 'always'); ?>> <?php _e('Always (on any status change)', 'mm-web-monitoring'); ?></label><br>
                        <label><input type="radio" name="mmwm_notification_trigger" value="when_error_only" <?php checked($notification_trigger, 'when_error_only'); ?>> <?php _e('On Error Only', 'mm-web-monitoring'); ?></label>
                    </fieldset>
                </td>
            </tr>
        </table>
    <?php
    }

    public function add_meta_box_script()
    {
        global $post_type;
        if ('mmwm_website' != $post_type) return;
    ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                function toggleHtmlSelector() {
                    $('#mmwm_html_selector_row').toggle($('input[name="mmwm_check_type"]:checked').val() === 'fetch_html');
                }
                toggleHtmlSelector();
                $('input[name="mmwm_check_type"]').on('change', toggleHtmlSelector);
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
    }
}
