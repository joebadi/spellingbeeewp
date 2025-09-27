<?php
/**
 * Admin AJAX Handler Class
 *
 * Handles all AJAX requests from admin interface
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class OSB_Admin_Ajax {

    /**
     * Handle AJAX requests
     */
    public static function handleAjaxRequest() {
        // Verify nonce
        if (!wp_verify_nonce($_POST['nonce'], 'osb_admin_nonce')) {
            wp_send_json_error(__('Security check failed.', 'spelling-bee-pro'));
        }

        $action = sanitize_text_field($_POST['sub_action'] ?? '');

        switch ($action) {
            case 'save_event':
                self::saveEvent();
                break;

            case 'save_school':
                self::saveSchool();
                break;

            case 'save_student':
                self::saveStudent();
                break;

            case 'update_registration_status':
                self::updateRegistrationStatus();
                break;

            case 'process_donation':
                self::processDonation();
                break;

            case 'upload_document':
                self::uploadDocument();
                break;

            case 'save_sponsor':
                self::saveSponsor();
                break;

            case 'resolve_conflict':
                self::resolveConflict();
                break;

            case 'send_bulk_email':
                self::sendBulkEmail();
                break;

            case 'generate_report':
                self::generateReport();
                break;

            case 'save_video':
                self::saveVideo();
                break;

            case 'get_video':
                self::getVideo();
                break;

            case 'get_event':
                self::getEvent();
                break;

            case 'delete_video':
                self::deleteVideo();
                break;

            case 'preview_bulk_operation':
                self::previewBulkOperation();
                break;

            case 'execute_bulk_operation':
                self::executeBulkOperation();
                break;

            case 'approve_school':
                self::approveSchool();
                break;

            case 'bulk_schools_action':
                self::bulkSchoolsAction();
                break;

            case 'get_registration_form_data':
                self::getRegistrationFormData();
                break;

            case 'create_student_wp_user':
                self::createStudentWpUser();
                break;

            case 'bulk_students_action':
                self::bulkStudentsAction();
                break;

            default:
                wp_send_json_error(__('Invalid action.', 'spelling-bee-pro'));
        }
    }

    /**
     * Save event
     */
    private static function saveEvent() {
        if (!current_user_can('osb_manage_events')) {
            wp_send_json_error(__('Permission denied.', 'spelling-bee-pro'));
        }

        $event_id = intval($_POST['event_id'] ?? 0);
        $event_data = array(
            'title' => sanitize_text_field($_POST['title']),
            'description' => sanitize_textarea_field($_POST['description']),
            'year' => intval($_POST['year']),
            'event_date' => sanitize_text_field($_POST['event_date']),
            'event_time' => sanitize_text_field($_POST['event_time']),
            'venue_name' => sanitize_text_field($_POST['venue_name']),
            'venue_address' => sanitize_textarea_field($_POST['venue_address']),
            'status' => sanitize_text_field($_POST['status']),
            'max_students_per_school' => intval($_POST['max_students_per_school']),
            'registration_deadline' => sanitize_text_field($_POST['registration_deadline']),
            'prize_fund_goal' => floatval($_POST['prize_fund_goal']),
            'flyer_url' => esc_url_raw($_POST['flyer_url'])
        );

        $db = OSB_Database::getInstance();

        if ($event_id) {
            $result = $db->updateEvent($event_id, $event_data);
            $message = __('Event updated successfully.', 'spelling-bee-pro');
        } else {
            $result = $db->createEvent($event_data);
            $message = __('Event created successfully.', 'spelling-bee-pro');
            $event_id = $result;
        }

        if ($result) {
            wp_send_json_success(array(
                'message' => $message,
                'event_id' => $event_id
            ));
        } else {
            wp_send_json_error(__('Failed to save event.', 'spelling-bee-pro'));
        }
    }

    /**
     * Save school
     */
    private static function saveSchool() {
        if (!current_user_can('osb_manage_schools')) {
            wp_send_json_error(__('Permission denied.', 'spelling-bee-pro'));
        }

        $school_id = intval($_POST['school_id'] ?? 0);
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
            'contact_phone' => sanitize_text_field($_POST['contact_phone']),
            'status' => sanitize_text_field($_POST['status'])
        );

        $db = OSB_Database::getInstance();

        if ($school_id) {
            $result = $db->updateSchool($school_id, $school_data);
            $message = __('School updated successfully.', 'spelling-bee-pro');
        } else {
            $result = $db->createSchool($school_data);
            $message = __('School created successfully.', 'spelling-bee-pro');
            $school_id = $result;
        }

        if ($result) {
            wp_send_json_success(array(
                'message' => $message,
                'school_id' => $school_id
            ));
        } else {
            wp_send_json_error(__('Failed to save school.', 'spelling-bee-pro'));
        }
    }

    /**
     * Save student
     */
    private static function saveStudent() {
        if (!current_user_can('osb_manage_students')) {
            wp_send_json_error(__('Permission denied.', 'spelling-bee-pro'));
        }

        $student_id = intval($_POST['student_id'] ?? 0);
        $student_data = array(
            'school_id' => intval($_POST['school_id']),
            'first_name' => sanitize_text_field($_POST['first_name']),
            'last_name' => sanitize_text_field($_POST['last_name']),
            'date_of_birth' => sanitize_text_field($_POST['date_of_birth']),
            'grade_level' => sanitize_text_field($_POST['grade_level']),
            'gender' => sanitize_text_field($_POST['gender']),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']),
            'parent_name' => sanitize_text_field($_POST['parent_name']),
            'parent_email' => sanitize_email($_POST['parent_email']),
            'parent_phone' => sanitize_text_field($_POST['parent_phone']),
            'emergency_contact_name' => sanitize_text_field($_POST['emergency_contact_name']),
            'emergency_contact_phone' => sanitize_text_field($_POST['emergency_contact_phone']),
            'medical_conditions' => sanitize_textarea_field($_POST['medical_conditions']),
            'dietary_restrictions' => sanitize_textarea_field($_POST['dietary_restrictions'])
        );

        $db = OSB_Database::getInstance();

        if ($student_id) {
            $result = $db->updateStudent($student_id, $student_data);
            $message = __('Student updated successfully.', 'spelling-bee-pro');
        } else {
            $result = $db->createStudent($student_data);
            $message = __('Student created successfully.', 'spelling-bee-pro');
            $student_id = $result;
        }

        if ($result) {
            wp_send_json_success(array(
                'message' => $message,
                'student_id' => $student_id
            ));
        } else {
            wp_send_json_error(__('Failed to save student.', 'spelling-bee-pro'));
        }
    }

    /**
     * Update registration status
     */
    private static function updateRegistrationStatus() {
        if (!current_user_can('osb_manage_registrations')) {
            wp_send_json_error(__('Permission denied.', 'spelling-bee-pro'));
        }

        $registration_id = intval($_POST['registration_id']);
        $status = sanitize_text_field($_POST['status']);
        $reason = sanitize_textarea_field($_POST['reason'] ?? '');

        $db = OSB_Database::getInstance();

        // Get current status before update for workflow automation
        $current_registration = $db->getRegistrationById($registration_id);
        $old_status = $current_registration ? $current_registration->status : null;

        $result = $db->updateRegistration($registration_id, array('status' => $status));

        if ($result) {
            // WORKFLOW AUTOMATION - Trigger status change workflow
            do_action('osb_registration_status_changed', $registration_id, $old_status, $status);
            // Send notification email
            $email_handler = OSB_Email_Handler::getInstance();

            if ($status === 'approved') {
                $email_handler->sendApprovalNotification($registration_id);
            } elseif ($status === 'rejected') {
                $email_handler->sendRejectionNotification($registration_id, $reason);
            }

            wp_send_json_success(array(
                'message' => sprintf(__('Registration status updated to %s.', 'spelling-bee-pro'), $status)
            ));
        } else {
            wp_send_json_error(__('Failed to update registration status.', 'spelling-bee-pro'));
        }
    }

    /**
     * Process donation
     */
    private static function processDonation() {
        if (!current_user_can('osb_manage_donations')) {
            wp_send_json_error(__('Permission denied.', 'spelling-bee-pro'));
        }

        $donation_data = array(
            'event_id' => intval($_POST['event_id']),
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
     * Upload document
     */
    private static function uploadDocument() {
        if (!current_user_can('osb_manage_documents')) {
            wp_send_json_error(__('Permission denied.', 'spelling-bee-pro'));
        }

        if (empty($_FILES['document'])) {
            wp_send_json_error(__('No file selected.', 'spelling-bee-pro'));
        }

        $file_handler = OSB_File_Handler::getInstance();
        $upload_type = sanitize_text_field($_POST['upload_type'] ?? 'documents');
        $subfolder = sanitize_text_field($_POST['subfolder'] ?? '');

        $result = $file_handler->uploadFile($_FILES['document'], $upload_type, $subfolder);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success($result);
        }
    }

    /**
     * Save sponsor
     */
    private static function saveSponsor() {
        if (!current_user_can('osb_manage_sponsors')) {
            wp_send_json_error(__('Permission denied.', 'spelling-bee-pro'));
        }

        $sponsor_id = intval($_POST['sponsor_id'] ?? 0);
        $sponsor_data = array(
            'event_id' => intval($_POST['event_id']),
            'name' => sanitize_text_field($_POST['name']),
            'website' => esc_url_raw($_POST['website']),
            'logo_url' => esc_url_raw($_POST['logo_url']),
            'tier' => sanitize_text_field($_POST['tier']),
            'contribution_amount' => floatval($_POST['contribution_amount']),
            'description' => sanitize_textarea_field($_POST['description']),
            'contact_person' => sanitize_text_field($_POST['contact_person']),
            'contact_email' => sanitize_email($_POST['contact_email']),
            'status' => sanitize_text_field($_POST['status'])
        );

        global $wpdb;
        $table_name = $wpdb->prefix . OSB_TABLE_PREFIX . 'sponsors';

        if ($sponsor_id) {
            $result = $wpdb->update(
                $table_name,
                $sponsor_data,
                array('id' => $sponsor_id),
                null,
                array('%d')
            );
            $message = __('Sponsor updated successfully.', 'spelling-bee-pro');
        } else {
            $sponsor_data['created_at'] = current_time('mysql');
            $result = $wpdb->insert($table_name, $sponsor_data);
            $message = __('Sponsor created successfully.', 'spelling-bee-pro');
            $sponsor_id = $wpdb->insert_id;
        }

        if ($result !== false) {
            wp_send_json_success(array(
                'message' => $message,
                'sponsor_id' => $sponsor_id
            ));
        } else {
            wp_send_json_error(__('Failed to save sponsor.', 'spelling-bee-pro'));
        }
    }

    /**
     * Resolve conflict
     */
    private static function resolveConflict() {
        if (!current_user_can('osb_resolve_conflicts')) {
            wp_send_json_error(__('Permission denied.', 'spelling-bee-pro'));
        }

        $conflict_id = intval($_POST['conflict_id']);
        $resolution = sanitize_text_field($_POST['resolution']);

        $user_manager = OSB_User_Manager::getInstance();
        $result = $user_manager->resolveConflict($conflict_id, $resolution);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success(array(
                'message' => __('Conflict resolved successfully.', 'spelling-bee-pro')
            ));
        }
    }

    /**
     * Send bulk email
     */
    private static function sendBulkEmail() {
        if (!current_user_can('osb_send_communications')) {
            wp_send_json_error(__('Permission denied.', 'spelling-bee-pro'));
        }

        $recipients = sanitize_text_field($_POST['recipients']);
        $subject = sanitize_text_field($_POST['subject']);
        $message = wp_kses_post($_POST['message']);
        $event_id = intval($_POST['event_id'] ?? 0);

        $email_handler = OSB_Email_Handler::getInstance();
        $db = OSB_Database::getInstance();

        $email_addresses = array();

        switch ($recipients) {
            case 'all_schools':
                $schools = $db->getAllSchools('active');
                foreach ($schools as $school) {
                    $email_addresses[] = $school->contact_email;
                }
                break;

            case 'event_registrations':
                if ($event_id) {
                    $registrations = $db->getEventRegistrations($event_id, 'approved');
                    foreach ($registrations as $registration) {
                        $email_addresses[] = $registration->contact_email;
                    }
                }
                break;

            case 'custom':
                $custom_emails = sanitize_textarea_field($_POST['custom_emails']);
                $email_addresses = array_map('trim', explode(',', $custom_emails));
                $email_addresses = array_filter($email_addresses, 'is_email');
                break;
        }

        $sent_count = 0;
        foreach ($email_addresses as $email) {
            if ($email_handler->sendEmail($email, $subject, $message)) {
                $sent_count++;
            }
        }

        wp_send_json_success(array(
            'message' => sprintf(__('Sent %d emails successfully.', 'spelling-bee-pro'), $sent_count),
            'sent_count' => $sent_count,
            'total_count' => count($email_addresses)
        ));
    }

    /**
     * Generate report
     */
    private static function generateReport() {
        if (!current_user_can('osb_view_reports')) {
            wp_send_json_error(__('Permission denied.', 'spelling-bee-pro'));
        }

        $report_type = sanitize_text_field($_POST['report_type']);
        $event_id = intval($_POST['event_id'] ?? 0);
        $format = sanitize_text_field($_POST['format'] ?? 'html');

        $db = OSB_Database::getInstance();
        $donation_calc = OSB_Donation_Calculator::getInstance();

        $report_data = array();

        switch ($report_type) {
            case 'event_summary':
                if ($event_id) {
                    $event = $db->getEvent($event_id);
                    $registrations = $db->getEventRegistrations($event_id);
                    $donations = $donation_calc->getDonationStats($event_id);

                    $report_data = array(
                        'event' => $event,
                        'registrations' => $registrations,
                        'donations' => $donations
                    );
                }
                break;

            case 'schools_list':
                $schools = $db->getAllSchools();
                $report_data = array('schools' => $schools);
                break;

            case 'donations_summary':
                if ($event_id) {
                    $donations = $donation_calc->getDonationStats($event_id);
                    $leaderboard = $donation_calc->getDonationLeaderboard($event_id, 20);
                    $report_data = array(
                        'donations' => $donations,
                        'leaderboard' => $leaderboard
                    );
                }
                break;
        }

        if ($format === 'csv') {
            $csv_data = self::generateCSVReport($report_type, $report_data);
            wp_send_json_success(array(
                'format' => 'csv',
                'data' => $csv_data,
                'filename' => $report_type . '_' . date('Y-m-d') . '.csv'
            ));
        } else {
            $html_report = self::generateHTMLReport($report_type, $report_data);
            wp_send_json_success(array(
                'format' => 'html',
                'data' => $html_report
            ));
        }
    }

    /**
     * Generate CSV report
     */
    private static function generateCSVReport($report_type, $data) {
        ob_start();
        $output = fopen('php://output', 'w');

        switch ($report_type) {
            case 'schools_list':
                fputcsv($output, array('School Name', 'Type', 'City', 'Contact Person', 'Email', 'Phone', 'Status'));
                foreach ($data['schools'] as $school) {
                    fputcsv($output, array(
                        $school->school_name,
                        $school->school_type,
                        $school->city,
                        $school->contact_person,
                        $school->contact_email,
                        $school->contact_phone,
                        $school->status
                    ));
                }
                break;

            case 'event_summary':
                fputcsv($output, array('School', 'Status', 'Registration Date', 'Students Count'));
                foreach ($data['registrations'] as $reg) {
                    fputcsv($output, array(
                        $reg->school_name,
                        $reg->status,
                        $reg->created_at,
                        // Would need to count students for this registration
                        ''
                    ));
                }
                break;
        }

        fclose($output);
        return ob_get_clean();
    }

    /**
     * Generate HTML report
     */
    private static function generateHTMLReport($report_type, $data) {
        $html = '<div class="osb-report">';
        $html .= '<h2>' . sprintf(__('%s Report', 'spelling-bee-pro'), ucwords(str_replace('_', ' ', $report_type))) . '</h2>';
        $html .= '<p><strong>' . __('Generated on:', 'spelling-bee-pro') . '</strong> ' . date('F j, Y g:i A') . '</p>';

        switch ($report_type) {
            case 'event_summary':
                if (isset($data['event'])) {
                    $html .= '<h3>' . $data['event']->title . '</h3>';
                    $html .= '<p><strong>' . __('Total Registrations:', 'spelling-bee-pro') . '</strong> ' . count($data['registrations']) . '</p>';
                    $html .= '<p><strong>' . __('Total Donations:', 'spelling-bee-pro') . '</strong> $' . number_format($data['donations']['total_amount'], 2) . '</p>';
                }
                break;

            case 'schools_list':
                $html .= '<table class="widefat">';
                $html .= '<thead><tr><th>' . __('School Name', 'spelling-bee-pro') . '</th><th>' . __('Contact', 'spelling-bee-pro') . '</th><th>' . __('Status', 'spelling-bee-pro') . '</th></tr></thead>';
                $html .= '<tbody>';
                foreach ($data['schools'] as $school) {
                    $html .= '<tr>';
                    $html .= '<td>' . esc_html($school->school_name) . '</td>';
                    $html .= '<td>' . esc_html($school->contact_person) . '<br>' . esc_html($school->contact_email) . '</td>';
                    $html .= '<td>' . esc_html($school->status) . '</td>';
                    $html .= '</tr>';
                }
                $html .= '</tbody></table>';
                break;
        }

        $html .= '</div>';
        return $html;
    }

    /**
     * Save event video
     */
    private static function saveVideo() {
        if (!current_user_can('osb_manage_events')) {
            wp_send_json_error(__('Permission denied.', 'spelling-bee-pro'));
        }

        $video_id = intval($_POST['video_id'] ?? 0);
        $event_id = intval($_POST['event_id'] ?? 0);

        if (!$event_id) {
            wp_send_json_error(__('Event ID is required.', 'spelling-bee-pro'));
        }

        // Map form fields to database columns based on actual schema
        $video_data = array(
            'event_id' => $event_id,
            'title' => sanitize_text_field($_POST['video_title']),
            'video_type' => sanitize_text_field($_POST['video_type']),
            'description' => sanitize_textarea_field($_POST['video_description']),
            'is_active' => isset($_POST['is_active']) ? 1 : 0
        );

        // Handle URL based on video type
        $video_url = esc_url_raw($_POST['video_url']);
        if ($_POST['video_type'] === 'flyer') {
            $video_data['flyer_image'] = $video_url;
        } else {
            $video_data['youtube_url'] = $video_url;
        }

        // Validate required fields
        if (empty($video_data['title']) || empty($video_url)) {
            wp_send_json_error(__('Video title and URL are required.', 'spelling-bee-pro'));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . OSB_TABLE_PREFIX . 'event_videos';

        if ($video_id) {
            // Update existing video
            $result = $wpdb->update(
                $table_name,
                $video_data,
                array('id' => $video_id),
                array('%d', '%s', '%s', '%s', '%s', '%s', '%d'),
                array('%d')
            );
            $message = __('Video updated successfully.', 'spelling-bee-pro');
        } else {
            // Create new video
            $video_data['created_at'] = current_time('mysql');
            $video_data['sort_order'] = 0;

            $result = $wpdb->insert(
                $table_name,
                $video_data,
                array('%d', '%s', '%s', '%s', '%s', '%s', '%d', '%s', '%d')
            );
            $video_id = $wpdb->insert_id;
            $message = __('Video added successfully.', 'spelling-bee-pro');
        }

        if ($result !== false) {
            wp_send_json_success(array(
                'message' => $message,
                'video_id' => $video_id
            ));
        } else {
            // Get last error for debugging
            $error = $wpdb->last_error;
            error_log('Video save error: ' . $error);
            wp_send_json_error(__('Failed to save video. Error: ', 'spelling-bee-pro') . $error);
        }
    }

    /**
     * Get event video data
     */
    private static function getVideo() {
        // Skip permission check for frontend requests to view videos
        $video_id = intval($_POST['video_id'] ?? 0);

        if (!$video_id) {
            wp_send_json_error(__('Video ID is required.', 'spelling-bee-pro'));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . OSB_TABLE_PREFIX . 'event_videos';

        $video = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $video_id
        ));

        if (!$video) {
            wp_send_json_error(__('Video not found.', 'spelling-bee-pro'));
        }

        wp_send_json_success($video);
    }

    /**
     * Delete event video
     */
    private static function deleteVideo() {
        if (!current_user_can('osb_manage_events')) {
            wp_send_json_error(__('Permission denied.', 'spelling-bee-pro'));
        }

        $video_id = intval($_POST['video_id'] ?? 0);

        if (!$video_id) {
            wp_send_json_error(__('Video ID is required.', 'spelling-bee-pro'));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . OSB_TABLE_PREFIX . 'event_videos';

        $result = $wpdb->delete(
            $table_name,
            array('id' => $video_id),
            array('%d')
        );

        if ($result !== false) {
            wp_send_json_success(array(
                'message' => __('Video deleted successfully.', 'spelling-bee-pro')
            ));
        } else {
            wp_send_json_error(__('Failed to delete video.', 'spelling-bee-pro'));
        }
    }

    /**
     * Get event data for frontend display
     */
    private static function getEvent() {
        $event_id = intval($_POST['event_id'] ?? 0);

        if (!$event_id) {
            wp_send_json_error(__('Event ID is required.', 'spelling-bee-pro'));
        }

        global $wpdb;
        $table_prefix = defined('OSB_TABLE_PREFIX') ? OSB_TABLE_PREFIX : 'osb_';
        $events_table = $wpdb->prefix . $table_prefix . 'events';

        $event = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$events_table} WHERE id = %d",
            $event_id
        ));

        if (!$event) {
            wp_send_json_error(__('Event not found.', 'spelling-bee-pro'));
        }

        wp_send_json_success($event);
    }

    /**
     * Preview bulk operation
     */
    private static function previewBulkOperation() {
        if (!current_user_can('osb_manage_registrations')) {
            wp_send_json_error(__('Permission denied.', 'spelling-bee-pro'));
        }

        $action = sanitize_text_field($_POST['bulk_action']);
        $filter = sanitize_text_field($_POST['registration_filter']);

        if (empty($action)) {
            wp_send_json_error(__('Please select an action.', 'spelling-bee-pro'));
        }

        // Get affected registrations based on filter
        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        $where_clause = '';
        switch ($filter) {
            case 'pending':
                $where_clause = "WHERE r.status = 'pending'";
                break;
            case 'documents_submitted':
                $where_clause = "WHERE r.status = 'documents_submitted'";
                break;
            case 'under_review':
                $where_clause = "WHERE r.status = 'under_review'";
                break;
            default:
                wp_send_json_error(__('Invalid filter selected.', 'spelling-bee-pro'));
        }

        $registrations = $wpdb->get_results(
            "SELECT r.id, r.status, s.school_name, r.created_at
             FROM {$table_prefix}registrations r
             LEFT JOIN {$table_prefix}schools s ON r.school_id = s.id
             {$where_clause}
             ORDER BY r.created_at DESC
             LIMIT 50"
        );

        if (empty($registrations)) {
            wp_send_json_error(sprintf(__('No registrations found with status: %s', 'spelling-bee-pro'), $filter));
        }

        $action_description = '';
        switch ($action) {
            case 'approve_all':
                $action_description = __('Mark all selected registrations as approved', 'spelling-bee-pro');
                break;
            case 'mark_documents_submitted':
                $action_description = __('Mark all selected registrations as documents submitted', 'spelling-bee-pro');
                break;
            case 'send_reminder':
                $action_description = __('Send reminder emails to all selected registrations', 'spelling-bee-pro');
                break;
        }

        wp_send_json_success(array(
            'action' => $action,
            'action_description' => $action_description,
            'affected_count' => count($registrations),
            'registrations' => array_slice($registrations, 0, 10), // Show first 10 for preview
            'total_found' => count($registrations)
        ));
    }

    /**
     * Execute bulk operation
     */
    private static function executeBulkOperation() {
        if (!current_user_can('osb_manage_registrations')) {
            wp_send_json_error(__('Permission denied.', 'spelling-bee-pro'));
        }

        if (!wp_verify_nonce($_POST['bulk_nonce'], 'osb_bulk_operation')) {
            wp_send_json_error(__('Security check failed.', 'spelling-bee-pro'));
        }

        $action = sanitize_text_field($_POST['bulk_action']);
        $filter = sanitize_text_field($_POST['registration_filter']);

        if (empty($action)) {
            wp_send_json_error(__('Please select an action.', 'spelling-bee-pro'));
        }

        // Get affected registrations
        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        $where_clause = '';
        switch ($filter) {
            case 'pending':
                $where_clause = "WHERE status = 'pending'";
                break;
            case 'documents_submitted':
                $where_clause = "WHERE status = 'documents_submitted'";
                break;
            case 'under_review':
                $where_clause = "WHERE status = 'under_review'";
                break;
            default:
                wp_send_json_error(__('Invalid filter selected.', 'spelling-bee-pro'));
        }

        $registration_ids = $wpdb->get_col(
            "SELECT id FROM {$table_prefix}registrations {$where_clause}"
        );

        if (empty($registration_ids)) {
            wp_send_json_error(__('No registrations found to process.', 'spelling-bee-pro'));
        }

        // Execute bulk operation using workflow automation
        $workflow_automation = OSB_Workflow_Automation::getInstance();
        $results = array('success' => 0, 'failed' => 0, 'processed' => array());

        foreach ($registration_ids as $registration_id) {
            $success = false;

            switch ($action) {
                case 'approve_all':
                    $db = OSB_Database::getInstance();
                    $current_reg = $db->getRegistrationById($registration_id);
                    if ($current_reg) {
                        $old_status = $current_reg->status;
                        $success = $db->updateRegistration($registration_id, array('status' => 'approved'));
                        if ($success) {
                            do_action('osb_registration_status_changed', $registration_id, $old_status, 'approved');
                        }
                    }
                    break;

                case 'mark_documents_submitted':
                    $db = OSB_Database::getInstance();
                    $current_reg = $db->getRegistrationById($registration_id);
                    if ($current_reg) {
                        $old_status = $current_reg->status;
                        $success = $db->updateRegistration($registration_id, array('status' => 'documents_submitted'));
                        if ($success) {
                            do_action('osb_registration_status_changed', $registration_id, $old_status, 'documents_submitted');
                        }
                    }
                    break;

                case 'send_reminder':
                    $email_handler = OSB_Email_Handler::getInstance();
                    $success = $email_handler->sendDeadlineReminder($registration_id, 'bulk_reminder');
                    break;
            }

            if ($success) {
                $results['success']++;
                $results['processed'][] = $registration_id;
            } else {
                $results['failed']++;
            }
        }

        // Create admin notification for bulk operation
        $workflow_automation->createAdminNotification(
            'bulk_operation',
            sprintf(
                __('Bulk operation completed: %s on %d registrations', 'spelling-bee-pro'),
                $action,
                count($registration_ids)
            ),
            array(
                'action' => $action,
                'filter' => $filter,
                'results' => $results,
                'priority' => 'medium'
            )
        );

        wp_send_json_success(array(
            'message' => sprintf(
                __('Bulk operation completed. %d successful, %d failed out of %d total.', 'spelling-bee-pro'),
                $results['success'],
                $results['failed'],
                count($registration_ids)
            ),
            'results' => $results
        ));
    }

    /**
     * Approve school
     */
    private static function approveSchool() {
        if (!current_user_can('osb_manage_schools')) {
            wp_send_json_error(__('Permission denied.', 'spelling-bee-pro'));
        }

        $school_id = intval($_POST['school_id'] ?? 0);

        if (!$school_id) {
            wp_send_json_error(__('School ID is required.', 'spelling-bee-pro'));
        }

        $db = OSB_Database::getInstance();

        // Get current school status
        $school = $db->getSchool($school_id);
        if (!$school) {
            wp_send_json_error(__('School not found.', 'spelling-bee-pro'));
        }

        $old_status = $school->status;

        // Update school status to approved
        $result = $db->updateSchool($school_id, array('status' => 'approved'));

        if ($result) {
            // Trigger status change workflow
            do_action('osb_school_status_changed', $school_id, $old_status, 'approved');

            // Send notification email if email handler exists
            if (class_exists('OSB_Email_Handler')) {
                $email_handler = OSB_Email_Handler::getInstance();
                $email_handler->sendSchoolApprovalNotification($school_id);
            }

            wp_send_json_success(array(
                'message' => sprintf(__('School "%s" has been approved successfully.', 'spelling-bee-pro'), $school->school_name),
                'school_id' => $school_id,
                'new_status' => 'approved'
            ));
        } else {
            wp_send_json_error(__('Failed to approve school.', 'spelling-bee-pro'));
        }
    }

    /**
     * Handle bulk schools actions
     */
    private static function bulkSchoolsAction() {
        if (!current_user_can('osb_manage_schools')) {
            wp_send_json_error(__('Permission denied.', 'spelling-bee-pro'));
        }

        $action = sanitize_text_field($_POST['bulk_action'] ?? '');
        $school_ids = array_map('intval', $_POST['school_ids'] ?? array());

        if (empty($action)) {
            wp_send_json_error(__('Please select an action.', 'spelling-bee-pro'));
        }

        if (empty($school_ids)) {
            wp_send_json_error(__('Please select at least one school.', 'spelling-bee-pro'));
        }

        $db = OSB_Database::getInstance();
        $results = array('success' => 0, 'failed' => 0, 'processed' => array());

        foreach ($school_ids as $school_id) {
            $school = $db->getSchool($school_id);
            if (!$school) {
                $results['failed']++;
                continue;
            }

            $success = false;
            $old_status = $school->status;

            switch ($action) {
                case 'approve':
                    $success = $db->updateSchool($school_id, array('status' => 'approved'));
                    if ($success) {
                        do_action('osb_school_status_changed', $school_id, $old_status, 'approved');
                    }
                    break;

                case 'reject':
                    $success = $db->updateSchool($school_id, array('status' => 'rejected'));
                    if ($success) {
                        do_action('osb_school_status_changed', $school_id, $old_status, 'rejected');
                    }
                    break;

                case 'pending':
                    $success = $db->updateSchool($school_id, array('status' => 'pending'));
                    if ($success) {
                        do_action('osb_school_status_changed', $school_id, $old_status, 'pending');
                    }
                    break;

                case 'delete':
                    if (current_user_can('osb_manage_schools')) {
                        $success = $db->deleteSchool($school_id);
                        if ($success) {
                            do_action('osb_school_deleted', $school_id, $school);
                        }
                    }
                    break;

                default:
                    wp_send_json_error(__('Invalid bulk action.', 'spelling-bee-pro'));
            }

            if ($success) {
                $results['success']++;
                $results['processed'][] = $school_id;
            } else {
                $results['failed']++;
            }
        }

        // Send notification emails for approved schools
        if ($action === 'approve' && class_exists('OSB_Email_Handler')) {
            $email_handler = OSB_Email_Handler::getInstance();
            foreach ($results['processed'] as $school_id) {
                $email_handler->sendSchoolApprovalNotification($school_id);
            }
        }

        $action_label = '';
        switch ($action) {
            case 'approve':
                $action_label = __('approved', 'spelling-bee-pro');
                break;
            case 'reject':
                $action_label = __('rejected', 'spelling-bee-pro');
                break;
            case 'pending':
                $action_label = __('set to pending', 'spelling-bee-pro');
                break;
            case 'delete':
                $action_label = __('deleted', 'spelling-bee-pro');
                break;
        }

        wp_send_json_success(array(
            'message' => sprintf(
                __('Bulk operation completed. %d schools %s successfully, %d failed out of %d total.', 'spelling-bee-pro'),
                $results['success'],
                $action_label,
                $results['failed'],
                count($school_ids)
            ),
            'results' => $results
        ));
    }

    /**
     * Get registration form data for modal display
     */
    private static function getRegistrationFormData() {
        if (!current_user_can('osb_manage_registrations')) {
            wp_send_json_error(__('Permission denied.', 'spelling-bee-pro'));
        }

        $registration_id = intval($_POST['registration_id']);
        if (!$registration_id) {
            wp_send_json_error(__('Registration ID required.', 'spelling-bee-pro'));
        }

        $db = OSB_Database::getInstance();
        $registration = $db->getRegistrationById($registration_id);

        if (!$registration) {
            wp_send_json_error(__('Registration not found.', 'spelling-bee-pro'));
        }

        // Get school data
        $school = $db->getSchool($registration->school_id);
        if (!$school) {
            wp_send_json_error(__('School not found.', 'spelling-bee-pro'));
        }

        // Get event data
        $event = $db->getEvent($registration->event_id);
        if (!$event) {
            wp_send_json_error(__('Event not found.', 'spelling-bee-pro'));
        }

        // Get students
        $students = $db->getStudentsBySchool($registration->school_id);

        // Get documents
        global $wpdb;
        $documents = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}osb_documents
                 WHERE registration_id = %d
                 ORDER BY upload_date DESC",
                $registration_id
            )
        );

        // Build HTML for modal content with tabbed interface
        ob_start();
        ?>
        <div class="osb-form-data-container">
            <!-- Tab Navigation -->
            <div class="osb-tab-navigation">
                <button type="button" class="osb-tab-button active" data-tab="overview">
                    <span class="osb-tab-icon">📊</span>
                    <span class="osb-tab-label">Overview</span>
                </button>
                <button type="button" class="osb-tab-button" data-tab="school">
                    <span class="osb-tab-icon">🏫</span>
                    <span class="osb-tab-label">School Details</span>
                </button>
                <?php if (!empty($students)): ?>
                <button type="button" class="osb-tab-button" data-tab="students">
                    <span class="osb-tab-icon">👨‍🎓</span>
                    <span class="osb-tab-label">Students (<?php echo count($students); ?>)</span>
                </button>
                <?php endif; ?>
                <?php if (!empty($documents)): ?>
                <button type="button" class="osb-tab-button" data-tab="documents">
                    <span class="osb-tab-icon">📎</span>
                    <span class="osb-tab-label">Documents (<?php echo count($documents); ?>)</span>
                </button>
                <?php endif; ?>
            </div>

            <!-- Tab Content -->
            <div class="osb-tab-content">
                <!-- Overview Tab -->
                <div class="osb-tab-pane active" id="osb-tab-overview">
                    <!-- Event Information -->
                    <div class="osb-form-section">
                        <div class="osb-form-section-header">
                            🏆 Competition Information
                        </div>
                        <div class="osb-form-section-content">
                            <div class="osb-data-grid">
                                <div class="osb-data-item">
                                    <div class="osb-data-label">Competition</div>
                                    <div class="osb-data-value"><?php echo esc_html($event->title); ?></div>
                                </div>
                                <div class="osb-data-item">
                                    <div class="osb-data-label">Year</div>
                                    <div class="osb-data-value"><?php echo esc_html($event->year); ?></div>
                                </div>
                                <div class="osb-data-item">
                                    <div class="osb-data-label">Event Date</div>
                                    <div class="osb-data-value"><?php echo esc_html(date('F j, Y', strtotime($event->event_date))); ?></div>
                                </div>
                                <div class="osb-data-item">
                                    <div class="osb-data-label">Venue</div>
                                    <div class="osb-data-value"><?php echo esc_html($event->venue_name ?: 'Not specified'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Registration Information -->
                    <div class="osb-form-section">
                        <div class="osb-form-section-header">
                            📝 Registration Details
                        </div>
                        <div class="osb-form-section-content">
                            <div class="osb-data-grid">
                                <div class="osb-data-item">
                                    <div class="osb-data-label">Registration ID</div>
                                    <div class="osb-data-value"><?php echo esc_html($registration->id); ?></div>
                                </div>
                                <div class="osb-data-item">
                                    <div class="osb-data-label">Registration Token</div>
                                    <div class="osb-data-value">
                                        <code><?php echo esc_html($registration->registration_token); ?></code>
                                    </div>
                                </div>
                                <div class="osb-data-item">
                                    <div class="osb-data-label">Registration Date</div>
                                    <div class="osb-data-value"><?php echo esc_html(date('F j, Y g:i A', strtotime($registration->created_at))); ?></div>
                                </div>
                                <div class="osb-data-item">
                                    <div class="osb-data-label">Status</div>
                                    <div class="osb-data-value">
                                        <span class="osb-status osb-status-<?php echo esc_attr($registration->status); ?>">
                                            <?php echo esc_html(ucfirst($registration->status)); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="osb-data-item">
                                    <div class="osb-data-label">Students Registered</div>
                                    <div class="osb-data-value"><?php echo count($students); ?></div>
                                </div>
                                <div class="osb-data-item">
                                    <div class="osb-data-label">Documents Uploaded</div>
                                    <div class="osb-data-value"><?php echo count($documents); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- School Details Tab -->
                <div class="osb-tab-pane" id="osb-tab-school">
                    <div class="osb-form-section">
                        <div class="osb-form-section-header">
                            🏫 School Information
                        </div>
                        <div class="osb-form-section-content">
                            <div class="osb-data-grid">
                                <div class="osb-data-item">
                                    <div class="osb-data-label">School Name</div>
                                    <div class="osb-data-value"><?php echo esc_html($school->school_name); ?></div>
                                </div>
                                <div class="osb-data-item">
                                    <div class="osb-data-label">School Type</div>
                                    <div class="osb-data-value"><?php echo esc_html(ucfirst($school->school_type)); ?></div>
                                </div>
                                <div class="osb-data-item">
                                    <div class="osb-data-label">School Status</div>
                                    <div class="osb-data-value">
                                        <span class="osb-status osb-status-<?php echo esc_attr($school->status); ?>">
                                            <?php echo esc_html(ucfirst($school->status)); ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="osb-data-item">
                                    <div class="osb-data-label">Address</div>
                                    <div class="osb-data-value"><?php echo esc_html($school->address ?: 'Not provided'); ?></div>
                                </div>
                                <div class="osb-data-item">
                                    <div class="osb-data-label">City</div>
                                    <div class="osb-data-value"><?php echo esc_html($school->city ?: 'Not provided'); ?></div>
                                </div>
                                <div class="osb-data-item">
                                    <div class="osb-data-label">State</div>
                                    <div class="osb-data-value"><?php echo esc_html($school->state ?: 'Not provided'); ?></div>
                                </div>
                                <div class="osb-data-item">
                                    <div class="osb-data-label">Postal Code</div>
                                    <div class="osb-data-value"><?php echo esc_html($school->postal_code ?: 'Not provided'); ?></div>
                                </div>
                                <div class="osb-data-item">
                                    <div class="osb-data-label">Country</div>
                                    <div class="osb-data-value"><?php echo esc_html($school->country ?: 'Not provided'); ?></div>
                                </div>
                                <div class="osb-data-item">
                                    <div class="osb-data-label">Contact Person</div>
                                    <div class="osb-data-value"><?php echo esc_html($school->contact_person); ?></div>
                                </div>
                                <div class="osb-data-item">
                                    <div class="osb-data-label">Contact Email</div>
                                    <div class="osb-data-value">
                                        <a href="mailto:<?php echo esc_attr($school->contact_email); ?>">
                                            <?php echo esc_html($school->contact_email); ?>
                                        </a>
                                    </div>
                                </div>
                                <div class="osb-data-item">
                                    <div class="osb-data-label">Contact Phone</div>
                                    <div class="osb-data-value"><?php echo esc_html($school->contact_phone ?: 'Not provided'); ?></div>
                                </div>
                                <div class="osb-data-item">
                                    <div class="osb-data-label">Registration Date</div>
                                    <div class="osb-data-value"><?php echo esc_html($school->created_at ? date('F j, Y g:i A', strtotime($school->created_at)) : 'Not available'); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Students Tab -->
                <?php if (!empty($students)): ?>
                <div class="osb-tab-pane" id="osb-tab-students">
                    <div class="osb-form-section">
                        <div class="osb-form-section-header">
                            👨‍🎓 Registered Students (<?php echo count($students); ?>)
                        </div>
                        <div class="osb-form-section-content">
                            <div class="osb-students-list">
                                <?php foreach ($students as $index => $student): ?>
                                <div class="osb-student-card">
                                    <div class="osb-student-header">
                                        <span class="osb-student-number">#<?php echo ($index + 1); ?></span>
                                        <span class="osb-student-name"><?php echo esc_html($student->first_name . ' ' . $student->last_name); ?></span>
                                    </div>
                                    <div class="osb-data-grid">
                                        <div class="osb-data-item">
                                            <div class="osb-data-label">Grade Level</div>
                                            <div class="osb-data-value"><?php echo esc_html($student->grade_level ?: 'Not specified'); ?></div>
                                        </div>
                                        <div class="osb-data-item">
                                            <div class="osb-data-label">Gender</div>
                                            <div class="osb-data-value"><?php echo esc_html($student->gender ?: 'Not specified'); ?></div>
                                        </div>
                                        <div class="osb-data-item">
                                            <div class="osb-data-label">Date of Birth</div>
                                            <div class="osb-data-value"><?php echo esc_html($student->date_of_birth ? date('F j, Y', strtotime($student->date_of_birth)) : 'Not provided'); ?></div>
                                        </div>
                                        <div class="osb-data-item">
                                            <div class="osb-data-label">Student Email</div>
                                            <div class="osb-data-value">
                                                <?php if ($student->email): ?>
                                                    <a href="mailto:<?php echo esc_attr($student->email); ?>">
                                                        <?php echo esc_html($student->email); ?>
                                                    </a>
                                                <?php else: ?>
                                                    Not provided
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="osb-data-item">
                                            <div class="osb-data-label">Student Phone</div>
                                            <div class="osb-data-value"><?php echo esc_html($student->phone ?: 'Not provided'); ?></div>
                                        </div>
                                        <div class="osb-data-item">
                                            <div class="osb-data-label">Parent/Guardian</div>
                                            <div class="osb-data-value"><?php echo esc_html($student->parent_name ?: 'Not provided'); ?></div>
                                        </div>
                                        <div class="osb-data-item">
                                            <div class="osb-data-label">Parent Email</div>
                                            <div class="osb-data-value">
                                                <?php if ($student->parent_email): ?>
                                                    <a href="mailto:<?php echo esc_attr($student->parent_email); ?>">
                                                        <?php echo esc_html($student->parent_email); ?>
                                                    </a>
                                                <?php else: ?>
                                                    Not provided
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        <div class="osb-data-item">
                                            <div class="osb-data-label">Parent Phone</div>
                                            <div class="osb-data-value"><?php echo esc_html($student->parent_phone ?: 'Not provided'); ?></div>
                                        </div>
                                        <div class="osb-data-item">
                                            <div class="osb-data-label">Emergency Contact</div>
                                            <div class="osb-data-value"><?php echo esc_html($student->emergency_contact_name ?: 'Not provided'); ?></div>
                                        </div>
                                        <div class="osb-data-item">
                                            <div class="osb-data-label">Emergency Phone</div>
                                            <div class="osb-data-value"><?php echo esc_html($student->emergency_contact_phone ?: 'Not provided'); ?></div>
                                        </div>
                                        <?php if ($student->medical_conditions): ?>
                                        <div class="osb-data-item osb-data-full-width">
                                            <div class="osb-data-label">Medical Conditions</div>
                                            <div class="osb-data-value"><?php echo esc_html($student->medical_conditions); ?></div>
                                        </div>
                                        <?php endif; ?>
                                        <?php if ($student->dietary_restrictions): ?>
                                        <div class="osb-data-item osb-data-full-width">
                                            <div class="osb-data-label">Dietary Restrictions</div>
                                            <div class="osb-data-value"><?php echo esc_html($student->dietary_restrictions); ?></div>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Documents Tab -->
                <?php if (!empty($documents)): ?>
                <div class="osb-tab-pane" id="osb-tab-documents">
                    <div class="osb-form-section">
                        <div class="osb-form-section-header">
                            📎 Uploaded Documents (<?php echo count($documents); ?>)
                        </div>
                        <div class="osb-form-section-content">
                            <div class="osb-documents-list">
                                <?php foreach ($documents as $document): ?>
                                <div class="osb-document-item">
                                    <div class="osb-document-info">
                                        <div class="osb-document-name">
                                            <span class="osb-document-icon">📄</span>
                                            <?php echo esc_html($document->file_name); ?>
                                        </div>
                                        <div class="osb-document-meta">
                                            <span class="osb-document-type">
                                                Type: <?php echo esc_html(ucfirst(str_replace('_', ' ', $document->document_type))); ?>
                                            </span>
                                            <span class="osb-document-size">
                                                Size: <?php echo esc_html(size_format($document->file_size)); ?>
                                            </span>
                                            <span class="osb-document-date">
                                                Uploaded: <?php echo esc_html(date('M j, Y g:i A', strtotime($document->upload_date))); ?>
                                            </span>
                                        </div>
                                    </div>
                                    <div class="osb-document-actions">
                                        <a href="<?php echo esc_url($document->file_path); ?>" target="_blank" class="button button-primary button-small">
                                            <span class="dashicons dashicons-visibility" style="font-size: 13px; vertical-align: middle;"></span>
                                            View
                                        </a>
                                        <a href="<?php echo esc_url($document->file_path); ?>" download class="button button-small">
                                            <span class="dashicons dashicons-download" style="font-size: 13px; vertical-align: middle;"></span>
                                            Download
                                        </a>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php

        $html = ob_get_clean();

        wp_send_json_success(array(
            'html' => $html,
            'registration' => $registration,
            'school' => $school,
            'event' => $event,
            'students_count' => count($students),
            'documents_count' => count($documents)
        ));
    }

    /**
     * Create WordPress user for a student
     */
    private static function createStudentWpUser() {
        if (!current_user_can('osb_manage_students')) {
            wp_send_json_error(__('Permission denied.', 'spelling-bee-pro'));
        }

        $student_id = intval($_POST['student_id']);
        if (!$student_id) {
            wp_send_json_error(__('Invalid student ID.', 'spelling-bee-pro'));
        }

        $user_manager = OSB_User_Manager::getInstance();
        $result = $user_manager->createStudentWordPressUser($student_id);

        if ($result['success']) {
            wp_send_json_success(array(
                'message' => $result['message'],
                'action' => $result['action'],
                'user_id' => $result['user_id'] ?? null,
                'username' => $result['username'] ?? null
            ));
        } else {
            wp_send_json_error($result['message']);
        }
    }

    /**
     * Handle bulk students actions
     */
    private static function bulkStudentsAction() {
        if (!current_user_can('osb_manage_students')) {
            wp_send_json_error(__('Permission denied.', 'spelling-bee-pro'));
        }

        $action = sanitize_text_field($_POST['bulk_action']);
        $student_ids = array_map('intval', $_POST['student_ids']);

        if (empty($student_ids)) {
            wp_send_json_error(__('No students selected.', 'spelling-bee-pro'));
        }

        switch ($action) {
            case 'delete':
                $deleted = 0;
                $failed = 0;

                foreach ($student_ids as $student_id) {
                    if (self::deleteStudentById($student_id)) {
                        $deleted++;
                    } else {
                        $failed++;
                    }
                }

                if ($deleted > 0) {
                    $message = sprintf(_n('%d student deleted successfully.', '%d students deleted successfully.', $deleted, 'spelling-bee-pro'), $deleted);
                    if ($failed > 0) {
                        $message .= ' ' . sprintf(_n('%d student could not be deleted.', '%d students could not be deleted.', $failed, 'spelling-bee-pro'), $failed);
                    }
                    wp_send_json_success(array('message' => $message));
                } else {
                    wp_send_json_error(__('No students were deleted.', 'spelling-bee-pro'));
                }
                break;

            default:
                wp_send_json_error(__('Invalid bulk action.', 'spelling-bee-pro'));
        }
    }

    /**
     * Delete a student by ID (helper method)
     */
    private static function deleteStudentById($student_id) {
        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        // Start transaction
        $wpdb->query('START TRANSACTION');

        try {
            // Delete related documents
            $wpdb->delete(
                "{$table_prefix}documents",
                array('student_id' => $student_id),
                array('%d')
            );

            // Delete WordPress user if linked
            $student = $wpdb->get_row($wpdb->prepare(
                "SELECT wp_user_id, first_name, last_name FROM {$table_prefix}students WHERE id = %d",
                $student_id
            ));

            if ($student && $student->wp_user_id) {
                // Only delete WordPress user if it has 'student' role
                $wp_user = get_userdata($student->wp_user_id);
                if ($wp_user && in_array('student', $wp_user->roles)) {
                    wp_delete_user($student->wp_user_id);
                }
            }

            // Delete student record
            $result = $wpdb->delete(
                "{$table_prefix}students",
                array('id' => $student_id),
                array('%d')
            );

            if ($result === false) {
                throw new Exception('Failed to delete student record');
            }

            // Log the deletion
            if ($student) {
                error_log('[OSB] Student deleted via bulk action: ' . $student->first_name . ' ' . $student->last_name . ' (ID: ' . $student_id . ')');
            }

            // Commit transaction
            $wpdb->query('COMMIT');
            return true;

        } catch (Exception $e) {
            // Rollback transaction
            $wpdb->query('ROLLBACK');
            error_log('[OSB] Failed to delete student ' . $student_id . ' via bulk action: ' . $e->getMessage());
            return false;
        }
    }
}