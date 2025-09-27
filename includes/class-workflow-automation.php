<?php
/**
 * Workflow Automation Class
 *
 * Handles automated workflows, notifications, and bulk operations
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class OSB_Workflow_Automation {

    /**
     * Instance of this class
     */
    private static $instance = null;

    /**
     * Database instance
     */
    private $db;

    /**
     * Email handler instance
     */
    private $email_handler;

    /**
     * Automation triggers
     */
    const TRIGGER_NEW_REGISTRATION = 'new_registration';
    const TRIGGER_DOCUMENT_UPLOADED = 'document_uploaded';
    const TRIGGER_STATUS_CHANGED = 'status_changed';
    const TRIGGER_DEADLINE_APPROACHING = 'deadline_approaching';
    const TRIGGER_BULK_OPERATION = 'bulk_operation';

    /**
     * Get instance
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Constructor
     */
    private function __construct() {
        $this->db = OSB_Database::getInstance();
        $this->email_handler = OSB_Email_Handler::getInstance();

        $this->setupHooks();
        $this->setupCronJobs();
    }

    /**
     * Setup WordPress hooks
     */
    private function setupHooks() {
        // Registration workflow triggers
        add_action('osb_registration_created', array($this, 'handleNewRegistration'), 10, 2);
        add_action('osb_registration_status_changed', array($this, 'handleStatusChange'), 10, 3);
        add_action('osb_document_uploaded', array($this, 'handleDocumentUpload'), 10, 2);

        // Admin notification hooks
        add_action('wp_ajax_osb_get_notifications', array($this, 'getAdminNotifications'));
        add_action('wp_ajax_osb_mark_notification_read', array($this, 'markNotificationRead'));
        add_action('wp_ajax_osb_bulk_update_registrations', array($this, 'handleBulkOperation'));

        // Cron job actions
        add_action('osb_daily_automation_check', array($this, 'runDailyAutomation'));
        add_action('osb_deadline_reminder_check', array($this, 'checkDeadlineReminders'));
    }

    /**
     * Setup cron jobs
     */
    private function setupCronJobs() {
        if (!wp_next_scheduled('osb_daily_automation_check')) {
            wp_schedule_event(time(), 'daily', 'osb_daily_automation_check');
        }

        if (!wp_next_scheduled('osb_deadline_reminder_check')) {
            wp_schedule_event(time(), 'hourly', 'osb_deadline_reminder_check');
        }
    }

    /**
     * Handle new registration
     */
    public function handleNewRegistration($registration_id, $school_id) {
        // Get registration and school details
        $registration = $this->db->getRegistrationById($registration_id);
        $school = $this->db->getSchool($school_id);

        if (!$registration || !$school) {
            return;
        }

        // Determine automation level based on school classification
        $classifier = OSB_School_Classifier::getInstance();
        $classification = $classifier->classifySchool($school_id);

        // Create admin notification
        $this->createAdminNotification(
            'new_registration',
            sprintf(
                __('New registration from %s (%s)', 'spelling-bee-pro'),
                $school->school_name,
                $classification
            ),
            array(
                'registration_id' => $registration_id,
                'school_id' => $school_id,
                'classification' => $classification,
                'priority' => $this->getNotificationPriority($classification)
            )
        );

        // Auto-advance high-reputation schools
        if ($classification === OSB_School_Classifier::CLASSIFICATION_PREMIUM) {
            $this->autoAdvanceRegistration($registration_id, 'documents_submitted');
        }

        // Schedule follow-up checks
        wp_schedule_single_event(
            time() + (24 * HOUR_IN_SECONDS),
            'osb_check_registration_progress',
            array($registration_id)
        );
    }

    /**
     * Handle status change
     */
    public function handleStatusChange($registration_id, $old_status, $new_status) {
        $registration = $this->db->getRegistrationById($registration_id);
        if (!$registration) {
            return;
        }

        // Create admin notification for significant status changes
        if (in_array($new_status, array('approved', 'rejected', 'confirmed'))) {
            $this->createAdminNotification(
                'status_changed',
                sprintf(
                    __('Registration #%d status changed to: %s', 'spelling-bee-pro'),
                    $registration_id,
                    ucfirst($new_status)
                ),
                array(
                    'registration_id' => $registration_id,
                    'old_status' => $old_status,
                    'new_status' => $new_status,
                    'priority' => 'medium'
                )
            );
        }

        // Trigger automated workflows based on status
        switch ($new_status) {
            case 'documents_submitted':
                $this->scheduleDocumentReview($registration_id);
                break;
            case 'approved':
                $this->scheduleConfirmationFollowUp($registration_id);
                break;
            case 'confirmed':
                $this->finalizeRegistration($registration_id);
                break;
        }
    }

    /**
     * Handle document upload
     */
    public function handleDocumentUpload($registration_id, $document_path) {
        $registration = $this->db->getRegistrationById($registration_id);
        if (!$registration) {
            return;
        }

        // Auto-update status if all required documents are uploaded
        if ($this->allRequiredDocumentsUploaded($registration_id)) {
            $this->autoAdvanceRegistration($registration_id, 'documents_submitted');
        }

        // Create admin notification
        $this->createAdminNotification(
            'document_uploaded',
            sprintf(
                __('New document uploaded for registration #%d', 'spelling-bee-pro'),
                $registration_id
            ),
            array(
                'registration_id' => $registration_id,
                'document_path' => $document_path,
                'priority' => 'low'
            )
        );
    }

    /**
     * Create admin notification
     */
    public function createAdminNotification($type, $message, $data = array()) {
        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        $notification_data = array(
            'type' => $type,
            'title' => $message,
            'message' => $this->generateNotificationMessage($type, $data),
            'data' => wp_json_encode($data),
            'priority' => $data['priority'] ?? 'medium',
            'is_read' => 0,
            'created_at' => current_time('mysql')
        );

        $wpdb->insert("{$table_prefix}admin_notifications", $notification_data);
    }

    /**
     * Generate detailed notification message
     */
    private function generateNotificationMessage($type, $data) {
        switch ($type) {
            case 'new_registration':
                return sprintf(
                    __('A new registration has been submitted. School classification: %s. Please review and take appropriate action.', 'spelling-bee-pro'),
                    $data['classification']
                );

            case 'document_uploaded':
                return __('Review the uploaded documents and update registration status accordingly.', 'spelling-bee-pro');

            case 'status_changed':
                return sprintf(
                    __('Registration status changed from %s to %s. Review if further action is needed.', 'spelling-bee-pro'),
                    $data['old_status'],
                    $data['new_status']
                );

            default:
                return __('Please review this item in the admin dashboard.', 'spelling-bee-pro');
        }
    }

    /**
     * Get notification priority based on school classification
     */
    private function getNotificationPriority($classification) {
        switch ($classification) {
            case OSB_School_Classifier::CLASSIFICATION_PREMIUM:
                return 'high';
            case OSB_School_Classifier::CLASSIFICATION_VERIFIED:
                return 'medium';
            default:
                return 'low';
        }
    }

    /**
     * Auto-advance registration status
     */
    private function autoAdvanceRegistration($registration_id, $new_status) {
        $this->db->updateRegistration($registration_id, array(
            'status' => $new_status,
            'updated_at' => current_time('mysql')
        ));

        // Trigger status change hook
        do_action('osb_registration_status_changed', $registration_id, null, $new_status);
    }

    /**
     * Check if all required documents are uploaded
     */
    private function allRequiredDocumentsUploaded($registration_id) {
        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        $uploaded_count = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$table_prefix}documents WHERE registration_id = %d",
                $registration_id
            )
        );

        // Minimum 3 required documents (school cert, student IDs, consent forms)
        return $uploaded_count >= 3;
    }

    /**
     * Schedule document review
     */
    private function scheduleDocumentReview($registration_id) {
        wp_schedule_single_event(
            time() + (2 * HOUR_IN_SECONDS),
            'osb_remind_document_review',
            array($registration_id)
        );
    }

    /**
     * Schedule confirmation follow-up
     */
    private function scheduleConfirmationFollowUp($registration_id) {
        wp_schedule_single_event(
            time() + (48 * HOUR_IN_SECONDS),
            'osb_confirmation_followup',
            array($registration_id)
        );
    }

    /**
     * Finalize registration
     */
    private function finalizeRegistration($registration_id) {
        // Track participation for school classification
        $registration = $this->db->getRegistrationById($registration_id);
        if ($registration) {
            $classifier = OSB_School_Classifier::getInstance();
            $event = $this->db->getEvent($registration->event_id);

            if ($event) {
                $event_year = date('Y', strtotime($event->event_date));
                $classifier->updateParticipationHistory(
                    $registration->school_id,
                    $event_year,
                    array('status' => 'completed', 'final_confirmed' => true)
                );
            }
        }
    }

    /**
     * Handle bulk operations
     */
    public function handleBulkOperation() {
        if (!current_user_can('osb_manage_registrations')) {
            wp_send_json_error(__('Permission denied.', 'spelling-bee-pro'));
        }

        $action = sanitize_text_field($_POST['bulk_action']);
        $registration_ids = array_map('intval', $_POST['registration_ids']);
        $results = array('success' => 0, 'failed' => 0);

        foreach ($registration_ids as $registration_id) {
            $success = $this->executeBulkAction($action, $registration_id);
            if ($success) {
                $results['success']++;
            } else {
                $results['failed']++;
            }
        }

        wp_send_json_success(array(
            'message' => sprintf(
                __('Bulk operation completed. %d successful, %d failed.', 'spelling-bee-pro'),
                $results['success'],
                $results['failed']
            ),
            'results' => $results
        ));
    }

    /**
     * Execute bulk action on single registration
     */
    private function executeBulkAction($action, $registration_id) {
        switch ($action) {
            case 'approve_all':
                return $this->db->updateRegistration($registration_id, array('status' => 'approved'));

            case 'mark_documents_submitted':
                return $this->db->updateRegistration($registration_id, array('status' => 'documents_submitted'));

            case 'send_reminder':
                return $this->email_handler->sendDeadlineReminder($registration_id, 'custom');

            default:
                return false;
        }
    }

    /**
     * Get admin notifications
     */
    public function getAdminNotifications() {
        if (!current_user_can('osb_manage_registrations')) {
            wp_send_json_error(__('Permission denied.', 'spelling-bee-pro'));
        }

        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        $notifications = $wpdb->get_results(
            "SELECT * FROM {$table_prefix}admin_notifications
             WHERE is_read = 0
             ORDER BY priority DESC, created_at DESC
             LIMIT 50"
        );

        wp_send_json_success($notifications);
    }

    /**
     * Mark notification as read
     */
    public function markNotificationRead() {
        if (!current_user_can('osb_manage_registrations')) {
            wp_send_json_error(__('Permission denied.', 'spelling-bee-pro'));
        }

        $notification_id = intval($_POST['notification_id']);

        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        $result = $wpdb->update(
            "{$table_prefix}admin_notifications",
            array('is_read' => 1, 'read_at' => current_time('mysql')),
            array('id' => $notification_id),
            array('%d', '%s'),
            array('%d')
        );

        if ($result !== false) {
            wp_send_json_success(__('Notification marked as read.', 'spelling-bee-pro'));
        } else {
            wp_send_json_error(__('Failed to update notification.', 'spelling-bee-pro'));
        }
    }

    /**
     * Run daily automation tasks
     */
    public function runDailyAutomation() {
        // Clean up old notifications (30 days)
        $this->cleanupOldNotifications();

        // Check for stalled registrations
        $this->checkStalledRegistrations();

        // Generate daily summary for admins
        $this->generateDailySummary();
    }

    /**
     * Check deadline reminders
     */
    public function checkDeadlineReminders() {
        // This will be called hourly to check for approaching deadlines
        $upcoming_events = $this->getUpcomingEvents();

        foreach ($upcoming_events as $event) {
            $this->checkEventDeadlines($event);
        }
    }

    /**
     * Cleanup old notifications
     */
    private function cleanupOldNotifications() {
        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        $wpdb->query(
            "DELETE FROM {$table_prefix}admin_notifications
             WHERE created_at < DATE_SUB(NOW(), INTERVAL 30 DAY)
             AND is_read = 1"
        );
    }

    /**
     * Check for stalled registrations
     */
    private function checkStalledRegistrations() {
        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        $stalled = $wpdb->get_results(
            "SELECT * FROM {$table_prefix}registrations
             WHERE status = 'pending'
             AND created_at < DATE_SUB(NOW(), INTERVAL 7 DAY)"
        );

        foreach ($stalled as $registration) {
            $this->createAdminNotification(
                'stalled_registration',
                sprintf(__('Registration #%d has been pending for over 7 days', 'spelling-bee-pro'), $registration->id),
                array(
                    'registration_id' => $registration->id,
                    'priority' => 'high'
                )
            );
        }
    }

    /**
     * Generate daily summary
     */
    private function generateDailySummary() {
        // Create a summary notification with daily stats
        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        $stats = $wpdb->get_row(
            "SELECT
                COUNT(*) as total_registrations,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN created_at >= DATE_SUB(NOW(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) as new_today
             FROM {$table_prefix}registrations"
        );

        if ($stats && $stats->new_today > 0) {
            $this->createAdminNotification(
                'daily_summary',
                sprintf(__('Daily Summary: %d new registrations', 'spelling-bee-pro'), $stats->new_today),
                array(
                    'stats' => $stats,
                    'priority' => 'low'
                )
            );
        }
    }

    /**
     * Get upcoming events
     */
    private function getUpcomingEvents() {
        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        return $wpdb->get_results(
            "SELECT * FROM {$table_prefix}events
             WHERE event_date > NOW()
             AND event_date <= DATE_ADD(NOW(), INTERVAL 30 DAY)"
        );
    }

    /**
     * Check event deadlines
     */
    private function checkEventDeadlines($event) {
        $days_until_event = floor((strtotime($event->event_date) - time()) / (24 * 60 * 60));

        // Send reminders at 14, 7, and 3 days before
        if (in_array($days_until_event, array(14, 7, 3))) {
            $this->createAdminNotification(
                'deadline_reminder',
                sprintf(
                    __('Event "%s" is in %d days - check registration status', 'spelling-bee-pro'),
                    $event->title,
                    $days_until_event
                ),
                array(
                    'event_id' => $event->id,
                    'days_remaining' => $days_until_event,
                    'priority' => $days_until_event <= 7 ? 'high' : 'medium'
                )
            );
        }
    }
}