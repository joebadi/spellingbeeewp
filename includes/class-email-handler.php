<?php
/**
 * Email Handler Class
 *
 * Handles all email communications and templates
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class OSB_Email_Handler {

    /**
     * Instance of this class
     */
    private static $instance = null;

    /**
     * Email templates directory
     */
    private $template_dir;

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
        $this->template_dir = OSB_PLUGIN_PATH . 'templates/emails/';
        $this->setupHooks();
    }

    /**
     * Setup WordPress hooks
     */
    private function setupHooks() {
        add_action('osb_send_welcome_email', array($this, 'sendWelcomeEmail'), 10, 2);
        add_action('osb_send_registration_confirmation', array($this, 'sendRegistrationConfirmation'), 10, 2);
        add_action('osb_send_approval_notification', array($this, 'sendApprovalNotification'), 10, 2);
        add_action('osb_send_rejection_notification', array($this, 'sendRejectionNotification'), 10, 2);
        add_action('osb_send_event_reminder', array($this, 'sendEventReminder'), 10, 2);
        add_action('osb_send_conflict_notification', array($this, 'sendConflictNotification'), 10, 2);

        // New automation hooks
        add_action('osb_send_document_checklist', array($this, 'sendDocumentChecklist'), 10, 2);
        add_action('osb_send_deadline_reminder', array($this, 'sendDeadlineReminder'), 10, 3);
        add_action('osb_send_status_update', array($this, 'sendStatusUpdate'), 10, 5);

        // Cron hook handlers
        add_action('osb_send_deadline_reminder', array($this, 'handleScheduledReminderCron'), 10, 2);
    }

    /**
     * Send welcome email to new users
     */
    public function sendWelcomeEmail($user_id, $role = 'school_representative') {
        $user = get_user_by('ID', $user_id);
        if (!$user) {
            return false;
        }

        $template_data = array(
            'user_name' => $user->display_name,
            'user_email' => $user->user_email,
            'role' => $role,
            'login_url' => wp_login_url(),
            'site_url' => home_url(),
            'organization_name' => get_option('osb_organization_name', 'Omafuru Foundation'),
        );

        $subject = sprintf(
            __('Welcome to %s Spelling Bee Competition', 'omafuru-spelling-bee'),
            $template_data['organization_name']
        );

        $message = $this->getEmailTemplate('welcome', $template_data);

        return $this->sendEmail($user->user_email, $subject, $message);
    }

    /**
     * Send registration confirmation email
     */
    public function sendRegistrationConfirmation($registration_id, $additional_data = array()) {
        $db = OSB_Database::getInstance();
        $registration = $db->getRegistrationByToken($registration_id);

        if (!$registration) {
            return false;
        }

        $school = $db->getSchool($registration->school_id);
        $event = $db->getEvent($registration->event_id);

        if (!$school || !$event) {
            return false;
        }

        $template_data = array(
            'school_name' => $school->school_name,
            'contact_person' => $school->contact_person,
            'event_title' => $event->title,
            'event_date' => date('F j, Y', strtotime($event->event_date)),
            'registration_token' => $registration->registration_token,
            'registration_url' => home_url('/registration-status/?token=' . $registration->registration_token),
            'organization_name' => get_option('osb_organization_name', 'Omafuru Foundation'),
            'contact_email' => get_option('osb_contact_email', get_option('admin_email')),
        );

        $template_data = array_merge($template_data, $additional_data);

        $subject = sprintf(
            __('Registration Confirmation - %s', 'omafuru-spelling-bee'),
            $event->title
        );

        $message = $this->getEmailTemplate('registration-confirmation', $template_data);

        return $this->sendEmail($school->contact_email, $subject, $message);
    }

    /**
     * Send approval notification
     */
    public function sendApprovalNotification($registration_id, $additional_message = '') {
        $db = OSB_Database::getInstance();
        $registration = $db->getRegistrationByToken($registration_id);

        if (!$registration) {
            return false;
        }

        $school = $db->getSchool($registration->school_id);
        $event = $db->getEvent($registration->event_id);

        if (!$school || !$event) {
            return false;
        }

        $template_data = array(
            'school_name' => $school->school_name,
            'contact_person' => $school->contact_person,
            'event_title' => $event->title,
            'event_date' => date('F j, Y', strtotime($event->event_date)),
            'additional_message' => $additional_message,
            'organization_name' => get_option('osb_organization_name', 'Omafuru Foundation'),
            'contact_email' => get_option('osb_contact_email', get_option('admin_email')),
        );

        $subject = sprintf(
            __('Registration Approved - %s', 'omafuru-spelling-bee'),
            $event->title
        );

        $message = $this->getEmailTemplate('registration-approved', $template_data);

        return $this->sendEmail($school->contact_email, $subject, $message);
    }

    /**
     * Send rejection notification
     */
    public function sendRejectionNotification($registration_id, $reason = '') {
        $db = OSB_Database::getInstance();
        $registration = $db->getRegistrationByToken($registration_id);

        if (!$registration) {
            return false;
        }

        $school = $db->getSchool($registration->school_id);
        $event = $db->getEvent($registration->event_id);

        if (!$school || !$event) {
            return false;
        }

        $template_data = array(
            'school_name' => $school->school_name,
            'contact_person' => $school->contact_person,
            'event_title' => $event->title,
            'rejection_reason' => $reason,
            'organization_name' => get_option('osb_organization_name', 'Omafuru Foundation'),
            'contact_email' => get_option('osb_contact_email', get_option('admin_email')),
        );

        $subject = sprintf(
            __('Registration Update - %s', 'omafuru-spelling-bee'),
            $event->title
        );

        $message = $this->getEmailTemplate('registration-rejected', $template_data);

        return $this->sendEmail($school->contact_email, $subject, $message);
    }

    /**
     * Send event reminder
     */
    public function sendEventReminder($event_id, $days_before = 7) {
        $db = OSB_Database::getInstance();
        $event = $db->getEvent($event_id);

        if (!$event) {
            return false;
        }

        $registrations = $db->getEventRegistrations($event_id, 'approved');

        foreach ($registrations as $registration) {
            $template_data = array(
                'school_name' => $registration->school_name,
                'contact_person' => $registration->contact_person,
                'event_title' => $event->title,
                'event_date' => date('F j, Y', strtotime($event->event_date)),
                'event_time' => date('g:i A', strtotime($event->event_time)),
                'venue_name' => $event->venue_name,
                'venue_address' => $event->venue_address,
                'days_before' => $days_before,
                'organization_name' => get_option('osb_organization_name', 'Omafuru Foundation'),
                'contact_email' => get_option('osb_contact_email', get_option('admin_email')),
            );

            $subject = sprintf(
                __('Event Reminder - %s in %d days', 'omafuru-spelling-bee'),
                $event->title,
                $days_before
            );

            $message = $this->getEmailTemplate('event-reminder', $template_data);
            $this->sendEmail($registration->contact_email, $subject, $message);
        }

        return true;
    }

    /**
     * Send conflict notification to admin
     */
    public function sendConflictNotification($conflict_id, $admin_emails = array()) {
        $db = OSB_Database::getInstance();
        $conflict = $db->getPendingUserConflicts();

        $conflict_data = null;
        foreach ($conflict as $c) {
            if ($c->id == $conflict_id) {
                $conflict_data = $c;
                break;
            }
        }

        if (!$conflict_data) {
            return false;
        }

        if (empty($admin_emails)) {
            $admin_emails = array(get_option('admin_email'));
        }

        $template_data = array(
            'conflict_type' => $conflict_data->conflict_type,
            'confidence_score' => $conflict_data->confidence_score,
            'existing_user_name' => $conflict_data->existing_user_name,
            'existing_user_email' => $conflict_data->existing_user_email,
            'new_user_data' => json_decode($conflict_data->new_user_data, true),
            'admin_url' => admin_url('admin.php?page=spelling-bee-conflicts'),
            'organization_name' => get_option('osb_organization_name', 'Omafuru Foundation'),
        );

        $subject = __('User Conflict Detected - Action Required', 'omafuru-spelling-bee');
        $message = $this->getEmailTemplate('conflict-notification', $template_data);

        foreach ($admin_emails as $email) {
            $this->sendEmail($email, $subject, $message);
        }

        return true;
    }

    /**
     * Send document checklist email
     */
    public function sendDocumentChecklist($registration_id, $additional_data = array()) {
        $db = OSB_Database::getInstance();
        $registration = $db->getRegistrationByToken($registration_id);

        if (!$registration) {
            return false;
        }

        $school = $db->getSchool($registration->school_id);
        $event = $db->getEvent($registration->event_id);

        if (!$school || !$event) {
            return false;
        }

        $template_data = array(
            'school_name' => $school->school_name,
            'contact_person' => $school->contact_person,
            'event_title' => $event->title,
            'event_date' => date('F j, Y', strtotime($event->event_date)),
            'event_time' => !empty($event->event_time) ? date('g:i A', strtotime($event->event_time)) : null,
            'registration_token' => $registration->registration_token,
            'registration_url' => home_url('/registration-status/?token=' . $registration->registration_token),
            'submission_deadline' => !empty($event->registration_deadline) ? date('F j, Y', strtotime($event->registration_deadline)) : null,
            'organization_name' => get_option('osb_organization_name', 'Omafuru Foundation'),
            'contact_email' => get_option('osb_contact_email', get_option('admin_email')),
            'medical_required' => get_option('osb_medical_clearance_required', false)
        );

        $template_data = array_merge($template_data, $additional_data);

        $subject = sprintf(
            __('Document Checklist - %s', 'omafuru-spelling-bee'),
            $event->title
        );

        $message = $this->getEmailTemplate('document-checklist', $template_data);

        return $this->sendEmail($school->contact_email, $subject, $message);
    }

    /**
     * Send deadline reminder email
     */
    public function sendDeadlineReminder($registration_id, $reminder_type = '7_days', $additional_data = array()) {
        $db = OSB_Database::getInstance();
        $registration = $db->getRegistrationByToken($registration_id);

        if (!$registration) {
            return false;
        }

        $school = $db->getSchool($registration->school_id);
        $event = $db->getEvent($registration->event_id);

        if (!$school || !$event) {
            return false;
        }

        // Get document status if available
        $documents_info = $this->getDocumentStatus($registration->id);

        $template_data = array(
            'school_name' => $school->school_name,
            'contact_person' => $school->contact_person,
            'event_title' => $event->title,
            'event_date' => date('F j, Y', strtotime($event->event_date)),
            'event_time' => !empty($event->event_time) ? date('g:i A', strtotime($event->event_time)) : null,
            'registration_token' => $registration->registration_token,
            'registration_url' => home_url('/registration-status/?token=' . $registration->registration_token),
            'submission_deadline' => !empty($event->registration_deadline) ? date('F j, Y', strtotime($event->registration_deadline)) : null,
            'reminder_type' => $reminder_type,
            'organization_name' => get_option('osb_organization_name', 'Omafuru Foundation'),
            'contact_email' => get_option('osb_contact_email', get_option('admin_email')),
            'support_phone' => get_option('osb_support_phone', '')
        );

        // Add document status info if available
        if ($documents_info) {
            $template_data = array_merge($template_data, $documents_info);
        }

        $template_data = array_merge($template_data, $additional_data);

        $urgency_titles = [
            '7_days' => __('Reminder: Document Submission Deadline Approaching', 'omafuru-spelling-bee'),
            '3_days' => __('Urgent: 3 Days Left to Submit Documents', 'omafuru-spelling-bee'),
            'final' => __('FINAL NOTICE: Document Submission Required', 'omafuru-spelling-bee'),
            '1_day' => __('FINAL NOTICE: 1 Day Left!', 'omafuru-spelling-bee')
        ];

        $subject = sprintf(
            $urgency_titles[$reminder_type] ?? $urgency_titles['7_days'],
            $event->title
        );

        $message = $this->getEmailTemplate('deadline-reminder', $template_data);

        return $this->sendEmail($school->contact_email, $subject, $message);
    }

    /**
     * Send status update email
     */
    public function sendStatusUpdate($registration_id, $new_status, $previous_status = null, $admin_notes = '', $additional_data = array()) {
        $db = OSB_Database::getInstance();
        $registration = $db->getRegistrationByToken($registration_id);

        if (!$registration) {
            return false;
        }

        $school = $db->getSchool($registration->school_id);
        $event = $db->getEvent($registration->event_id);

        if (!$school || !$event) {
            return false;
        }

        $template_data = array(
            'school_name' => $school->school_name,
            'contact_person' => $school->contact_person,
            'event_title' => $event->title,
            'event_date' => date('F j, Y', strtotime($event->event_date)),
            'event_time' => !empty($event->event_time) ? date('g:i A', strtotime($event->event_time)) : null,
            'venue_name' => $event->venue_name,
            'venue_address' => $event->venue_address,
            'registration_token' => $registration->registration_token,
            'registration_url' => home_url('/registration-status/?token=' . $registration->registration_token),
            'new_status' => $new_status,
            'previous_status' => $previous_status,
            'admin_notes' => $admin_notes,
            'organization_name' => get_option('osb_organization_name', 'Omafuru Foundation'),
            'contact_email' => get_option('osb_contact_email', get_option('admin_email'))
        );

        $template_data = array_merge($template_data, $additional_data);

        $status_titles = [
            'pending' => __('Registration Received - Under Review', 'omafuru-spelling-bee'),
            'documents_submitted' => __('Documents Received', 'omafuru-spelling-bee'),
            'under_review' => __('Application Under Review', 'omafuru-spelling-bee'),
            'approved' => __('Registration Approved!', 'omafuru-spelling-bee'),
            'rejected' => __('Registration Status Update', 'omafuru-spelling-bee'),
            'confirmed' => __('Registration Confirmed!', 'omafuru-spelling-bee')
        ];

        $subject = sprintf(
            $status_titles[$new_status] ?? __('Registration Status Update', 'omafuru-spelling-bee'),
            $event->title
        );

        $message = $this->getEmailTemplate('status-update', $template_data);

        return $this->sendEmail($school->contact_email, $subject, $message);
    }

    /**
     * Schedule reminder emails for a registration
     */
    public function scheduleReminderEmails($registration_id) {
        $db = OSB_Database::getInstance();
        $registration = $db->getRegistrationByToken($registration_id);

        if (!$registration) {
            return false;
        }

        $event = $db->getEvent($registration->event_id);
        if (!$event || empty($event->registration_deadline)) {
            return false;
        }

        $deadline = strtotime($event->registration_deadline);
        $current_time = current_time('timestamp');

        // Only schedule if deadline is in the future
        if ($deadline <= $current_time) {
            return false;
        }

        $days_until_deadline = ceil(($deadline - $current_time) / DAY_IN_SECONDS);

        // Schedule 7-day reminder (if deadline is more than 7 days away)
        if ($days_until_deadline > 7) {
            $seven_day_time = $deadline - (7 * DAY_IN_SECONDS);
            wp_schedule_single_event($seven_day_time, 'osb_send_deadline_reminder', [$registration_id, '7_days']);
        }

        // Schedule 3-day reminder (if deadline is more than 3 days away)
        if ($days_until_deadline > 3) {
            $three_day_time = $deadline - (3 * DAY_IN_SECONDS);
            wp_schedule_single_event($three_day_time, 'osb_send_deadline_reminder', [$registration_id, '3_days']);
        }

        // Schedule final reminder (1 day before deadline)
        if ($days_until_deadline > 1) {
            $final_time = $deadline - DAY_IN_SECONDS;
            wp_schedule_single_event($final_time, 'osb_send_deadline_reminder', [$registration_id, 'final']);
        }

        return true;
    }

    /**
     * Get document status for registration
     */
    private function getDocumentStatus($registration_id) {
        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        // Get registration data
        $registration = $wpdb->get_row($wpdb->prepare(
            "SELECT documents_required, documents_uploaded FROM {$table_prefix}registrations WHERE id = %d",
            $registration_id
        ));

        if (!$registration) {
            return null;
        }

        $required_docs = json_decode($registration->documents_required, true) ?: [];
        $uploaded_docs = json_decode($registration->documents_uploaded, true) ?: [];

        $total_documents = count($required_docs);
        $documents_submitted = count($uploaded_docs);
        $missing_documents = array_diff($required_docs, array_keys($uploaded_docs));

        return [
            'total_documents' => $total_documents,
            'documents_submitted' => $documents_submitted,
            'missing_documents' => array_values($missing_documents)
        ];
    }

    /**
     * Handle scheduled reminder cron job
     */
    public function handleScheduledReminderCron($registration_id, $reminder_type) {
        // This method handles the cron execution of deadline reminders
        $this->sendDeadlineReminder($registration_id, $reminder_type);
    }

    /**
     * Get email template
     */
    private function getEmailTemplate($template_name, $data = array()) {
        $template_file = $this->template_dir . $template_name . '.php';

        if (file_exists($template_file)) {
            // Extract data variables
            extract($data);

            // Start output buffering
            ob_start();
            include $template_file;
            $content = ob_get_clean();

            return $content;
        }

        // Fallback to simple template
        return $this->getSimpleTemplate($template_name, $data);
    }

    /**
     * Get simple email template (fallback)
     */
    private function getSimpleTemplate($template_name, $data = array()) {
        $organization_name = isset($data['organization_name']) ? $data['organization_name'] : 'Omafuru Foundation';
        $contact_email = isset($data['contact_email']) ? $data['contact_email'] : get_option('admin_email');

        switch ($template_name) {
            case 'welcome':
                return sprintf(
                    __("Dear %s,\n\nWelcome to %s Spelling Bee Competition!\n\nYour account has been created with the role of %s. You can log in at: %s\n\nThank you for your participation!\n\nBest regards,\n%s Team\n\nContact: %s", 'omafuru-spelling-bee'),
                    $data['user_name'],
                    $organization_name,
                    $data['role'],
                    $data['login_url'],
                    $organization_name,
                    $contact_email
                );

            case 'registration-confirmation':
                return sprintf(
                    __("Dear %s,\n\nThank you for registering %s for %s.\n\nYour registration has been received and is currently under review. You will be notified once the review is complete.\n\nRegistration Details:\n- Event: %s\n- Date: %s\n- Registration Token: %s\n\nYou can check your registration status at: %s\n\nBest regards,\n%s Team\n\nContact: %s", 'omafuru-spelling-bee'),
                    $data['contact_person'],
                    $data['school_name'],
                    $data['event_title'],
                    $data['event_title'],
                    $data['event_date'],
                    $data['registration_token'],
                    $data['registration_url'],
                    $organization_name,
                    $contact_email
                );

            case 'registration-approved':
                return sprintf(
                    __("Dear %s,\n\nCongratulations! Your registration for %s has been approved.\n\nEvent Details:\n- Event: %s\n- Date: %s\n\n%s\n\nWe look forward to your participation!\n\nBest regards,\n%s Team\n\nContact: %s", 'omafuru-spelling-bee'),
                    $data['contact_person'],
                    $data['school_name'],
                    $data['event_title'],
                    $data['event_date'],
                    $data['additional_message'],
                    $organization_name,
                    $contact_email
                );

            default:
                return sprintf(
                    __("Hello,\n\nThis is a notification from %s Spelling Bee Competition.\n\nBest regards,\n%s Team\n\nContact: %s", 'omafuru-spelling-bee'),
                    $organization_name,
                    $organization_name,
                    $contact_email
                );
        }
    }

    /**
     * Send email using WordPress wp_mail
     */
    private function sendEmail($to, $subject, $message, $headers = array()) {
        if (empty($headers)) {
            $headers = array(
                'Content-Type: text/html; charset=UTF-8',
                'From: ' . get_option('osb_organization_name', 'Omafuru Foundation') . ' <' . get_option('osb_contact_email', get_option('admin_email')) . '>'
            );
        }

        return wp_mail($to, $subject, $message, $headers);
    }

    /**
     * Queue email for later sending
     */
    public function queueEmail($to, $subject, $message, $send_time = null) {
        if (!$send_time) {
            $send_time = current_time('mysql');
        }

        global $wpdb;
        $table_name = $wpdb->prefix . OSB_TABLE_PREFIX . 'communications';

        return $wpdb->insert(
            $table_name,
            array(
                'type' => 'email',
                'recipient' => $to,
                'subject' => $subject,
                'message' => $message,
                'scheduled_time' => $send_time,
                'status' => 'pending',
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );
    }

    /**
     * Process queued emails
     */
    public function processQueuedEmails($limit = 50) {
        global $wpdb;
        $table_name = $wpdb->prefix . OSB_TABLE_PREFIX . 'communications';

        $queued_emails = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$table_name}
                 WHERE type = 'email'
                 AND status = 'pending'
                 AND scheduled_time <= %s
                 ORDER BY scheduled_time ASC
                 LIMIT %d",
                current_time('mysql'),
                $limit
            )
        );

        foreach ($queued_emails as $email) {
            $sent = $this->sendEmail($email->recipient, $email->subject, $email->message);

            $wpdb->update(
                $table_name,
                array(
                    'status' => $sent ? 'sent' : 'failed',
                    'sent_at' => current_time('mysql')
                ),
                array('id' => $email->id),
                array('%s', '%s'),
                array('%d')
            );
        }

        return count($queued_emails);
    }

    /**
     * Get email statistics
     */
    public function getEmailStats() {
        global $wpdb;
        $table_name = $wpdb->prefix . OSB_TABLE_PREFIX . 'communications';

        return $wpdb->get_results(
            "SELECT status, COUNT(*) as count
             FROM {$table_name}
             WHERE communication_type = 'email'
             GROUP BY status",
            OBJECT_K
        );
    }
}