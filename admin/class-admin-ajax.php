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
            wp_send_json_error(__('Security check failed.', 'omafuru-spelling-bee'));
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

            default:
                wp_send_json_error(__('Invalid action.', 'omafuru-spelling-bee'));
        }
    }

    /**
     * Save event
     */
    private static function saveEvent() {
        if (!current_user_can('osb_manage_events')) {
            wp_send_json_error(__('Permission denied.', 'omafuru-spelling-bee'));
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
            $message = __('Event updated successfully.', 'omafuru-spelling-bee');
        } else {
            $result = $db->createEvent($event_data);
            $message = __('Event created successfully.', 'omafuru-spelling-bee');
            $event_id = $result;
        }

        if ($result) {
            wp_send_json_success(array(
                'message' => $message,
                'event_id' => $event_id
            ));
        } else {
            wp_send_json_error(__('Failed to save event.', 'omafuru-spelling-bee'));
        }
    }

    /**
     * Save school
     */
    private static function saveSchool() {
        if (!current_user_can('osb_manage_schools')) {
            wp_send_json_error(__('Permission denied.', 'omafuru-spelling-bee'));
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
            $message = __('School updated successfully.', 'omafuru-spelling-bee');
        } else {
            $result = $db->createSchool($school_data);
            $message = __('School created successfully.', 'omafuru-spelling-bee');
            $school_id = $result;
        }

        if ($result) {
            wp_send_json_success(array(
                'message' => $message,
                'school_id' => $school_id
            ));
        } else {
            wp_send_json_error(__('Failed to save school.', 'omafuru-spelling-bee'));
        }
    }

    /**
     * Save student
     */
    private static function saveStudent() {
        if (!current_user_can('osb_manage_students')) {
            wp_send_json_error(__('Permission denied.', 'omafuru-spelling-bee'));
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
            $message = __('Student updated successfully.', 'omafuru-spelling-bee');
        } else {
            $result = $db->createStudent($student_data);
            $message = __('Student created successfully.', 'omafuru-spelling-bee');
            $student_id = $result;
        }

        if ($result) {
            wp_send_json_success(array(
                'message' => $message,
                'student_id' => $student_id
            ));
        } else {
            wp_send_json_error(__('Failed to save student.', 'omafuru-spelling-bee'));
        }
    }

    /**
     * Update registration status
     */
    private static function updateRegistrationStatus() {
        if (!current_user_can('osb_manage_registrations')) {
            wp_send_json_error(__('Permission denied.', 'omafuru-spelling-bee'));
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
                'message' => sprintf(__('Registration status updated to %s.', 'omafuru-spelling-bee'), $status)
            ));
        } else {
            wp_send_json_error(__('Failed to update registration status.', 'omafuru-spelling-bee'));
        }
    }

    /**
     * Process donation
     */
    private static function processDonation() {
        if (!current_user_can('osb_manage_donations')) {
            wp_send_json_error(__('Permission denied.', 'omafuru-spelling-bee'));
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
            wp_send_json_error(__('Permission denied.', 'omafuru-spelling-bee'));
        }

        if (empty($_FILES['document'])) {
            wp_send_json_error(__('No file selected.', 'omafuru-spelling-bee'));
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
            wp_send_json_error(__('Permission denied.', 'omafuru-spelling-bee'));
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
            $message = __('Sponsor updated successfully.', 'omafuru-spelling-bee');
        } else {
            $sponsor_data['created_at'] = current_time('mysql');
            $result = $wpdb->insert($table_name, $sponsor_data);
            $message = __('Sponsor created successfully.', 'omafuru-spelling-bee');
            $sponsor_id = $wpdb->insert_id;
        }

        if ($result !== false) {
            wp_send_json_success(array(
                'message' => $message,
                'sponsor_id' => $sponsor_id
            ));
        } else {
            wp_send_json_error(__('Failed to save sponsor.', 'omafuru-spelling-bee'));
        }
    }

    /**
     * Resolve conflict
     */
    private static function resolveConflict() {
        if (!current_user_can('osb_resolve_conflicts')) {
            wp_send_json_error(__('Permission denied.', 'omafuru-spelling-bee'));
        }

        $conflict_id = intval($_POST['conflict_id']);
        $resolution = sanitize_text_field($_POST['resolution']);

        $user_manager = OSB_User_Manager::getInstance();
        $result = $user_manager->resolveConflict($conflict_id, $resolution);

        if (is_wp_error($result)) {
            wp_send_json_error($result->get_error_message());
        } else {
            wp_send_json_success(array(
                'message' => __('Conflict resolved successfully.', 'omafuru-spelling-bee')
            ));
        }
    }

    /**
     * Send bulk email
     */
    private static function sendBulkEmail() {
        if (!current_user_can('osb_send_communications')) {
            wp_send_json_error(__('Permission denied.', 'omafuru-spelling-bee'));
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
            'message' => sprintf(__('Sent %d emails successfully.', 'omafuru-spelling-bee'), $sent_count),
            'sent_count' => $sent_count,
            'total_count' => count($email_addresses)
        ));
    }

    /**
     * Generate report
     */
    private static function generateReport() {
        if (!current_user_can('osb_view_reports')) {
            wp_send_json_error(__('Permission denied.', 'omafuru-spelling-bee'));
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
        $html .= '<h2>' . sprintf(__('%s Report', 'omafuru-spelling-bee'), ucwords(str_replace('_', ' ', $report_type))) . '</h2>';
        $html .= '<p><strong>' . __('Generated on:', 'omafuru-spelling-bee') . '</strong> ' . date('F j, Y g:i A') . '</p>';

        switch ($report_type) {
            case 'event_summary':
                if (isset($data['event'])) {
                    $html .= '<h3>' . $data['event']->title . '</h3>';
                    $html .= '<p><strong>' . __('Total Registrations:', 'omafuru-spelling-bee') . '</strong> ' . count($data['registrations']) . '</p>';
                    $html .= '<p><strong>' . __('Total Donations:', 'omafuru-spelling-bee') . '</strong> $' . number_format($data['donations']['total_amount'], 2) . '</p>';
                }
                break;

            case 'schools_list':
                $html .= '<table class="widefat">';
                $html .= '<thead><tr><th>' . __('School Name', 'omafuru-spelling-bee') . '</th><th>' . __('Contact', 'omafuru-spelling-bee') . '</th><th>' . __('Status', 'omafuru-spelling-bee') . '</th></tr></thead>';
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
            wp_send_json_error(__('Permission denied.', 'omafuru-spelling-bee'));
        }

        $video_id = intval($_POST['video_id'] ?? 0);
        $event_id = intval($_POST['event_id'] ?? 0);

        if (!$event_id) {
            wp_send_json_error(__('Event ID is required.', 'omafuru-spelling-bee'));
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
            wp_send_json_error(__('Video title and URL are required.', 'omafuru-spelling-bee'));
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
            $message = __('Video updated successfully.', 'omafuru-spelling-bee');
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
            $message = __('Video added successfully.', 'omafuru-spelling-bee');
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
            wp_send_json_error(__('Failed to save video. Error: ', 'omafuru-spelling-bee') . $error);
        }
    }

    /**
     * Get event video data
     */
    private static function getVideo() {
        // Skip permission check for frontend requests to view videos
        $video_id = intval($_POST['video_id'] ?? 0);

        if (!$video_id) {
            wp_send_json_error(__('Video ID is required.', 'omafuru-spelling-bee'));
        }

        global $wpdb;
        $table_name = $wpdb->prefix . OSB_TABLE_PREFIX . 'event_videos';

        $video = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$table_name} WHERE id = %d",
            $video_id
        ));

        if (!$video) {
            wp_send_json_error(__('Video not found.', 'omafuru-spelling-bee'));
        }

        wp_send_json_success($video);
    }

    /**
     * Delete event video
     */
    private static function deleteVideo() {
        if (!current_user_can('osb_manage_events')) {
            wp_send_json_error(__('Permission denied.', 'omafuru-spelling-bee'));
        }

        $video_id = intval($_POST['video_id'] ?? 0);

        if (!$video_id) {
            wp_send_json_error(__('Video ID is required.', 'omafuru-spelling-bee'));
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
                'message' => __('Video deleted successfully.', 'omafuru-spelling-bee')
            ));
        } else {
            wp_send_json_error(__('Failed to delete video.', 'omafuru-spelling-bee'));
        }
    }

    /**
     * Get event data for frontend display
     */
    private static function getEvent() {
        $event_id = intval($_POST['event_id'] ?? 0);

        if (!$event_id) {
            wp_send_json_error(__('Event ID is required.', 'omafuru-spelling-bee'));
        }

        global $wpdb;
        $table_prefix = defined('OSB_TABLE_PREFIX') ? OSB_TABLE_PREFIX : 'osb_';
        $events_table = $wpdb->prefix . $table_prefix . 'events';

        $event = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$events_table} WHERE id = %d",
            $event_id
        ));

        if (!$event) {
            wp_send_json_error(__('Event not found.', 'omafuru-spelling-bee'));
        }

        wp_send_json_success($event);
    }

    /**
     * Preview bulk operation
     */
    private static function previewBulkOperation() {
        if (!current_user_can('osb_manage_registrations')) {
            wp_send_json_error(__('Permission denied.', 'omafuru-spelling-bee'));
        }

        $action = sanitize_text_field($_POST['bulk_action']);
        $filter = sanitize_text_field($_POST['registration_filter']);

        if (empty($action)) {
            wp_send_json_error(__('Please select an action.', 'omafuru-spelling-bee'));
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
                wp_send_json_error(__('Invalid filter selected.', 'omafuru-spelling-bee'));
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
            wp_send_json_error(sprintf(__('No registrations found with status: %s', 'omafuru-spelling-bee'), $filter));
        }

        $action_description = '';
        switch ($action) {
            case 'approve_all':
                $action_description = __('Mark all selected registrations as approved', 'omafuru-spelling-bee');
                break;
            case 'mark_documents_submitted':
                $action_description = __('Mark all selected registrations as documents submitted', 'omafuru-spelling-bee');
                break;
            case 'send_reminder':
                $action_description = __('Send reminder emails to all selected registrations', 'omafuru-spelling-bee');
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
            wp_send_json_error(__('Permission denied.', 'omafuru-spelling-bee'));
        }

        if (!wp_verify_nonce($_POST['bulk_nonce'], 'osb_bulk_operation')) {
            wp_send_json_error(__('Security check failed.', 'omafuru-spelling-bee'));
        }

        $action = sanitize_text_field($_POST['bulk_action']);
        $filter = sanitize_text_field($_POST['registration_filter']);

        if (empty($action)) {
            wp_send_json_error(__('Please select an action.', 'omafuru-spelling-bee'));
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
                wp_send_json_error(__('Invalid filter selected.', 'omafuru-spelling-bee'));
        }

        $registration_ids = $wpdb->get_col(
            "SELECT id FROM {$table_prefix}registrations {$where_clause}"
        );

        if (empty($registration_ids)) {
            wp_send_json_error(__('No registrations found to process.', 'omafuru-spelling-bee'));
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
                __('Bulk operation completed: %s on %d registrations', 'omafuru-spelling-bee'),
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
                __('Bulk operation completed. %d successful, %d failed out of %d total.', 'omafuru-spelling-bee'),
                $results['success'],
                $results['failed'],
                count($registration_ids)
            ),
            'results' => $results
        ));
    }
}