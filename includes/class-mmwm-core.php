<?php

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

class MMWM_Core
{

    protected $actions;
    protected $filters;

    public function __construct()
    {
        $this->actions = array();
        $this->filters = array();
    }

    private function load_dependencies()
    {
        require_once MMWM_PLUGIN_DIR . 'includes/class-mmwm-cpt.php';
        require_once MMWM_PLUGIN_DIR . 'includes/class-mmwm-cron.php';
        require_once MMWM_PLUGIN_DIR . 'includes/class-mmwm-admin.php';
    }

    private function define_hooks()
    {
        $cpt_handler = new MMWM_CPT();
        add_action('init', array($cpt_handler, 'register_cpt'));
        add_action('add_meta_boxes', array($cpt_handler, 'add_meta_boxes'));
        add_action('save_post', array($cpt_handler, 'save_meta_data'));
        add_action('admin_footer-post.php', array($cpt_handler, 'add_meta_box_script'));
        add_action('admin_footer-post-new.php', array($cpt_handler, 'add_meta_box_script'));

        $cron_handler = new MMWM_Cron();
        add_filter('cron_schedules', array($cron_handler, 'add_cron_intervals'));
        add_action('mmwm_scheduled_check_event', array($cron_handler, 'run_checks'));
        add_action('wp_ajax_mmwm_run_check_now', array($cron_handler, 'handle_ajax_run_check_now'));
        add_action('wp_ajax_mmwm_update_monitoring_status', array($cron_handler, 'handle_ajax_update_monitoring_status'));

        $admin_handler = new MMWM_Admin();
        add_action('admin_menu', array($admin_handler, 'add_bulk_add_menu'));
        add_filter('manage_mmwm_website_posts_columns', array($admin_handler, 'add_custom_columns'));
        add_action('manage_mmwm_website_posts_custom_column', array($admin_handler, 'render_custom_columns'), 10, 2);
        add_filter('manage_edit-mmwm_website_sortable_columns', array($admin_handler, 'make_columns_sortable'));
        add_action('pre_get_posts', array($admin_handler, 'sort_custom_columns'));

        // --- PERBAIKAN BUG DI SINI ---
        // Menggunakan hook 'admin_footer' yang lebih general
        add_action('admin_footer', array($admin_handler, 'add_list_page_scripts_and_styles'));
        // -----------------------------

        add_action('wp_ajax_mmwm_update_interval', array($admin_handler, 'handle_ajax_update_interval'));
        add_action('wp_ajax_mmwm_bulk_add_sites', array($admin_handler, 'handle_ajax_bulk_add'));
        add_action('wp_ajax_mmwm_bulk_action_handler', array($admin_handler, 'handle_ajax_bulk_action'));
    }

    public function run()
    {
        $this->load_dependencies();
        $this->define_hooks();
    }

    public static function activate()
    {
        if (! wp_next_scheduled('mmwm_scheduled_check_event')) {
            wp_schedule_event(time(), 'every_five_minutes', 'mmwm_scheduled_check_event');
        }
        flush_rewrite_rules();
    }

    public static function deactivate()
    {
        wp_clear_scheduled_hook('mmwm_scheduled_check_event');
        flush_rewrite_rules();
    }
}
