<?php

/**
 * Plugin Name:       MM Web Monitoring
 * Description:       A simple plugin to monitor website performance and uptime.
 * Version:           1.1.1
 * Author:            Budi Haryono
 * Author URI:        https://budiharyono.id/
 * License:           GPL2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       mm-web-monitoring
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Tags:              performance, uptime, monitoring, web, plugin
 * Plugin URI:        https://budiharyono.id/
 */

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

// Define constants
define('MMWM_VERSION', '1.1.1');
define('MMWM_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('MMWM_PLUGIN_URL', plugin_dir_url(__FILE__));

// Include the main core class.
require_once MMWM_PLUGIN_DIR . 'includes/class-mmwm-core.php';

// Include activator for activation hooks
require_once MMWM_PLUGIN_DIR . 'includes/core/class-mmwm-activator.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function mmwm_run_plugin()
{
    try {
        $plugin = new MMWM_Core();
        $plugin->run();
    } catch (Exception $e) {
        // Log error but don't break the site
        error_log('MMWM Plugin Error: ' . $e->getMessage());
    }
}

// Initialize plugin after all plugins are loaded
add_action('plugins_loaded', 'mmwm_run_plugin');

/**
 * Activation and deactivation hooks.
 */
register_activation_hook(__FILE__, array('MMWM_Activator', 'activate'));
register_deactivation_hook(__FILE__, array('MMWM_Core', 'deactivate'));
