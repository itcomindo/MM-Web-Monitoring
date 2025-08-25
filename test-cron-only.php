<?php
echo "Testing MMWM_Cron class loading...\n";

try {
    // Load dependencies manually
    require_once 'includes/utilities/class-mmwm-wp-compat.php';
    require_once 'includes/interfaces/interface-mmwm-scheduler.php';
    require_once 'includes/utilities/class-mmwm-validator.php';
    require_once 'includes/utilities/class-mmwm-sanitizer.php';
    require_once 'includes/utilities/class-mmwm-html-parser.php';
    require_once 'includes/monitoring/class-mmwm-scheduler.php';
    require_once 'includes/monitoring/class-mmwm-checker.php';
    require_once 'includes/class-mmwm-cron.php';

    echo "✅ All dependencies loaded\n";

    // Test creating instance
    $cron = new MMWM_Cron();
    echo "✅ MMWM_Cron instance created successfully\n";

    echo "\n🎉 MMWM_Cron class is working properly!\n";
} catch (Error $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}
