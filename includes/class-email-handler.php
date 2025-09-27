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
            'organization_name' => get_option('osb_organization_name', 'Spelling Bee Organization'),
        );

        $subject = sprintf(
            __('Welcome to %s Spelling Bee Competition', 'spelling-bee-pro'),
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
            'registration_url' => home_url('/spellingbee-dashboard/?token=' . $registration->registration_token),
            'organization_name' => get_option('osb_organization_name', 'Spelling Bee Organization'),
            'contact_email' => get_option('osb_contact_email', get_option('admin_email')),
        );

        $template_data = array_merge($template_data, $additional_data);

        $subject = sprintf(
            __('Registration Confirmation - %s', 'spelling-bee-pro'),
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
            'organization_name' => get_option('osb_organization_name', 'Spelling Bee Organization'),
            'contact_email' => get_option('osb_contact_email', get_option('admin_email')),
        );

        $subject = sprintf(
            __('Registration Approved - %s', 'spelling-bee-pro'),
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
            'organization_name' => get_option('osb_organization_name', 'Spelling Bee Organization'),
            'contact_email' => get_option('osb_contact_email', get_option('admin_email')),
        );

        $subject = sprintf(
            __('Registration Update - %s', 'spelling-bee-pro'),
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
                'organization_name' => get_option('osb_organization_name', 'Spelling Bee Organization'),
                'contact_email' => get_option('osb_contact_email', get_option('admin_email')),
            );

            $subject = sprintf(
                __('Event Reminder - %s in %d days', 'spelling-bee-pro'),
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
            'organization_name' => get_option('osb_organization_name', 'Spelling Bee Organization'),
        );

        $subject = __('User Conflict Detected - Action Required', 'spelling-bee-pro');
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
            'registration_url' => home_url('/spellingbee-dashboard/?token=' . $registration->registration_token),
            'submission_deadline' => !empty($event->registration_deadline) ? date('F j, Y', strtotime($event->registration_deadline)) : null,
            'organization_name' => get_option('osb_organization_name', 'Spelling Bee Organization'),
            'contact_email' => get_option('osb_contact_email', get_option('admin_email')),
            'medical_required' => get_option('osb_medical_clearance_required', false)
        );

        $template_data = array_merge($template_data, $additional_data);

        $subject = sprintf(
            __('Document Checklist - %s', 'spelling-bee-pro'),
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
            'registration_url' => home_url('/spellingbee-dashboard/?token=' . $registration->registration_token),
            'submission_deadline' => !empty($event->registration_deadline) ? date('F j, Y', strtotime($event->registration_deadline)) : null,
            'reminder_type' => $reminder_type,
            'organization_name' => get_option('osb_organization_name', 'Spelling Bee Organization'),
            'contact_email' => get_option('osb_contact_email', get_option('admin_email')),
            'support_phone' => get_option('osb_support_phone', '')
        );

        // Add document status info if available
        if ($documents_info) {
            $template_data = array_merge($template_data, $documents_info);
        }

        $template_data = array_merge($template_data, $additional_data);

        $urgency_titles = [
            '7_days' => __('Reminder: Document Submission Deadline Approaching', 'spelling-bee-pro'),
            '3_days' => __('Urgent: 3 Days Left to Submit Documents', 'spelling-bee-pro'),
            'final' => __('FINAL NOTICE: Document Submission Required', 'spelling-bee-pro'),
            '1_day' => __('FINAL NOTICE: 1 Day Left!', 'spelling-bee-pro')
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
            'registration_url' => home_url('/spellingbee-dashboard/?token=' . $registration->registration_token),
            'new_status' => $new_status,
            'previous_status' => $previous_status,
            'admin_notes' => $admin_notes,
            'organization_name' => get_option('osb_organization_name', 'Spelling Bee Organization'),
            'contact_email' => get_option('osb_contact_email', get_option('admin_email'))
        );

        $template_data = array_merge($template_data, $additional_data);

        $status_titles = [
            'pending' => __('Registration Received - Under Review', 'spelling-bee-pro'),
            'documents_submitted' => __('Documents Received', 'spelling-bee-pro'),
            'under_review' => __('Application Under Review', 'spelling-bee-pro'),
            'approved' => __('Registration Approved!', 'spelling-bee-pro'),
            'rejected' => __('Registration Status Update', 'spelling-bee-pro'),
            'confirmed' => __('Registration Confirmed!', 'spelling-bee-pro')
        ];

        $subject = sprintf(
            $status_titles[$new_status] ?? __('Registration Status Update', 'spelling-bee-pro'),
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
        $organization_name = isset($data['organization_name']) ? $data['organization_name'] : 'Spelling Bee Organization';
        $contact_email = isset($data['contact_email']) ? $data['contact_email'] : get_option('admin_email');

        switch ($template_name) {
            case 'welcome':
                return sprintf(
                    __("Dear %s,\n\nWelcome to %s Spelling Bee Competition!\n\nYour account has been created with the role of %s. You can log in at: %s\n\nThank you for your participation!\n\nBest regards,\n%s Team\n\nContact: %s", 'spelling-bee-pro'),
                    $data['user_name'],
                    $organization_name,
                    $data['role'],
                    $data['login_url'],
                    $organization_name,
                    $contact_email
                );

            case 'registration-confirmation':
                return sprintf(
                    __("Dear %s,\n\nThank you for registering %s for %s.\n\nYour registration has been received and is currently under review. You will be notified once the review is complete.\n\nRegistration Details:\n- Event: %s\n- Date: %s\n- Registration Token: %s\n\nYou can check your registration status at: %s\n\nBest regards,\n%s Team\n\nContact: %s", 'spelling-bee-pro'),
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
                    __("Dear %s,\n\nCongratulations! Your registration for %s has been approved.\n\nEvent Details:\n- Event: %s\n- Date: %s\n\n%s\n\nWe look forward to your participation!\n\nBest regards,\n%s Team\n\nContact: %s", 'spelling-bee-pro'),
                    $data['contact_person'],
                    $data['school_name'],
                    $data['event_title'],
                    $data['event_date'],
                    $data['additional_message'],
                    $organization_name,
                    $contact_email
                );

            case 'immediate_dashboard_access':
                return $this->getFormattedEmailTemplate('immediate_dashboard_access', $data);

            case 'documents_submitted_for_review':
                return sprintf(
                    __("Dear %s,\n\n📋 Documents Submitted Successfully!\n\nThank you! We have received your complete submission for %s.\n\nSchool: %s\nContact Person: %s\n\n✅ WHAT WE RECEIVED:\n• All required documents\n• Student registration details\n• Application materials\n\n⏳ WHAT HAPPENS NEXT:\n• Our team will review your complete submission\n• We'll verify all documents and student information\n• You'll receive a final approval notification once review is complete\n• Please allow 2-3 business days for the review process\n\n📌 Your dashboard remains accessible if you need to make any updates: %s\n\nThank you for your patience during the review process.\n\nIf you have any questions, please contact us at: %s\n\nBest regards,\n%s Team", 'spelling-bee-pro'),
                    $data['contact_person'],
                    $organization_name,
                    $data['school_name'],
                    $data['contact_person'],
                    isset($data['dashboard_url']) ? $data['dashboard_url'] : home_url('/spellingbee-dashboard/'),
                    $contact_email,
                    $organization_name
                );

            case 'school_approval':
                $dashboard_info = '';
                if (!empty($data['dashboard_url']) && !empty($data['registration_token'])) {
                    $dashboard_info = sprintf(
                        __("\n\n🎯 ACCESS YOUR DASHBOARD:\nDashboard URL: %s\nRegistration Token: %s\n\nSave this token! You'll need it to access your dashboard and manage student registrations.", 'spelling-bee-pro'),
                        $data['dashboard_url'],
                        $data['registration_token']
                    );
                }

                return sprintf(
                    __("Dear %s,\n\n🎉 FINAL APPROVAL GRANTED!\n\nCongratulations! Your complete application for %s has been reviewed and FULLY APPROVED!\n\nSchool: %s\nContact Person: %s\n\n✅ WHAT THIS MEANS:\n• All your documents have been verified\n• Your student registrations are confirmed\n• You are officially registered for the competition\n• You can now participate in all events%s\n\nThank you for completing the registration process successfully!\n\nIf you have any questions, please contact us at: %s\n\nBest regards,\n%s Team", 'spelling-bee-pro'),
                    $data['contact_person'],
                    $organization_name,
                    $data['school_name'],
                    $data['contact_person'],
                    $dashboard_info,
                    $contact_email,
                    $organization_name
                );

            case 'school_dashboard_access':
                return $this->getEmailTemplate('school-dashboard-access', $data);

            default:
                return sprintf(
                    __("Hello,\n\nThis is a notification from %s Spelling Bee Competition.\n\nBest regards,\n%s Team\n\nContact: %s", 'spelling-bee-pro'),
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
                'From: ' . get_option('osb_organization_name', 'Spelling Bee Organization') . ' <' . get_option('osb_contact_email', get_option('admin_email')) . '>'
            );
        }

        // Hook to fix SSL verification issues
        add_action('phpmailer_init', array($this, 'configurePHPMailerSSL'));

        $result = wp_mail($to, $subject, $message, $headers);

        // Remove the hook after sending
        remove_action('phpmailer_init', array($this, 'configurePHPMailerSSL'));

        return $result;
    }

    /**
     * Configure PHPMailer to handle SSL issues
     */
    public function configurePHPMailerSSL($phpmailer) {
        // Disable SSL certificate verification
        $phpmailer->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Set additional SMTP settings
        $phpmailer->isSMTP();
        $phpmailer->SMTPDebug = 0; // Set to 2 for debugging
        $phpmailer->SMTPAuth = true;
        $phpmailer->SMTPSecure = 'tls';
        $phpmailer->Port = 587;

        // Log SMTP configuration for debugging
        error_log('OSB Email: PHPMailer configured with SSL bypass');
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
     * Send immediate dashboard access email after registration
     */
    public function sendImmediateDashboardAccess($registration_id) {
        $db = OSB_Database::getInstance();
        $registration = $db->getRegistrationById($registration_id);

        if (!$registration) {
            return false;
        }

        $school = $db->getSchool($registration->school_id);
        if (!$school) {
            return false;
        }

        $dashboard_url = home_url('/spellingbee-dashboard/?token=' . $registration->registration_token);

        $data = array(
            'school_name' => $school->school_name,
            'contact_person' => $school->contact_person,
            'organization_name' => get_option('osb_organization_name', 'Spelling Bee Organization'),
            'contact_email' => get_option('osb_contact_email', get_option('admin_email')),
            'dashboard_url' => $dashboard_url,
            'registration_token' => $registration->registration_token
        );

        $subject = sprintf(__('Welcome! Access Your Dashboard - %s', 'spelling-bee-pro'), $school->school_name);

        $message = $this->getSimpleTemplate('immediate_dashboard_access', $data);

        return $this->sendEmail($school->contact_email, $subject, $message);
    }

    /**
     * Send dashboard access email to school with temporary token
     */
    public function sendSchoolDashboardAccess($school, $temp_token) {
        if (!$school) {
            return false;
        }

        $dashboard_url = home_url('/spellingbee-dashboard/?token=' . $temp_token);

        $data = array(
            'school_name' => $school->school_name,
            'contact_person' => $school->contact_person,
            'organization_name' => get_option('osb_organization_name', 'Spelling Bee Organization'),
            'contact_email' => get_option('osb_contact_email', get_option('admin_email')),
            'dashboard_url' => $dashboard_url,
            'temp_token' => $temp_token
        );

        $subject = sprintf(__('Welcome! Access Your Dashboard - %s', 'spelling-bee-pro'), $school->school_name);

        $message = $this->getSimpleTemplate('school_dashboard_access', $data);

        return $this->sendEmail($school->contact_email, $subject, $message);
    }

    /**
     * Send school approval notification
     */
    public function sendSchoolApprovalNotification($school_id) {
        $db = OSB_Database::getInstance();
        $school = $db->getSchool($school_id);

        if (!$school) {
            return false;
        }

        // Get the most recent registration for this school to get the token
        $registrations = $db->getRegistrationsBySchool($school_id);
        $registration_token = '';
        $dashboard_url = home_url('/spellingbee-dashboard/');

        if (!empty($registrations)) {
            $latest_registration = $registrations[0]; // Most recent registration
            $registration_token = $latest_registration->registration_token;
            $dashboard_url = home_url('/spellingbee-dashboard/?token=' . $registration_token);
        }

        $data = array(
            'school_name' => $school->school_name,
            'contact_person' => $school->contact_person,
            'organization_name' => get_option('osb_organization_name', 'Spelling Bee Organization'),
            'contact_email' => get_option('osb_contact_email', get_option('admin_email')),
            'dashboard_url' => $dashboard_url,
            'registration_token' => $registration_token
        );

        $subject = sprintf(__('School Registration Approved - %s', 'spelling-bee-pro'), $school->school_name);

        $message = $this->getSimpleTemplate('school_approval', $data);

        return $this->sendEmail($school->contact_email, $subject, $message);
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

    /**
     * Get professionally formatted HTML email template
     */
    private function getFormattedEmailTemplate($template_name, $data = array()) {
        $organization_name = isset($data['organization_name']) ? $data['organization_name'] : 'Spelling Bee Organization';
        $contact_email = isset($data['contact_email']) ? $data['contact_email'] : get_option('admin_email');

        switch ($template_name) {
            case 'immediate_dashboard_access':
                return $this->buildDashboardAccessEmail($data, $organization_name, $contact_email);
            default:
                return $this->getSimpleTemplate($template_name, $data);
        }
    }

    /**
     * Build professional dashboard access email
     */
    private function buildDashboardAccessEmail($data, $organization_name, $contact_email) {
        $html = '
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Welcome to ' . esc_html($organization_name) . '</title>
        </head>
        <body style="margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background-color: #f4f4f4;">
            <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">

                <!-- Header -->
                <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 40px 30px; text-align: center;">
                    <h1 style="margin: 0; font-size: 28px; font-weight: bold;">🎉 Welcome to ' . esc_html($organization_name) . '!</h1>
                    <p style="margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;">Registration Complete - Dashboard Access Granted</p>
                </div>

                <!-- Content -->
                <div style="padding: 40px 30px;">
                    <!-- Greeting -->
                    <div style="margin-bottom: 30px;">
                        <h2 style="color: #333; margin: 0 0 15px 0; font-size: 24px;">Dear ' . esc_html($data['contact_person']) . ',</h2>
                        <p style="color: #666; font-size: 16px; line-height: 1.6; margin: 0;">
                            Congratulations! Your school registration has been completed successfully, and you now have immediate access to your dashboard.
                        </p>
                    </div>

                    <!-- School Info Card -->
                    <div style="background-color: #f8f9fa; border-left: 4px solid #667eea; padding: 20px; margin: 30px 0; border-radius: 4px;">
                        <h3 style="margin: 0 0 15px 0; color: #333; font-size: 18px;">📚 School Information</h3>
                        <table style="width: 100%; border-collapse: collapse;">
                            <tr>
                                <td style="padding: 8px 0; color: #666; font-weight: bold; width: 40%;">School Name:</td>
                                <td style="padding: 8px 0; color: #333;">' . esc_html($data['school_name']) . '</td>
                            </tr>
                            <tr>
                                <td style="padding: 8px 0; color: #666; font-weight: bold;">Contact Person:</td>
                                <td style="padding: 8px 0; color: #333;">' . esc_html($data['contact_person']) . '</td>
                            </tr>
                        </table>
                    </div>

                    <!-- Dashboard Access Card -->
                    <div style="background-color: #e8f5e8; border-left: 4px solid #28a745; padding: 25px; margin: 30px 0; border-radius: 4px;">
                        <h3 style="margin: 0 0 20px 0; color: #28a745; font-size: 20px;">🎯 Access Your Dashboard</h3>

                        <div style="margin: 20px 0;">
                            <a href="' . esc_url($data['dashboard_url']) . '" style="display: inline-block; background-color: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px; transition: background-color 0.3s;">
                                🚀 ACCESS DASHBOARD NOW
                            </a>
                        </div>

                        <div style="margin-top: 20px; padding: 15px; background-color: #fff; border-radius: 4px; border: 1px solid #ddd;">
                            <p style="margin: 0 0 10px 0; color: #666; font-size: 14px; font-weight: bold;">Your Registration Token:</p>
                            <code style="background-color: #f1f1f1; padding: 8px 12px; border-radius: 4px; font-family: Courier, monospace; font-size: 14px; color: #333; display: block; word-break: break-all;">' . esc_html($data['registration_token']) . '</code>
                            <p style="margin: 10px 0 0 0; color: #888; font-size: 12px;"><strong>Important:</strong> Save this token! You\'ll need it to access your dashboard.</p>
                        </div>
                    </div>

                    <!-- Next Steps -->
                    <div style="margin: 30px 0;">
                        <h3 style="color: #333; margin: 0 0 20px 0; font-size: 20px;">📋 Next Steps</h3>
                        <div style="margin-left: 20px;">
                            <div style="margin: 15px 0; display: flex; align-items: center;">
                                <div style="width: 8px; height: 8px; background-color: #667eea; border-radius: 50%; margin-right: 15px; flex-shrink: 0;"></div>
                                <span style="color: #666; font-size: 16px; line-height: 1.5;">Upload required documents</span>
                            </div>
                            <div style="margin: 15px 0; display: flex; align-items: center;">
                                <div style="width: 8px; height: 8px; background-color: #667eea; border-radius: 50%; margin-right: 15px; flex-shrink: 0;"></div>
                                <span style="color: #666; font-size: 16px; line-height: 1.5;">Register your students</span>
                            </div>
                            <div style="margin: 15px 0; display: flex; align-items: center;">
                                <div style="width: 8px; height: 8px; background-color: #667eea; border-radius: 50%; margin-right: 15px; flex-shrink: 0;"></div>
                                <span style="color: #666; font-size: 16px; line-height: 1.5;">Complete all requirements</span>
                            </div>
                            <div style="margin: 15px 0; display: flex; align-items: center;">
                                <div style="width: 8px; height: 8px; background-color: #667eea; border-radius: 50%; margin-right: 15px; flex-shrink: 0;"></div>
                                <span style="color: #666; font-size: 16px; line-height: 1.5;">Submit for final approval</span>
                            </div>
                        </div>
                    </div>

                    <!-- Support Section -->
                    <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin: 30px 0; border-radius: 4px;">
                        <h3 style="margin: 0 0 15px 0; color: #856404; font-size: 18px;">💬 Need Help?</h3>
                        <p style="color: #856404; font-size: 14px; line-height: 1.6; margin: 0;">
                            If you have any questions or need assistance, please don\'t hesitate to contact us at
                            <a href="mailto:' . esc_attr($contact_email) . '" style="color: #856404; font-weight: bold;">' . esc_html($contact_email) . '</a>
                        </p>
                    </div>
                </div>

                <!-- Footer -->
                <div style="background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #dee2e6;">
                    <p style="margin: 0 0 10px 0; color: #666; font-size: 16px; font-weight: bold;">Best regards,</p>
                    <p style="margin: 0; color: #333; font-size: 18px; font-weight: bold;">' . esc_html($organization_name) . ' Team</p>
                    <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #dee2e6;">
                        <p style="margin: 0; color: #888; font-size: 12px;">
                            This email was sent to you because you registered a school for our spelling bee competition.
                            <br>Please do not reply to this automated email.
                        </p>
                    </div>
                </div>
            </div>
        </body>
        </html>';

        return $html;
    }
}