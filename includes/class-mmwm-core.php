<?php

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

class MMWM_Core
{

    public function __construct()
    {
        $this->load_dependencies();
    }

    private function load_dependencies()
    {
        // Load WordPress compatibility layer first
        require_once MMWM_PLUGIN_DIR . 'includes/utilities/class-mmwm-wp-compat.php';

        // Load interfaces
        require_once MMWM_PLUGIN_DIR . 'includes/interfaces/interface-mmwm-checker.php';
        require_once MMWM_PLUGIN_DIR . 'includes/interfaces/interface-mmwm-notifier.php';
        require_once MMWM_PLUGIN_DIR . 'includes/interfaces/interface-mmwm-scheduler.php';
        require_once MMWM_PLUGIN_DIR . 'includes/interfaces/interface-mmwm-ssl-checker.php';
        require_once MMWM_PLUGIN_DIR . 'includes/interfaces/interface-mmwm-domain-checker.php';

        // Load utilities
        require_once MMWM_PLUGIN_DIR . 'includes/utilities/class-mmwm-validator.php';
        require_once MMWM_PLUGIN_DIR . 'includes/utilities/class-mmwm-sanitizer.php';
        require_once MMWM_PLUGIN_DIR . 'includes/utilities/class-mmwm-html-parser.php';
        require_once MMWM_PLUGIN_DIR . 'includes/utilities/class-mmwm-email-template.php';

        // Load core classes
        require_once MMWM_PLUGIN_DIR . 'includes/core/class-mmwm-loader.php';
        require_once MMWM_PLUGIN_DIR . 'includes/core/class-mmwm-activator.php';

        // Load monitoring classes
        require_once MMWM_PLUGIN_DIR . 'includes/monitoring/class-mmwm-checker.php';
        require_once MMWM_PLUGIN_DIR . 'includes/monitoring/class-mmwm-notifier.php';
        require_once MMWM_PLUGIN_DIR . 'includes/monitoring/class-mmwm-scheduler.php';
        require_once MMWM_PLUGIN_DIR . 'includes/monitoring/class-mmwm-ssl-checker.php';
        require_once MMWM_PLUGIN_DIR . 'includes/monitoring/class-mmwm-domain-checker.php';

        // Load existing classes
        require_once MMWM_PLUGIN_DIR . 'includes/class-mmwm-cpt.php';
        require_once MMWM_PLUGIN_DIR . 'includes/class-mmwm-cron.php';
        require_once MMWM_PLUGIN_DIR . 'includes/class-mmwm-admin.php';
    }

