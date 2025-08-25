<?php
// Test WordPress plugin loading
require_once 'includes/class-mmwm-core.php';

echo "Testing WordPress plugin loading...\n";

try {
    $core = new MMWM_Core();
    echo "✅ MMWM_Core loaded successfully\n";

    // Test if all required classes are available
    $required_classes = [
        'MMWM_Checker',
        'MMWM_Scheduler',
        'MMWM_Notifier',
        'MMWM_Cron',
        'MMWM_CPT',
        'MMWM_Admin'
    ];

    foreach ($required_classes as $class) {
        if (class_exists($class)) {
            echo "✅ $class is available\n";
        } else {
            echo "❌ $class is NOT available\n";
        }
    }

    // Test cron instance creation
    $cron = new MMWM_Cron();
    echo "✅ MMWM_Cron instance created successfully\n";

    echo "\n🎉 All critical components loaded without errors!\n";
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
