<?php
/**
 * Enhanced Expression of Interest Form Processor
 * Handles digital EOI form submission, validation, and storage
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class OSB_EOI_Processor {

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

        // Hook into WordPress actions
        add_action('wp_ajax_osb_submit_enhanced_eoi', array($this, 'handleEOISubmission'));
        add_action('wp_ajax_nopriv_osb_submit_enhanced_eoi', array($this, 'handleEOISubmission'));
        add_action('wp_ajax_osb_autosave_eoi', array($this, 'handleAutoSave'));
        add_action('wp_ajax_nopriv_osb_autosave_eoi', array($this, 'handleAutoSave'));
    }

    /**
     * Handle Enhanced EOI Form Submission
     */
    public function handleEOISubmission() {
        try {
            // Verify nonce
            if (!wp_verify_nonce($_POST['osb_eoi_nonce'], 'osb_submit_eoi')) {
                throw new Exception('Security verification failed. Please refresh the page and try again.');
            }

            // Verify reCAPTCHA
            if (!$this->verifyRecaptcha()) {
                throw new Exception('reCAPTCHA verification failed. Please try again.');
            }

            // Validate and sanitize form data
            $form_data = $this->validateAndSanitizeFormData($_POST);

            // Validate signature
            if (empty($form_data['digital_signature'])) {
                throw new Exception('Digital signature is required.');
            }

            // Validate signature format
            if (!$this->isValidSignatureData($form_data['digital_signature'])) {
                throw new Exception('Invalid signature format.');
            }

            // Get registration and school data
            $registration_id = intval($_POST['registration_id']);
            $school_id = intval($_POST['school_id']);

            if (!$registration_id && !$school_id) {
                throw new Exception('Invalid registration or school ID.');
            }

            // Process the submission
            $result = $this->processEOISubmission($form_data, $registration_id, $school_id);

            if ($result['success']) {
                // Send confirmation email
                $this->sendConfirmationEmail($result['registration_id'], $form_data);

                // Log admin notification
                $this->logAdminNotification($result['registration_id'], 'eoi_submitted');

                wp_send_json_success(array(
                    'message' => 'Expression of Interest submitted successfully!',
                    'registration_id' => $result['registration_id'],
                    'redirect_url' => $this->getSuccessRedirectUrl($result['registration_id'])
                ));
            } else {
                throw new Exception($result['message'] ?? 'Submission failed. Please try again.');
            }

        } catch (Exception $e) {
            error_log('EOI Submission Error: ' . $e->getMessage());
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * Handle Auto-save functionality
     */
    public function handleAutoSave() {
        try {
            // Verify nonce
            if (!wp_verify_nonce($_POST['nonce'], 'osb_submit_eoi')) {
                throw new Exception('Security verification failed.');
            }

            $form_data = json_decode(stripslashes($_POST['form_data']), true);
            if (!$form_data) {
                throw new Exception('Invalid form data.');
            }

            // Get registration/school ID from form data
            $registration_id = intval($form_data['registration_id'] ?? 0);
            $school_id = intval($form_data['school_id'] ?? 0);

            if (!$registration_id && !$school_id) {
                throw new Exception('No valid registration found for auto-save.');
            }

            // Save auto-save data
            $success = $this->saveAutoSaveData($form_data, $registration_id, $school_id);

            if ($success) {
                wp_send_json_success(array('message' => 'Form auto-saved successfully.'));
            } else {
                throw new Exception('Auto-save failed.');
            }

        } catch (Exception $e) {
            error_log('EOI Auto-save Error: ' . $e->getMessage());
            wp_send_json_error($e->getMessage());
        }
    }

    /**
     * Validate and sanitize form data
     */
    private function validateAndSanitizeFormData($post_data) {
        $form_data = array();

        // Required fields mapping
        $required_fields = array(
            'school_name' => 'sanitize_text_field',
            'school_address' => 'sanitize_textarea_field',
            'school_phone' => 'sanitize_text_field',
            'school_email' => 'sanitize_email',
            'school_type' => 'sanitize_text_field',
            'admin_title' => 'sanitize_text_field',
            'admin_name' => 'sanitize_text_field',
            'admin_phone' => 'sanitize_text_field',
            'admin_email' => 'sanitize_email',
            'expected_participants' => 'sanitize_text_field',
            'previous_participation' => 'sanitize_text_field',
            'motivation' => 'sanitize_textarea_field',
            'emergency_name' => 'sanitize_text_field',
            'emergency_relationship' => 'sanitize_text_field',
            'emergency_phone' => 'sanitize_text_field',
            'digital_signature' => 'wp_kses_post'
        );

        // Optional fields mapping
        $optional_fields = array(
            'establishment_year' => 'intval',
            'special_requirements' => 'sanitize_textarea_field',
            'emergency_email' => 'sanitize_email',
            'form_completion_time' => 'intval',
            'device_info' => 'sanitize_text_field'
        );

        // Terms checkboxes
        $terms_fields = array(
            'terms_competition_rules',
            'terms_student_eligibility',
            'terms_media_consent',
            'terms_data_processing'
        );

        // Validate and sanitize required fields
        foreach ($required_fields as $field => $sanitizer) {
            if (empty($post_data[$field])) {
                throw new Exception("Required field '{$field}' is missing or empty.");
            }

            $value = $sanitizer($post_data[$field]);
            if (empty($value) && $field !== 'digital_signature') {
                throw new Exception("Required field '{$field}' contains invalid data.");
            }

            $form_data[$field] = $value;
        }

        // Validate and sanitize optional fields
        foreach ($optional_fields as $field => $sanitizer) {
            if (!empty($post_data[$field])) {
                $form_data[$field] = $sanitizer($post_data[$field]);
            }
        }

        // Validate terms checkboxes
        foreach ($terms_fields as $field) {
            if (empty($post_data[$field]) || $post_data[$field] !== '1') {
                throw new Exception("You must accept all terms and conditions to proceed.");
            }
            $form_data[$field] = 1;
        }

        // Additional validation
        $this->performAdditionalValidation($form_data);

        return $form_data;
    }

    /**
     * Perform additional validation on form data
     */
    private function performAdditionalValidation($form_data) {
        // Validate email addresses
        if (!is_email($form_data['school_email'])) {
            throw new Exception('School email address is invalid.');
        }

        if (!is_email($form_data['admin_email'])) {
            throw new Exception('Administrator email address is invalid.');
        }

        if (!empty($form_data['emergency_email']) && !is_email($form_data['emergency_email'])) {
            throw new Exception('Emergency contact email address is invalid.');
        }

        // Validate phone numbers (basic validation)
        $phone_fields = array('school_phone', 'admin_phone', 'emergency_phone');
        foreach ($phone_fields as $field) {
            if (!empty($form_data[$field])) {
                $phone = preg_replace('/[^\d+\-\(\)\s]/', '', $form_data[$field]);
                if (strlen($phone) < 10) {
                    throw new Exception("Phone number in '{$field}' appears to be too short.");
                }
            }
        }

        // Validate school type
        $valid_school_types = array('public', 'private', 'international', 'religious');
        if (!in_array($form_data['school_type'], $valid_school_types)) {
            throw new Exception('Invalid school type selected.');
        }

        // Validate expected participants
        $valid_participant_ranges = array('1-3', '4-6', '7-10', '11-15', '16+');
        if (!in_array($form_data['expected_participants'], $valid_participant_ranges)) {
            throw new Exception('Invalid expected participants range selected.');
        }

        // Validate motivation length
        if (strlen($form_data['motivation']) < 20) {
            throw new Exception('Motivation statement must be at least 20 characters long.');
        }

        // Validate establishment year if provided
        if (!empty($form_data['establishment_year'])) {
            $current_year = date('Y');
            if ($form_data['establishment_year'] < 1800 || $form_data['establishment_year'] > $current_year) {
                throw new Exception('Establishment year must be between 1800 and ' . $current_year . '.');
            }
        }
    }

    /**
     * Validate signature data format
     */
    private function isValidSignatureData($signature_data) {
        // Check if it's a valid base64 data URL for PNG image
        if (!preg_match('/^data:image\/png;base64,/', $signature_data)) {
            return false;
        }

        // Extract base64 data
        $base64_data = substr($signature_data, strpos($signature_data, ',') + 1);

        // Validate base64 format
        if (!base64_decode($base64_data, true)) {
            return false;
        }

        // Check signature size (should be reasonable)
        $decoded_size = strlen(base64_decode($base64_data));
        if ($decoded_size < 1000 || $decoded_size > 500000) { // 1KB to 500KB
            return false;
        }

        return true;
    }

    /**
     * Verify Google reCAPTCHA
     */
    private function verifyRecaptcha() {
        if (empty($_POST['g-recaptcha-response'])) {
            return false;
        }

        $recaptcha_secret = get_option('osb_recaptcha_secret_key');
        if (empty($recaptcha_secret)) {
            // If reCAPTCHA is not configured, skip verification
            error_log('reCAPTCHA secret key not configured, skipping verification');
            return true;
        }

        $response = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array(
            'body' => array(
                'secret' => $recaptcha_secret,
                'response' => $_POST['g-recaptcha-response'],
                'remoteip' => $_SERVER['REMOTE_ADDR']
            )
        ));

        if (is_wp_error($response)) {
            error_log('reCAPTCHA verification failed: ' . $response->get_error_message());
            return false;
        }

        $result = json_decode(wp_remote_retrieve_body($response), true);
        return isset($result['success']) && $result['success'] === true;
    }

    /**
     * Process EOI submission and save to database
     */
    private function processEOISubmission($form_data, $registration_id, $school_id) {
        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        // Start transaction
        $wpdb->query('START TRANSACTION');

        try {
            // Get current timestamp
            $current_time = current_time('mysql');
            $signature_timestamp = current_time('mysql');

            // Prepare signature data
            $signature_data = $form_data['digital_signature'];
            unset($form_data['digital_signature']);

            // Get client IP address
            $ip_address = $this->getClientIPAddress();

            // Prepare device info
            $device_info = !empty($form_data['device_info']) ? $form_data['device_info'] : json_encode(array(
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
                'ip_address' => $ip_address,
                'timestamp' => $current_time
            ));

            if ($registration_id) {
                // Update existing registration
                $update_data = array(
                    'eoi_form_data' => json_encode($form_data),
                    'digital_signature' => $signature_data,
                    'signature_timestamp' => $signature_timestamp,
                    'signature_ip_address' => $ip_address,
                    'submission_device_info' => $device_info,
                    'form_completion_time' => intval($form_data['form_completion_time'] ?? 0),
                    'expression_of_interest' => $current_time,
                    'status' => 'submitted',
                    'updated_at' => $current_time
                );

                $result = $wpdb->update(
                    "{$table_prefix}registrations",
                    $update_data,
                    array('id' => $registration_id),
                    array('%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s'),
                    array('%d')
                );

                if ($result === false) {
                    throw new Exception('Failed to update registration: ' . $wpdb->last_error);
                }

                $final_registration_id = $registration_id;

            } else {
                // Create new registration
                $insert_data = array(
                    'school_id' => $school_id,
                    'event_id' => 1, // Default event ID - should be dynamic based on current event
                    'token' => $this->generateUniqueToken(),
                    'eoi_form_data' => json_encode($form_data),
                    'digital_signature' => $signature_data,
                    'signature_timestamp' => $signature_timestamp,
                    'signature_ip_address' => $ip_address,
                    'submission_device_info' => $device_info,
                    'form_completion_time' => intval($form_data['form_completion_time'] ?? 0),
                    'expression_of_interest' => $current_time,
                    'status' => 'submitted',
                    'created_at' => $current_time,
                    'updated_at' => $current_time
                );

                $result = $wpdb->insert(
                    "{$table_prefix}registrations",
                    $insert_data,
                    array('%d', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%s', '%s', '%s')
                );

                if ($result === false) {
                    throw new Exception('Failed to create registration: ' . $wpdb->last_error);
                }

                $final_registration_id = $wpdb->insert_id;
            }

            // Update step progress
            $this->updateStepProgress($final_registration_id, 2, true);

            // Commit transaction
            $wpdb->query('COMMIT');

            return array(
                'success' => true,
                'registration_id' => $final_registration_id,
                'message' => 'EOI submitted successfully'
            );

        } catch (Exception $e) {
            // Rollback transaction
            $wpdb->query('ROLLBACK');
            throw $e;
        }
    }

    /**
     * Save auto-save data
     */
    private function saveAutoSaveData($form_data, $registration_id, $school_id) {
        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        $auto_save_data = array(
            'form_data_cache' => json_encode($form_data),
            'auto_save_timestamp' => current_time('mysql')
        );

        if ($registration_id) {
            return $wpdb->update(
                "{$table_prefix}registrations",
                $auto_save_data,
                array('id' => $registration_id),
                array('%s', '%s'),
                array('%d')
            ) !== false;
        } else {
            // For schools without registration yet, we might need to create a temporary record
            // For now, we'll skip auto-save for non-registered schools
            return true;
        }
    }

    /**
     * Update step progress for registration
     */
    private function updateStepProgress($registration_id, $step, $completed) {
        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        // Get current progress
        $current_progress = $wpdb->get_var($wpdb->prepare(
            "SELECT step_progress FROM {$table_prefix}registrations WHERE id = %d",
            $registration_id
        ));

        $progress = $current_progress ? json_decode($current_progress, true) : array();

        // Update step
        $progress["step_{$step}"] = array(
            'completed' => $completed,
            'completed_at' => current_time('mysql')
        );

        // Update database
        return $wpdb->update(
            "{$table_prefix}registrations",
            array(
                'step_progress' => json_encode($progress),
                'last_active_step' => $completed ? $step + 1 : $step
            ),
            array('id' => $registration_id),
            array('%s', '%d'),
            array('%d')
        );
    }

    /**
     * Send confirmation email
     */
    private function sendConfirmationEmail($registration_id, $form_data) {
        try {
            // Get registration data
            $registration = $this->db->getRegistration($registration_id);
            if (!$registration) {
                throw new Exception('Registration not found for email sending');
            }

            // Prepare email data
            $email_data = array(
                'school_name' => $form_data['school_name'],
                'admin_name' => $form_data['admin_name'],
                'admin_email' => $form_data['admin_email'],
                'submission_id' => $registration->token,
                'submission_date' => date('F j, Y \a\t g:i A'),
                'next_steps' => array(
                    'Your submission is now pending admin approval',
                    'You will receive an email notification when approved',
                    'Draw dates and group assignments will be communicated via email/SMS'
                )
            );

            // Send confirmation email
            $this->email_handler->sendEOIConfirmationEmail($form_data['admin_email'], $email_data);

            // Send admin notification email
            $this->email_handler->sendAdminEOINotification($email_data);

        } catch (Exception $e) {
            error_log('Failed to send EOI confirmation email: ' . $e->getMessage());
            // Don't throw exception here as the main submission was successful
        }
    }

    /**
     * Log admin notification
     */
    private function logAdminNotification($registration_id, $notification_type) {
        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        $wpdb->insert(
            "{$table_prefix}admin_notifications",
            array(
                'type' => $notification_type,
                'title' => 'New EOI Submission',
                'message' => "A new Expression of Interest has been submitted and requires review.",
                'data' => json_encode(array(
                    'registration_id' => $registration_id,
                    'action_required' => 'review_and_approve'
                )),
                'priority' => 'medium',
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%s', '%s')
        );
    }

    /**
     * Generate unique token for registration
     */
    private function generateUniqueToken() {
        do {
            $token = 'osb_' . wp_generate_password(12, false);
        } while ($this->db->getRegistrationByToken($token));

        return $token;
    }

    /**
     * Get client IP address
     */
    private function getClientIPAddress() {
        $ip_keys = array('HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_FORWARDED', 'HTTP_FORWARDED_FOR', 'HTTP_FORWARDED', 'REMOTE_ADDR');

        foreach ($ip_keys as $key) {
            if (array_key_exists($key, $_SERVER) === true) {
                foreach (explode(',', $_SERVER[$key]) as $ip) {
                    $ip = trim($ip);
                    if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) !== false) {
                        return $ip;
                    }
                }
            }
        }

        return $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    }

    /**
     * Get success redirect URL
     */
    private function getSuccessRedirectUrl($registration_id) {
        // Return to dashboard with success parameter
        $registration = $this->db->getRegistration($registration_id);
        if ($registration && !empty($registration->token)) {
            return add_query_arg(array(
                'step' => 3,
                'eoi_success' => 1
            ), home_url('/register/') . '?token=' . $registration->token);
        }

        return home_url('/register/');
    }

    /**
     * Get stored EOI data for pre-filling form
     */
    public function getStoredEOIData($registration_id) {
        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        $eoi_data = $wpdb->get_var($wpdb->prepare(
            "SELECT eoi_form_data FROM {$table_prefix}registrations WHERE id = %d",
            $registration_id
        ));

        return $eoi_data ? json_decode($eoi_data, true) : array();
    }

    /**
     * Check if EOI is already submitted
     */
    public function isEOISubmitted($registration_id) {
        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        $signature = $wpdb->get_var($wpdb->prepare(
            "SELECT digital_signature FROM {$table_prefix}registrations WHERE id = %d",
            $registration_id
        ));

        return !empty($signature);
    }
}

// Initialize the processor
OSB_EOI_Processor::getInstance();