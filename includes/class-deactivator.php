<?php
/**
 * Plugin Deactivator Class
 *
 * Handles plugin deactivation and cleanup
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class OSB_Deactivator {

    /**
     * Plugin deactivation hook
     */
    public static function deactivate() {
        // Clear scheduled events
        self::clearScheduledEvents();

        // Flush rewrite rules
        flush_rewrite_rules();

        // Log deactivation
        error_log('Spelling Bee Pro Plugin deactivated');

        // Note: We don't remove user roles or database tables on deactivation
        // This preserves data in case of accidental deactivation
    }

    /**
     * Clear all scheduled WordPress events
     */
    private static function clearScheduledEvents() {
        // Clear any scheduled cron events
        $scheduled_events = array(
            'osb_daily_cleanup',
            'osb_email_reminders',
            'osb_backup_data',
        );

        foreach ($scheduled_events as $event) {
            $timestamp = wp_next_scheduled($event);
            if ($timestamp) {
                wp_unschedule_event($timestamp, $event);
            }
        }
    }

    /**
     * Complete plugin removal (called on uninstall)
     */
    public static function uninstall() {
        // Only run if user has proper permissions
        if (!current_user_can('activate_plugins')) {
            return;
        }

        // Check if this is the correct plugin being uninstalled
        if (__FILE__ != WP_UNINSTALL_PLUGIN) {
            return;
        }

        // Remove user roles
        self::removeUserRoles();

        // Remove database tables (optional - ask user)
        if (get_option('osb_remove_data_on_uninstall', false)) {
            self::removeDatabaseTables();
        }

        // Remove plugin options
        self::removePluginOptions();

        // Remove uploaded files (optional - ask user)
        if (get_option('osb_remove_files_on_uninstall', false)) {
            self::removeUploadedFiles();
        }

        // Clear any remaining scheduled events
        self::clearScheduledEvents();

        // Log uninstallation
        error_log('Spelling Bee Pro Plugin completely uninstalled');
    }

    /**
     * Remove custom user roles
     */
    private static function removeUserRoles() {
        // Remove custom roles
        remove_role('school_representative');
        remove_role('student');
        remove_role('parent_guardian');

        // Remove custom capabilities from administrator role
        $admin_role = get_role('administrator');
        if ($admin_role) {
            $admin_capabilities = array(
                'osb_manage_events',
                'osb_manage_schools',
                'osb_manage_students',
                'osb_manage_registrations',
                'osb_manage_documents',
                'osb_manage_donations',
                'osb_manage_sponsors',
                'osb_view_reports',
                'osb_manage_settings',
                'osb_resolve_conflicts',
                'osb_send_communications'
            );

            foreach ($admin_capabilities as $capability) {
                $admin_role->remove_cap($capability);
            }
        }
    }

    /**
     * Remove database tables
     */
    private static function removeDatabaseTables() {
        global $wpdb;

        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        $tables = array(
            'communications',
            'system_settings',
            'user_conflicts',
            'sponsors',
            'donations',
            'documents',
            'registrations',
            'students',
            'schools',
            'event_videos',
            'events'
        );

        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS {$table_prefix}{$table}");
        }
    }

    /**
     * Remove plugin options
     */
    private static function removePluginOptions() {
        $options_to_remove = array(
            'osb_plugin_version',
            'osb_activation_time',
            'osb_registration_enabled',
            'osb_max_students_per_school',
            'osb_min_students_per_school',
            'osb_require_parent_consent',
            'osb_auto_approve_schools',
            'osb_email_notifications_enabled',
            'osb_donation_enabled',
            'osb_prize_distribution',
            'osb_contact_email',
            'osb_organization_name',
            'osb_remove_data_on_uninstall',
            'osb_remove_files_on_uninstall'
        );

        foreach ($options_to_remove as $option) {
            delete_option($option);
        }

        // Remove user meta related to plugin
        delete_metadata('user', 0, 'osb_school_id', '', true);
        delete_metadata('user', 0, 'osb_student_id', '', true);
        delete_metadata('user', 0, 'osb_children_students', '', true);
    }

    /**
     * Remove uploaded files
     */
    private static function removeUploadedFiles() {
        $upload_base = wp_upload_dir()['basedir'] . '/spelling-bee-pro/';

        if (is_dir($upload_base)) {
            self::removeDirectory($upload_base);
        }
    }

    /**
     * Recursively remove directory and its contents
     */
    private static function removeDirectory($dir) {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), array('.', '..'));

        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            if (is_dir($path)) {
                self::removeDirectory($path);
            } else {
                unlink($path);
            }
        }

        rmdir($dir);
    }

    /**
     * Remove plugin pages (only if explicitly requested)
     * Note: Called only during uninstall, not deactivation
     */
    public static function removePluginPages() {
        $page_options = array(
            'osb_dashboard_page_id',
            'osb_registration_status_page_id'
        );

        foreach ($page_options as $option_name) {
            $page_id = get_option($option_name);
            if ($page_id) {
                $page = get_post($page_id);
                if ($page && $page->post_type === 'page') {
                    wp_delete_post($page_id, true); // Force delete (skip trash)
                    error_log("Removed page: {$page->post_title} (ID: {$page_id})");
                }
                delete_option($option_name);
            }
        }
    }
}