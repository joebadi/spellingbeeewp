<?php
/**
 * Migration Check Script
 *
 * Run this script to manually check and run database migrations
 * Access: yoursite.com/wp-content/plugins/spellingbee/migration-check.php
 */

// Prevent direct execution if WordPress isn't loaded
if (!defined('ABSPATH')) {
    // Try to load WordPress
    $wp_load_paths = [
        '../../../../wp-load.php',
        '../../../wp-load.php',
        '../../../../wp-config.php'
    ];

    $wp_loaded = false;
    foreach ($wp_load_paths as $path) {
        if (file_exists($path)) {
            require_once($path);
            $wp_loaded = true;
            break;
        }
    }

    if (!$wp_loaded) {
        die('Could not load WordPress. Please make sure this script is in the correct plugin directory.');
    }
}

// Security check - only allow admins
if (!current_user_can('administrator')) {
    wp_die('Access denied. Administrator privileges required.');
}

echo "<h1>Omafuru Spelling Bee - Migration Check</h1>";

// Check if plugin constants are defined
echo "<h2>1. Plugin Constants Check</h2>";
if (defined('OSB_TABLE_PREFIX')) {
    echo "✅ OSB_TABLE_PREFIX defined: " . OSB_TABLE_PREFIX . "<br>";
} else {
    echo "❌ OSB_TABLE_PREFIX not defined<br>";
    // Try to load the plugin manually
    $plugin_file = dirname(__FILE__) . '/omafuru-spelling-bee.php';
    if (file_exists($plugin_file)) {
        echo "Attempting to load plugin...<br>";
        include_once($plugin_file);
    }
}

if (defined('OSB_PLUGIN_VERSION')) {
    echo "✅ OSB_PLUGIN_VERSION defined: " . OSB_PLUGIN_VERSION . "<br>";
} else {
    echo "❌ OSB_PLUGIN_VERSION not defined<br>";
}

// Check database version
echo "<h2>2. Database Version Check</h2>";
$current_db_version = get_option('osb_db_version', 'not set');
echo "Current DB Version: " . $current_db_version . "<br>";

if (defined('OSB_TABLE_PREFIX') && class_exists('OSB_Database_Migration')) {
    echo "Required DB Version: " . OSB_Database_Migration::DB_VERSION . "<br>";

    // Check if migration is needed
    $migration_needed = OSB_Database_Migration::migrationNeeded();
    echo "Migration Needed: " . ($migration_needed ? 'YES' : 'NO') . "<br>";
} else {
    echo "❌ OSB_Database_Migration class not found<br>";
}

// Check existing tables
echo "<h2>3. Database Tables Check</h2>";
global $wpdb;

if (defined('OSB_TABLE_PREFIX')) {
    $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;
} else {
    $table_prefix = $wpdb->prefix . 'osb_'; // fallback
    echo "Using fallback table prefix: osb_<br>";
}

$tables_to_check = [
    'events',
    'schools',
    'students',
    'registrations',
    'documents',
    'donations',
    'sponsors',
    'admin_notifications' // This is the new table from migration 1.2.0
];

foreach ($tables_to_check as $table) {
    $table_name = $table_prefix . $table;
    $exists = $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
    echo ($exists ? "✅" : "❌") . " Table: $table_name<br>";
}

// Run migration if needed
echo "<h2>4. Migration Execution</h2>";
if (isset($_GET['run_migration']) && $_GET['run_migration'] == 'yes') {
    echo "Running migration...<br>";

    try {
        OSB_Database_Migration::migrate();
        echo "✅ Migration completed successfully<br>";

        // Check new DB version
        $new_db_version = get_option('osb_db_version');
        echo "New DB Version: " . $new_db_version . "<br>";

    } catch (Exception $e) {
        echo "❌ Migration failed: " . $e->getMessage() . "<br>";
    }
} else {
    echo '<a href="?run_migration=yes" style="background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px;">Run Migration Now</a><br>';
}

// Check admin_notifications table specifically
echo "<h2>5. Admin Notifications Table Details</h2>";
$admin_notifications_table = $table_prefix . 'admin_notifications';
$table_exists = $wpdb->get_var("SHOW TABLES LIKE '$admin_notifications_table'") == $admin_notifications_table;

if ($table_exists) {
    echo "✅ admin_notifications table exists<br>";

    // Show table structure
    $columns = $wpdb->get_results("DESCRIBE $admin_notifications_table");
    echo "<h3>Table Structure:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    foreach ($columns as $column) {
        echo "<tr>";
        echo "<td>{$column->Field}</td>";
        echo "<td>{$column->Type}</td>";
        echo "<td>{$column->Null}</td>";
        echo "<td>{$column->Key}</td>";
        echo "<td>{$column->Default}</td>";
        echo "</tr>";
    }
    echo "</table>";

    // Count records
    $count = $wpdb->get_var("SELECT COUNT(*) FROM $admin_notifications_table");
    echo "<br>Records in table: $count<br>";

} else {
    echo "❌ admin_notifications table does not exist<br>";
}

echo "<h2>6. Test Registration Process</h2>";
echo '<a href="' . home_url() . '" target="_blank">Go to Frontend</a> to test registration<br>';

echo "<h2>7. WordPress Email Configuration</h2>";
$admin_email = get_option('admin_email');
echo "WordPress Admin Email: " . $admin_email . "<br>";

// Test email sending capability
if (isset($_GET['test_email']) && $_GET['test_email'] == 'yes') {
    $test_email_result = wp_mail(
        $admin_email,
        'OSB Plugin Email Test',
        'This is a test email from the Omafuru Spelling Bee plugin to verify email functionality.',
        ['Content-Type: text/html; charset=UTF-8']
    );

    echo "Test email sent: " . ($test_email_result ? '✅ SUCCESS' : '❌ FAILED') . "<br>";

    if (!$test_email_result) {
        global $phpmailer;
        if (isset($phpmailer)) {
            echo "Email error: " . $phpmailer->ErrorInfo . "<br>";
        }
    }
} else {
    echo '<a href="?test_email=yes" style="background: #28a745; color: white; padding: 8px 16px; text-decoration: none; border-radius: 3px;">Send Test Email</a><br>';
}

echo "<hr>";
echo "<p>This diagnostic script helps identify issues with database migrations and email functionality.</p>";
?>