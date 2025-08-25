<?php

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

/**
 * Interface for website checking functionality
 */
interface MMWM_Checker_Interface
{
    /**
     * Perform a check on a website
     *
     * @param int $post_id The website post ID
     * @return array Check result with status and details
     */
    public function perform_check($post_id);

    /**
     * Validate URL before checking
     *
     * @param string $url The URL to validate
     * @return bool True if valid, false otherwise
     */
    public function validate_url($url);

    /**
     * Get check configuration for a website
     *
     * @param int $post_id The website post ID
     * @return array Configuration array
     */
    public function get_check_config($post_id);
}
