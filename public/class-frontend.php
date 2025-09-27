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

        // Dashboard authentication
        add_action('wp_ajax_osb_send_dashboard_link', array($this, 'handleSendDashboardLink'));
        add_action('wp_ajax_nopriv_osb_send_dashboard_link', array($this, 'handleSendDashboardLink'));

        // Document uploads
        add_action('wp_ajax_osb_upload_documents', array($this, 'handleDocumentUpload'));
        add_action('wp_ajax_nopriv_osb_upload_documents', array($this, 'handleDocumentUpload'));

        // Student management
        add_action('wp_ajax_osb_save_students', array($this, 'handleSaveStudents'));
        add_action('wp_ajax_nopriv_osb_save_students', array($this, 'handleSaveStudents'));
        add_action('wp_ajax_osb_add_student', array($this, 'handleAddStudent'));
        add_action('wp_ajax_nopriv_osb_add_student', array($this, 'handleAddStudent'));
        add_action('wp_ajax_osb_get_student', array($this, 'handleGetStudent'));
        add_action('wp_ajax_nopriv_osb_get_student', array($this, 'handleGetStudent'));
        add_action('wp_ajax_osb_update_student', array($this, 'handleUpdateStudent'));
        add_action('wp_ajax_nopriv_osb_update_student', array($this, 'handleUpdateStudent'));
        add_action('wp_ajax_osb_delete_student', array($this, 'handleDeleteStudent'));
        add_action('wp_ajax_nopriv_osb_delete_student', array($this, 'handleDeleteStudent'));

        // New dashboard workflow AJAX handlers
        add_action('wp_ajax_osb_select_competition', array($this, 'handleCompetitionSelection'));
        add_action('wp_ajax_nopriv_osb_select_competition', array($this, 'handleCompetitionSelection'));
        add_action('wp_ajax_osb_upload_eoi_document', array($this, 'handleEoiDocumentUpload'));
        add_action('wp_ajax_nopriv_osb_upload_eoi_document', array($this, 'handleEoiDocumentUpload'));
        add_action('wp_ajax_osb_download_template', array($this, 'handleTemplateDownload'));
        add_action('wp_ajax_nopriv_osb_download_template', array($this, 'handleTemplateDownload'));
        add_action('wp_ajax_osb_submit_final_registration', array($this, 'handleFinalRegistrationSubmission'));
        add_action('wp_ajax_nopriv_osb_submit_final_registration', array($this, 'handleFinalRegistrationSubmission'));

        // Custom post types and taxonomies if needed
        add_action('init', array($this, 'registerCustomPostTypes'));

        // Custom query vars and rewrite rules
        add_action('init', array($this, 'addQueryVars'));
        add_action('init', array($this, 'addRewriteRules'));
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
                wp_send_json_error(__('Invalid action.', 'spelling-bee-pro'));
        }
    }

    /**
     * Handle registration submission - Simplified workflow: register → immediate access → redirect
     */
    public function handleRegistrationSubmission() {
        error_log('OSB Registration: Started - Simplified workflow');
        error_log('OSB Registration: POST data - ' . print_r(array_keys($_POST), true));

        // Verify nonce
        $nonce_value = $_POST['osb_registration_nonce'] ?? $_POST['nonce'] ?? '';
        if (!wp_verify_nonce($nonce_value, 'osb_registration')) {
            error_log('OSB Registration: FAILED - Nonce verification failed. Nonce value: ' . $nonce_value);
            wp_send_json_error(__('Security check failed.', 'spelling-bee-pro'));
        }

        // Verify reCAPTCHA if enabled
        if (get_option('osb_recaptcha_enabled', '1') === '1') {
            $recaptcha_response = $_POST['g-recaptcha-response'] ?? '';
            if (empty($recaptcha_response)) {
                error_log('OSB Registration: FAILED - reCAPTCHA response missing');
                wp_send_json_error(__('Please complete the reCAPTCHA verification.', 'spelling-bee-pro'));
            }

            $secret_key = get_option('osb_recaptcha_secret_key', '');
            if (empty($secret_key)) {
                error_log('OSB Registration: WARNING - reCAPTCHA enabled but secret key not configured');
                wp_send_json_error(__('reCAPTCHA configuration error. Please contact the administrator.', 'spelling-bee-pro'));
            }

            $verify_url = 'https://www.google.com/recaptcha/api/siteverify';
            $response = wp_remote_post($verify_url, array(
                'body' => array(
                    'secret' => $secret_key,
                    'response' => $recaptcha_response,
                    'remoteip' => $_SERVER['REMOTE_ADDR']
                )
            ));

            if (is_wp_error($response)) {
                error_log('OSB Registration: FAILED - reCAPTCHA verification error: ' . $response->get_error_message());
                wp_send_json_error(__('reCAPTCHA verification failed. Please try again.', 'spelling-bee-pro'));
            }

            $body = wp_remote_retrieve_body($response);
            $result = json_decode($body, true);

            if (!$result['success']) {
                $error_codes = isset($result['error-codes']) ? implode(', ', $result['error-codes']) : 'unknown';
                error_log('OSB Registration: FAILED - reCAPTCHA verification failed: ' . $error_codes);
                wp_send_json_error(__('reCAPTCHA verification failed. Please try again.', 'spelling-bee-pro'));
            }

            error_log('OSB Registration: reCAPTCHA verification successful');
        }

        try {
            // Process the complete registration in one step
            $this->handleCompleteRegistration();
        } catch (Exception $e) {
            error_log('OSB Registration: EXCEPTION - ' . $e->getMessage());
            error_log('OSB Registration: EXCEPTION - Stack trace: ' . $e->getTraceAsString());
            wp_send_json_error(__('Registration failed due to an error: ', 'spelling-bee-pro') . $e->getMessage());
        }
    }

    /**
     * Handle complete registration - Simplified workflow
     */
    private function handleCompleteRegistration() {
        error_log('OSB Registration: Starting complete registration process');

        // Validate required fields
        $required_fields = ['school_name', 'school_type', 'address', 'city', 'state', 'contact_person', 'contact_email'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $missing_fields[] = $field;
            }
        }

        if (!empty($missing_fields)) {
            error_log('OSB Registration: FAILED - Missing required fields: ' . implode(', ', $missing_fields));
            wp_send_json_error(__('Missing required fields: ', 'spelling-bee-pro') . implode(', ', $missing_fields));
        }

        // Sanitize school data
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

        $user_manager = OSB_User_Manager::getInstance();
        $db = OSB_Database::getInstance();

        if (!$user_manager || !$db) {
            error_log('OSB Registration: FAILED - Could not get required instances');
            wp_send_json_error(__('System error: Required components not available.', 'spelling-bee-pro'));
        }

        error_log('OSB Registration: Checking for existing school with email: ' . $school_data['contact_email']);

        // Check for existing school
        $existing_school = $db->getSchoolByEmail($school_data['contact_email']);

        if ($existing_school) {
            error_log('OSB Registration: Found existing school with ID: ' . $existing_school->id);

            // Check if this school has an existing registration with token
            $registrations = $db->getRegistrationsBySchool($existing_school->id);
            $dashboard_url = home_url('/spellingbee-dashboard/');

            if (!empty($registrations)) {
                $latest_registration = $registrations[0];
                $dashboard_url = home_url('/spellingbee-dashboard/?token=' . $latest_registration->registration_token);
            }

            wp_send_json_error(array(
                'message' => sprintf(
                    __('Email already registered! %s is already in our system.', 'spelling-bee-pro'),
                    esc_html($existing_school->school_name)
                ),
                'type' => 'existing_user',
                'school_name' => $existing_school->school_name,
                'dashboard_url' => $dashboard_url,
                'action_message' => __('Access your existing dashboard instead:', 'spelling-bee-pro')
            ));
        }

        error_log('OSB Registration: No existing school found, creating new school');

        // Process school representative user
        error_log('OSB Registration: Processing school representative user');
        $representative_data = array(
            'contact_person' => $school_data['contact_person'],
            'contact_email' => $school_data['contact_email'],
            'phone' => $school_data['contact_phone']
        );
        $user_result = $user_manager->processSchoolRepresentative($representative_data);

        if (is_wp_error($user_result)) {
            error_log('OSB Registration: FAILED - User creation failed: ' . $user_result->get_error_message());
            wp_send_json_error($user_result->get_error_message());
        }

        if (!$user_result['success'] || empty($user_result['user_id'])) {
            error_log('OSB Registration: FAILED - User processing failed: ' . ($user_result['message'] ?? 'Unknown error'));
            wp_send_json_error('User processing failed: ' . ($user_result['message'] ?? 'Unknown error'));
        }

        error_log('OSB Registration: User processed successfully, User ID: ' . $user_result['user_id']);

        // Create school record
        $school_data['wp_user_id'] = $user_result['user_id'];
        $school_data['status'] = get_option('osb_auto_approve_schools', false) ? 'active' : 'pending';

        error_log('OSB Registration: Creating school record');
        $school_id = $db->createSchool($school_data);

        if (!$school_id) {
            error_log('OSB Registration: FAILED - School creation failed');
            global $wpdb;
            $db_error = $wpdb->last_error;
            error_log('OSB Registration: Database error: ' . $db_error);
            wp_send_json_error(__('Failed to register school.', 'spelling-bee-pro'));
        }

        error_log('OSB Registration: School created successfully with ID: ' . $school_id);

        // Generate a temporary access token for the school to access dashboard
        // No registration record is created yet - only after completing dashboard workflow
        $temp_token = wp_generate_password(32, false);

        // Store temporary token in school record for dashboard access
        // First ensure the temp_token column exists
        $this->ensureSchoolsTableUpgrade();
        $db->updateSchool($school_id, array('temp_token' => $temp_token));

        // Send email with dashboard access link
        $email_status = '';
        try {
            $email_handler = OSB_Email_Handler::getInstance();
            $school = $db->getSchool($school_id);
            $email_sent = $email_handler->sendSchoolDashboardAccess($school, $temp_token);

            if ($email_sent) {
                $email_status = 'Email sent successfully';
            } else {
                $email_status = 'Email failed to send - check SMTP configuration';
            }

            error_log("OSB School Registration {$school_id}: {$email_status}");

        } catch (Exception $e) {
            $email_status = 'Email error: ' . $e->getMessage();
            error_log("OSB School Registration {$school_id}: {$email_status}");
        }

        // Return success with redirect information
        $response_data = array(
            'message' => __('School registration completed successfully! Redirecting to your dashboard...', 'spelling-bee-pro'),
            'school_id' => $school_id,
            'temp_token' => $temp_token,
            'dashboard_url' => home_url('/spellingbee-dashboard/?token=' . $temp_token),
            'redirect' => true,
            'email_status' => $email_status
        );

        error_log('OSB Registration: Sending success response: ' . print_r($response_data, true));
        wp_send_json_success($response_data);
    }

    /**
     * Ensure schools table has required columns
     */
    private function ensureSchoolsTableUpgrade() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'osb_schools';

        // Check if temp_token column exists
        $columns = $wpdb->get_col("DESCRIBE {$table_name}", 0);

        if (!in_array('temp_token', $columns)) {
            // Add missing columns
            $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN temp_token varchar(64) DEFAULT NULL AFTER contact_email");
            $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN city varchar(100) DEFAULT NULL AFTER address");
            $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN postal_code varchar(20) DEFAULT NULL AFTER city");
            $wpdb->query("ALTER TABLE {$table_name} ADD COLUMN country varchar(100) DEFAULT 'Nigeria' AFTER postal_code");

            // Add index for temp_token
            $indexes = $wpdb->get_results("SHOW INDEX FROM {$table_name}");
            $index_names = array_column($indexes, 'Key_name');

            if (!in_array('idx_temp_token', $index_names)) {
                $wpdb->query("ALTER TABLE {$table_name} ADD KEY idx_temp_token (temp_token)");
            }
        }
    }

    /**
     * Handle school registration step (Step 1) - LEGACY METHOD - Keep for backward compatibility
     */
    private function handleSchoolRegistrationStep() {
        error_log('OSB Registration: Starting school registration step');

        // Validate required fields
        $required_fields = ['school_name', 'school_type', 'address', 'city', 'state', 'contact_person', 'contact_email'];
        $missing_fields = [];

        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                $missing_fields[] = $field;
            }
        }

        if (!empty($missing_fields)) {
            error_log('OSB Registration: FAILED - Missing required fields: ' . implode(', ', $missing_fields));
            wp_send_json_error(__('Missing required fields: ', 'spelling-bee-pro') . implode(', ', $missing_fields));
        }

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
                wp_send_json_error(sprintf(__('%s is required.', 'spelling-bee-pro'), ucfirst(str_replace('_', ' ', $field))));
            }
        }

        error_log('OSB Registration: School data validated, getting instances');

        $user_manager = OSB_User_Manager::getInstance();
        $db = OSB_Database::getInstance();

        if (!$user_manager || !$db) {
            error_log('OSB Registration: FAILED - Could not get required instances');
            wp_send_json_error(__('System error: Required components not available.', 'spelling-bee-pro'));
        }

        error_log('OSB Registration: Checking for existing school with email: ' . $school_data['contact_email']);

        // Check for existing school
        $existing_school = $db->getSchoolByEmail($school_data['contact_email']);

        if ($existing_school) {
            error_log('OSB Registration: Found existing school with ID: ' . $existing_school->id);

            // Check if this school has an existing registration with token
            $registrations = $db->getRegistrationsBySchool($existing_school->id);
            $dashboard_url = home_url('/spellingbee-dashboard/');

            if (!empty($registrations)) {
                $latest_registration = $registrations[0];
                $dashboard_url = home_url('/spellingbee-dashboard/?token=' . $latest_registration->registration_token);
            }

            wp_send_json_error(array(
                'message' => sprintf(
                    __('Email already registered! %s is already in our system.', 'spelling-bee-pro'),
                    esc_html($existing_school->school_name)
                ),
                'type' => 'existing_user',
                'school_name' => $existing_school->school_name,
                'dashboard_url' => $dashboard_url,
                'action_message' => __('Access your existing dashboard instead:', 'spelling-bee-pro')
            ));
        } else {
            error_log('OSB Registration: No existing school found, creating new school');

            // Process school representative user
            error_log('OSB Registration: Processing school representative user');
            $representative_data = array(
                'contact_person' => $school_data['contact_person'],
                'contact_email' => $school_data['contact_email'],
                'phone' => $school_data['contact_phone']
            );
            $user_result = $user_manager->processSchoolRepresentative($representative_data);

            if (is_wp_error($user_result)) {
                error_log('OSB Registration: FAILED - User creation failed: ' . $user_result->get_error_message());
                wp_send_json_error($user_result->get_error_message());
            }

            if (!$user_result['success'] || empty($user_result['user_id'])) {
                error_log('OSB Registration: FAILED - User processing failed: ' . ($user_result['message'] ?? 'Unknown error'));
                wp_send_json_error('User processing failed: ' . ($user_result['message'] ?? 'Unknown error'));
            }

            error_log('OSB Registration: User processed successfully, User ID: ' . $user_result['user_id']);

            // Create school record
            $school_data['wp_user_id'] = $user_result['user_id'];
            $school_data['status'] = get_option('osb_auto_approve_schools', false) ? 'active' : 'pending';

            error_log('OSB Registration: Creating school record with data: ' . print_r($school_data, true));

            $school_id = $db->createSchool($school_data);

            if ($school_id) {
                error_log('OSB Registration: School created successfully with ID: ' . $school_id);
                wp_send_json_success(array(
                    'message' => __('School registered successfully. Please proceed to student registration.', 'spelling-bee-pro'),
                    'school_id' => $school_id,
                    'next_step' => 2,
                    'user_created' => $user_result['created']
                ));
            } else {
                error_log('OSB Registration: FAILED - School creation failed');

                // Get last database error
                global $wpdb;
                $db_error = $wpdb->last_error;
                error_log('OSB Registration: Database error: ' . $db_error);
                wp_send_json_error(__('Failed to register school.', 'spelling-bee-pro'));
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
            wp_send_json_error(__('School ID and student information are required.', 'spelling-bee-pro'));
        }

        $db = OSB_Database::getInstance();
        $user_manager = OSB_User_Manager::getInstance();

        $registered_students = array();
        $max_students = intval(get_option('osb_max_students_per_school', 5));

        if (count($students) > $max_students) {
            wp_send_json_error(sprintf(__('Maximum %d students allowed per school.', 'spelling-bee-pro'), $max_students));
        }

        foreach ($students as $student_data) {
            $student_data = array_map('sanitize_text_field', $student_data);
            $student_data['school_id'] = $school_id;

            // Process parent user if provided
            if (!empty($student_data['parent_email'])) {
                $parent_data = array(
                    'parent_name' => $student_data['parent_name'],
                    'parent_email' => $student_data['parent_email'],
                    'parent_phone' => $student_data['parent_phone']
                );
                $parent_result = $user_manager->processParentGuardian($parent_data, $student_data['school_id']);

                if (!is_wp_error($parent_result)) {
                    $student_data['parent_wp_user_id'] = $parent_result['user_id'];
                }
            }

            // Process student user if email provided
            if (!empty($student_data['email'])) {
                $student_user_data = array(
                    'name' => $student_data['first_name'] . ' ' . $student_data['last_name'],
                    'email' => $student_data['email'],
                    'phone' => $student_data['phone'] ?? ''
                );
                $student_result = $user_manager->processStudent($student_user_data);

                if (!is_wp_error($student_result)) {
                    $student_data['wp_user_id'] = $student_result['user_id'];
                }
            }

            $student_id = $db->createStudent($student_data);

            if ($student_id) {
                $registered_students[] = $student_id;
            } else {
                wp_send_json_error(__('Failed to register one or more students.', 'spelling-bee-pro'));
            }
        }

        wp_send_json_success(array(
            'message' => sprintf(__('%d students registered successfully.', 'spelling-bee-pro'), count($registered_students)),
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
            wp_send_json_error(__('School ID is required.', 'spelling-bee-pro'));
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
            'message' => sprintf(__('%d documents uploaded successfully.', 'spelling-bee-pro'), count($uploaded_documents)),
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
            wp_send_json_error(__('School ID is required.', 'spelling-bee-pro'));
        }

        $db = OSB_Database::getInstance();

        // Get school and students for review
        $school = $db->getSchool($school_id);
        $students = $db->getStudentsBySchool($school_id);

        if (!$school) {
            wp_send_json_error(__('School not found.', 'spelling-bee-pro'));
        }

        wp_send_json_success(array(
            'message' => __('Registration details retrieved for review.', 'spelling-bee-pro'),
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
            wp_send_json_error(__('All required fields must be completed and agreement must be accepted.', 'spelling-bee-pro'));
        }

        $db = OSB_Database::getInstance();

        // Check if already registered for this event
        $existing_registration = $db->getRegistration($event_id, $school_id);

        if ($existing_registration) {
            wp_send_json_error(__('School is already registered for this event.', 'spelling-bee-pro'));
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
            // SCHOOL CLASSIFICATION - Track this registration for future classification
            $this->trackSchoolParticipation($school_id, $event_id, $registration_id);

            // WORKFLOW AUTOMATION - Trigger new registration workflow
            do_action('osb_registration_created', $registration_id, $school_id);

            // Get registration with token for dashboard access
            $registration = $db->getRegistrationById($registration_id);

            // Send immediate dashboard access email
            $email_status = '';
            try {
                $email_handler = OSB_Email_Handler::getInstance();
                $email_sent = $email_handler->sendImmediateDashboardAccess($registration_id);

                // ENHANCED EMAIL AUTOMATION (only if basic email works)
                if ($email_sent) {
                    // Send document checklist email (1 hour after registration)
                    wp_schedule_single_event(time() + HOUR_IN_SECONDS, 'osb_send_document_checklist', [$registration_id]);

                    // Schedule deadline reminder emails
                    $email_handler->scheduleReminderEmails($registration_id);
                    $email_status = 'Email sent successfully';
                } else {
                    $email_status = 'Email failed to send - check SMTP configuration';
                }

                // Log email status
                error_log("OSB Registration {$registration_id}: {$email_status}");

            } catch (Exception $e) {
                // Log email error but don't fail the registration
                $email_status = 'Email error: ' . $e->getMessage();
                error_log("OSB Registration {$registration_id}: {$email_status}");
            }

            wp_send_json_success(array(
                'message' => __('Registration completed successfully! Redirecting to your dashboard...', 'spelling-bee-pro'),
                'registration_id' => $registration_id,
                'registration_token' => $registration->registration_token,
                'dashboard_url' => home_url('/spellingbee-dashboard/?token=' . $registration->registration_token),
                'redirect' => true,
                'email_status' => $email_status
            ));
        } else {
            wp_send_json_error(__('Failed to submit registration.', 'spelling-bee-pro'));
        }
    }

    /**
     * Handle donation submission
     */
    public function handleDonationSubmission() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'osb_public_nonce')) {
            wp_send_json_error(__('Security check failed.', 'spelling-bee-pro'));
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
            wp_send_json_error(__('Registration token is required.', 'spelling-bee-pro'));
        }

        $db = OSB_Database::getInstance();
        $registration = $db->getRegistrationByToken($token);

        if (!$registration) {
            wp_send_json_error(__('Invalid registration token.', 'spelling-bee-pro'));
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
            wp_send_json_error(__('Event not found.', 'spelling-bee-pro'));
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
     * Add custom rewrite rules
     */
    public function addRewriteRules() {
        // Rewrite rule for spellingbee-dashboard with optional token
        add_rewrite_rule(
            '^spellingbee-dashboard/?$',
            'index.php?osb_page=spellingbee-dashboard',
            'top'
        );

        add_rewrite_rule(
            '^spellingbee-dashboard/([^/]+)/?$',
            'index.php?osb_page=spellingbee-dashboard&token=$matches[1]',
            'top'
        );

        // Rewrite rule for registration-status (legacy support)
        add_rewrite_rule(
            '^registration-status/?$',
            'index.php?osb_page=registration-status',
            'top'
        );

        add_rewrite_rule(
            '^registration-status/([^/]+)/?$',
            'index.php?osb_page=registration-status&token=$matches[1]',
            'top'
        );
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
            case 'spellingbee-dashboard':
                $this->displaySpellingbeeDashboard();
                break;
        }
    }

    /**
     * Display registration status page
     */
    private function displayRegistrationStatusPage() {
        $token = get_query_var('token');

        if (empty($token)) {
            wp_die(__('Registration token is required.', 'spelling-bee-pro'));
        }

        $db = OSB_Database::getInstance();
        $registration = $db->getRegistrationByToken($token);

        if (!$registration) {
            wp_die(__('Invalid registration token.', 'spelling-bee-pro'));
        }

        // Load template
        include OSB_PLUGIN_PATH . 'public/templates/registration-status.php';
        exit;
    }

    /**
     * Display SpellingBee Dashboard
     */
    private function displaySpellingbeeDashboard() {
        $token = get_query_var('token');

        $registration = null;
        $students = array();

        // If token is provided, try to load registration
        if (!empty($token)) {
            $db = OSB_Database::getInstance();
            $registration = $db->getRegistrationByToken($token);

            if ($registration) {
                // Get students for this registration
                $students = $db->getStudentsBySchool($registration->school_id);
            }
        }

        // Load dashboard template (shows login form if no valid registration)
        include OSB_PLUGIN_PATH . 'templates/shortcodes/spellingbee-dashboard.php';
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
            return '<div class="osb-error">' . __('No active event found.', 'spelling-bee-pro') . '</div>';
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
            return '<div class="osb-error">' . __('No active event found.', 'spelling-bee-pro') . '</div>';
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
            wp_send_json_error(__('Security check failed.', 'spelling-bee-pro'));
        }

        $registration_id = intval($_POST['registration_id'] ?? 0);
        $event_id = intval($_POST['event_id']);
        $current_step = intval($_POST['current_step']);
        $form_data = sanitize_textarea_field($_POST['form_data']);

        if (empty($event_id) || empty($form_data)) {
            wp_send_json_error(__('Invalid data provided.', 'spelling-bee-pro'));
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
                    'message' => __('Registration auto-saved successfully.', 'spelling-bee-pro')
                ));
            } else {
                wp_send_json_error(__('Failed to auto-save registration.', 'spelling-bee-pro'));
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
                    'message' => __('Registration auto-saved successfully.', 'spelling-bee-pro')
                ));
            } else {
                wp_send_json_error(__('Failed to create auto-save registration.', 'spelling-bee-pro'));
            }
        }
    }

    /**
     * Handle load resume data AJAX request
     */
    public function handleLoadResumeData() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'osb_registration')) {
            wp_send_json_error(__('Security check failed.', 'spelling-bee-pro'));
        }

        $token = sanitize_text_field($_POST['token']);

        if (empty($token)) {
            wp_send_json_error(__('Invalid token provided.', 'spelling-bee-pro'));
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
            wp_send_json_error(__('Registration not found or has already been completed.', 'spelling-bee-pro'));
        }
    }

    /**
     * Handle update step progress AJAX request
     */
    public function handleUpdateStepProgress() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'osb_registration')) {
            wp_send_json_error(__('Security check failed.', 'spelling-bee-pro'));
        }

        $registration_id = intval($_POST['registration_id']);
        $step = intval($_POST['step']);

        if (empty($registration_id) || empty($step)) {
            wp_send_json_error(__('Invalid data provided.', 'spelling-bee-pro'));
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
                'message' => __('Step progress updated successfully.', 'spelling-bee-pro'),
                'step_progress' => $step_progress
            ));
        } else {
            wp_send_json_error(__('Failed to update step progress.', 'spelling-bee-pro'));
        }
    }

    /**
     * Handle chunked file upload AJAX request
     */
    public function handleUploadFileChunk() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'osb_registration')) {
            wp_send_json_error(__('Security check failed.', 'spelling-bee-pro'));
        }

        $file_id = sanitize_text_field($_POST['file_id']);
        $file_name = sanitize_file_name($_POST['file_name']);
        $chunk_number = intval($_POST['chunk_number']);
        $total_chunks = intval($_POST['total_chunks']);
        $registration_id = intval($_POST['registration_id']);

        if (empty($file_id) || empty($file_name) || !isset($_FILES['file_chunk'])) {
            wp_send_json_error(__('Invalid chunk data provided.', 'spelling-bee-pro'));
        }

        // Create upload directory for chunks
        $upload_dir = wp_upload_dir();
        $chunk_dir = trailingslashit($upload_dir['basedir']) . 'spelling-bee-pro/chunks/' . $file_id . '/';

        if (!wp_mkdir_p($chunk_dir)) {
            wp_send_json_error(__('Could not create upload directory.', 'spelling-bee-pro'));
        }

        // Save chunk
        $chunk_file = $chunk_dir . 'chunk_' . str_pad($chunk_number, 6, '0', STR_PAD_LEFT);

        if (!move_uploaded_file($_FILES['file_chunk']['tmp_name'], $chunk_file)) {
            wp_send_json_error(__('Failed to save file chunk.', 'spelling-bee-pro'));
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
                'message' => __('File uploaded successfully.', 'spelling-bee-pro'),
                'file_path' => $final_file_path,
                'final_chunk' => true
            ));
        } else {
            wp_send_json_success(array(
                'message' => __('Chunk uploaded successfully.', 'spelling-bee-pro'),
                'chunk_number' => $chunk_number
            ));
        }
    }

    /**
     * Reassemble chunked file
     */
    private function reassembleChunkedFile($file_id, $file_name, $total_chunks, $registration_id) {
        $upload_dir = wp_upload_dir();
        $chunk_dir = trailingslashit($upload_dir['basedir']) . 'spelling-bee-pro/chunks/' . $file_id . '/';
        $documents_dir = trailingslashit($upload_dir['basedir']) . 'spelling-bee-pro/documents/';

        // Create documents directory
        if (!wp_mkdir_p($documents_dir)) {
            return new WP_Error('directory_error', __('Could not create final upload directory.', 'spelling-bee-pro'));
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
            return new WP_Error('file_error', __('Could not create final file.', 'spelling-bee-pro'));
        }

        // Reassemble chunks
        for ($i = 0; $i < $total_chunks; $i++) {
            $chunk_file = $chunk_dir . 'chunk_' . str_pad($i, 6, '0', STR_PAD_LEFT);

            if (!file_exists($chunk_file)) {
                fclose($final_file);
                unlink($final_path);
                return new WP_Error('chunk_missing', __('Missing file chunk.', 'spelling-bee-pro'));
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
            return new WP_Error('validation_failed', __('File validation failed.', 'spelling-bee-pro'));
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

    /**
     * Handle sending dashboard link via email
     */
    public function handleSendDashboardLink() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'osb_dashboard_login')) {
            wp_send_json_error(__('Security check failed.', 'spelling-bee-pro'));
        }

        $email = sanitize_email($_POST['email'] ?? '');

        if (empty($email)) {
            wp_send_json_error(__('Email address is required.', 'spelling-bee-pro'));
        }

        // Find school by email
        $db = OSB_Database::getInstance();
        global $wpdb;

        $table_name = $wpdb->prefix . OSB_TABLE_PREFIX . 'schools';
        $school = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE contact_email = %s",
            $email
        ));

        if (!$school) {
            wp_send_json_error(__('No registration found for this email address.', 'spelling-bee-pro'));
        }

        // Get registration token
        $registrations_table = $wpdb->prefix . OSB_TABLE_PREFIX . 'registrations';
        $registration = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$registrations_table} WHERE school_id = %d ORDER BY created_at DESC LIMIT 1",
            $school->id
        ));

        if (!$registration) {
            wp_send_json_error(__('No active registration found for this school.', 'spelling-bee-pro'));
        }

        // Send dashboard link email
        $dashboard_url = home_url('/spellingbee-dashboard/?token=' . $registration->registration_token);

        $subject = __('Access Your SpellingBee Dashboard', 'spelling-bee-pro');

        $message = sprintf(
            __('Hello %s,\n\nYou can access your SpellingBee Dashboard using the link below:\n\n%s\n\nYour registration token is: %s\n\nIf you have any questions, please contact us.\n\nBest regards,\nSpellingBee Team', 'spelling-bee-pro'),
            $school->contact_person,
            $dashboard_url,
            $registration->registration_token
        );

        $email_handler = OSB_Email_Handler::getInstance();
        $sent = $email_handler->sendEmail($email, $subject, $message);

        if ($sent) {
            wp_send_json_success(__('Dashboard link sent successfully! Please check your email.', 'spelling-bee-pro'));
        } else {
            wp_send_json_error(__('Failed to send email. Please try again or contact support.', 'spelling-bee-pro'));
        }
    }

    /**
     * Handle document upload for schools
     */
    public function handleDocumentUpload() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'osb_document_upload')) {
            wp_send_json_error(__('Security check failed.', 'spelling-bee-pro'));
        }

        $token = sanitize_text_field($_POST['token'] ?? '');
        $doc_type = sanitize_text_field($_POST['doc_type'] ?? '');

        if (empty($token)) {
            wp_send_json_error(__('Registration token is required.', 'spelling-bee-pro'));
        }

        if (empty($doc_type)) {
            wp_send_json_error(__('Document type is required.', 'spelling-bee-pro'));
        }

        // Verify registration
        $db = OSB_Database::getInstance();
        $registration = $db->getRegistrationByToken($token);

        if (!$registration) {
            wp_send_json_error(__('Invalid registration token.', 'spelling-bee-pro'));
        }

        // Check if files were uploaded
        if (empty($_FILES['documents'])) {
            wp_send_json_error(__('No files were uploaded.', 'spelling-bee-pro'));
        }

        // Use the existing file handler
        $file_handler = OSB_File_Handler::getInstance();
        $uploaded_files = array();
        $upload_errors = array();

        // Handle multiple files
        $files = $_FILES['documents'];
        $file_count = is_array($files['name']) ? count($files['name']) : 1;

        for ($i = 0; $i < $file_count; $i++) {
            // Prepare individual file array
            $individual_file = array(
                'name' => is_array($files['name']) ? $files['name'][$i] : $files['name'],
                'type' => is_array($files['type']) ? $files['type'][$i] : $files['type'],
                'tmp_name' => is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'],
                'error' => is_array($files['error']) ? $files['error'][$i] : $files['error'],
                'size' => is_array($files['size']) ? $files['size'][$i] : $files['size']
            );

            // Upload the file
            $result = $file_handler->uploadFile(
                $individual_file,
                'documents',
                'school_' . $registration->school_id . '/' . $doc_type
            );

            if (is_wp_error($result)) {
                $upload_errors[] = $result->get_error_message();
            } else {
                $uploaded_files[] = $result['url'];

                // Log the upload
                error_log("[OSB] Document uploaded: Type={$doc_type}, School={$registration->school_id}, File={$result['url']}");
            }
        }

        if (!empty($upload_errors)) {
            wp_send_json_error(__('Some files failed to upload: ', 'spelling-bee-pro') . implode(', ', $upload_errors));
        }

        if (empty($uploaded_files)) {
            wp_send_json_error(__('No files were successfully uploaded.', 'spelling-bee-pro'));
        }

        // TODO: Save document information to database
        // For now, we'll just return success

        wp_send_json_success(array(
            'message' => sprintf(__('%d file(s) uploaded successfully.', 'spelling-bee-pro'), count($uploaded_files)),
            'files' => $uploaded_files,
            'doc_type' => $doc_type
        ));
    }

    /**
     * Handle saving students for a school
     */
    public function handleSaveStudents() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'] ?? '', 'osb_save_students')) {
            wp_send_json_error(__('Security check failed.', 'spelling-bee-pro'));
        }

        $token = sanitize_text_field($_POST['token'] ?? '');
        $students = $_POST['students'] ?? array();

        if (empty($token)) {
            wp_send_json_error(__('Registration token is required.', 'spelling-bee-pro'));
        }

        if (empty($students) || !is_array($students)) {
            wp_send_json_error(__('No student data provided.', 'spelling-bee-pro'));
        }

        // Verify registration
        $db = OSB_Database::getInstance();
        $registration = $db->getRegistrationByToken($token);

        if (!$registration) {
            wp_send_json_error(__('Invalid registration token.', 'spelling-bee-pro'));
        }

        // Validate student count
        $student_count = count($students);
        if ($student_count < 3 || $student_count > 5) {
            wp_send_json_error(__('Schools must register between 3-5 students.', 'spelling-bee-pro'));
        }

        global $wpdb;
        $students_table = $wpdb->prefix . OSB_TABLE_PREFIX . 'students';

        // Start transaction
        $wpdb->query('START TRANSACTION');

        try {
            // Delete existing students for this school (we're replacing all)
            $wpdb->delete(
                $students_table,
                array('school_id' => $registration->school_id),
                array('%d')
            );

            // Insert new students
            $saved_students = 0;
            foreach ($students as $student_data) {
                // Validate required fields
                $required_fields = ['first_name', 'last_name', 'age', 'grade', 'date_of_birth'];
                foreach ($required_fields as $field) {
                    if (empty($student_data[$field])) {
                        throw new Exception(sprintf(__('Missing required field: %s', 'spelling-bee-pro'), $field));
                    }
                }

                // Validate age
                $age = intval($student_data['age']);
                if ($age < 13 || $age > 18) {
                    throw new Exception(__('Student age must be between 13 and 18 years.', 'spelling-bee-pro'));
                }

                // Prepare student data
                $insert_data = array(
                    'school_id' => $registration->school_id,
                    'registration_id' => $registration->id,
                    'first_name' => sanitize_text_field($student_data['first_name']),
                    'last_name' => sanitize_text_field($student_data['last_name']),
                    'age' => $age,
                    'grade' => sanitize_text_field($student_data['grade']),
                    'date_of_birth' => sanitize_text_field($student_data['date_of_birth']),
                    'student_id' => !empty($student_data['student_id']) ? sanitize_text_field($student_data['student_id']) : null,
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                );

                $result = $wpdb->insert(
                    $students_table,
                    $insert_data,
                    array('%d', '%d', '%s', '%s', '%d', '%s', '%s', '%s', '%s', '%s')
                );

                if ($result === false) {
                    throw new Exception(__('Failed to save student data.', 'spelling-bee-pro'));
                }

                $saved_students++;
            }

            // Commit transaction
            $wpdb->query('COMMIT');

            // Log the operation
            error_log("[OSB] Students saved: School ID {$registration->school_id}, Count: {$saved_students}");

            wp_send_json_success(array(
                'message' => sprintf(__('%d students saved successfully.', 'spelling-bee-pro'), $saved_students),
                'student_count' => $saved_students
            ));

        } catch (Exception $e) {
            // Rollback transaction
            $wpdb->query('ROLLBACK');

            error_log("[OSB] Error saving students: " . $e->getMessage());
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * Handle adding a single student to registration
     */
    public function handleAddStudent() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'osb_dashboard_nonce')) {
            wp_send_json_error(__('Security check failed.', 'spelling-bee-pro'));
        }

        $token = sanitize_text_field($_POST['token'] ?? '');
        if (empty($token)) {
            wp_send_json_error(__('Registration token is required.', 'spelling-bee-pro'));
        }

        // Verify registration
        $db = OSB_Database::getInstance();
        $registration = $db->getRegistrationByToken($token);

        if (!$registration) {
            wp_send_json_error(__('Invalid registration token.', 'spelling-bee-pro'));
        }

        // Get the event to check max students per school setting
        $event = $db->getEvent($registration->event_id);
        if (!$event) {
            wp_send_json_error(__('Event not found.', 'spelling-bee-pro'));
        }

        // Check current student count for this school
        $current_students = $db->getStudentsBySchool($registration->school_id);
        $current_count = is_array($current_students) ? count($current_students) : 0;
        $max_students = intval($event->max_students_per_school);

        // Validate against max students limit
        if ($current_count >= $max_students) {
            wp_send_json_error(sprintf(__('Maximum students limit reached. This event allows maximum %d students per school.', 'spelling-bee-pro'), $max_students));
        }

        // Validate required fields
        $required_fields = ['first_name', 'last_name', 'grade', 'date_of_birth', 'parent_name', 'parent_email'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error(sprintf(__('Required field missing: %s', 'spelling-bee-pro'), $field));
            }
        }

        // Calculate age from date of birth
        $dob = sanitize_text_field($_POST['date_of_birth']);
        $age = date_diff(date_create($dob), date_create('today'))->y;

        // Check for duplicate student (same first name, last name, and date of birth in the same school)
        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;
        $existing_student = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$table_prefix}students
             WHERE school_id = %d
             AND LOWER(first_name) = LOWER(%s)
             AND LOWER(last_name) = LOWER(%s)
             AND birth_date = %s",
            $registration->school_id,
            sanitize_text_field($_POST['first_name']),
            sanitize_text_field($_POST['last_name']),
            $dob
        ));

        if ($existing_student) {
            wp_send_json_error(__('A student with the same name and date of birth already exists in this school.', 'spelling-bee-pro'));
        }

        // Prepare student data
        $student_data = array(
            'school_id' => $registration->school_id,
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'grade_level' => sanitize_text_field($_POST['grade']),
            'age' => $age,
            'birth_date' => $dob,
            'parent_name' => sanitize_text_field($_POST['parent_name']),
            'parent_email' => sanitize_email($_POST['parent_email']),
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        );

        // Handle file uploads
        $file_handler = OSB_File_Handler::getInstance();
        $uploaded_files = array();
        $file_results = array();

        // Birth certificate
        if (isset($_FILES['birth_certificate']) && $_FILES['birth_certificate']['error'] === UPLOAD_ERR_OK) {
            $birth_cert_result = $file_handler->uploadFile($_FILES['birth_certificate'], 'documents', 'birth_certificates');
            if (is_wp_error($birth_cert_result)) {
                wp_send_json_error(__('Birth certificate upload failed: ', 'spelling-bee-pro') . $birth_cert_result->get_error_message());
            }
            $uploaded_files['birth_certificate'] = $birth_cert_result['url'];
            $file_results['birth_certificate'] = $birth_cert_result;
        }

        // Parental consent
        if (isset($_FILES['parental_consent']) && $_FILES['parental_consent']['error'] === UPLOAD_ERR_OK) {
            $consent_result = $file_handler->uploadFile($_FILES['parental_consent'], 'documents', 'parental_consent');
            if (is_wp_error($consent_result)) {
                wp_send_json_error(__('Parental consent upload failed: ', 'spelling-bee-pro') . $consent_result->get_error_message());
            }
            $uploaded_files['parental_consent'] = $consent_result['url'];
            $file_results['parental_consent'] = $consent_result;
        }

        try {
            // Add student to database
            $student_id = $db->createStudent($student_data);

            if ($student_id) {
                // If we have uploaded files, create document records
                if (!empty($uploaded_files)) {
                    foreach ($uploaded_files as $doc_type => $file_url) {
                        $file_result = isset($file_results[$doc_type]) ? $file_results[$doc_type] : null;

                        $document_data = array(
                            'registration_id' => $registration->id,
                            'student_id' => $student_id,
                            'document_type' => $doc_type,
                            'file_name' => $file_result ? $file_result['filename'] : basename($file_url),
                            'file_path' => $file_url,
                            'file_size' => $file_result ? $file_result['size'] : 0,
                            'mime_type' => $file_result ? $this->getMimeTypeFromExtension($file_result['type']) : 'application/pdf',
                            'upload_date' => current_time('mysql'),
                            'is_verified' => 0
                        );

                        $db->addDocument($document_data);
                    }
                }

                wp_send_json_success(array(
                    'message' => __('Student added successfully!', 'spelling-bee-pro'),
                    'student_id' => $student_id
                ));
            } else {
                wp_send_json_error(__('Failed to add student to database.', 'spelling-bee-pro'));
            }

        } catch (Exception $e) {
            error_log("[OSB] Error adding student: " . $e->getMessage());
            wp_send_json_error(__('Error adding student: ', 'spelling-bee-pro') . $e->getMessage());
        }
    }

    /**
     * Handle getting a single student data for editing
     */
    public function handleGetStudent() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'osb_dashboard_nonce')) {
            wp_send_json_error(__('Security check failed.', 'spelling-bee-pro'));
        }

        $token = sanitize_text_field($_POST['token'] ?? '');
        if (empty($token)) {
            wp_send_json_error(__('Registration token is required.', 'spelling-bee-pro'));
        }

        $student_id = intval($_POST['student_id'] ?? 0);
        if (!$student_id) {
            wp_send_json_error(__('Student ID is required.', 'spelling-bee-pro'));
        }

        // Verify registration
        $db = OSB_Database::getInstance();
        $registration = $db->getRegistrationByToken($token);

        if (!$registration) {
            wp_send_json_error(__('Invalid registration token.', 'spelling-bee-pro'));
        }

        // Get student and verify it belongs to this school
        $student = $db->getStudent($student_id);
        if (!$student || $student->school_id != $registration->school_id) {
            wp_send_json_error(__('Student not found or access denied.', 'spelling-bee-pro'));
        }

        wp_send_json_success($student);
    }

    /**
     * Handle updating a student's information
     */
    public function handleUpdateStudent() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'osb_dashboard_nonce')) {
            wp_send_json_error(__('Security check failed.', 'spelling-bee-pro'));
        }

        $token = sanitize_text_field($_POST['token'] ?? '');
        if (empty($token)) {
            wp_send_json_error(__('Registration token is required.', 'spelling-bee-pro'));
        }

        $student_id = intval($_POST['student_id'] ?? 0);
        if (!$student_id) {
            wp_send_json_error(__('Student ID is required.', 'spelling-bee-pro'));
        }

        // Verify registration
        $db = OSB_Database::getInstance();
        $registration = $db->getRegistrationByToken($token);

        if (!$registration) {
            wp_send_json_error(__('Invalid registration token.', 'spelling-bee-pro'));
        }

        // Get student and verify it belongs to this school
        $student = $db->getStudent($student_id);
        if (!$student || $student->school_id != $registration->school_id) {
            wp_send_json_error(__('Student not found or access denied.', 'spelling-bee-pro'));
        }

        // Validate required fields
        $required_fields = ['first_name', 'last_name', 'grade_level', 'birth_date', 'parent_name', 'parent_email'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error(sprintf(__('Required field missing: %s', 'spelling-bee-pro'), $field));
            }
        }

        // Calculate age from date of birth
        $dob = sanitize_text_field($_POST['birth_date']);
        $age = date_diff(date_create($dob), date_create('today'))->y;

        // Check for duplicate student (same first name, last name, and date of birth in the same school, excluding current student)
        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;
        $existing_student = $wpdb->get_row($wpdb->prepare(
            "SELECT id FROM {$table_prefix}students
             WHERE school_id = %d
             AND LOWER(first_name) = LOWER(%s)
             AND LOWER(last_name) = LOWER(%s)
             AND birth_date = %s
             AND id != %d",
            $registration->school_id,
            sanitize_text_field($_POST['first_name']),
            sanitize_text_field($_POST['last_name']),
            $dob,
            $student_id
        ));

        if ($existing_student) {
            wp_send_json_error(__('A student with the same name and date of birth already exists in this school.', 'spelling-bee-pro'));
        }

        // Prepare updated student data
        $student_data = array(
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'grade_level' => sanitize_text_field($_POST['grade_level']),
            'age' => $age,
            'birth_date' => $dob,
            'parent_name' => sanitize_text_field($_POST['parent_name']),
            'parent_email' => sanitize_email($_POST['parent_email']),
            'updated_at' => current_time('mysql')
        );

        try {
            // Handle file uploads if provided
            $file_handler = OSB_File_Handler::getInstance();
            $uploaded_files = array();
            $file_results = array();

            // Birth certificate
            if (isset($_FILES['birth_certificate']) && $_FILES['birth_certificate']['error'] === UPLOAD_ERR_OK) {
                $birth_cert_result = $file_handler->uploadFile($_FILES['birth_certificate'], 'documents', 'birth_certificates');
                if (is_wp_error($birth_cert_result)) {
                    wp_send_json_error(__('Birth certificate upload failed: ', 'spelling-bee-pro') . $birth_cert_result->get_error_message());
                }
                $uploaded_files['birth_certificate'] = $birth_cert_result['url'];
                $file_results['birth_certificate'] = $birth_cert_result;
            }

            // Parental consent
            if (isset($_FILES['parental_consent']) && $_FILES['parental_consent']['error'] === UPLOAD_ERR_OK) {
                $consent_result = $file_handler->uploadFile($_FILES['parental_consent'], 'documents', 'parental_consent');
                if (is_wp_error($consent_result)) {
                    wp_send_json_error(__('Parental consent upload failed: ', 'spelling-bee-pro') . $consent_result->get_error_message());
                }
                $uploaded_files['parental_consent'] = $consent_result['url'];
                $file_results['parental_consent'] = $consent_result;
            }

            // Update student in database
            $result = $db->updateStudent($student_id, $student_data);

            if ($result) {
                // If we have uploaded files, update or create document records
                if (!empty($uploaded_files)) {
                    global $wpdb;
                    $registration = $db->getRegistrationByToken(sanitize_text_field($_POST['token']));

                    foreach ($uploaded_files as $doc_type => $file_url) {
                        $file_result = isset($file_results[$doc_type]) ? $file_results[$doc_type] : null;

                        // Check if document record exists
                        $existing_doc = $wpdb->get_row($wpdb->prepare(
                            "SELECT id FROM {$wpdb->prefix}" . OSB_TABLE_PREFIX . "documents
                             WHERE student_id = %d AND document_type = %s",
                            $student_id, $doc_type
                        ));

                        $document_data = array(
                            'registration_id' => $registration->id,
                            'student_id' => $student_id,
                            'document_type' => $doc_type,
                            'file_name' => $file_result ? $file_result['filename'] : basename($file_url),
                            'file_path' => $file_url,
                            'file_size' => $file_result ? $file_result['size'] : 0,
                            'mime_type' => $file_result ? $this->getMimeTypeFromExtension($file_result['type']) : 'application/pdf',
                            'upload_date' => current_time('mysql'),
                            'is_verified' => 0
                        );

                        if ($existing_doc) {
                            // Update existing document
                            $wpdb->update(
                                $wpdb->prefix . OSB_TABLE_PREFIX . 'documents',
                                $document_data,
                                array('id' => $existing_doc->id)
                            );
                        } else {
                            // Create new document record
                            $db->addDocument($document_data);
                        }
                    }
                }

                wp_send_json_success(array(
                    'message' => __('Student updated successfully!', 'spelling-bee-pro'),
                    'student_id' => $student_id,
                    'files_updated' => !empty($uploaded_files) ? count($uploaded_files) : 0
                ));
            } else {
                wp_send_json_error(__('Failed to update student in database.', 'spelling-bee-pro'));
            }

        } catch (Exception $e) {
            error_log("[OSB] Error updating student: " . $e->getMessage());
            wp_send_json_error(__('Error updating student: ', 'spelling-bee-pro') . $e->getMessage());
        }
    }

    /**
     * Handle deleting a student
     */
    public function handleDeleteStudent() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'osb_dashboard_nonce')) {
            wp_send_json_error(__('Security check failed.', 'spelling-bee-pro'));
        }

        $token = sanitize_text_field($_POST['token'] ?? '');
        if (empty($token)) {
            wp_send_json_error(__('Registration token is required.', 'spelling-bee-pro'));
        }

        $student_id = intval($_POST['student_id'] ?? 0);
        if (!$student_id) {
            wp_send_json_error(__('Student ID is required.', 'spelling-bee-pro'));
        }

        // Verify registration
        $db = OSB_Database::getInstance();
        $registration = $db->getRegistrationByToken($token);

        if (!$registration) {
            wp_send_json_error(__('Invalid registration token.', 'spelling-bee-pro'));
        }

        // Get student and verify it belongs to this school
        $student = $db->getStudent($student_id);
        if (!$student || $student->school_id != $registration->school_id) {
            wp_send_json_error(__('Student not found or access denied.', 'spelling-bee-pro'));
        }

        try {
            // Use the existing admin deletion logic temporarily until database method is deployed
            global $wpdb;
            $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

            // Start transaction
            $wpdb->query('START TRANSACTION');

            // Get student data first for logging
            $student_data = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$table_prefix}students WHERE id = %d",
                $student_id
            ));

            // Delete related documents first
            $documents_deleted = $wpdb->delete(
                $table_prefix . 'documents',
                array('student_id' => $student_id),
                array('%d')
            );

            // Delete related WordPress user if exists and has student role
            if (!empty($student_data->wp_user_id)) {
                $wp_user = get_userdata($student_data->wp_user_id);
                if ($wp_user && in_array('student', $wp_user->roles)) {
                    wp_delete_user($student_data->wp_user_id);
                }
            }

            // Delete the student record
            $student_deleted = $wpdb->delete(
                $table_prefix . 'students',
                array('id' => $student_id),
                array('%d')
            );

            if ($student_deleted === false) {
                throw new Exception('Failed to delete student record');
            }

            // Commit transaction
            $wpdb->query('COMMIT');

            // Log the deletion
            if ($student_data) {
                error_log('[OSB] Student deleted via dashboard: ' . $student_data->first_name . ' ' . $student_data->last_name . ' (ID: ' . $student_id . ')');
            }

            wp_send_json_success(array(
                'message' => __('Student removed successfully!', 'spelling-bee-pro'),
                'student_id' => $student_id
            ));

        } catch (Exception $e) {
            // Rollback transaction
            $wpdb->query('ROLLBACK');
            error_log("[OSB] Error deleting student: " . $e->getMessage());
            wp_send_json_error(__('Error deleting student: ', 'spelling-bee-pro') . $e->getMessage());
        }
    }

    /**
     * Get MIME type from file extension
     */
    private function getMimeTypeFromExtension($extension) {
        $mime_types = array(
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif'
        );

        return isset($mime_types[$extension]) ? $mime_types[$extension] : 'application/octet-stream';
    }

    /**
     * Handle competition selection for new dashboard workflow
     */
    public function handleCompetitionSelection() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'osb_dashboard_nonce')) {
            wp_send_json_error(__('Security check failed.', 'spelling-bee-pro'));
        }

        $event_id = intval($_POST['event_id']);
        if (!$event_id) {
            wp_send_json_error(__('Invalid event selected.', 'spelling-bee-pro'));
        }

        $token = sanitize_text_field($_POST['token'] ?? $_GET['token'] ?? '');
        if (!$token) {
            wp_send_json_error(__('Authentication token required.', 'spelling-bee-pro'));
        }

        $db = OSB_Database::getInstance();

        // First try to get existing registration by token
        $registration = $db->getRegistrationByToken($token);

        if ($registration) {
            // Update existing registration with selected event
            $updated = $db->updateRegistration($registration->id, array(
                'event_id' => $event_id,
                'updated_at' => current_time('mysql')
            ));

            if ($updated) {
                wp_send_json_success(array(
                    'message' => __('Competition selected successfully.', 'spelling-bee-pro'),
                    'registration_token' => $registration->registration_token,
                    'redirect_url' => home_url('/spellingbee-dashboard/?token=' . $registration->registration_token)
                ));
            } else {
                wp_send_json_error(__('Failed to update registration.', 'spelling-bee-pro'));
            }
        } else {
            // Check if it's a temporary school token and create new registration
            $school = $db->getSchoolByTempToken($token);

            if ($school) {
                // Create new registration record
                $registration_data = array(
                    'event_id' => $event_id,
                    'school_id' => $school->id,
                    'status' => 'pending',
                    'created_at' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                );

                $registration_id = $db->createRegistration($registration_data);

                if ($registration_id) {
                    // Get the registration with generated token
                    $new_registration = $db->getRegistrationById($registration_id);

                    wp_send_json_success(array(
                        'message' => __('Competition selected successfully. Registration created.', 'spelling-bee-pro'),
                        'registration_token' => $new_registration->registration_token,
                        'redirect_url' => home_url('/spellingbee-dashboard/?token=' . $new_registration->registration_token)
                    ));
                } else {
                    wp_send_json_error(__('Failed to create registration.', 'spelling-bee-pro'));
                }
            } else {
                wp_send_json_error(__('Invalid authentication token.', 'spelling-bee-pro'));
            }
        }
    }

    /**
     * Handle final registration submission
     */
    public function handleFinalRegistrationSubmission() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'osb_dashboard_nonce')) {
            wp_send_json_error(__('Security check failed.', 'spelling-bee-pro'));
        }

        // Get current registration
        $token = sanitize_text_field($_POST['token'] ?? $_GET['token'] ?? '');
        if (!$token) {
            wp_send_json_error(__('Registration token required.', 'spelling-bee-pro'));
        }

        $db = OSB_Database::getInstance();
        $registration = $db->getRegistrationByToken($token);

        if (!$registration) {
            wp_send_json_error(__('Invalid registration token.', 'spelling-bee-pro'));
        }

        // Update registration status to submitted
        $updated = $db->updateRegistration($registration->id, array(
            'status' => 'submitted',
            'submitted_at' => current_time('mysql'),
            'updated_at' => current_time('mysql')
        ));

        if ($updated) {
            // Send notification email to admin
            try {
                $email_handler = OSB_Email_Handler::getInstance();
                $email_handler->sendStatusUpdate($registration->id, 'submitted', 'pending');
            } catch (Exception $e) {
                error_log('OSB: Failed to send submission notification email: ' . $e->getMessage());
            }

            wp_send_json_success(array('message' => __('Registration submitted successfully for admin review.', 'spelling-bee-pro')));
        } else {
            wp_send_json_error(__('Failed to submit registration.', 'spelling-bee-pro'));
        }
    }

    /**
     * Handle EOI document upload
     */
    public function handleEoiDocumentUpload() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'osb_dashboard_nonce')) {
            wp_send_json_error(__('Security check failed.', 'spelling-bee-pro'));
        }

        $token = sanitize_text_field($_POST['token'] ?? '');
        if (!$token) {
            wp_send_json_error(__('Authentication token required.', 'spelling-bee-pro'));
        }

        // Get registration
        $db = OSB_Database::getInstance();
        $registration = $db->getRegistrationByToken($token);

        if (!$registration) {
            wp_send_json_error(__('Invalid registration token.', 'spelling-bee-pro'));
        }

        // Handle file upload
        if (!isset($_FILES['eoi_document']) || $_FILES['eoi_document']['error'] !== UPLOAD_ERR_OK) {
            wp_send_json_error(__('No file uploaded or upload error.', 'spelling-bee-pro'));
        }

        $file = $_FILES['eoi_document'];

        // Validate file type
        $allowed_types = array('application/pdf');
        $file_type = wp_check_filetype($file['name']);
        if (!in_array($file_type['type'], $allowed_types)) {
            wp_send_json_error(__('Only PDF files are allowed.', 'spelling-bee-pro'));
        }

        // Validate file size (5MB max)
        if ($file['size'] > 5 * 1024 * 1024) {
            wp_send_json_error(__('File size too large. Maximum 5MB allowed.', 'spelling-bee-pro'));
        }

        // Handle the upload
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');

        $upload_overrides = array('test_form' => false);
        $uploaded_file = wp_handle_upload($file, $upload_overrides);

        if (isset($uploaded_file['error'])) {
            wp_send_json_error(__('Upload failed: ', 'spelling-bee-pro') . $uploaded_file['error']);
        }

        // Update registration with EOI document
        $updated = $db->updateRegistration($registration->id, array(
            'expression_of_interest' => $uploaded_file['url'],
            'updated_at' => current_time('mysql')
        ));

        if ($updated) {
            wp_send_json_success(array(
                'message' => __('Expression of Interest uploaded successfully.', 'spelling-bee-pro'),
                'file_url' => $uploaded_file['url']
            ));
        } else {
            wp_send_json_error(__('Failed to save upload information.', 'spelling-bee-pro'));
        }
    }

    /**
     * Handle template download
     */
    public function handleTemplateDownload() {
        // Verify nonce
        if (!wp_verify_nonce($_GET['nonce'], 'osb_download_template')) {
            wp_die(__('Security check failed.', 'spelling-bee-pro'));
        }

        $template = sanitize_text_field($_GET['template'] ?? '');
        if (!$template) {
            wp_die(__('Template not specified.', 'spelling-bee-pro'));
        }

        // Define available templates
        $templates = array(
            'expression-of-interest' => array(
                'name' => 'Expression of Interest Form',
                'filename' => 'expression-of-interest-form.pdf'
            ),
            'parental-consent' => array(
                'name' => 'Parental Consent Form',
                'filename' => 'parental-consent-form.pdf'
            )
        );

        if (!isset($templates[$template])) {
            wp_die(__('Invalid template requested.', 'spelling-bee-pro'));
        }

        $template_info = $templates[$template];
        $plugin_dir = plugin_dir_path(dirname(__FILE__));
        $template_path = $plugin_dir . 'templates/documents/' . $template_info['filename'];

        // Check if template file exists
        if (!file_exists($template_path)) {
            // Create a simple PDF placeholder for now
            $this->generatePlaceholderPdf($template_info['name'], $template_info['filename']);
            return;
        }

        // Serve the file
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $template_info['filename'] . '"');
        header('Content-Length: ' . filesize($template_path));
        readfile($template_path);
        exit;
    }

    /**
     * Generate placeholder PDF for templates
     */
    private function generatePlaceholderPdf($template_name, $filename) {
        // Create a simple text response for now
        header('Content-Type: application/pdf');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        // Simple PDF content (this is a placeholder - in production you'd use a proper PDF library)
        $pdf_content = '%PDF-1.4
1 0 obj
<<
/Type /Catalog
/Pages 2 0 R
>>
endobj

2 0 obj
<<
/Type /Pages
/Kids [3 0 R]
/Count 1
>>
endobj

3 0 obj
<<
/Type /Page
/Parent 2 0 R
/MediaBox [0 0 612 792]
/Contents 4 0 R
>>
endobj

4 0 obj
<<
/Length 44
>>
stream
BT
/F1 12 Tf
72 720 Td
(' . $template_name . ') Tj
ET
endstream
endobj

xref
0 5
0000000000 65535 f
0000000009 00000 n
0000000058 00000 n
0000000115 00000 n
0000000202 00000 n
trailer
<<
/Size 5
/Root 1 0 R
>>
startxref
296
%%EOF';

        echo $pdf_content;
        exit;
    }
}