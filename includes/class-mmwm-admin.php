<?php

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

class MMWM_Admin
{

    public function add_admin_menu()
    {
        // **PERUBAHAN:** Pindahkan submenu ke bawah CPT 'mmwm_website'
        add_submenu_page(
            'edit.php?post_type=mmwm_website', // Parent slug
            __('Monitoring Summary', 'mm-web-monitoring'),
            __('Summary', 'mm-web-monitoring'),
            'manage_options',
            'mmwm-summary',
            array($this, 'render_summary_page')
        );
    }

    public function render_summary_page()
    {
?>
        <div class="wrap">
            <h1><?php _e('Web Monitoring Summary', 'mm-web-monitoring'); ?></h1>
            <p><?php _e('This table shows the current status of all websites being monitored.', 'mm-web-monitoring'); ?></p>

            <table class="wp-list-table widefat fixed striped">
                <thead>
                    <tr>
                        <th scope="col"><?php _e('Website', 'mm-web-monitoring'); ?></th>
                        <th scope="col" style="width: 12%;"><?php _e('Check Result', 'mm-web-monitoring'); ?></th>
                        <th scope="col" style="width: 15%;"><?php _e('Last Check', 'mm-web-monitoring'); ?></th>
                        <th scope="col" style="width: 15%;"><?php _e('Monitoring Status', 'mm-web-monitoring'); ?></th>
                        <th scope="col" style="width: 25%;"><?php _e('Actions', 'mm-web-monitoring'); ?></th>
                    </tr>
                </thead>
                <tbody id="mmwm-summary-list">
                    <?php
                    $args = ['post_type' => 'mmwm_website', 'posts_per_page' => -1, 'post_status' => 'publish', 'orderby' => 'title', 'order' => 'ASC'];
                    $websites = new WP_Query($args);

                    if ($websites->have_posts()) :
                        while ($websites->have_posts()) : $websites->the_post();
                            $post_id = get_the_ID();
                            $status = get_post_meta($post_id, '_mmwm_status', true);
                            $last_check_timestamp = get_post_meta($post_id, '_mmwm_last_check', true);
                            $monitoring_status = get_post_meta($post_id, '_mmwm_monitoring_status', true) ?: 'stopped';

                            $status_color = '#777'; // Default gray
                            if ($status === 'UP') $status_color = '#28a745';
                            if ($status === 'DOWN') $status_color = '#dc3545';
                            if ($status === 'CONTENT_ERROR') $status_color = '#ffc107';

                    ?>
                            <tr data-postid="<?php echo $post_id; ?>">
                                <td>
                                    <strong><a href="<?php echo get_edit_post_link($post_id); ?>"><?php the_title(); ?></a></strong>
                                    <div class="row-actions">
                                        <span><a href="<?php echo get_edit_post_link($post_id); ?>">Edit</a></span>
                                    </div>
                                </td>
                                <td class="check-result-cell">
                                    <span style="color: <?php echo $status_color; ?>; font-weight: bold;">
                                        <?php echo esc_html($status ?: 'Not Checked'); ?>
                                    </span>
                                </td>
                                <td class="last-check-cell">
                                    <?php
                                    if ($last_check_timestamp) {
                                        echo sprintf('<span title="%s">%s</span>', esc_attr(wp_date('Y-m-d H:i:s', $last_check_timestamp)), esc_html(human_time_diff($last_check_timestamp) . ' ago'));
                                    } else {
                                        echo 'N/A';
                                    }
                                    ?>
                                </td>
                                <td class="monitoring-status-cell">
                                    <span class="monitoring-status-text"><?php echo esc_html(ucfirst($monitoring_status)); ?></span>
                                </td>
                                <td class="actions-cell">
                                    <button class="button button-small mmwm-run-check-now" data-postid="<?php echo $post_id; ?>"><?php _e('Check Now', 'mm-web-monitoring'); ?></button>
                                    <?php if ($monitoring_status === 'active'): ?>
                                        <button class="button button-small mmwm-summary-action" data-action="paused"><?php _e('Pause', 'mm-web-monitoring'); ?></button>
                                    <?php elseif ($monitoring_status === 'paused' || $monitoring_status === 'stopped'): ?>
                                        <button class="button button-small button-primary mmwm-summary-action" data-action="active"><?php _e('Start', 'mm-web-monitoring'); ?></button>
                                    <?php endif; ?>
                                    <span class="spinner"></span>
                                </td>
                            </tr>
                        <?php
                        endwhile;
                        wp_reset_postdata();
                    else :
                        ?>
                        <tr>
                            <td colspan="5"><?php _e('No websites have been added for monitoring yet.', 'mm-web-monitoring'); ?></td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php $this->add_summary_page_script(); ?>
    <?php
    }

    private function add_summary_page_script()
    {
        $ajax_nonce = wp_create_nonce('mmwm_ajax_nonce');
    ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                var ajax_nonce = '<?php echo $ajax_nonce; ?>';

                // Handler for Start/Pause buttons
                $('#mmwm-summary-list').on('click', '.mmwm-summary-action', function(e) {
                    e.preventDefault();
                    var button = $(this);
                    var row = button.closest('tr');
                    var post_id = row.data('postid');
                    var new_status = button.data('action');

                    button.siblings('.spinner').addClass('is-active');
                    button.prop('disabled', true);

                    $.post(ajaxurl, {
                            action: 'mmwm_update_monitoring_status',
                            post_id: post_id,
                            new_status: new_status,
                            _ajax_nonce: ajax_nonce
                        })
                        .done(function(response) {
                            if (response.success) {
                                location.reload(); // Reload to reflect changes consistently for now
                            } else {
                                alert('Error: ' + response.data.message);
                            }
                        }).always(function() {
                            button.siblings('.spinner').removeClass('is-active');
                            button.prop('disabled', false);
                        });
                });

                // Handler for "Check Now" button
                $('#mmwm-summary-list').on('click', '.mmwm-run-check-now', function(e) {
                    e.preventDefault();
                    var button = $(this);
                    var row = button.closest('tr');
                    var post_id = row.data('postid');

                    button.prop('disabled', true).text('Checking...');
                    button.siblings('.spinner').addClass('is-active');

                    $.post(ajaxurl, {
                            action: 'mmwm_run_check_now',
                            post_id: post_id,
                            _ajax_nonce: ajax_nonce
                        })
                        .done(function(response) {
                            if (response.success) {
                                var data = response.data;
                                row.find('.check-result-cell span').text(data.status).css('color', data.status_color);
                                row.find('.last-check-cell').html(data.last_check_html);
                            } else {
                                alert('Error: ' + response.data.message);
                            }
                        }).fail(function() {
                            alert('An unknown error occurred.');
                        }).always(function() {
                            button.siblings('.spinner').removeClass('is-active');
                            button.prop('disabled', false).text('Check Now');
                        });
                });
            });
        </script>
<?php
    }
}
