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
