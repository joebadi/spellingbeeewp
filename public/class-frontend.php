<?php
/**
 * Frontend Class
 *
 * Handles public-facing functionality and AJAX requests
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class OSB_Frontend {

    /**
     * Instance of this class
     */
    private static $instance = null;

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
        $this->setupHooks();
    }

    /**
     * Setup WordPress hooks
     */
    private function setupHooks() {
        // AJAX handlers for public forms
        add_action('wp_ajax_osb_submit_registration', array($this, 'handleRegistrationSubmission'));
        add_action('wp_ajax_nopriv_osb_submit_registration', array($this, 'handleRegistrationSubmission'));

        add_action('wp_ajax_osb_submit_donation', array($this, 'handleDonationSubmission'));
        add_action('wp_ajax_nopriv_osb_submit_donation', array($this, 'handleDonationSubmission'));

        add_action('wp_ajax_osb_check_registration_status', array($this, 'handleRegistrationStatusCheck'));
        add_action('wp_ajax_nopriv_osb_check_registration_status', array($this, 'handleRegistrationStatusCheck'));

        add_action('wp_ajax_osb_get_event_info', array($this, 'handleEventInfoRequest'));
        add_action('wp_ajax_nopriv_osb_get_event_info', array($this, 'handleEventInfoRequest'));

        // PROGRESS TRACKING AJAX handlers
        add_action('wp_ajax_osb_auto_save_registration', array($this, 'handleAutoSaveRegistration'));
        add_action('wp_ajax_nopriv_osb_auto_save_registration', array($this, 'handleAutoSaveRegistration'));

        add_action('wp_ajax_osb_load_resume_data', array($this, 'handleLoadResumeData'));
        add_action('wp_ajax_nopriv_osb_load_resume_data', array($this, 'handleLoadResumeData'));

        add_action('wp_ajax_osb_update_step_progress', array($this, 'handleUpdateStepProgress'));
        add_action('wp_ajax_nopriv_osb_update_step_progress', array($this, 'handleUpdateStepProgress'));

        // MOBILE UPLOAD OPTIMIZATION AJAX handlers
        add_action('wp_ajax_osb_upload_file_chunk', array($this, 'handleUploadFileChunk'));
        add_action('wp_ajax_nopriv_osb_upload_file_chunk', array($this, 'handleUploadFileChunk'));

        // Custom post types and taxonomies if needed
        add_action('init', array($this, 'registerCustomPostTypes'));

        // Custom query vars for registration status page
        add_action('init', array($this, 'addQueryVars'));
        add_action('template_redirect', array($this, 'handleCustomPages'));
    }

    /**
     * Handle public AJAX requests (legacy method for backward compatibility)
     */
    public static function handlePublicAjax() {
        $instance = self::getInstance();
        $action = sanitize_text_field($_POST['sub_action'] ?? '');

        switch ($action) {
            case 'submit_registration':
                $instance->handleRegistrationSubmission();
                break;

            case 'submit_donation':
                $instance->handleDonationSubmission();
                break;

            case 'check_registration_status':
                $instance->handleRegistrationStatusCheck();
                break;

            default:
                wp_send_json_error(__('Invalid action.', 'omafuru-spelling-bee'));
        }
    }

    /**
     * Handle registration submission
     */
    public function handleRegistrationSubmission() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'osb_public_nonce')) {
            wp_send_json_error(__('Security check failed.', 'omafuru-spelling-bee'));
        }

        $step = intval($_POST['step'] ?? 1);

        switch ($step) {
            case 1:
                $this->handleSchoolRegistrationStep();
                break;

            case 2:
                $this->handleStudentRegistrationStep();
                break;

            case 3:
                $this->handleDocumentUploadStep();
                break;

            case 4:
                $this->handleReviewStep();
                break;

            case 5:
                $this->handleFinalSubmissionStep();
                break;

            default:
                wp_send_json_error(__('Invalid registration step.', 'omafuru-spelling-bee'));
        }
    }

    /**
     * Handle school registration step (Step 1)
     */
    private function handleSchoolRegistrationStep() {
        $school_data = array(
            'school_name' => sanitize_text_field($_POST['school_name']),
            'school_type' => sanitize_text_field($_POST['school_type']),
            'address' => sanitize_textarea_field($_POST['address']),
            'city' => sanitize_text_field($_POST['city']),
            'state' => sanitize_text_field($_POST['state']),
            'postal_code' => sanitize_text_field($_POST['postal_code']),
            'country' => sanitize_text_field($_POST['country']),
            'contact_person' => sanitize_text_field($_POST['contact_person']),
            'contact_email' => sanitize_email($_POST['contact_email']),
            'contact_phone' => sanitize_text_field($_POST['contact_phone'])
        );

        // Validate required fields
        $required_fields = array('school_name', 'contact_person', 'contact_email');
        foreach ($required_fields as $field) {
            if (empty($school_data[$field])) {
                wp_send_json_error(sprintf(__('%s is required.', 'omafuru-spelling-bee'), ucfirst(str_replace('_', ' ', $field))));
            }
        }

        $user_manager = OSB_User_Manager::getInstance();
        $db = OSB_Database::getInstance();

        // Check for existing school
        $existing_school = $db->getSchoolByEmail($school_data['contact_email']);

        if ($existing_school) {
            // Get school classification and profile
            $classifier = OSB_School_Classifier::getInstance();
            $school_profile = $classifier->getSchoolProfile($existing_school->id);
            $recognition_summary = $classifier->getSchoolRecognitionSummary($existing_school->id);

            wp_send_json_success(array(
                'message' => sprintf(
                    __('Welcome back! %s recognized as %s.', 'omafuru-spelling-bee'),
                    esc_html($existing_school->school_name),
                    esc_html($recognition_summary['classification_label'])
                ),
                'school_id' => $existing_school->id,
                'school_data' => $existing_school,
                'classification' => $school_profile['classification'],
                'recognition_summary' => $recognition_summary,
                'pre_filled_data' => $this->getPreFilledSchoolData($existing_school),
                'next_step' => 2,
                'returning_school' => true
            ));
        } else {
            // Process school representative user
            $user_result = $user_manager->processSchoolRepresentative(
                $school_data['contact_person'],
                $school_data['contact_email'],
                $school_data['contact_phone']
            );

            if (is_wp_error($user_result)) {
                wp_send_json_error($user_result->get_error_message());
            }

            // Create school record
            $school_data['wp_user_id'] = $user_result['user_id'];
            $school_data['status'] = get_option('osb_auto_approve_schools', false) ? 'active' : 'pending';

            $school_id = $db->createSchool($school_data);

            if ($school_id) {
                wp_send_json_success(array(
                    'message' => __('School registered successfully. Please proceed to student registration.', 'omafuru-spelling-bee'),
                    'school_id' => $school_id,
                    'next_step' => 2,
                    'user_created' => $user_result['created']
                ));
            } else {
                wp_send_json_error(__('Failed to register school.', 'omafuru-spelling-bee'));
            }
        }
    }

    /**
     * Handle student registration step (Step 2)
     */
    private function handleStudentRegistrationStep() {
        $school_id = intval($_POST['school_id']);
        $students = $_POST['students'] ?? array();

        if (empty($school_id) || empty($students)) {
            wp_send_json_error(__('School ID and student information are required.', 'omafuru-spelling-bee'));
        }

        $db = OSB_Database::getInstance();
        $user_manager = OSB_User_Manager::getInstance();

        $registered_students = array();
        $max_students = intval(get_option('osb_max_students_per_school', 5));

        if (count($students) > $max_students) {
            wp_send_json_error(sprintf(__('Maximum %d students allowed per school.', 'omafuru-spelling-bee'), $max_students));
        }

        foreach ($students as $student_data) {
            $student_data = array_map('sanitize_text_field', $student_data);
            $student_data['school_id'] = $school_id;

            // Process parent user if provided
            if (!empty($student_data['parent_email'])) {
                $parent_result = $user_manager->processParentGuardian(
                    $student_data['parent_name'],
                    $student_data['parent_email'],
                    $student_data['parent_phone']
                );

                if (!is_wp_error($parent_result)) {
                    $student_data['parent_wp_user_id'] = $parent_result['user_id'];
                }
            }

            // Process student user if email provided
            if (!empty($student_data['email'])) {
                $student_name = $student_data['first_name'] . ' ' . $student_data['last_name'];
                $student_result = $user_manager->processStudent(
                    $student_name,
                    $student_data['email'],
                    $student_data['phone'] ?? ''
                );

                if (!is_wp_error($student_result)) {
                    $student_data['wp_user_id'] = $student_result['user_id'];
                }
            }

            $student_id = $db->createStudent($student_data);

            if ($student_id) {
                $registered_students[] = $student_id;
            } else {
                wp_send_json_error(__('Failed to register one or more students.', 'omafuru-spelling-bee'));
            }
        }

        wp_send_json_success(array(
            'message' => sprintf(__('%d students registered successfully.', 'omafuru-spelling-bee'), count($registered_students)),
            'student_ids' => $registered_students,
            'next_step' => 3
        ));
    }

    /**
     * Handle document upload step (Step 3)
     */
    private function handleDocumentUploadStep() {
        $school_id = intval($_POST['school_id']);

        if (empty($school_id)) {
            wp_send_json_error(__('School ID is required.', 'omafuru-spelling-bee'));
        }

        $file_handler = OSB_File_Handler::getInstance();
        $uploaded_documents = array();

        // Handle multiple document uploads
        if (!empty($_FILES['documents'])) {
            foreach ($_FILES['documents']['name'] as $key => $name) {
                if (!empty($name)) {
                    $file_data = array(
                        'name' => $_FILES['documents']['name'][$key],
                        'type' => $_FILES['documents']['type'][$key],
                        'tmp_name' => $_FILES['documents']['tmp_name'][$key],
                        'error' => $_FILES['documents']['error'][$key],
                        'size' => $_FILES['documents']['size'][$key]
                    );

                    $upload_result = $file_handler->uploadFile(
                        $file_data,
                        'documents',
                        'school_' . $school_id
                    );

                    if (is_wp_error($upload_result)) {
                        wp_send_json_error($upload_result->get_error_message());
                    }

                    $uploaded_documents[] = $upload_result;
                }
            }
        }

        wp_send_json_success(array(
            'message' => sprintf(__('%d documents uploaded successfully.', 'omafuru-spelling-bee'), count($uploaded_documents)),
            'documents' => $uploaded_documents,
            'next_step' => 4
        ));
    }

    /**
     * Handle review step (Step 4)
     */
    private function handleReviewStep() {
        $school_id = intval($_POST['school_id']);

        if (empty($school_id)) {
            wp_send_json_error(__('School ID is required.', 'omafuru-spelling-bee'));
        }

        $db = OSB_Database::getInstance();

        // Get school and students for review
        $school = $db->getSchool($school_id);
        $students = $db->getStudentsBySchool($school_id);

        if (!$school) {
            wp_send_json_error(__('School not found.', 'omafuru-spelling-bee'));
        }

        wp_send_json_success(array(
            'message' => __('Registration details retrieved for review.', 'omafuru-spelling-bee'),
            'school' => $school,
            'students' => $students,
            'next_step' => 5
        ));
    }

    /**
     * Handle final submission step (Step 5)
     */
    private function handleFinalSubmissionStep() {
        $school_id = intval($_POST['school_id']);
        $event_id = intval($_POST['event_id']);
        $agreement_accepted = isset($_POST['agreement_accepted']);

        if (empty($school_id) || empty($event_id) || !$agreement_accepted) {
            wp_send_json_error(__('All required fields must be completed and agreement must be accepted.', 'omafuru-spelling-bee'));
        }

        $db = OSB_Database::getInstance();

        // Check if already registered for this event
        $existing_registration = $db->getRegistration($event_id, $school_id);

        if ($existing_registration) {
            wp_send_json_error(__('School is already registered for this event.', 'omafuru-spelling-bee'));
        }

        // Create registration record
        $registration_data = array(
            'event_id' => $event_id,
            'school_id' => $school_id,
            'status' => 'pending',
            'registration_date' => current_time('mysql'),
            'agreement_accepted' => 1,
            'created_at' => current_time('mysql')
        );

        $registration_id = $db->createRegistration($registration_data);

        if ($registration_id) {
            // Send confirmation email
            $email_handler = OSB_Email_Handler::getInstance();
            $email_handler->sendRegistrationConfirmation($registration_id);

            // ENHANCED EMAIL AUTOMATION
            // Send document checklist email (1 hour after registration)
            wp_schedule_single_event(time() + HOUR_IN_SECONDS, 'osb_send_document_checklist', [$registration_id]);

            // Schedule deadline reminder emails
            $email_handler->scheduleReminderEmails($registration_id);

            // SCHOOL CLASSIFICATION - Track this registration for future classification
            $this->trackSchoolParticipation($school_id, $event_id, $registration_id);

            // WORKFLOW AUTOMATION - Trigger new registration workflow
            do_action('osb_registration_created', $registration_id, $school_id);

            // Get registration token for status checking
            $registration = $db->getRegistrationByToken($registration_id);

            wp_send_json_success(array(
                'message' => __('Registration submitted successfully! You will receive a confirmation email shortly.', 'omafuru-spelling-bee'),
                'registration_id' => $registration_id,
                'registration_token' => $registration->registration_token,
                'status_url' => home_url('/registration-status/?token=' . $registration->registration_token)
            ));
        } else {
            wp_send_json_error(__('Failed to submit registration.', 'omafuru-spelling-bee'));
        }
    }

    /**
     * Handle donation submission
     */
    public function handleDonationSubmission() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'osb_public_nonce')) {
            wp_send_json_error(__('Security check failed.', 'omafuru-spelling-bee'));
        }

        $donation_data = array(
            'event_id' => intval($_POST['event_id'] ?? 0),
            'donor_name' => sanitize_text_field($_POST['donor_name']),
            'donor_email' => sanitize_email($_POST['donor_email']),
            'donor_phone' => sanitize_text_field($_POST['donor_phone']),
            'amount' => floatval($_POST['amount']),
            'donor_message' => sanitize_textarea_field($_POST['donor_message']),
            'is_anonymous' => isset($_POST['is_anonymous']) ? 1 : 0
        );

        $donation_calc = OSB_Donation_Calculator::getInstance();
        $result = $donation_calc->processDonation($donation_data);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success($result);
        }
    }

    /**
     * Handle registration status check
     */
    public function handleRegistrationStatusCheck() {
        $token = sanitize_text_field($_POST['token'] ?? '');

        if (empty($token)) {
            wp_send_json_error(__('Registration token is required.', 'omafuru-spelling-bee'));
        }

        $db = OSB_Database::getInstance();
        $registration = $db->getRegistrationByToken($token);

        if (!$registration) {
            wp_send_json_error(__('Invalid registration token.', 'omafuru-spelling-bee'));
        }

        // Get students for this registration
        $students = $db->getStudentsBySchool($registration->school_id);

        wp_send_json_success(array(
            'registration' => $registration,
            'students' => $students
        ));
    }

    /**
     * Handle event info request
     */
    public function handleEventInfoRequest() {
        $event_id = intval($_POST['event_id'] ?? 0);

        $db = OSB_Database::getInstance();

        if ($event_id) {
            $event = $db->getEvent($event_id);
        } else {
            $event = $db->getCurrentEvent();
        }

        if (!$event) {
            wp_send_json_error(__('Event not found.', 'omafuru-spelling-bee'));
        }

        // Get additional event data
        $donation_calc = OSB_Donation_Calculator::getInstance();
        $total_donations = $donation_calc->getTotalDonations($event->id);
        $registrations_count = count($db->getEventRegistrations($event->id, 'approved'));

        wp_send_json_success(array(
            'event' => $event,
            'total_donations' => $total_donations,
            'registrations_count' => $registrations_count
        ));
    }

    /**
     * Register custom post types if needed
     */
    public function registerCustomPostTypes() {
        // This could be used for creating custom post types for news, announcements, etc.
        // Currently not implemented as we're using custom database tables
    }

    /**
     * Add custom query vars
     */
    public function addQueryVars() {
        global $wp;
        $wp->add_query_var('osb_page');
        $wp->add_query_var('token');
    }

    /**
     * Handle custom pages
     */
    public function handleCustomPages() {
        $osb_page = get_query_var('osb_page');

        switch ($osb_page) {
            case 'registration-status':
                $this->displayRegistrationStatusPage();
                break;
        }
    }

    /**
     * Display registration status page
     */
    private function displayRegistrationStatusPage() {
        $token = get_query_var('token');

        if (empty($token)) {
            wp_die(__('Registration token is required.', 'omafuru-spelling-bee'));
        }

        $db = OSB_Database::getInstance();
        $registration = $db->getRegistrationByToken($token);

        if (!$registration) {
            wp_die(__('Invalid registration token.', 'omafuru-spelling-bee'));
        }

        // Load template
        include OSB_PLUGIN_PATH . 'public/templates/registration-status.php';
        exit;
    }

    /**
     * Get current event for frontend display
     */
    public function getCurrentEvent() {
        $db = OSB_Database::getInstance();
        return $db->getCurrentEvent();
    }

    /**
     * Get events for display
     */
    public function getEvents($status = null) {
        $db = OSB_Database::getInstance();
        return $db->getAllEvents($status);
    }

    /**
     * Format currency for display
     */
    public function formatCurrency($amount, $currency = 'USD') {
        $currency_symbols = array(
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'NGN' => '₦'
        );

        $symbol = isset($currency_symbols[$currency]) ? $currency_symbols[$currency] : $currency . ' ';
        return $symbol . number_format($amount, 2);
    }

    /**
     * Get registration form HTML
     */
    public function getRegistrationFormHtml($event_id = null, $step = 1) {
        $db = OSB_Database::getInstance();

        if ($event_id) {
            $event = $db->getEvent($event_id);
        } else {
            $event = $db->getCurrentEvent();
        }

        if (!$event) {
            return '<div class="osb-error">' . __('No active event found.', 'omafuru-spelling-bee') . '</div>';
        }

        ob_start();
        include OSB_PLUGIN_PATH . 'public/templates/registration-form.php';
        return ob_get_clean();
    }

    /**
     * Get donation form HTML
     */
    public function getDonationFormHtml($event_id = null) {
        $db = OSB_Database::getInstance();
        $donation_calc = OSB_Donation_Calculator::getInstance();

        if ($event_id) {
            $event = $db->getEvent($event_id);
        } else {
            $event = $db->getCurrentEvent();
        }

        if (!$event) {
            return '<div class="osb-error">' . __('No active event found.', 'omafuru-spelling-bee') . '</div>';
        }

        $suggested_amounts = $donation_calc->getSuggestedAmounts();

        ob_start();
        include OSB_PLUGIN_PATH . 'public/templates/donation-form.php';
        return ob_get_clean();
    }

    /**
     * Handle auto-save registration AJAX request
     */
    public function handleAutoSaveRegistration() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'osb_registration')) {
            wp_send_json_error(__('Security check failed.', 'omafuru-spelling-bee'));
        }

        $registration_id = intval($_POST['registration_id'] ?? 0);
        $event_id = intval($_POST['event_id']);
        $current_step = intval($_POST['current_step']);
        $form_data = sanitize_textarea_field($_POST['form_data']);

        if (empty($event_id) || empty($form_data)) {
            wp_send_json_error(__('Invalid data provided.', 'omafuru-spelling-bee'));
        }

        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        if ($registration_id && $registration_id > 0) {
            // Update existing registration
            $result = $wpdb->update(
                "{$table_prefix}registrations",
                array(
                    'form_data_cache' => $form_data,
                    'last_active_step' => $current_step,
                    'auto_save_timestamp' => current_time('mysql')
                ),
                array('id' => $registration_id),
                array('%s', '%d', '%s'),
                array('%d')
            );

            if ($result !== false) {
                wp_send_json_success(array(
                    'registration_id' => $registration_id,
                    'message' => __('Registration auto-saved successfully.', 'omafuru-spelling-bee')
                ));
            } else {
                wp_send_json_error(__('Failed to auto-save registration.', 'omafuru-spelling-bee'));
            }
        } else {
            // Create new draft registration
            $registration_token = wp_generate_password(12, false);

            $registration_data = array(
                'event_id' => $event_id,
                'registration_token' => $registration_token,
                'status' => 'draft',
                'form_data_cache' => $form_data,
                'last_active_step' => $current_step,
                'auto_save_timestamp' => current_time('mysql'),
                'created_at' => current_time('mysql'),
                'updated_at' => current_time('mysql')
            );

            $registration_id = $wpdb->insert(
                "{$table_prefix}registrations",
                $registration_data,
                array('%d', '%s', '%s', '%s', '%d', '%s', '%s', '%s')
            );

            if ($registration_id) {
                wp_send_json_success(array(
                    'registration_id' => $wpdb->insert_id,
                    'registration_token' => $registration_token,
                    'message' => __('Registration auto-saved successfully.', 'omafuru-spelling-bee')
                ));
            } else {
                wp_send_json_error(__('Failed to create auto-save registration.', 'omafuru-spelling-bee'));
            }
        }
    }

    /**
     * Handle load resume data AJAX request
     */
    public function handleLoadResumeData() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'osb_registration')) {
            wp_send_json_error(__('Security check failed.', 'omafuru-spelling-bee'));
        }

        $token = sanitize_text_field($_POST['token']);

        if (empty($token)) {
            wp_send_json_error(__('Invalid token provided.', 'omafuru-spelling-bee'));
        }

        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        $registration = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_prefix}registrations WHERE registration_token = %s AND status IN ('draft', 'pending')",
                $token
            )
        );

        if ($registration) {
            wp_send_json_success($registration);
        } else {
            wp_send_json_error(__('Registration not found or has already been completed.', 'omafuru-spelling-bee'));
        }
    }

    /**
     * Handle update step progress AJAX request
     */
    public function handleUpdateStepProgress() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'osb_registration')) {
            wp_send_json_error(__('Security check failed.', 'omafuru-spelling-bee'));
        }

        $registration_id = intval($_POST['registration_id']);
        $step = intval($_POST['step']);

        if (empty($registration_id) || empty($step)) {
            wp_send_json_error(__('Invalid data provided.', 'omafuru-spelling-bee'));
        }

        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        // Get current step progress
        $current_progress = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT step_progress FROM {$table_prefix}registrations WHERE id = %d",
                $registration_id
            )
        );

        $step_progress = $current_progress ? json_decode($current_progress, true) : array();

        // Update step progress
        $step_key = "step_{$step}";
        $step_progress[$step_key] = array(
            'completed' => true,
            'completed_at' => current_time('mysql')
        );

        // Update database
        $result = $wpdb->update(
            "{$table_prefix}registrations",
            array(
                'step_progress' => wp_json_encode($step_progress),
                'last_active_step' => $step,
                'updated_at' => current_time('mysql')
            ),
            array('id' => $registration_id),
            array('%s', '%d', '%s'),
            array('%d')
        );

        if ($result !== false) {
            wp_send_json_success(array(
                'message' => __('Step progress updated successfully.', 'omafuru-spelling-bee'),
                'step_progress' => $step_progress
            ));
        } else {
            wp_send_json_error(__('Failed to update step progress.', 'omafuru-spelling-bee'));
        }
    }

    /**
     * Handle chunked file upload AJAX request
     */
    public function handleUploadFileChunk() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'osb_registration')) {
            wp_send_json_error(__('Security check failed.', 'omafuru-spelling-bee'));
        }

        $file_id = sanitize_text_field($_POST['file_id']);
        $file_name = sanitize_file_name($_POST['file_name']);
        $chunk_number = intval($_POST['chunk_number']);
        $total_chunks = intval($_POST['total_chunks']);
        $registration_id = intval($_POST['registration_id']);

        if (empty($file_id) || empty($file_name) || !isset($_FILES['file_chunk'])) {
            wp_send_json_error(__('Invalid chunk data provided.', 'omafuru-spelling-bee'));
        }

        // Create upload directory for chunks
        $upload_dir = wp_upload_dir();
        $chunk_dir = trailingslashit($upload_dir['basedir']) . 'omafuru-spelling-bee/chunks/' . $file_id . '/';

        if (!wp_mkdir_p($chunk_dir)) {
            wp_send_json_error(__('Could not create upload directory.', 'omafuru-spelling-bee'));
        }

        // Save chunk
        $chunk_file = $chunk_dir . 'chunk_' . str_pad($chunk_number, 6, '0', STR_PAD_LEFT);

        if (!move_uploaded_file($_FILES['file_chunk']['tmp_name'], $chunk_file)) {
            wp_send_json_error(__('Failed to save file chunk.', 'omafuru-spelling-bee'));
        }

        // Check if all chunks have been uploaded
        if ($chunk_number + 1 >= $total_chunks) {
            // Reassemble file
            $final_file_path = $this->reassembleChunkedFile($file_id, $file_name, $total_chunks, $registration_id);

            if (is_wp_error($final_file_path)) {
                wp_send_json_error($final_file_path->get_error_message());
            }

            // Clean up chunks
            $this->cleanupChunks($chunk_dir);

            wp_send_json_success(array(
                'message' => __('File uploaded successfully.', 'omafuru-spelling-bee'),
                'file_path' => $final_file_path,
                'final_chunk' => true
            ));
        } else {
            wp_send_json_success(array(
                'message' => __('Chunk uploaded successfully.', 'omafuru-spelling-bee'),
                'chunk_number' => $chunk_number
            ));
        }
    }

    /**
     * Reassemble chunked file
     */
    private function reassembleChunkedFile($file_id, $file_name, $total_chunks, $registration_id) {
        $upload_dir = wp_upload_dir();
        $chunk_dir = trailingslashit($upload_dir['basedir']) . 'omafuru-spelling-bee/chunks/' . $file_id . '/';
        $documents_dir = trailingslashit($upload_dir['basedir']) . 'omafuru-spelling-bee/documents/';

        // Create documents directory
        if (!wp_mkdir_p($documents_dir)) {
            return new WP_Error('directory_error', __('Could not create final upload directory.', 'omafuru-spelling-bee'));
        }

        // Generate unique filename
        $extension = pathinfo($file_name, PATHINFO_EXTENSION);
        $base_name = pathinfo($file_name, PATHINFO_FILENAME);
        $timestamp = current_time('timestamp');
        $unique_name = sanitize_file_name($base_name) . '_' . $timestamp . '_' . wp_generate_password(8, false) . '.' . $extension;
        $final_path = $documents_dir . $unique_name;

        // Open final file for writing
        $final_file = fopen($final_path, 'wb');
        if (!$final_file) {
            return new WP_Error('file_error', __('Could not create final file.', 'omafuru-spelling-bee'));
        }

        // Reassemble chunks
        for ($i = 0; $i < $total_chunks; $i++) {
            $chunk_file = $chunk_dir . 'chunk_' . str_pad($i, 6, '0', STR_PAD_LEFT);

            if (!file_exists($chunk_file)) {
                fclose($final_file);
                unlink($final_path);
                return new WP_Error('chunk_missing', __('Missing file chunk.', 'omafuru-spelling-bee'));
            }

            $chunk_data = file_get_contents($chunk_file);
            fwrite($final_file, $chunk_data);
        }

        fclose($final_file);

        // Set proper permissions
        chmod($final_path, 0644);

        // Validate final file
        if (!$this->validateUploadedFile($final_path, $file_name)) {
            unlink($final_path);
            return new WP_Error('validation_failed', __('File validation failed.', 'omafuru-spelling-bee'));
        }

        // Log file upload
        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        $wpdb->insert(
            "{$table_prefix}documents",
            array(
                'registration_id' => $registration_id,
                'original_name' => $file_name,
                'file_path' => $final_path,
                'file_size' => filesize($final_path),
                'uploaded_at' => current_time('mysql')
            ),
            array('%d', '%s', '%s', '%d', '%s')
        );

        // WORKFLOW AUTOMATION - Trigger document upload workflow
        do_action('osb_document_uploaded', $registration_id, $final_path);

        return $final_path;
    }

    /**
     * Validate uploaded file
     */
    private function validateUploadedFile($file_path, $original_name) {
        // Check file exists
        if (!file_exists($file_path)) {
            return false;
        }

        // Check file size (max 10MB for assembled files)
        if (filesize($file_path) > 10 * 1024 * 1024) {
            return false;
        }

        // Check file type
        $allowed_types = array('pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png');
        $extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));

        if (!in_array($extension, $allowed_types)) {
            return false;
        }

        // Additional MIME type check
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file_path);
        finfo_close($finfo);

        $allowed_mimes = array(
            'application/pdf',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'image/jpeg',
            'image/png'
        );

        return in_array($mime_type, $allowed_mimes);
    }

    /**
     * Clean up chunk files
     */
    private function cleanupChunks($chunk_dir) {
        if (!is_dir($chunk_dir)) {
            return;
        }

        $files = glob($chunk_dir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        rmdir($chunk_dir);
    }

    /**
     * Get pre-filled data for returning schools
     */
    private function getPreFilledSchoolData($school) {
        return array(
            'school_name' => $school->school_name ?: '',
            'school_type' => $school->school_type ?: '',
            'contact_person' => $school->contact_person ?: '',
            'contact_email' => $school->contact_email ?: '',
            'contact_phone' => $school->phone ?: '', // Note: DB field is 'phone', form field is 'contact_phone'
            'address' => $school->address ?: '',
            'state' => $school->state ?: '',
            'city' => $this->extractCityFromAddress($school->address ?: ''),
            'postal_code' => $this->extractPostalCodeFromAddress($school->address ?: ''),
            'country' => 'NG' // Default for Nigerian schools
        );
    }

    /**
     * Extract city from address (simple implementation)
     */
    private function extractCityFromAddress($address) {
        // Simple extraction - can be enhanced based on address format
        $parts = explode(',', $address);
        return count($parts) >= 2 ? trim($parts[count($parts) - 2]) : '';
    }

    /**
     * Extract postal code from address (simple implementation)
     */
    private function extractPostalCodeFromAddress($address) {
        // Simple extraction - can be enhanced based on address format
        $parts = explode(',', $address);
        $last_part = trim(end($parts));

        // Check if last part is numeric (postal code)
        if (is_numeric($last_part)) {
            return $last_part;
        }

        return '';
    }

    /**
     * Track school participation for classification purposes
     */
    private function trackSchoolParticipation($school_id, $event_id, $registration_id) {
        // Get event details
        $db = OSB_Database::getInstance();
        $event = $db->getEvent($event_id);

        if (!$event) {
            return;
        }

        // Add hook for when registration status changes to 'confirmed'
        add_action('osb_registration_status_changed', function($reg_id, $old_status, $new_status) use ($school_id, $event) {
            if ($reg_id === $registration_id && $new_status === 'confirmed') {
                $this->finalizeSchoolParticipation($school_id, $event);
            }
        }, 10, 3);

        // If registration is already confirmed, track immediately
        $registration = $db->getRegistration($event_id, $school_id);
        if ($registration && $registration->status === 'confirmed') {
            $this->finalizeSchoolParticipation($school_id, $event);
        }
    }

    /**
     * Finalize school participation tracking
     */
    private function finalizeSchoolParticipation($school_id, $event) {
        $classifier = OSB_School_Classifier::getInstance();

        // Extract year from event date
        $event_year = date('Y', strtotime($event->event_date));

        // Get additional performance data
        $performance_data = array(
            'event_title' => $event->title,
            'event_date' => $event->event_date,
            'completion_date' => current_time('mysql'),
            'status' => 'completed'
        );

        // Update participation history
        $classifier->updateParticipationHistory($school_id, $event_year, $performance_data);

        // Log the tracking
        error_log("[OSB] School participation tracked: School ID {$school_id}, Year {$event_year}");
    }
}