<?php

// Exit if accessed directly.
if (! defined('ABSPATH')) {
    exit;
}

/**
 * HTML parsing utility class
 */
class MMWM_HTML_Parser
{
    /**
     * Find HTML element in content using CSS selector
     *
     * @param string $html The HTML content to search
     * @param string $selector The CSS selector to find
     * @return bool True if element found, false otherwise
     */
    public static function find_element($html, $selector)
    {
        if (empty($html) || empty($selector)) {
            return false;
        }

        // Enable internal error handling for libxml
        $use_errors = libxml_use_internal_errors(true);

        try {
            $dom = new DOMDocument();

            // Load HTML with error suppression for malformed HTML
            $loaded = @$dom->loadHTML('<?xml encoding="UTF-8">' . $html);

            if (!$loaded) {
                return false;
            }

            $xpath = new DOMXPath($dom);
            $query = self::convert_css_to_xpath($selector);

            if (empty($query)) {
                // Fallback to simple text search
                return strpos($html, $selector) !== false;
            }

            $results = $xpath->query($query);
            return $results && $results->length > 0;
        } catch (Exception $e) {
            error_log('MMWM HTML Parser Error: ' . $e->getMessage());
            return false;
        } finally {
            // Restore previous error handling
            libxml_use_internal_errors($use_errors);
            libxml_clear_errors();
        }
    }

    /**
     * Convert CSS selector to XPath query
     *
     * @param string $selector CSS selector
     * @return string XPath query
     */
    private static function convert_css_to_xpath($selector)
    {
        $selector = trim($selector);
        $first_char = substr($selector, 0, 1);

        switch ($first_char) {
            case '#':
                // ID selector: #myid
                $element_id = substr($selector, 1);
                return "//*[@id='" . self::escape_xpath_value($element_id) . "']";

            case '.':
                // Class selector: .myclass
                $class_name = substr($selector, 1);
                return "//*[contains(concat(' ', normalize-space(@class), ' '), ' " . self::escape_xpath_value($class_name) . " ')]";

            default:
                // Tag selector or complex selector
                if (preg_match('/^[a-zA-Z][a-zA-Z0-9]*$/', $selector)) {
                    // Simple tag selector
                    return "//" . $selector;
                }

                // For complex selectors, return empty to fallback to text search
                return '';
        }
    }

    /**
     * Escape value for XPath query
     *
     * @param string $value Value to escape
     * @return string Escaped value
     */
    private static function escape_xpath_value($value)
    {
        // Simple escaping for XPath
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Get meta description from HTML
     *
     * @param string $html HTML content
     * @return string Meta description or empty string
     */
    public static function get_meta_description($html)
    {
        if (preg_match('/<meta\s+name=["\']description["\']\s+content=["\'](.*?)["\']/i', $html, $matches)) {
            return trim($matches[1]);
        }
        return '';
    }

    /**
     * Get page title from HTML
     *
     * @param string $html HTML content
     * @return string Page title or empty string
     */
    public static function get_page_title($html)
    {
        if (preg_match('/<title>(.*?)<\/title>/i', $html, $matches)) {
            return trim(html_entity_decode($matches[1]));
        }
        return '';
    }
}
