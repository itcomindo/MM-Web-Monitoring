<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @package   MM_Web_Monitoring
 */

// If uninstall not called from WordPress, then exit.
if (! defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// 1. Clear the scheduled cron event.
wp_clear_scheduled_hook('mmwm_scheduled_check_event');

// 2. Delete all 'mmwm_website' posts and their meta data.
$args = array(
    'post_type'      => 'mmwm_website',
    'posts_per_page' => -1,
    'post_status'    => 'any', // Get all statuses including trash
    'fields'         => 'ids', // Only get post IDs to be efficient
);

$website_posts = get_posts($args);

if (! empty($website_posts)) {
    foreach ($website_posts as $post_id) {
        // true = bypass trash and delete permanently
        wp_delete_post($post_id, true);
    }
}

// 3. Flush rewrite rules one last time (optional but good practice).
flush_rewrite_rules();
