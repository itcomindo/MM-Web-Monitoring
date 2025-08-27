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
            'capabilities'          => array(
                'edit_post'          => 'manage_options',
                'read_post'          => 'manage_options',
                'delete_post'        => 'manage_options',
                'edit_posts'         => 'manage_options',
                'edit_others_posts'  => 'manage_options',
                'delete_posts'       => 'manage_options',
                'publish_posts'      => 'manage_options',
                'read_private_posts' => 'manage_options',
            ),
        );
        register_post_type('mmwm_website', $args);
    }

    public function add_meta_boxes()
    {
        add_meta_box('mmwm_website_details', 'Monitoring Settings', [$this, 'render_meta_box'], 'mmwm_website', 'normal', 'high');
        add_meta_box('mmwm_website_status', 'Current Monitoring Status', [$this, 'render_status_metabox'], 'mmwm_website', 'side', 'high');
        add_meta_box('mmwm_monitoring_history', '7-Day Monitoring History', [$this, 'render_history_metabox'], 'mmwm_website', 'side', 'default');
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
        
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#mmwm-enable-domain-monitoring').on('click', function() {
                var button = $(this);
                var spinner = button.next('.spinner');
                var postId = <?php echo $post->ID; ?>;
                
                // Disable button and show spinner
                button.prop('disabled', true);
                spinner.css('visibility', 'visible');
                
                // Send AJAX request
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: {
                        action: 'mmwm_enable_domain_monitoring',
                        post_id: postId,
                        nonce: '<?php echo wp_create_nonce("mmwm_enable_domain_monitoring_nonce"); ?>'
                    },
                    success: function(response) {
                        // Hide spinner
                        spinner.css('visibility', 'hidden');
                        
                        if (response.success) {
                            // Replace button with success message
                            button.replaceWith('<div class="notice notice-success inline"><p>' + response.data.message + '</p></div>');
                            
                            // If manual input is needed, show the date picker
                            if (response.data.need_manual_input) {
                                // Tampilkan pesan error dan form input manual
                                var errorDiv = $('<div class="mmwm-domain-check-failed" style="margin-top: 10px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107; border-radius: 4px;">' +
                                    '<strong style="color: #856404;"><?php _e("⚠️ Domain Check Failed", "mm-web-monitoring"); ?></strong><br>' +
                                    '<p style="margin: 5px 0; color: #856404;">' + response.data.message + '</p>');
                                
                                // Tambahkan link WHOIS jika tersedia
                                if (response.data.whois_link && response.data.root_domain) {
                                    errorDiv.append('<p style="margin: 5px 0; color: #856404;"><a href="' + response.data.whois_link + '" target="_blank" style="color: #0073aa;"><?php _e("Check WHOIS information for", "mm-web-monitoring"); ?> ' + response.data.root_domain + ' &raquo;</a></p>');
                                }
                                
                                errorDiv.append('<p style="margin: 5px 0; color: #856404;"><?php _e("Please enter the domain expiry date manually below:", "mm-web-monitoring"); ?></p>' +
                                    '<div style="margin-top: 10px;">' +
                                    '<label for="mmwm_manual_domain_expiry"><strong><?php _e("Manual Domain Expiry Date:", "mm-web-monitoring"); ?></strong></label><br>' +
                                    '<input type="date" name="mmwm_manual_domain_expiry" id="mmwm_manual_domain_expiry" value="" min="<?php echo date("Y-m-d"); ?>" style="width: 200px; padding: 5px; margin-top: 5px;">' +
                                    '<p class="description"><?php _e("Enter when this domain registration will expire.", "mm-web-monitoring"); ?></p>' +
                                    '</div>');
                                
                                // Tampilkan di container yang sudah ada
                                $('#mmwm-domain-check-failed-container').html(errorDiv);
                                
                                // Jika container tidak ada, tambahkan setelah tombol
                                if ($('#mmwm-domain-check-failed-container').length === 0) {
                                    button.after(errorDiv);
                                }
                            } else {
                                // Update expiry date display
                                if (response.data.expiry_date) {
                                    $('.mmwm-domain-expiry-info').html(response.data.expiry_date).show();
                                }
                            }
                        } else {
                            // Show error message and re-enable button
                            button.after('<div class="notice notice-error inline"><p>' + response.data.message + '</p></div>');
                            button.prop('disabled', false);
                        }
                    },
                    error: function() {
                        // Hide spinner, show error message and re-enable button
                        spinner.css('visibility', 'hidden');
                        button.after('<div class="notice notice-error inline"><p><?php _e("Error checking domain. Please try again.", "mm-web-monitoring"); ?></p></div>');
                        button.prop('disabled', false);
                    }
                });
            });
        });
        </script>
    <?php
    }

    public function render_history_metabox($post)
    {
        // Get monitoring history for the last 7 days
        $history = $this->get_monitoring_history($post->ID, 7);

        if (empty($history)) {
            echo '<p>' . __('No monitoring history available yet.', 'mm-web-monitoring') . '</p>';
            return;
        }

        echo '<div style="max-height: 300px; overflow-y: auto;">';
        foreach ($history as $date => $events) {
            $date_formatted = date('F j, Y', strtotime($date));
            $down_count = count($events);

            echo '<div style="margin-bottom: 10px; padding: 8px; background: #f9f9f9; border-left: 3px solid ' . ($down_count > 0 ? '#dc3545' : '#28a745') . ';">';
            echo '<strong>' . esc_html($date_formatted) . '</strong><br>';

            if ($down_count > 0) {
                echo '<span style="color: #dc3545;">' . sprintf(_n('%d detected down event', '%d detected down events', $down_count, 'mm-web-monitoring'), $down_count) . '</span>';

                // Show down times
                foreach ($events as $event) {
                    $time = date('H:i', strtotime($event['timestamp']));
                    echo '<br><small style="color: #666;">• ' . sprintf(__('Down at %s', 'mm-web-monitoring'), $time) . '</small>';
                }
            } else {
                echo '<span style="color: #28a745;">' . __('No downtime detected', 'mm-web-monitoring') . '</span>';
            }

            echo '</div>';
        }
        echo '</div>';
    }

    /**
     * Get monitoring history for specified number of days
     */
    private function get_monitoring_history($post_id, $days = 7)
    {
        $history = get_post_meta($post_id, '_mmwm_monitoring_history', true);
        if (!is_array($history)) {
            $history = array();
        }

        // Filter history for the last X days
        $cutoff_date = date('Y-m-d', strtotime("-{$days} days"));
        $filtered_history = array();

        // Create entries for each day in the range, even if no events
        for ($i = $days - 1; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-{$i} days"));
            $filtered_history[$date] = isset($history[$date]) ? $history[$date] : array();
        }

        return $filtered_history;
    }

    /**
     * Log monitoring event to history
     */
    public function log_monitoring_event($post_id, $status, $timestamp = null)
    {
        if ($timestamp === null) {
            $timestamp = current_time('mysql');
        }

        $date = date('Y-m-d', strtotime($timestamp));
        $history = get_post_meta($post_id, '_mmwm_monitoring_history', true);
        if (!is_array($history)) {
            $history = array();
        }

        // Only log down events for history
        if ($status === 'DOWN' || $status === 'FAILED') {
            if (!isset($history[$date])) {
                $history[$date] = array();
            }

            $history[$date][] = array(
                'timestamp' => $timestamp,
                'status' => $status
            );

            // Keep only last 30 days of history to prevent database bloat
            $cutoff_date = date('Y-m-d', strtotime('-30 days'));
            foreach ($history as $hist_date => $events) {
                if ($hist_date < $cutoff_date) {
                    unset($history[$hist_date]);
                }
            }

            update_post_meta($post_id, '_mmwm_monitoring_history', $history);
        }
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
                    <div class="mmwm-radio-group">
                        <div class="mmwm-radio-option">
                            <input type="radio" name="mmwm_notification_trigger" value="always" id="mmwm_notification_always" <?php checked($notification_trigger, 'always'); ?>>
                            <div class="mmwm-radio-content">
                                <strong><?php _e('Always', 'mm-web-monitoring'); ?></strong>
                                <small><?php _e('Kirim email setiap kali pengecekan berjalan, apapun hasilnya (UP, DOWN, atau sama seperti sebelumnya).', 'mm-web-monitoring'); ?></small>
                            </div>
                        </div>
                        <div class="mmwm-radio-option">
                            <input type="radio" name="mmwm_notification_trigger" value="when_error_only" id="mmwm_notification_error_only" <?php checked($notification_trigger, 'when_error_only'); ?>>
                            <div class="mmwm-radio-content">
                                <strong><?php _e('On Error & Recovery', 'mm-web-monitoring'); ?></strong>
                                <small><?php _e('Kirim email jika status berubah dari UP -> DOWN atau jika status tetap DOWN. Juga kirim saat pulih (DOWN -> UP).', 'mm-web-monitoring'); ?></small>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php _e('Monitoring Interval', 'mm-web-monitoring'); ?></label></th>
                <td>
                    <?php $monitoring_interval = get_post_meta($post->ID, '_mmwm_monitoring_interval', true) ?: '15min'; ?>
                    <select name="mmwm_monitoring_interval" style="width: 200px;">
                        <option value="1min" <?php selected($monitoring_interval, '1min'); ?>><?php _e('Every 1 minute', 'mm-web-monitoring'); ?></option>
                        <option value="3min" <?php selected($monitoring_interval, '3min'); ?>><?php _e('Every 3 minutes', 'mm-web-monitoring'); ?></option>
                        <option value="5min" <?php selected($monitoring_interval, '5min'); ?>><?php _e('Every 5 minutes', 'mm-web-monitoring'); ?></option>
                        <option value="7min" <?php selected($monitoring_interval, '7min'); ?>><?php _e('Every 7 minutes', 'mm-web-monitoring'); ?></option>
                        <option value="10min" <?php selected($monitoring_interval, '10min'); ?>><?php _e('Every 10 minutes', 'mm-web-monitoring'); ?></option>
                        <option value="15min" <?php selected($monitoring_interval, '15min'); ?>><?php _e('Every 15 minutes', 'mm-web-monitoring'); ?></option>
                        <option value="25min" <?php selected($monitoring_interval, '25min'); ?>><?php _e('Every 25 minutes', 'mm-web-monitoring'); ?></option>
                        <option value="30min" <?php selected($monitoring_interval, '30min'); ?>><?php _e('Every 30 minutes', 'mm-web-monitoring'); ?></option>
                        <option value="45min" <?php selected($monitoring_interval, '45min'); ?>><?php _e('Every 45 minutes', 'mm-web-monitoring'); ?></option>
                        <option value="60min" <?php selected($monitoring_interval, '60min'); ?>><?php _e('Every 60 minutes', 'mm-web-monitoring'); ?></option>
                        <option value="1hour" <?php selected($monitoring_interval, '1hour'); ?>><?php _e('Every hour', 'mm-web-monitoring'); ?></option>
                        <option value="6hour" <?php selected($monitoring_interval, '6hour'); ?>><?php _e('Every 6 hours', 'mm-web-monitoring'); ?></option>
                        <option value="12hour" <?php selected($monitoring_interval, '12hour'); ?>><?php _e('Every 12 hours', 'mm-web-monitoring'); ?></option>
                        <option value="24hour" <?php selected($monitoring_interval, '24hour'); ?>><?php _e('Once daily', 'mm-web-monitoring'); ?></option>
                    </select>
                    <p class="description"><?php _e('Frequency of monitoring checks for this website.', 'mm-web-monitoring'); ?></p>
                </td>
            </tr>
            <tr valign="top" id="mmwm-email-log-row">
                <th scope="row"><label><?php _e('Email Log', 'mm-web-monitoring'); ?></label></th>
                <td>
                    <div id="mmwm-last-email-log"><?php echo esc_html($email_log ?: 'N/A'); ?></div>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row"><label><?php _e('Domain Monitoring', 'mm-web-monitoring'); ?></label></th>
                <td>
                    <?php
                    $domain_monitoring_enabled = get_post_meta($post->ID, '_mmwm_domain_monitoring_enabled', true);
                    $domain_error = get_post_meta($post->ID, '_mmwm_domain_error', true);
                    $manual_override = get_post_meta($post->ID, '_mmwm_domain_manual_override', true);
                    ?>
                    <label>
                        <input type="checkbox" name="mmwm_domain_monitoring_enabled" value="1" <?php checked($domain_monitoring_enabled, '1'); ?> />
                        <?php _e('Enable domain expiration monitoring', 'mm-web-monitoring'); ?>
                    </label>
                    <p class="description"><?php _e('Monitor domain registration expiration and receive alerts 10 days before expiry.', 'mm-web-monitoring'); ?></p>
                    <p class="description" style="color: #0073aa; font-style: italic;">
                        <?php _e('After enabling, please click Update/Publish to save your changes first.', 'mm-web-monitoring'); ?>
                    </p>

                    <?php if ($domain_monitoring_enabled === '1' && $post->ID): ?>
                        <?php
                        $domain_expiry_date = get_post_meta($post->ID, '_mmwm_domain_expiry_date', true);
                        $domain_days_until_expiry = get_post_meta($post->ID, '_mmwm_domain_days_until_expiry', true);
                        $domain_last_check = get_post_meta($post->ID, '_mmwm_domain_last_check', true);
                        $domain_monitoring_status = get_post_meta($post->ID, '_mmwm_domain_monitoring_status', true);
                        ?>
                        
                        <?php if (empty($domain_last_check) && empty($domain_monitoring_status)): ?>
                            <div style="margin-top: 15px;">
                                <button type="button" class="button button-primary" id="mmwm-enable-domain-monitoring">
                                    <?php
                                // Ambil informasi SSL
                                $ssl_is_active = get_post_meta($post->ID, '_mmwm_ssl_is_active', true);
                                $ssl_error = get_post_meta($post->ID, '_mmwm_ssl_error', true);
                                $ssl_expiry_date = get_post_meta($post->ID, '_mmwm_ssl_expiry_date', true);
                                $ssl_days_until_expiry = get_post_meta($post->ID, '_mmwm_ssl_days_until_expiry', true);
                                $ssl_issuer = get_post_meta($post->ID, '_mmwm_ssl_issuer', true);
                                $ssl_last_check = get_post_meta($post->ID, '_mmwm_ssl_last_check', true);
                                
                                _e('Click Enable Domain Expiry Monitoring', 'mm-web-monitoring'); ?>
                                </button>
                                <span class="spinner" style="float:none;"></span>
                            </div>
                        <?php endif; ?>

                        <?php
                        // Tampilkan informasi SSL jika URL menggunakan HTTPS
                        $url = get_post_meta($post->ID, '_mmwm_target_url', true);
                        if (strpos($url, 'https://') === 0) :
                            $ssl_is_active = get_post_meta($post->ID, '_mmwm_ssl_is_active', true);
                            $ssl_error = get_post_meta($post->ID, '_mmwm_ssl_error', true);
                            $ssl_expiry_date = get_post_meta($post->ID, '_mmwm_ssl_expiry_date', true);
                            $ssl_days_until_expiry = get_post_meta($post->ID, '_mmwm_ssl_days_until_expiry', true);
                            $ssl_issuer = get_post_meta($post->ID, '_mmwm_ssl_issuer', true);
                            $ssl_last_check = get_post_meta($post->ID, '_mmwm_ssl_last_check', true);
                        ?>
                            <?php
                                // Tentukan warna dan ikon berdasarkan status SSL
                                $border_color = '#dc3545'; // Default merah untuk error
                                $status_color = '#dc3545';
                                $status_icon = '❌';
                                $status_text = __('Invalid or Expired', 'mm-web-monitoring');
                                
                                if ($ssl_is_active == '1') {
                                    if ($ssl_days_until_expiry < 0) {
                                        // SSL sudah kedaluwarsa
                                        $border_color = '#dc3545';
                                        $status_color = '#dc3545';
                                        $status_icon = '❌';
                                        $status_text = __('Expired', 'mm-web-monitoring');
                                    } elseif ($ssl_days_until_expiry <= 10) {
                                        // SSL akan kedaluwarsa dalam 10 hari
                                        $border_color = '#ffc107';
                                        $status_color = '#ffc107';
                                        $status_icon = '⚠️';
                                        $status_text = __('Valid but expiring soon', 'mm-web-monitoring');
                                    } elseif ($ssl_days_until_expiry <= 30) {
                                        // SSL akan kedaluwarsa dalam 30 hari
                                        $border_color = '#17a2b8';
                                        $status_color = '#17a2b8';
                                        $status_icon = 'ℹ️';
                                        $status_text = __('Valid but expiring in a month', 'mm-web-monitoring');
                                    } else {
                                        // SSL valid dan masih lama
                                        $border_color = '#28a745';
                                        $status_color = '#28a745';
                                        $status_icon = '✅';
                                        $status_text = __('Valid', 'mm-web-monitoring');
                                    }
                                }
                            ?>
                            <div style="margin-top: 15px; padding: 15px; background: #f9f9f9; border-left: 4px solid <?php echo $border_color; ?>; border-radius: 4px;">
                                <h4 style="margin-top: 0; margin-bottom: 10px;"><?php _e('SSL Certificate Information:', 'mm-web-monitoring'); ?></h4>
                                
                                <?php if ($ssl_is_active == '1') : ?>
                                    <div style="display: flex; align-items: center; margin-bottom: 8px;">
                                        <strong style="min-width: 120px;"><?php _e('Status:', 'mm-web-monitoring'); ?></strong> 
                                        <span style="color: <?php echo $status_color; ?>; font-weight: bold; display: flex; align-items: center;">
                                            <span style="margin-right: 5px;"><?php echo $status_icon; ?></span>
                                            <?php echo $status_text; ?>
                                        </span>
                                    </div>
                                    
                                    <div style="display: flex; margin-bottom: 8px;">
                                        <strong style="min-width: 120px;"><?php _e('Expires:', 'mm-web-monitoring'); ?></strong>
                                        <?php echo esc_html($ssl_expiry_date); ?>
                                    </div>
                                    
                                    <div style="display: flex; margin-bottom: 8px;">
                                        <strong style="min-width: 120px;"><?php _e('Days remaining:', 'mm-web-monitoring'); ?></strong>
                                        <span style="color: <?php echo $status_color; ?>; font-weight: bold;">
                                            <?php echo esc_html($ssl_days_until_expiry); ?>
                                        </span>
                                    </div>
                                    
                                    <?php if (!empty($ssl_issuer)) : ?>
                                        <div style="display: flex; margin-bottom: 8px;">
                                            <strong style="min-width: 120px;"><?php _e('Issuer:', 'mm-web-monitoring'); ?></strong>
                                            <?php echo esc_html($ssl_issuer); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if ($ssl_days_until_expiry < 0) : ?>
                                        <div style="margin-top: 10px; padding: 10px; background: #f8d7da; border-radius: 4px; color: #721c24;">
                                            <strong><?php _e('Warning:', 'mm-web-monitoring'); ?></strong>
                                            <?php _e('Your SSL certificate has expired. Visitors to your site will see security warnings. Renew your certificate immediately.', 'mm-web-monitoring'); ?>
                                        </div>
                                    <?php elseif ($ssl_days_until_expiry <= 10) : ?>
                                        <div style="margin-top: 10px; padding: 10px; background: #fff3cd; border-radius: 4px; color: #856404;">
                                            <strong><?php _e('Warning:', 'mm-web-monitoring'); ?></strong>
                                            <?php printf(__('Your SSL certificate will expire in %d days. Plan to renew it soon to avoid security warnings.', 'mm-web-monitoring'), $ssl_days_until_expiry); ?>
                                        </div>
                                    <?php elseif ($ssl_days_until_expiry <= 30) : ?>
                                        <div style="margin-top: 10px; padding: 10px; background: #d1ecf1; border-radius: 4px; color: #0c5460;">
                                            <strong><?php _e('Notice:', 'mm-web-monitoring'); ?></strong>
                                            <?php printf(__('Your SSL certificate will expire in %d days. Consider planning for renewal.', 'mm-web-monitoring'), $ssl_days_until_expiry); ?>
                                        </div>
                                    <?php endif; ?>
                                    
                                <?php else : ?>
                                    <div style="display: flex; align-items: center; margin-bottom: 8px;">
                                        <strong style="min-width: 120px;"><?php _e('Status:', 'mm-web-monitoring'); ?></strong> 
                                        <span style="color: #dc3545; font-weight: bold;">
                                            ❌ <?php _e('Invalid or Expired', 'mm-web-monitoring'); ?>
                                        </span>
                                    </div>
                                    
                                    <?php if (!empty($ssl_error)) : ?>
                                        <div style="display: flex; margin-bottom: 8px;">
                                            <strong style="min-width: 120px;"><?php _e('Error:', 'mm-web-monitoring'); ?></strong>
                                            <?php echo esc_html($ssl_error); ?>
                                        </div>
                                        
                                        <div style="margin-top: 10px; padding: 10px; background: #f8d7da; border-radius: 4px; color: #721c24;">
                                            <strong><?php _e('Problem:', 'mm-web-monitoring'); ?></strong>
                                            <?php 
                                            // Cek jika error mengandung kode error tertentu dan tampilkan pesan yang sesuai
                                            if (strpos($ssl_error, 'not HTTPS') !== false) {
                                                _e('This URL is using HTTP protocol. SSL certificates are only available for HTTPS URLs.', 'mm-web-monitoring');
                                            } elseif (strpos($ssl_error, 'Connection failed') !== false) {
                                                _e('Could not connect to the server to check SSL certificate. The server might be down or blocking connections.', 'mm-web-monitoring');
                                            } elseif (strpos($ssl_error, 'No SSL certificate found') !== false) {
                                                _e('No valid SSL certificate was found on this server. The certificate might be misconfigured.', 'mm-web-monitoring');
                                            } elseif (strpos($ssl_error, 'Failed to parse') !== false) {
                                                _e('The SSL certificate was found but could not be processed. It might be corrupted or invalid.', 'mm-web-monitoring');
                                            } else {
                                                _e('There was a problem checking the SSL certificate. Try again later or contact your hosting provider.', 'mm-web-monitoring');
                                            }
                                            ?>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                                
                                <?php if ($ssl_last_check) : ?>
                                    <div style="display: flex; margin-top: 10px; font-size: 0.9em; color: #6c757d;">
                                        <strong style="min-width: 120px;"><?php _e('Last checked:', 'mm-web-monitoring'); ?></strong>
                                        <?php echo esc_html(wp_date('Y-m-d H:i:s', $ssl_last_check)); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div style="margin-top: 10px; text-align: right;">
                                    <button type="button" class="button" id="mmwm-check-ssl-now" data-post-id="<?php echo esc_attr($post->ID); ?>">
                                        <?php _e('Check SSL Now', 'mm-web-monitoring'); ?>
                                    </button>
                                    <span class="spinner" style="float:none;"></span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ($domain_error && !$manual_override): ?>
                            <!-- Elemen ini akan dibuat secara dinamis melalui JavaScript saat pemeriksaan domain gagal -->
                            <div id="mmwm-domain-check-failed-container"></div>
                        <?php else: ?>
                            <div style="margin-top: 10px; padding: 10px; background: #f9f9f9; border-left: 4px solid #0073aa;">
                                <strong><?php _e('Domain Information:', 'mm-web-monitoring'); ?></strong><br>
                                <?php if ($domain_expiry_date): ?>
                                    <strong><?php _e('Expires:', 'mm-web-monitoring'); ?></strong> <?php echo esc_html($domain_expiry_date); ?>
                                    <?php if ($manual_override): ?>
                                        <span style="color: #0073aa; font-weight: bold;"> (Manual)</span>
                                    <?php endif; ?><br>
                                    <strong><?php _e('Days until expiry:', 'mm-web-monitoring'); ?></strong>
                                    <span style="color: <?php echo $domain_days_until_expiry <= 30 ? '#dc3545' : ($domain_days_until_expiry <= 60 ? '#ffc107' : '#28a745'); ?>; font-weight: bold;">
                                        <?php echo esc_html($domain_days_until_expiry); ?>
                                    </span><br>
                                <?php endif; ?>
                                <?php if ($domain_last_check && !$manual_override): ?>
                                    <strong><?php _e('Last checked:', 'mm-web-monitoring'); ?></strong> <?php echo esc_html(wp_date('Y-m-d H:i:s', $domain_last_check)); ?>
                                <?php elseif (!$manual_override): ?>
                                    <em><?php _e('Domain not checked yet. Save to check domain expiration.', 'mm-web-monitoring'); ?></em>
                                <?php endif; ?>

                                <?php if ($manual_override): ?>
                                    <div style="margin-top: 10px;">
                                        <label for="mmwm_manual_domain_expiry_update"><strong><?php _e('Update Manual Expiry Date:', 'mm-web-monitoring'); ?></strong></label><br>
                                        <input type="date"
                                            name="mmwm_manual_domain_expiry"
                                            id="mmwm_manual_domain_expiry_update"
                                            value="<?php echo esc_attr($domain_expiry_date); ?>"
                                            min="<?php echo date('Y-m-d'); ?>"
                                            style="width: 200px; padding: 5px; margin-top: 5px;">
                                        <p class="description"><?php _e('Update the manual domain expiry date if needed.', 'mm-web-monitoring'); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <?php if ($post->ID) : ?>
            <div id="mmwm-action-buttons" style="margin-top: 15px;">
                <button type="button" class="button button-primary" data-action="active" style="<?php echo ($monitoring_status !== 'active') ? '' : 'display:none;'; ?>" title="<?php _e('Start monitoring this website', 'mm-web-monitoring'); ?>">Start</button>
                <button type="button" class="button" data-action="paused" style="<?php echo ($monitoring_status === 'active') ? '' : 'display:none;'; ?>" title="<?php _e('Temporarily pause monitoring', 'mm-web-monitoring'); ?>">Pause</button>
                <button type="button" class="button button-danger" data-action="stopped" style="<?php echo ($monitoring_status !== 'stopped') ? '' : 'display:none;'; ?>" title="<?php _e('Stop monitoring completely', 'mm-web-monitoring'); ?>">Stop</button>
                <button type="button" class="button button-secondary" id="mmwm-run-check-now" title="<?php _e('Run a check immediately', 'mm-web-monitoring'); ?>">Check Now</button>
                <span class="spinner" style="float:none;"></span>
            </div>
            <div style="margin-top: 10px; font-size: 12px; color: #666;">
                <p class="description">
                    <strong><?php _e('Start:', 'mm-web-monitoring'); ?></strong> <?php _e('Begin or resume monitoring this website', 'mm-web-monitoring'); ?><br>
                    <strong><?php _e('Pause:', 'mm-web-monitoring'); ?></strong> <?php _e('Temporarily suspend monitoring without losing settings', 'mm-web-monitoring'); ?><br>
                    <strong><?php _e('Stop:', 'mm-web-monitoring'); ?></strong> <?php _e('Completely stop monitoring this website', 'mm-web-monitoring'); ?><br>
                    <strong><?php _e('Check Now:', 'mm-web-monitoring'); ?></strong> <?php _e('Run an immediate check regardless of schedule', 'mm-web-monitoring'); ?>
                </p>
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
        <style>
            /* Modern metabox styling */
            .postbox {
                border: 1px solid #e1e5e9;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
                margin-bottom: 20px;
            }

            .postbox .postbox-header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                border-radius: 8px 8px 0 0;
                padding: 15px 20px;
                border-bottom: none;
            }

            .postbox .postbox-header h2 {
                color: white;
                font-size: 16px;
                font-weight: 600;
                margin: 0;
            }

            .inside {
                padding: 20px;
                background: #fff;
                border-radius: 0 0 8px 8px;
            }

            /* Enhanced form table styling */
            .form-table {
                background: white;
                border-radius: 8px;
                overflow: hidden;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            }

            .form-table th {
                background: #f8f9fa;
                color: #495057;
                font-weight: 500;
                border-bottom: 1px solid #e9ecef;
                padding: 15px 20px;
                width: 200px;
            }

            .form-table td {
                padding: 15px 20px;
                border-bottom: 1px solid #e9ecef;
                background: white;
            }

            .form-table tr:last-child th,
            .form-table tr:last-child td {
                border-bottom: none;
            }

            /* Enhanced radio button styling */
            .mmwm-radio-group {
                border: 1px solid #e1e5e9;
                padding: 20px;
                border-radius: 8px;
                background: #f8f9fa;
                margin: 10px 0;
            }

            .mmwm-radio-option {
                display: flex;
                align-items: flex-start;
                padding: 15px;
                background: white;
                border-radius: 8px;
                margin-bottom: 12px;
                border: 2px solid #e9ecef;
                cursor: pointer;
                transition: all 0.3s ease;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            }

            .mmwm-radio-option:hover {
                border-color: #667eea;
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.15);
                transform: translateY(-1px);
            }

            .mmwm-radio-option input[type="radio"] {
                margin-right: 15px;
                margin-top: 3px;
                transform: scale(1.3);
                accent-color: #667eea;
            }

            .mmwm-radio-option input[type="radio"]:checked+.mmwm-radio-content {
                color: #667eea;
                font-weight: 500;
            }

            .mmwm-radio-option:has(input[type="radio"]:checked) {
                border-color: #667eea;
                background: linear-gradient(135deg, #f0f8ff 0%, #e3f2fd 100%);
                box-shadow: 0 4px 12px rgba(102, 126, 234, 0.2);
            }

            .mmwm-radio-content {
                flex: 1;
            }

            .mmwm-radio-content h4 {
                margin: 0 0 8px 0;
                font-size: 14px;
                font-weight: 600;
                color: #495057;
            }

            .mmwm-radio-content p {
                margin: 0;
                font-size: 13px;
                color: #6c757d;
                line-height: 1.4;
            }

            /* Modern form controls */
            input[type="text"],
            input[type="url"],
            input[type="email"],
            select,
            textarea {
                border: 2px solid #e9ecef;
                border-radius: 6px;
                padding: 10px 12px;
                font-size: 14px;
                transition: all 0.3s ease;
                background: white;
            }

            input[type="text"]:focus,
            input[type="url"]:focus,
            input[type="email"]:focus,
            select:focus,
            textarea:focus {
                border-color: #667eea;
                box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
                outline: none;
            }

            /* Modern button styling */
            .button {
                border-radius: 6px;
                font-weight: 500;
                transition: all 0.3s ease;
            }

            .button-primary {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                border: none;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            .button-primary:hover {
                transform: translateY(-1px);
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            }

            /* Status headline improvements */
            #mmwm-status-headline {
                border-radius: 8px;
                font-size: 14px;
                letter-spacing: 0.5px;
                text-align: center;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }

            /* History metabox styling */
            .mmwm-history-item {
                margin-bottom: 12px;
                padding: 12px;
                background: #f8f9fa;
                border-left: 4px solid #28a745;
                border-radius: 0 6px 6px 0;
                transition: all 0.3s ease;
            }

            .mmwm-history-item.has-downtime {
                border-left-color: #dc3545;
                background: #fff5f5;
            }

            .mmwm-history-item:hover {
                background: #e9ecef;
                transform: translateX(2px);
            }

            /* Status details improvements */
            #mmwm-status-details p {
                margin: 8px 0;
                padding: 8px;
                background: #f8f9fa;
                border-radius: 4px;
                border-left: 3px solid #667eea;
            }

            /* Description text styling */
            .description {
                color: #6c757d;
                font-style: italic;
                margin-top: 5px;
            }

            /* Domain monitoring section */
            #domain-expiry-details {
                background: #f0f8ff;
                border: 1px solid #e3f2fd;
                border-radius: 6px;
                padding: 15px;
                margin-top: 10px;
            }

            /* Checkbox styling */
            input[type="checkbox"] {
                transform: scale(1.2);
                accent-color: #667eea;
                margin-right: 8px;
            }

            /* Responsive improvements */
            @media (max-width: 768px) {
                .form-table th {
                    width: auto;
                    display: block;
                    text-align: left;
                    padding-bottom: 5px;
                }

                .form-table td {
                    display: block;
                    padding-top: 5px;
                }

                .mmwm-radio-option {
                    padding: 12px;
                }
            }

            .mmwm-radio-content strong {
                display: block;
                font-size: 14px;
                margin-bottom: 4px;
                color: #0073aa;
            }

            .mmwm-radio-content small {
                font-size: 12px;
                color: #666;
                line-height: 1.4;
            }

            .mmwm-radio-option:last-child {
                margin-bottom: 0;
            }
        </style>

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

        // Handle HTML selector without sanitizing HTML tags
        if (isset($_POST['mmwm_html_selector'])) {
            $html_selector = wp_unslash($_POST['mmwm_html_selector']);
            // Only strip slashes but preserve HTML tags
            update_post_meta($post_id, '_mmwm_html_selector', $html_selector);
        }

        if (isset($_POST['mmwm_notification_email'])) update_post_meta($post_id, '_mmwm_notification_email', sanitize_email($_POST['mmwm_notification_email']));

        if (isset($_POST['mmwm_notification_trigger'])) {
            $trigger = sanitize_text_field($_POST['mmwm_notification_trigger']);
            if (in_array($trigger, ['always', 'when_error_only'])) {
                update_post_meta($post_id, '_mmwm_notification_trigger', $trigger);
            }
        }

        // Handle domain monitoring
        $domain_monitoring_enabled = isset($_POST['mmwm_domain_monitoring_enabled']) ? '1' : '0';
        $old_domain_monitoring = get_post_meta($post_id, '_mmwm_domain_monitoring_enabled', true);
        update_post_meta($post_id, '_mmwm_domain_monitoring_enabled', $domain_monitoring_enabled);
        
        // If domain monitoring was enabled but now disabled, clear scheduled events
        if ($old_domain_monitoring === '1' && $domain_monitoring_enabled === '0') {
            // Clear any scheduled domain expiry notifications
            wp_clear_scheduled_hook('mmwm_domain_expiry_notification', array($post_id));
            
            // Reset domain monitoring status
            update_post_meta($post_id, '_mmwm_domain_monitoring_status', '');
            update_post_meta($post_id, '_mmwm_domain_last_check', '');
        }

        // Handle manual domain expiry date if provided
        if (isset($_POST['mmwm_manual_domain_expiry']) && !empty($_POST['mmwm_manual_domain_expiry'])) {
            $manual_date = sanitize_text_field($_POST['mmwm_manual_domain_expiry']);
            if (strtotime($manual_date)) {
                update_post_meta($post_id, '_mmwm_domain_expiry_date', $manual_date);
                update_post_meta($post_id, '_mmwm_domain_manual_override', '1');

                // Calculate days until expiry
                $days_diff = floor((strtotime($manual_date) - time()) / (60 * 60 * 24));
                update_post_meta($post_id, '_mmwm_domain_days_until_expiry', $days_diff);
                update_post_meta($post_id, '_mmwm_domain_error', '');
                
                // Schedule notification for domain expiry
                if ($domain_monitoring_enabled === '1') {
                    $expiry_timestamp = strtotime($manual_date);
                    $notification_timestamp = $expiry_timestamp - (10 * 86400); // 10 days before expiry
                    
                    // Clear any existing scheduled notifications
                    wp_clear_scheduled_hook('mmwm_domain_expiry_notification', array($post_id));
                    
                    // Only schedule if notification date is in the future
                    if ($notification_timestamp > time()) {
                        wp_schedule_single_event($notification_timestamp, 'mmwm_domain_expiry_notification', array($post_id));
                    }
                }
            }
        }

        // Handle monitoring interval
        if (isset($_POST['mmwm_monitoring_interval'])) {
            $interval = sanitize_text_field($_POST['mmwm_monitoring_interval']);
            $allowed_intervals = ['1min', '3min', '5min', '7min', '10min', '15min', '25min', '30min', '45min', '60min', '1hour', '6hour', '12hour', '24hour'];
            if (in_array($interval, $allowed_intervals)) {
                update_post_meta($post_id, '_mmwm_monitoring_interval', $interval);
            }
        }

        // If domain monitoring was just enabled or URL changed, check domain expiration (with error handling)
        if ($domain_monitoring_enabled === '1' && (
            $old_domain_monitoring !== '1' ||
            (isset($_POST['mmwm_target_url']) && get_post_meta($post_id, '_mmwm_target_url', true) !== esc_url_raw($_POST['mmwm_target_url']))
        )) {
            // Only check if manual override is not set
            if (!get_post_meta($post_id, '_mmwm_domain_manual_override', true)) {
                $this->check_domain_expiration($post_id);
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

    /**
     * Check domain expiration for a website
     *
     * @param int $post_id Website post ID
     */
    private function check_domain_expiration($post_id)
    {
        $url = get_post_meta($post_id, '_mmwm_target_url', true);

        if (empty($url)) {
            return;
        }

        $domain_checker = new MMWM_Domain_Checker();
        $result = $domain_checker->check_domain_expiration($url);

        // Save domain expiration data
        update_post_meta($post_id, '_mmwm_domain_last_check', time());

        if ($result['success']) {
            update_post_meta($post_id, '_mmwm_domain_expiry_date', $result['expiry_date']);
            update_post_meta($post_id, '_mmwm_domain_days_until_expiry', $result['days_until_expiry']);
            update_post_meta($post_id, '_mmwm_domain_registrar', $result['registrar']);
            update_post_meta($post_id, '_mmwm_domain_root_domain', $result['root_domain']);
            update_post_meta($post_id, '_mmwm_domain_error', '');

            // Send notification if expiring soon
            if (isset($result['is_expiring_soon']) && $result['is_expiring_soon']) {
                $this->send_domain_expiring_notification($post_id, $result);
            }
        } else {
            update_post_meta($post_id, '_mmwm_domain_error', $result['error']);
            update_post_meta($post_id, '_mmwm_domain_expiry_date', '');
            update_post_meta($post_id, '_mmwm_domain_days_until_expiry', '');
        }
    }

    /**
     * Send domain expiring notification
     *
     * @param int $post_id Website post ID
     * @param array $domain_result Domain check result
     */
    private function send_domain_expiring_notification($post_id, $domain_result)
    {
        // Check if notification was already sent recently (within 24 hours)
        $last_domain_notification = get_post_meta($post_id, '_mmwm_domain_last_notification', true);

        if ($last_domain_notification && (time() - $last_domain_notification) < 86400) {
            return; // Don't spam notifications
        }

        $title = get_the_title($post_id);
        $email_to = get_post_meta($post_id, '_mmwm_notification_email', true);

        if (empty($email_to)) {
            $email_to = get_option('mmwm_default_email', get_option('admin_email'));
        }

        $domain = $domain_result['root_domain'];
        $subject = "MW-DOMAIN-EXP-{$domain}";

        // Use unified email template
        $email_data = [
            'title' => $title,
            'domain' => $domain_result['root_domain'],
            'days_left' => $domain_result['days_until_expiry'],
            'expiry_date' => $domain_result['expiry_date'],
            'registrar' => $domain_result['registrar'] ?? 'Unknown'
        ];

        $body = MMWM_Email_Template::build_domain_expiring_notification($email_data);
        $headers = MMWM_Email_Template::get_html_headers();

        if (function_exists('wp_mail')) {
            $sent = wp_mail($email_to, $subject, $body, $headers);
            if ($sent) {
                update_post_meta($post_id, '_mmwm_domain_last_notification', time());
            }
        }
    }
}
