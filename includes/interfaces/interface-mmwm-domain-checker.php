<?php

if (!defined('ABSPATH')) {
    exit;
}

interface MMWM_Domain_Checker_Interface
{
    /**
     * Check domain expiration status
     *
     * @param string $domain The domain to check
     * @return array Domain expiration information
     */
    public function check_domain_expiration($domain);

    /**
     * Extract root domain from URL or domain
     *
     * @param string $url_or_domain URL or domain
     * @return string Root domain
     */
    public function extract_root_domain($url_or_domain);

    /**
     * Check if domain will expire soon
     *
     * @param string $domain The domain to check
     * @param int $days Number of days before expiration to warn
     * @return bool True if domain expires within specified days
     */
    public function is_domain_expiring_soon($domain, $days = 10);
}
