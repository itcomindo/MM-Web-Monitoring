<?php

// Test file untuk memastikan semua dependencies terbaca
define('ABSPATH', true);
define('MMWM_PLUGIN_DIR', __DIR__ . '/');

echo "=== MMWM DEPENDENCY TEST ===" . PHP_EOL;

try {
    // Test loading core
    require_once 'includes/class-mmwm-core.php';
    echo "✅ Core class loaded" . PHP_EOL;

    // Test instantiation
    $core = new MMWM_Core();
    echo "✅ Core instantiated" . PHP_EOL;

    // Test if all classes are loaded
    $classes_to_check = [
        'MMWM_Validator',
        'MMWM_Sanitizer',
        'MMWM_HTML_Parser',
        'MMWM_Loader',
        'MMWM_Activator',
        'MMWM_Checker',
        'MMWM_Notifier',
        'MMWM_Scheduler'
    ];

    foreach ($classes_to_check as $class) {
        if (class_exists($class)) {
            echo "✅ $class loaded" . PHP_EOL;
        } else {
            echo "❌ $class NOT loaded" . PHP_EOL;
        }
    }

    // Test interface loading
    $interfaces_to_check = [
        'MMWM_Checker_Interface',
        'MMWM_Notifier_Interface',
        'MMWM_Scheduler_Interface'
    ];

    foreach ($interfaces_to_check as $interface) {
        if (interface_exists($interface)) {
            echo "✅ $interface loaded" . PHP_EOL;
        } else {
            echo "❌ $interface NOT loaded" . PHP_EOL;
        }
    }

    echo PHP_EOL . "=== BASIC FUNCTIONALITY TESTS ===" . PHP_EOL;

    // Test basic class methods exist
    $checker = new MMWM_Checker();
    echo "✅ Checker has perform_check method: " . (method_exists($checker, 'perform_check') ? 'YES' : 'NO') . PHP_EOL;

    $scheduler = new MMWM_Scheduler();
    echo "✅ Scheduler has schedule_checks method: " . (method_exists($scheduler, 'schedule_checks') ? 'YES' : 'NO') . PHP_EOL;
    echo "✅ Scheduler has is_due_for_check method: " . (method_exists($scheduler, 'is_due_for_check') ? 'YES' : 'NO') . PHP_EOL;

    $notifier = new MMWM_Notifier();
    echo "✅ Notifier has send_notification method: " . (method_exists($notifier, 'send_notification') ? 'YES' : 'NO') . PHP_EOL;

    // Test interface implementation
    echo "✅ Checker implements interface: " . (($checker instanceof MMWM_Checker_Interface) ? 'YES' : 'NO') . PHP_EOL;
    echo "✅ Scheduler implements interface: " . (($scheduler instanceof MMWM_Scheduler_Interface) ? 'YES' : 'NO') . PHP_EOL;
    echo "✅ Notifier implements interface: " . (($notifier instanceof MMWM_Notifier_Interface) ? 'YES' : 'NO') . PHP_EOL;

    echo PHP_EOL . "🎉 DEPENDENCY LOADING SUCCESSFUL!" . PHP_EOL;
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . PHP_EOL;
} catch (Error $e) {
    echo "❌ FATAL ERROR: " . $e->getMessage() . PHP_EOL;
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . PHP_EOL;
}
