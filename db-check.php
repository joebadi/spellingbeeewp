<?php
/**
 * Simple Database Checker
 *
 * A simpler script to check database tables without complex WordPress loading
 */

// Basic security - must be accessed from WordPress admin
if (!isset($_SERVER['HTTP_REFERER']) || strpos($_SERVER['HTTP_REFERER'], 'wp-admin') === false) {
    die('Direct access not allowed. Please access from WordPress admin.');
}

// Try to load WordPress minimal configuration
$wp_config_paths = [
    '../../../../wp-config.php',
    '../../../wp-config.php',
    '../../wp-config.php'
];

$wp_loaded = false;
foreach ($wp_config_paths as $config_path) {
    if (file_exists($config_path)) {
        require_once($config_path);
        $wp_loaded = true;
        break;
    }
}

if (!$wp_loaded) {
    die('Could not load WordPress configuration.');
}

// Connect to database directly
$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
if ($mysqli->connect_error) {
    die('Database connection failed: ' . $mysqli->connect_error);
}

echo "<h1>Omafuru Spelling Bee - Simple Database Check</h1>";

// Check WordPress table prefix
$table_prefix = $GLOBALS['table_prefix'] ?? 'wp_';
echo "<p><strong>WordPress Table Prefix:</strong> {$table_prefix}</p>";

// Check OSB tables
$osb_prefix = $table_prefix . 'osb_';
echo "<p><strong>OSB Table Prefix:</strong> {$osb_prefix}</p>";

echo "<h2>Database Tables Status</h2>";

$tables_to_check = [
    'events' => 'Events management',
    'schools' => 'School information',
    'students' => 'Student records',
    'registrations' => 'Registration data',
    'documents' => 'Document uploads',
    'donations' => 'Donation records',
    'sponsors' => 'Sponsor information',
    'admin_notifications' => 'Admin notifications (NEW in v1.2.0)',
    'communications' => 'Email communications',
    'user_conflicts' => 'User conflict resolution',
    'system_settings' => 'System configuration'
];

$missing_tables = [];
$existing_tables = [];

foreach ($tables_to_check as $table => $description) {
    $table_name = $osb_prefix . $table;
    $result = $mysqli->query("SHOW TABLES LIKE '{$table_name}'");

    if ($result && $result->num_rows > 0) {
        echo "✅ <strong>{$table_name}</strong> - {$description}<br>";
        $existing_tables[] = $table;

        // Count records for existing tables
        $count_result = $mysqli->query("SELECT COUNT(*) as count FROM {$table_name}");
        if ($count_result) {
            $count = $count_result->fetch_assoc()['count'];
            echo "&nbsp;&nbsp;&nbsp; Records: {$count}<br>";
        }
    } else {
        echo "❌ <strong>{$table_name}</strong> - {$description} <span style='color: red;'>(MISSING)</span><br>";
        $missing_tables[] = $table;
    }
}

// Check database version option
echo "<h2>Database Version Check</h2>";
$version_query = $mysqli->query("SELECT option_value FROM {$table_prefix}options WHERE option_name = 'osb_db_version'");
if ($version_query && $version_query->num_rows > 0) {
    $db_version = $version_query->fetch_assoc()['option_value'];
    echo "Current Database Version: <strong>{$db_version}</strong><br>";
} else {
    echo "❌ Database version not set (osb_db_version option missing)<br>";
}

echo "Required Database Version: <strong>1.2.0</strong><br>";

// Summary
echo "<h2>Summary</h2>";
echo "<p>Tables Found: " . count($existing_tables) . "/" . count($tables_to_check) . "</p>";

if (!empty($missing_tables)) {
    echo "<div style='background: #f8d7da; padding: 15px; border-left: 4px solid #dc3545; margin: 20px 0;'>";
    echo "<h3>⚠️ Missing Tables</h3>";
    echo "<p>The following tables are missing and need to be created:</p>";
    echo "<ul>";
    foreach ($missing_tables as $table) {
        echo "<li><strong>{$osb_prefix}{$table}</strong></li>";
    }
    echo "</ul>";
    echo "<p><strong>Solution:</strong> You need to run the database migration. Try deactivating and reactivating the plugin.</p>";
    echo "</div>";
} else {
    echo "<div style='background: #d4edda; padding: 15px; border-left: 4px solid #28a745; margin: 20px 0;'>";
    echo "<h3>✅ All Tables Present</h3>";
    echo "<p>All required database tables exist. The database structure is complete.</p>";
    echo "</div>";
}

// Check specific admin_notifications table structure
if (in_array('admin_notifications', $existing_tables)) {
    echo "<h2>Admin Notifications Table Details</h2>";
    $structure_query = $mysqli->query("DESCRIBE {$osb_prefix}admin_notifications");
    if ($structure_query) {
        echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
        echo "<tr style='background: #f1f1f1;'><th>Column</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
        while ($row = $structure_query->fetch_assoc()) {
            echo "<tr>";
            echo "<td>{$row['Field']}</td>";
            echo "<td>{$row['Type']}</td>";
            echo "<td>{$row['Null']}</td>";
            echo "<td>{$row['Key']}</td>";
            echo "<td>{$row['Default']}</td>";
            echo "</tr>";
        }
        echo "</table>";
    }
}

// Manual migration option
if (!empty($missing_tables)) {
    echo "<h2>Manual Database Creation</h2>";
    echo "<p>If the automatic migration isn't working, you can manually create the missing tables:</p>";
    echo "<ol>";
    echo "<li>Deactivate the plugin</li>";
    echo "<li>Reactivate the plugin (this should trigger migration)</li>";
    echo "<li>If that doesn't work, check your WordPress debug.log for errors</li>";
    echo "<li>Contact your hosting provider if you continue to have database issues</li>";
    echo "</ol>";
}

$mysqli->close();

echo "<hr>";
echo "<p><em>Generated: " . date('Y-m-d H:i:s') . "</em></p>";
echo "<p><a href='#' onclick='window.location.reload()'>Refresh Check</a></p>";
?>