<?php
/**
 * Database Migration Class
 *
 * Handles database schema updates and migrations
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class OSB_Database_Migration {

    /**
     * Current database version
     */
    const DB_VERSION = '1.2.0';

    /**
     * Run necessary database migrations
     */
    public static function migrate() {
        $current_version = get_option('osb_db_version', '1.0.0');

        if (version_compare($current_version, self::DB_VERSION, '<')) {
            self::runMigrations($current_version);
            update_option('osb_db_version', self::DB_VERSION);
        }
    }

    /**
     * Run migrations based on current version
     */
    private static function runMigrations($current_version) {
        // Migration for version 1.1.0 - Progress Tracking
        if (version_compare($current_version, '1.1.0', '<')) {
            self::migration_1_1_0();
        }

        // Migration for version 1.2.0 - Workflow Automation
        if (version_compare($current_version, '1.2.0', '<')) {
            self::migration_1_2_0();
        }
    }

    /**
     * Migration 1.1.0 - Add progress tracking to registrations
     */
    private static function migration_1_1_0() {
        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        $sql = "ALTER TABLE {$table_prefix}registrations
                ADD COLUMN step_progress JSON DEFAULT NULL COMMENT 'Tracks completion of each step',
                ADD COLUMN last_active_step INT DEFAULT 1 COMMENT 'Last step user was working on',
                ADD COLUMN form_data_cache TEXT DEFAULT NULL COMMENT 'Cached form data for resume functionality',
                ADD COLUMN auto_save_timestamp TIMESTAMP NULL DEFAULT NULL COMMENT 'Last auto-save time'";

        $result = $wpdb->query($sql);

        if ($result === false) {
            error_log('OSB Migration 1.1.0 failed: ' . $wpdb->last_error);
            return false;
        }

        error_log('OSB Migration 1.1.0 completed successfully');
        return true;
    }

    /**
     * Migration 1.2.0 - Add admin notifications table for workflow automation
     */
    private static function migration_1_2_0() {
        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        $sql = "CREATE TABLE IF NOT EXISTS {$table_prefix}admin_notifications (
                    id int(11) NOT NULL AUTO_INCREMENT,
                    type varchar(50) NOT NULL COMMENT 'Notification type',
                    title varchar(255) NOT NULL COMMENT 'Notification title',
                    message text NOT NULL COMMENT 'Notification message',
                    data text DEFAULT NULL COMMENT 'JSON data for notification',
                    priority enum('low','medium','high') DEFAULT 'medium' COMMENT 'Priority level',
                    is_read tinyint(1) DEFAULT 0 COMMENT 'Read status',
                    read_at timestamp NULL DEFAULT NULL COMMENT 'When notification was read',
                    created_at timestamp DEFAULT CURRENT_TIMESTAMP,
                    PRIMARY KEY (id),
                    KEY idx_type (type),
                    KEY idx_priority (priority),
                    KEY idx_is_read (is_read),
                    KEY idx_created (created_at)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $result = $wpdb->query($sql);

        if ($result === false) {
            error_log('OSB Migration 1.2.0 failed: ' . $wpdb->last_error);
            return false;
        }

        error_log('OSB Migration 1.2.0 completed successfully');
        return true;
    }

    /**
     * Initialize progress tracking for existing registrations
     */
    public static function initializeExistingRegistrations() {
        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        // Initialize step_progress for existing registrations without it
        $default_progress = json_encode([
            'step_1' => ['completed' => true, 'completed_at' => current_time('mysql')],
            'step_2' => ['completed' => false],
            'step_3' => ['completed' => false],
            'step_4' => ['completed' => false],
            'step_5' => ['completed' => false]
        ]);

        $sql = $wpdb->prepare(
            "UPDATE {$table_prefix}registrations
             SET step_progress = %s, last_active_step = 2
             WHERE step_progress IS NULL",
            $default_progress
        );

        return $wpdb->query($sql);
    }

    /**
     * Check if migration is needed
     */
    public static function migrationNeeded() {
        $current_version = get_option('osb_db_version', '1.0.0');
        return version_compare($current_version, self::DB_VERSION, '<');
    }

    /**
     * Get current database version
     */
    public static function getCurrentVersion() {
        return get_option('osb_db_version', '1.0.0');
    }

    /**
     * Manually run migration (for admin use)
     */
    public static function forceMigration() {
        delete_option('osb_db_version');
        self::migrate();
        return "Migration completed. Current version: " . self::DB_VERSION;
    }

    /**
     * Initialize progress tracking for a new registration
     */
    public static function initializeRegistrationProgress($registration_id) {
        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        $default_progress = json_encode([
            'step_1' => ['completed' => false],
            'step_2' => ['completed' => false],
            'step_3' => ['completed' => false],
            'step_4' => ['completed' => false],
            'step_5' => ['completed' => false]
        ]);

        $wpdb->update(
            "{$table_prefix}registrations",
            [
                'step_progress' => $default_progress,
                'last_active_step' => 1
            ],
            ['id' => $registration_id],
            ['%s', '%d'],
            ['%d']
        );

        return true;
    }

    /**
     * Generate resume URL for a registration
     */
    public static function generateResumeUrl($registration_token, $event_id) {
        $event_url = home_url('/events/' . $event_id . '/register/');
        return add_query_arg('resume', $registration_token, $event_url);
    }
}

// Run migration on plugin activation/upgrade
add_action('plugins_loaded', function() {
    if (OSB_Database_Migration::migrationNeeded()) {
        OSB_Database_Migration::migrate();
    }
});