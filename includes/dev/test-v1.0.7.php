/**
* Simple test script untuk memverifikasi fitur-fitur v1.0.7
* Untuk development purposes only
*/

// Test 1: Auto-reload interval setting
function test_auto_reload_setting() {
$interval = get_option('mmwm_auto_reload_interval', 30);
echo "Current auto-reload interval: " . $interval . " seconds\n";

if ($interval >= 10 && $interval <= 300) {
    echo "✅ Auto-reload interval within valid range\n" ;
    } else {
    echo "❌ Auto-reload interval outside valid range\n" ;
    }
    }

    // Test 2: Email template loading
    function test_email_template_loading() {
    if (class_exists('MMWM_Email_Template')) {
    echo "✅ MMWM_Email_Template class loaded successfully\n" ;

    // Test method availability
    if (method_exists('MMWM_Email_Template', 'build_monitoring_report' )) {
    echo "✅ build_monitoring_report method available\n" ;
    }
    if (method_exists('MMWM_Email_Template', 'build_ssl_expiring_notification' )) {
    echo "✅ build_ssl_expiring_notification method available\n" ;
    }
    if (method_exists('MMWM_Email_Template', 'build_domain_expiring_notification' )) {
    echo "✅ build_domain_expiring_notification method available\n" ;
    }
    } else {
    echo "❌ MMWM_Email_Template class not loaded\n" ;
    }
    }

    // Test 3: Domain checker loading
    function test_domain_checker_loading() {
    if (class_exists('MMWM_Domain_Checker')) {
    echo "✅ MMWM_Domain_Checker class loaded successfully\n" ;

    if (method_exists('MMWM_Domain_Checker', 'extract_root_domain' )) {
    echo "✅ extract_root_domain method available\n" ;
    }
    if (method_exists('MMWM_Domain_Checker', 'check_domain_expiration' )) {
    echo "✅ check_domain_expiration method available\n" ;
    }
    } else {
    echo "❌ MMWM_Domain_Checker class not loaded\n" ;
    }
    }

    // Test 4: Interface loading
    function test_interface_loading() {
    if (interface_exists('MMWM_Domain_Checker_Interface')) {
    echo "✅ MMWM_Domain_Checker_Interface loaded successfully\n" ;
    } else {
    echo "❌ MMWM_Domain_Checker_Interface not loaded\n" ;
    }
    }

    // Run tests only if this file is called directly via wp-cli or similar
    if (defined('WP_CLI') && WP_CLI) {
    echo "=== MM Web Monitoring v1.0.7 Feature Tests ===\n\n" ;

    echo "Test 1: Auto-reload interval setting\n" ;
    test_auto_reload_setting();
    echo "\n" ;

    echo "Test 2: Email template loading\n" ;
    test_email_template_loading();
    echo "\n" ;

    echo "Test 3: Domain checker loading\n" ;
    test_domain_checker_loading();
    echo "\n" ;

    echo "Test 4: Interface loading\n" ;
    test_interface_loading();
    echo "\n" ;

    echo "=== Test completed ===\n" ;
    }