    private function define_hooks()
    {
        $cpt_handler = new MMWM_CPT();
        add_action('init', array($cpt_handler, 'register_cpt'));
        add_action('add_meta_boxes', array($cpt_handler, 'add_meta_boxes'));
        add_action('save_post', array($cpt_handler, 'save_meta_data'), 10, 2);
        add_action('admin_footer', array($cpt_handler, 'add_meta_box_script'));

        // Hook yang aman untuk menjalankan skrip di header admin
        add_action('admin_head', array($cpt_handler, 'add_cpt_header_scripts'));

        $cron_handler = new MMWM_Cron();
        add_filter('cron_schedules', array($cron_handler, 'add_cron_intervals'));
        add_action('mmwm_scheduled_check_event', array($cron_handler, 'run_checks'));
        add_action('mmwm_daily_global_check_event', array($cron_handler, 'run_daily_global_check'));
        add_action('mmwm_sequential_global_check', array($cron_handler, 'handle_sequential_global_check'));
        add_action('mmwm_silent_check', array($cron_handler, 'handle_silent_check'));
        add_action('wp_ajax_mmwm_run_check_now', array($cron_handler, 'handle_ajax_run_check_now'));
        add_action('wp_ajax_mmwm_update_monitoring_status', array($cron_handler, 'handle_ajax_update_monitoring_status'));
        add_action('wp_ajax_mmwm_enable_domain_monitoring', array($cron_handler, 'handle_ajax_enable_domain_monitoring'));

        // Schedule daily global check on plugin activation
        add_action('init', array($cron_handler, 'schedule_daily_global_check'));
        
        // Tambahkan endpoint untuk external cron trigger
        add_action('init', array('MMWM_Core', 'register_cron_endpoint'));
        
        // Tambahkan hook untuk menangani external cron trigger
        add_action('template_redirect', array('MMWM_Core', 'handle_cron_endpoint'));

        $admin_handler = new MMWM_Admin();
        // Global options page
        add_action('admin_menu', array($admin_handler, 'add_global_options_page'));
        // Bulk add menu
        add_action('admin_menu', array($admin_handler, 'add_bulk_add_menu'));

        // CPT list table hooks
        add_filter('manage_mmwm_website_posts_columns', array($admin_handler, 'add_custom_columns'));
        add_action('manage_mmwm_website_posts_custom_column', array($admin_handler, 'render_custom_columns'), 10, 2);
        add_filter('manage_edit-mmwm_website_sortable_columns', array($admin_handler, 'make_columns_sortable'));
        add_action('pre_get_posts', array($admin_handler, 'sort_custom_columns'));
        add_action('admin_footer', array($admin_handler, 'add_list_page_scripts_and_styles'));

        // Bulk actions
        add_filter('bulk_actions-edit-mmwm_website', array($admin_handler, 'add_bulk_actions'));
        add_filter('handle_bulk_actions-edit-mmwm_website', array($admin_handler, 'handle_bulk_actions'), 10, 3);
        add_action('admin_notices', array($admin_handler, 'show_bulk_action_admin_notice'));

        // AJAX handlers
        add_action('wp_ajax_mmwm_update_interval', array($admin_handler, 'handle_ajax_update_interval'));
        add_action('wp_ajax_mmwm_update_host_in', array($admin_handler, 'handle_ajax_update_host_in')); // New hook
        add_action('wp_ajax_mmwm_update_notification_email', array($admin_handler, 'handle_ajax_update_notification_email'));
        add_action('wp_ajax_mmwm_update_notification_trigger', array($admin_handler, 'handle_ajax_update_notification_trigger'));
        add_action('wp_ajax_mmwm_bulk_add_sites', array($admin_handler, 'handle_ajax_bulk_add'));
        add_action('wp_ajax_mmwm_bulk_action_handler', array($admin_handler, 'handle_ajax_bulk_action'));
        add_action('wp_ajax_mmwm_toggle_user_agent', array($admin_handler, 'handle_ajax_toggle_user_agent'));
        add_action('wp_ajax_mmwm_regenerate_cron_key', array($admin_handler, 'ajax_regenerate_cron_key'));
    }

    public function run()
    {
        $this->load_dependencies();
        $this->define_hooks();
    }

    public static function activate()
    {
        // Pastikan event cron terjadwal dengan benar
        if (! wp_next_scheduled('mmwm_scheduled_check_event')) {
            wp_schedule_event(time(), 'every_five_minutes', 'mmwm_scheduled_check_event');
        }
        
        // Tambahkan opsi untuk memastikan cron berjalan
        add_option('mmwm_last_cron_run', time());
        add_option('mmwm_cron_health_check', 'active');
        
        flush_rewrite_rules();
    }

    public static function deactivate()
    {
        wp_clear_scheduled_hook('mmwm_scheduled_check_event');
        wp_clear_scheduled_hook('mmwm_daily_global_check_event');
        flush_rewrite_rules();
    }
    
    /**
     * Register custom endpoint for external cron trigger
     */
    public static function register_cron_endpoint()
    {
        add_rewrite_rule(
            'mmwm-cron-trigger/([a-zA-Z0-9]+)/?$',
            'index.php?mmwm_cron_trigger=$matches[1]',
            'top'
        );
        add_rewrite_tag('%mmwm_cron_trigger%', '([a-zA-Z0-9]+)');
    }
    
    /**
     * Handle external cron trigger request
     */
    public static function handle_cron_endpoint()
    {
        $cron_key = get_query_var('mmwm_cron_trigger');
        if (!empty($cron_key)) {
            // Verifikasi kunci keamanan
            $stored_key = get_option('mmwm_cron_security_key');
            
            // Jika kunci belum ada, buat kunci baru
            if (empty($stored_key)) {
                $stored_key = wp_generate_password(32, false);
                update_option('mmwm_cron_security_key', $stored_key);
            }
            
            // Verifikasi kunci yang diberikan
            if ($cron_key === $stored_key) {
                // Jalankan pemeriksaan
                do_action('mmwm_scheduled_check_event');
                
                // Perbarui waktu cron terakhir
                update_option('mmwm_last_cron_run', time());
                update_option('mmwm_cron_health_check', 'active');
                
                // Kirim respons dan hentikan eksekusi
                header('Content-Type: application/json');
                echo json_encode(array('status' => 'success', 'message' => 'Cron triggered successfully'));
                exit;
            } else {
                // Kunci tidak valid
                header('Content-Type: application/json');
                echo json_encode(array('status' => 'error', 'message' => 'Invalid security key'));
                exit;
            }
        }
    }
}
