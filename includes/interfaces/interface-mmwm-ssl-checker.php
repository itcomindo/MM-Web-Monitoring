<?php

if (!defined('ABSPATH')) {
    exit;
}

interface MMWM_SSL_Checker_Interface
{
    /**
     * Check SSL certificate status
     *
     * @param string $url The URL to check
     * @return array SSL status information
     */
    public function check_ssl_status($url);

    /**
     * Get SSL expiration date
     *
     * @param string $url The URL to check
     * @return array SSL expiration information
     */
    public function get_ssl_expiration($url);

    /**
     * Check if SSL will expire soon
     *
     * @param string $url The URL to check
     * @param int $days Number of days before expiration to warn
     * @return bool True if SSL expires within specified days
     */
    public function is_ssl_expiring_soon($url, $days = 10);
}
