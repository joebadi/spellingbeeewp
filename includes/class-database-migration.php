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
    const DB_VERSION = '1.3.0';

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

        // Migration for version 1.2.1 - Add expression_of_interest column
        if (version_compare($current_version, '1.2.1', '<')) {
            self::migration_1_2_1();
        }

        // Migration for version 1.2.2 - Add missing student columns
        if (version_compare($current_version, '1.2.2', '<')) {
            self::migration_1_2_2();
        }

        // Migration for version 1.3.0 - Enhanced EOI and Digital Signatures
        if (version_compare($current_version, '1.3.0', '<')) {
            self::migration_1_3_0();
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
     * Migration 1.2.1 - Add expression_of_interest column to registrations table
     */
    private static function migration_1_2_1() {
        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        // Check if column already exists
        $column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$table_prefix}registrations LIKE 'expression_of_interest'");

        if (empty($column_exists)) {
            $sql = "ALTER TABLE {$table_prefix}registrations
                    ADD COLUMN expression_of_interest varchar(500) DEFAULT NULL COMMENT 'URL to uploaded expression of interest document' AFTER documents_uploaded";

            $result = $wpdb->query($sql);

            if ($result === false) {
                error_log('OSB Migration 1.2.1 failed: ' . $wpdb->last_error);
                return false;
            }

            error_log('OSB Migration 1.2.1 completed successfully - added expression_of_interest column');
        } else {
            error_log('OSB Migration 1.2.1 skipped - expression_of_interest column already exists');
        }

        return true;
    }

    /**
     * Migration 1.2.2 - Add missing student columns
     */
    private static function migration_1_2_2() {
        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        // Check if columns exist and add them if they don't
        $columns_to_add = [
            'grade_level' => "varchar(10) DEFAULT NULL COMMENT 'Student grade level'"
        ];

        $success = true;

        foreach ($columns_to_add as $column_name => $column_definition) {
            // Check if column already exists
            $column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$table_prefix}students LIKE '{$column_name}'");

            if (empty($column_exists)) {
                $sql = "ALTER TABLE {$table_prefix}students ADD COLUMN {$column_name} {$column_definition}";
                $result = $wpdb->query($sql);

                if ($result === false) {
                    error_log("OSB Migration 1.2.2 failed adding {$column_name}: " . $wpdb->last_error);
                    $success = false;
                } else {
                    error_log("OSB Migration 1.2.2 successfully added {$column_name} column");
                }
            } else {
                error_log("OSB Migration 1.2.2 skipped {$column_name} - column already exists");
            }
        }

        if ($success) {
            error_log('OSB Migration 1.2.2 completed successfully - added missing student columns');
        }

        return $success;
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

    /**
     * Migration 1.3.0 - Enhanced EOI and Digital Signatures
     */
    private static function migration_1_3_0() {
        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        $success = true;

        // Add digital signature and enhanced EOI columns to registrations table
        $registrations_columns = [
            'digital_signature' => "LONGTEXT NULL COMMENT 'Base64 encoded signature data'",
            'signature_timestamp' => "TIMESTAMP NULL COMMENT 'When signature was captured'",
            'eoi_form_data' => "JSON NULL COMMENT 'Complete EOI form responses'",
            'submission_device_info' => "VARCHAR(500) NULL COMMENT 'Device/browser info for audit'",
            'signature_ip_address' => "VARCHAR(45) NULL COMMENT 'IP address when signature was captured'",
            'form_completion_time' => "INT NULL COMMENT 'Time taken to complete form in seconds'"
        ];

        foreach ($registrations_columns as $column_name => $column_definition) {
            $column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$table_prefix}registrations LIKE '{$column_name}'");

            if (empty($column_exists)) {
                $sql = "ALTER TABLE {$table_prefix}registrations ADD COLUMN {$column_name} {$column_definition}";
                $result = $wpdb->query($sql);

                if ($result === false) {
                    error_log("OSB Migration 1.3.0 failed adding {$column_name}: " . $wpdb->last_error);
                    $success = false;
                } else {
                    error_log("OSB Migration 1.3.0 successfully added {$column_name} column");
                }
            } else {
                error_log("OSB Migration 1.3.0 skipped {$column_name} - column already exists");
            }
        }

        // Add draw_date and notification settings to events table
        $events_columns = [
            'draw_date' => "DATETIME NULL COMMENT 'Date for group draws'",
            'notification_settings' => "JSON NULL COMMENT 'Multi-channel notification preferences'"
        ];

        foreach ($events_columns as $column_name => $column_definition) {
            $column_exists = $wpdb->get_results("SHOW COLUMNS FROM {$table_prefix}events LIKE '{$column_name}'");

            if (empty($column_exists)) {
                $sql = "ALTER TABLE {$table_prefix}events ADD COLUMN {$column_name} {$column_definition}";
                $result = $wpdb->query($sql);

                if ($result === false) {
                    error_log("OSB Migration 1.3.0 failed adding {$column_name} to events: " . $wpdb->last_error);
                    $success = false;
                } else {
                    error_log("OSB Migration 1.3.0 successfully added {$column_name} to events table");
                }
            }
        }

        // Create notification queue table
        $notification_queue_sql = "CREATE TABLE IF NOT EXISTS {$table_prefix}notification_queue (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_id INT NOT NULL,
            school_id INT NOT NULL,
            notification_type ENUM('email','sms','whatsapp') NOT NULL,
            template_name VARCHAR(100) NOT NULL,
            message_data JSON NULL,
            scheduled_at DATETIME NOT NULL,
            sent_at TIMESTAMP NULL,
            status ENUM('pending','sent','failed','retry') DEFAULT 'pending',
            delivery_info JSON NULL,
            retry_count INT DEFAULT 0,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_status (status),
            INDEX idx_scheduled (scheduled_at),
            INDEX idx_event_school (event_id, school_id),
            FOREIGN KEY (event_id) REFERENCES {$table_prefix}events(id) ON DELETE CASCADE,
            FOREIGN KEY (school_id) REFERENCES {$table_prefix}schools(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $result = $wpdb->query($notification_queue_sql);
        if ($result === false) {
            error_log("OSB Migration 1.3.0 failed creating notification_queue table: " . $wpdb->last_error);
            $success = false;
        } else {
            error_log("OSB Migration 1.3.0 successfully created notification_queue table");
        }

        // Create notification history table
        $notification_history_sql = "CREATE TABLE IF NOT EXISTS {$table_prefix}notification_history (
            id INT AUTO_INCREMENT PRIMARY KEY,
            event_id INT NOT NULL,
            notification_type VARCHAR(50) NOT NULL,
            recipient_count INT NOT NULL,
            sent_by INT NOT NULL,
            sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            message_template VARCHAR(100),
            delivery_summary JSON NULL,
            INDEX idx_event (event_id),
            INDEX idx_sent_by (sent_by),
            FOREIGN KEY (event_id) REFERENCES {$table_prefix}events(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

        $result = $wpdb->query($notification_history_sql);
        if ($result === false) {
            error_log("OSB Migration 1.3.0 failed creating notification_history table: " . $wpdb->last_error);
            $success = false;
        } else {
            error_log("OSB Migration 1.3.0 successfully created notification_history table");
        }

        // Update existing registrations to disable upload requirement for EOI
        $update_sql = "UPDATE {$table_prefix}registrations
                       SET step_progress = JSON_SET(
                           COALESCE(step_progress, '{}'),
                           '$.eoi_requires_upload', false,
                           '$.eoi_digital_form', true
                       )
                       WHERE step_progress IS NOT NULL";
        $wpdb->query($update_sql);

        if ($success) {
            error_log('OSB Migration 1.3.0 completed successfully - Enhanced EOI and Digital Signatures');
        }

        return $success;
    }
}

// Run migration on plugin activation/upgrade
add_action('plugins_loaded', function() {
    if (OSB_Database_Migration::migrationNeeded()) {
        OSB_Database_Migration::migrate();
    }
});