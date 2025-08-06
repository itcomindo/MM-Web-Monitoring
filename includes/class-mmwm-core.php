<?php

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

/**
 * The core plugin class.
 */
class MMWM_Core
{

    /**
     * The loader that's responsible for maintaining and registering all hooks.
     *
     * @var      array    $actions       The actions registered with WordPress to be dispatched.
     * @var      array    $filters       The filters registered with WordPress to be dispatched.
     */
    protected $actions;
    protected $filters;

    /**
     * Define the core functionality of the plugin.
     */
    public function __construct()
    {
        $this->actions = array();
        $this->filters = array();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies()
    {
        require_once MMWM_PLUGIN_DIR . 'includes/class-mmwm-cpt.php';
        require_once MMWM_PLUGIN_DIR . 'includes/class-mmwm-cron.php';
        require_once MMWM_PLUGIN_DIR . 'includes/class-mmwm-admin.php';
    }

    /**
     * Define the CPT, Admin, and Cron hooks.
     */
    private function define_hooks()
    {
        $cpt_handler = new MMWM_CPT();
        add_action('init', array($cpt_handler, 'register_cpt'));
        add_action('add_meta_boxes', array($cpt_handler, 'add_meta_boxes'));
        add_action('save_post', array($cpt_handler, 'save_meta_data'));
        add_action('admin_footer-post.php', array($cpt_handler, 'add_meta_box_script')); // More specific hook
        add_action('admin_footer-post-new.php', array($cpt_handler, 'add_meta_box_script'));

        $cron_handler = new MMWM_Cron();
        add_filter('cron_schedules', array($cron_handler, 'add_cron_intervals'));
        add_action('mmwm_scheduled_check_event', array($cron_handler, 'run_checks'));

        // --- AJAX HOOKS BARU ---
        add_action('wp_ajax_mmwm_run_check_now', array($cron_handler, 'handle_ajax_run_check_now'));
        add_action('wp_ajax_mmwm_update_monitoring_status', array($cron_handler, 'handle_ajax_update_monitoring_status'));
        // -----------------------

        $admin_handler = new MMWM_Admin();
        add_action('admin_menu', array($admin_handler, 'add_admin_menu'));
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run()
    {
        $this->load_dependencies();
        $this->define_hooks();
    }

    /**
     * The code that runs during plugin activation.
     */
    public static function activate()
    {
        // Schedule cron job if not already scheduled
        if (! wp_next_scheduled('mmwm_scheduled_check_event')) {
            wp_schedule_event(time(), 'every_five_minutes', 'mmwm_scheduled_check_event');
        }
        // Flush rewrite rules for CPT
        flush_rewrite_rules();
    }

    /**
     * The code that runs during plugin deactivation.
     */
    public static function deactivate()
    {
        // Unschedule the cron job
        wp_clear_scheduled_hook('mmwm_scheduled_check_event');
        // Flush rewrite rules
        flush_rewrite_rules();
    }
}
