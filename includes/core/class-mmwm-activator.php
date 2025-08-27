<?php

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Plugin activation and deactivation handler
 */
class MMWM_Activator
{
    /**
     * Activate the plugin
     *
     * @since 1.0.0
     */
    public static function activate()
    {
        // Register the custom post type first
        require_once MMWM_PLUGIN_DIR . 'includes/class-mmwm-cpt.php';
        $cpt = new MMWM_CPT();
        $cpt->register_cpt();

        // Schedule the cron event
        self::setup_cron_schedule();

        // Flush rewrite rules after registering CPT
        flush_rewrite_rules();

        // Set default options
        self::set_default_options();
        
        // Run silent monitoring for all active websites
        self::run_silent_monitoring();

        // Log activation
        error_log('MMWM Plugin activated successfully');
    }

    /**
     * Deactivate the plugin
     *
     * @since 1.0.0
     */
    public static function deactivate()
    {
        // Clear scheduled cron events
        self::clear_cron_schedule();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Log deactivation
        error_log('MMWM Plugin deactivated');
    }

    /**
     * Setup cron schedule for monitoring
     *
     * @since 1.0.0
     */
    private static function setup_cron_schedule()
    {
        if (!wp_next_scheduled('mmwm_scheduled_check_event')) {
            wp_schedule_event(time(), 'every_five_minutes', 'mmwm_scheduled_check_event');
        }
    }

    /**
     * Clear cron schedule
     *
     * @since 1.0.0
     */
    private static function clear_cron_schedule()
    {
        wp_clear_scheduled_hook('mmwm_scheduled_check_event');
    }

    /**
     * Set default plugin options
     *
     * @since 1.0.0
     */
    private static function set_default_options()
    {
        // Set default email if not already set
        if (!get_option('mmwm_default_email')) {
            add_option('mmwm_default_email', get_option('admin_email'));
        }

        // Set other default options
        if (!get_option('mmwm_version')) {
            add_option('mmwm_version', MMWM_VERSION);
        }
    }

    /**
     * Run silent monitoring for all active websites
     * 
     * @since 1.1.1
     */
    public static function run_silent_monitoring()
    {
        // Get all websites with monitoring enabled and due for check
        $websites = get_posts(array(
            'post_type' => 'mmwm_website',
            'posts_per_page' => -1,
            'meta_query' => array(
                array(
                    'key' => '_mmwm_monitoring_status',
                    'value' => 'active',
                    'compare' => '='
                )
            )
        ));

        if (empty($websites)) {
            return;
        }

        // Load the cron class to perform checks
        require_once MMWM_PLUGIN_DIR . 'includes/class-mmwm-cron.php';
        $cron = new MMWM_Cron();

        // Process websites sequentially with small delays
        foreach ($websites as $index => $website) {
            // Check if the website is due for check
            $last_check = get_post_meta($website->ID, '_mmwm_last_check', true);
            $check_interval = get_post_meta($website->ID, '_mmwm_check_interval', true) ?: 5;
            $check_interval_seconds = $check_interval * 60;
            
            // If no last check or check is due
            if (empty($last_check) || (time() - $last_check) > $check_interval_seconds) {
                // Schedule each check with progressive delay to avoid server overload
                $delay = $index * 5; // 5 seconds between each check
                wp_schedule_single_event(time() + $delay, 'mmwm_silent_check', array($website->ID));
            }
        }
        
        // Log silent monitoring initiation
        error_log('MMWM Silent monitoring initiated for due websites');
    }
    
    /**
     * Check if plugin needs upgrade
     *
     * @since 1.0.0
     * @return bool True if upgrade needed
     */
    public static function needs_upgrade()
    {
        $current_version = get_option('mmwm_version', '0.0.0');
        return version_compare($current_version, MMWM_VERSION, '<');
    }

    /**
     * Perform plugin upgrade
     *
     * @since 1.0.0
     */
    public static function upgrade()
    {
        $current_version = get_option('mmwm_version', '0.0.0');

        // Perform version-specific upgrades here
        // if (version_compare($current_version, '1.1.0', '<')) {
        //     // Upgrade to 1.1.0
        // }

        // Update version
        update_option('mmwm_version', MMWM_VERSION);

        error_log('MMWM Plugin upgraded from ' . $current_version . ' to ' . MMWM_VERSION);
    }
}
