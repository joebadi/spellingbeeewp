<?php
/**
 * Admin Menu Class
 *
 * Handles admin menu creation and page routing
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class OSB_Admin_Menu {

    /**
     * Instance of this class
     */
    private static $instance = null;

    /**
     * Menu slug prefix
     */
    private $menu_prefix = 'spelling-bee';

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
        add_action('admin_menu', array($this, 'addMenuPages'));
        add_action('admin_init', array($this, 'handleActions'));
    }

    /**
     * Add menu pages
     */
    public function addMenuPages() {
        // Main menu page
        add_menu_page(
            __('Spelling Bee', 'omafuru-spelling-bee'),
            __('Spelling Bee', 'omafuru-spelling-bee'),
            'osb_manage_events',
            $this->menu_prefix,
            array($this, 'renderDashboard'),
            'dashicons-awards',
            30
        );

        // Dashboard (same as main page)
        add_submenu_page(
            $this->menu_prefix,
            __('Dashboard', 'omafuru-spelling-bee'),
            __('Dashboard', 'omafuru-spelling-bee'),
            'osb_manage_events',
            $this->menu_prefix,
            array($this, 'renderDashboard')
        );

        // Events
        add_submenu_page(
            $this->menu_prefix,
            __('Events', 'omafuru-spelling-bee'),
            __('Events', 'omafuru-spelling-bee'),
            'osb_manage_events',
            $this->menu_prefix . '-events',
            array($this, 'renderEvents')
        );

        // Schools
        add_submenu_page(
            $this->menu_prefix,
            __('Schools', 'omafuru-spelling-bee'),
            __('Schools', 'omafuru-spelling-bee'),
            'osb_manage_schools',
            $this->menu_prefix . '-schools',
            array($this, 'renderSchools')
        );

        // Students
        add_submenu_page(
            $this->menu_prefix,
            __('Students', 'omafuru-spelling-bee'),
            __('Students', 'omafuru-spelling-bee'),
            'osb_manage_students',
            $this->menu_prefix . '-students',
            array($this, 'renderStudents')
        );

        // Registrations
        add_submenu_page(
            $this->menu_prefix,
            __('Registrations', 'omafuru-spelling-bee'),
            __('Registrations', 'omafuru-spelling-bee'),
            'osb_manage_registrations',
            $this->menu_prefix . '-registrations',
            array($this, 'renderRegistrations')
        );

        // Donations
        add_submenu_page(
            $this->menu_prefix,
            __('Donations', 'omafuru-spelling-bee'),
            __('Donations', 'omafuru-spelling-bee'),
            'osb_manage_donations',
            $this->menu_prefix . '-donations',
            array($this, 'renderDonations')
        );

        // Documents
        add_submenu_page(
            $this->menu_prefix,
            __('Documents', 'omafuru-spelling-bee'),
            __('Documents', 'omafuru-spelling-bee'),
            'osb_manage_documents',
            $this->menu_prefix . '-documents',
            array($this, 'renderDocuments')
        );

        // Sponsors
        add_submenu_page(
            $this->menu_prefix,
            __('Sponsors', 'omafuru-spelling-bee'),
            __('Sponsors', 'omafuru-spelling-bee'),
            'osb_manage_sponsors',
            $this->menu_prefix . '-sponsors',
            array($this, 'renderSponsors')
        );

        // User Conflicts
        add_submenu_page(
            $this->menu_prefix,
            __('User Conflicts', 'omafuru-spelling-bee'),
            __('Conflicts', 'omafuru-spelling-bee'),
            'osb_resolve_conflicts',
            $this->menu_prefix . '-conflicts',
            array($this, 'renderConflicts')
        );

        // Communications
        add_submenu_page(
            $this->menu_prefix,
            __('Communications', 'omafuru-spelling-bee'),
            __('Communications', 'omafuru-spelling-bee'),
            'osb_send_communications',
            $this->menu_prefix . '-communications',
            array($this, 'renderCommunications')
        );

        // Reports
        add_submenu_page(
            $this->menu_prefix,
            __('Reports', 'omafuru-spelling-bee'),
            __('Reports', 'omafuru-spelling-bee'),
            'osb_view_reports',
            $this->menu_prefix . '-reports',
            array($this, 'renderReports')
        );

        // Settings
        add_submenu_page(
            $this->menu_prefix,
            __('Settings', 'omafuru-spelling-bee'),
            __('Settings', 'omafuru-spelling-bee'),
            'osb_manage_settings',
            $this->menu_prefix . '-settings',
            array($this, 'renderSettings')
        );

        // Automation & Notifications
        add_submenu_page(
            $this->menu_prefix,
            __('Automation', 'omafuru-spelling-bee'),
            __('Automation', 'omafuru-spelling-bee'),
            'osb_manage_registrations',
            $this->menu_prefix . '-automation',
            array($this, 'renderAutomation')
        );
    }

    /**
     * Handle admin actions
     */
    public function handleActions() {
        if (!isset($_GET['page']) || strpos($_GET['page'], $this->menu_prefix) !== 0) {
            return;
        }

        $action = sanitize_text_field($_GET['action'] ?? '');
        $nonce = sanitize_text_field($_GET['_wpnonce'] ?? '');

        if (empty($action) || !wp_verify_nonce($nonce, 'osb_admin_action')) {
            return;
        }

        switch ($action) {
            case 'delete_event':
                $this->handleDeleteEvent();
                break;

            case 'delete_school':
                $this->handleDeleteSchool();
                break;

            case 'approve_registration':
                $this->handleApproveRegistration();
                break;

            case 'reject_registration':
                $this->handleRejectRegistration();
                break;

            case 'resolve_conflict':
                $this->handleResolveConflict();
                break;
        }
    }

    /**
     * Render Dashboard
     */
    public function renderDashboard() {
        $db = OSB_Database::getInstance();
        $stats = $db->getStatistics();
        $current_event = $db->getCurrentEvent();

        // Get recent activities
        $recent_registrations = $db->getEventRegistrations(
            $current_event ? $current_event->id : 0,
            null
        );
        $recent_registrations = array_slice($recent_registrations, 0, 5);

        // Get pending conflicts
        $pending_conflicts = $db->getPendingUserConflicts();

        $this->renderPage('dashboard', array(
            'stats' => $stats,
            'current_event' => $current_event,
            'recent_registrations' => $recent_registrations,
            'pending_conflicts' => $pending_conflicts
        ));
    }

    /**
     * Render Events page
     */
    public function renderEvents() {
        $db = OSB_Database::getInstance();
        $action = sanitize_text_field($_GET['action'] ?? 'list');
        $event_id = intval($_GET['event_id'] ?? 0);

        switch ($action) {
            case 'edit':
            case 'new':
                $event = $event_id ? $db->getEvent($event_id) : null;
                $this->renderPage('events-form', array('event' => $event));
                break;

            case 'view':
                $event = $db->getEvent($event_id);
                $registrations = $db->getEventRegistrations($event_id);
                $this->renderPage('events-view', array(
                    'event' => $event,
                    'registrations' => $registrations
                ));
                break;

            default:
                $events = $db->getAllEvents();
                $this->renderPage('events-list', array('events' => $events));
                break;
        }
    }

    /**
     * Render Schools page
     */
    public function renderSchools() {
        $db = OSB_Database::getInstance();
        $action = sanitize_text_field($_GET['action'] ?? 'list');
        $school_id = intval($_GET['school_id'] ?? 0);

        switch ($action) {
            case 'edit':
            case 'new':
                $school = $school_id ? $db->getSchool($school_id) : null;
                $this->renderPage('schools-form', array('school' => $school));
                break;

            case 'view':
                $school = $db->getSchool($school_id);
                $students = $db->getStudentsBySchool($school_id);
                $this->renderPage('schools-view', array(
                    'school' => $school,
                    'students' => $students
                ));
                break;

            default:
                $schools = $db->getAllSchools();
                $this->renderPage('schools-list', array('schools' => $schools));
                break;
        }
    }

    /**
     * Render Students page
     */
    public function renderStudents() {
        $db = OSB_Database::getInstance();
        $action = sanitize_text_field($_GET['action'] ?? 'list');
        $student_id = intval($_GET['student_id'] ?? 0);

        switch ($action) {
            case 'edit':
            case 'new':
                $student = $student_id ? $db->getStudent($student_id) : null;
                $schools = $db->getAllSchools('active');
                $this->renderPage('students-form', array(
                    'student' => $student,
                    'schools' => $schools
                ));
                break;

            case 'view':
                $student = $db->getStudent($student_id);
                $this->renderPage('students-view', array('student' => $student));
                break;

            default:
                global $wpdb;
                $students = $wpdb->get_results(
                    "SELECT st.*, s.school_name
                     FROM {$wpdb->prefix}osb_students st
                     LEFT JOIN {$wpdb->prefix}osb_schools s ON st.school_id = s.id
                     ORDER BY st.created_at DESC
                     LIMIT 100"
                );
                $this->renderPage('students-list', array('students' => $students));
                break;
        }
    }

    /**
     * Render Registrations page
     */
    public function renderRegistrations() {
        $db = OSB_Database::getInstance();
        $action = sanitize_text_field($_GET['action'] ?? 'list');
        $registration_id = intval($_GET['registration_id'] ?? 0);
        $event_id = intval($_GET['event_id'] ?? 0);

        switch ($action) {
            case 'view':
                $registration = $db->getRegistrationByToken($registration_id);
                $students = $db->getStudentsBySchool($registration->school_id);
                $this->renderPage('registrations-view', array(
                    'registration' => $registration,
                    'students' => $students
                ));
                break;

            default:
                $events = $db->getAllEvents();
                $current_event = $db->getCurrentEvent();
                $selected_event_id = $event_id ?: ($current_event ? $current_event->id : 0);

                $registrations = $selected_event_id ?
                    $db->getEventRegistrations($selected_event_id) : array();

                $this->renderPage('registrations-list', array(
                    'events' => $events,
                    'registrations' => $registrations,
                    'selected_event_id' => $selected_event_id
                ));
                break;
        }
    }

    /**
     * Render Donations page
     */
    public function renderDonations() {
        $donation_calc = OSB_Donation_Calculator::getInstance();
        $db = OSB_Database::getInstance();

        $event_id = intval($_GET['event_id'] ?? 0);
        $current_event = $db->getCurrentEvent();
        $selected_event_id = $event_id ?: ($current_event ? $current_event->id : null);

        $stats = $donation_calc->getDonationStats($selected_event_id);
        $events = $db->getAllEvents();

        if ($selected_event_id) {
            $total_donations = $donation_calc->getTotalDonations($selected_event_id);
            $prize_breakdown = $donation_calc->calculatePrizeBreakdown($total_donations);
        } else {
            $total_donations = 0;
            $prize_breakdown = array();
        }

        // Get recent donations
        global $wpdb;
        if ($selected_event_id) {
            $donations = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT d.*, e.title as event_title
                     FROM {$wpdb->prefix}osb_donations d
                     LEFT JOIN {$wpdb->prefix}osb_events e ON d.event_id = e.id
                     WHERE d.event_id = %d
                     ORDER BY d.donated_at DESC
                     LIMIT 50",
                    $selected_event_id
                )
            );
        } else {
            $donations = $wpdb->get_results(
                "SELECT d.*, e.title as event_title
                 FROM {$wpdb->prefix}osb_donations d
                 LEFT JOIN {$wpdb->prefix}osb_events e ON d.event_id = e.id
                 ORDER BY d.donated_at DESC
                 LIMIT 50"
            );
        }

        $this->renderPage('donations', array(
            'stats' => $stats,
            'events' => $events,
            'selected_event_id' => $selected_event_id,
            'total_donations' => $total_donations,
            'prize_breakdown' => $prize_breakdown,
            'donations' => $donations
        ));
    }

    /**
     * Render Documents page
     */
    public function renderDocuments() {
        $file_handler = OSB_File_Handler::getInstance();
        $db = OSB_Database::getInstance();

        // Get documents data
        $storage_stats = $file_handler->getStorageStats();
        $events = $db->getAllEvents();

        // Get documents
        global $wpdb;
        $documents = $wpdb->get_results(
            "SELECT d.*, e.title as event_title, s.school_name as school_name
             FROM {$wpdb->prefix}osb_documents d
             LEFT JOIN {$wpdb->prefix}osb_events e ON d.event_id = e.id
             LEFT JOIN {$wpdb->prefix}osb_schools s ON d.school_id = s.id
             ORDER BY d.upload_date DESC
             LIMIT 100"
        );

        $this->renderPage('documents', array(
            'storage_stats' => $storage_stats,
            'events' => $events,
            'documents' => $documents
        ));
    }

    /**
     * Render Sponsors page
     */
    public function renderSponsors() {
        $action = sanitize_text_field($_GET['action'] ?? 'list');
        $sponsor_id = intval($_GET['sponsor_id'] ?? 0);

        // Get sponsors data
        global $wpdb;
        $sponsors = $wpdb->get_results(
            "SELECT s.*, e.title as event_title
             FROM {$wpdb->prefix}osb_sponsors s
             LEFT JOIN {$wpdb->prefix}osb_events e ON s.event_id = e.id
             ORDER BY s.created_at DESC"
        );

        $db = OSB_Database::getInstance();
        $events = $db->getAllEvents();

        $this->renderPage('sponsors', array(
            'sponsors' => $sponsors,
            'events' => $events,
            'action' => $action,
            'sponsor_id' => $sponsor_id
        ));
    }

    /**
     * Render User Conflicts page
     */
    public function renderConflicts() {
        $db = OSB_Database::getInstance();

        // Get conflicts data
        global $wpdb;
        $conflicts = $wpdb->get_results(
            "SELECT c.*, e.title as event_title
             FROM {$wpdb->prefix}osb_conflicts c
             LEFT JOIN {$wpdb->prefix}osb_events e ON c.event_id = e.id
             ORDER BY c.status ASC, c.severity DESC, c.created_at DESC"
        );

        $events = $db->getAllEvents();

        $this->renderPage('conflicts', array(
            'conflicts' => $conflicts,
            'events' => $events
        ));
    }

    /**
     * Render Communications page
     */
    public function renderCommunications() {
        $email_handler = OSB_Email_Handler::getInstance();
        $db = OSB_Database::getInstance();

        // Get email stats and communications data
        $email_stats = $email_handler->getEmailStats();
        $events = $db->getAllEvents();

        // Get recent communications
        global $wpdb;
        $communications = $wpdb->get_results(
            "SELECT c.*, e.title as event_title
             FROM {$wpdb->prefix}osb_communications c
             LEFT JOIN {$wpdb->prefix}osb_events e ON c.event_id = e.id
             ORDER BY c.created_at DESC
             LIMIT 50"
        );

        $this->renderPage('communications', array(
            'email_stats' => $email_stats,
            'events' => $events,
            'communications' => $communications
        ));
    }

    /**
     * Render Reports page
     */
    public function renderReports() {
        $db = OSB_Database::getInstance();
        $donation_calc = OSB_Donation_Calculator::getInstance();

        $events = $db->getAllEvents();
        $selected_event_id = intval($_GET['event_id'] ?? 0);

        $report_data = array();
        if ($selected_event_id) {
            $event = $db->getEvent($selected_event_id);
            $registrations = $db->getEventRegistrations($selected_event_id);
            $donations = $donation_calc->getDonationStats($selected_event_id);

            $report_data = array(
                'event' => $event,
                'registrations' => $registrations,
                'donations' => $donations
            );
        }

        $this->renderPage('reports', array(
            'events' => $events,
            'selected_event_id' => $selected_event_id,
            'report_data' => $report_data
        ));
    }

    /**
     * Render Settings page
     */
    public function renderSettings() {
        if (isset($_POST['submit'])) {
            $this->saveSettings();
        }

        $donation_calc = OSB_Donation_Calculator::getInstance();
        $prize_distribution = $donation_calc->getPrizeDistribution();

        $this->renderPage('settings', array(
            'prize_distribution' => $prize_distribution
        ));
    }

    /**
     * Render Automation page
     */
    public function renderAutomation() {
        // Get automation statistics
        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        $notification_stats = $wpdb->get_row(
            "SELECT
                COUNT(*) as total,
                SUM(CASE WHEN is_read = 0 THEN 1 ELSE 0 END) as unread,
                SUM(CASE WHEN priority = 'high' THEN 1 ELSE 0 END) as high_priority
             FROM {$table_prefix}admin_notifications"
        );

        $recent_notifications = $wpdb->get_results(
            "SELECT * FROM {$table_prefix}admin_notifications
             ORDER BY created_at DESC LIMIT 20"
        );

        $automation_stats = array(
            'notifications' => $notification_stats,
            'recent' => $recent_notifications
        );

        $this->renderPage('automation', array(
            'stats' => $automation_stats
        ));
    }

    /**
     * Render page template
     */
    private function renderPage($template, $data = array()) {
        $template_file = OSB_PLUGIN_PATH . 'admin/templates/' . $template . '.php';

        if (file_exists($template_file)) {
            extract($data);
            include $template_file;
        } else {
            echo '<div class="wrap">';
            echo '<h1>' . __('Page Not Found', 'omafuru-spelling-bee') . '</h1>';
            echo '<p>' . sprintf(__('Template %s not found.', 'omafuru-spelling-bee'), $template) . '</p>';
            echo '</div>';
        }
    }

    /**
     * Handle event deletion
     */
    private function handleDeleteEvent() {
        if (!current_user_can('osb_manage_events')) {
            wp_die(__('You do not have permission to delete events.', 'omafuru-spelling-bee'));
        }

        $event_id = intval($_GET['event_id']);
        // Add deletion logic here
        wp_redirect(admin_url('admin.php?page=' . $this->menu_prefix . '-events&deleted=1'));
        exit;
    }

    /**
     * Handle registration approval
     */
    private function handleApproveRegistration() {
        if (!current_user_can('osb_manage_registrations')) {
            wp_die(__('You do not have permission to approve registrations.', 'omafuru-spelling-bee'));
        }

        $registration_id = intval($_GET['registration_id']);
        $db = OSB_Database::getInstance();
        $db->updateRegistration($registration_id, array('status' => 'approved'));

        // Send approval email
        $email_handler = OSB_Email_Handler::getInstance();
        $email_handler->sendApprovalNotification($registration_id);

        wp_redirect(admin_url('admin.php?page=' . $this->menu_prefix . '-registrations&approved=1'));
        exit;
    }

    /**
     * Handle registration rejection
     */
    private function handleRejectRegistration() {
        if (!current_user_can('osb_manage_registrations')) {
            wp_die(__('You do not have permission to reject registrations.', 'omafuru-spelling-bee'));
        }

        $registration_id = intval($_GET['registration_id']);
        $reason = sanitize_textarea_field($_GET['reason'] ?? '');

        $db = OSB_Database::getInstance();
        $db->updateRegistration($registration_id, array('status' => 'rejected'));

        // Send rejection email
        $email_handler = OSB_Email_Handler::getInstance();
        $email_handler->sendRejectionNotification($registration_id, $reason);

        wp_redirect(admin_url('admin.php?page=' . $this->menu_prefix . '-registrations&rejected=1'));
        exit;
    }

    /**
     * Handle conflict resolution
     */
    private function handleResolveConflict() {
        if (!current_user_can('osb_resolve_conflicts')) {
            wp_die(__('You do not have permission to resolve conflicts.', 'omafuru-spelling-bee'));
        }

        $conflict_id = intval($_GET['conflict_id']);
        $resolution = sanitize_text_field($_GET['resolution']);

        $db = OSB_Database::getInstance();
        $user_manager = OSB_User_Manager::getInstance();

        if ($resolution === 'merge') {
            $user_manager->resolveConflict($conflict_id, 'merge');
        } else {
            $user_manager->resolveConflict($conflict_id, 'create_new');
        }

        wp_redirect(admin_url('admin.php?page=' . $this->menu_prefix . '-conflicts&resolved=1'));
        exit;
    }

    /**
     * Save settings
     */
    private function saveSettings() {
        if (!current_user_can('osb_manage_settings')) {
            wp_die(__('You do not have permission to save settings.', 'omafuru-spelling-bee'));
        }

        // Save general settings
        $settings_to_save = array(
            'osb_organization_name',
            'osb_contact_email',
            'osb_registration_enabled',
            'osb_max_students_per_school',
            'osb_min_students_per_school',
            'osb_require_parent_consent',
            'osb_auto_approve_schools',
            'osb_email_notifications_enabled',
            'osb_donation_enabled'
        );

        foreach ($settings_to_save as $setting) {
            if (isset($_POST[$setting])) {
                update_option($setting, sanitize_text_field($_POST[$setting]));
            }
        }

        // Save prize distribution
        if (isset($_POST['prize_distribution'])) {
            $distribution = array();
            foreach ($_POST['prize_distribution'] as $key => $value) {
                $distribution[sanitize_key($key)] = intval($value);
            }

            $donation_calc = OSB_Donation_Calculator::getInstance();
            $result = $donation_calc->updatePrizeDistribution($distribution);

            if (is_wp_error($result)) {
                add_settings_error('osb_settings', 'prize_distribution', $result->get_error_message());
            }
        }

        if (!get_settings_errors('osb_settings')) {
            add_settings_error('osb_settings', 'settings_saved', __('Settings saved successfully.', 'omafuru-spelling-bee'), 'updated');
        }
    }

    /**
     * Get admin URL for action
     */
    public function getAdminUrl($page, $args = array()) {
        $base_url = admin_url('admin.php?page=' . $this->menu_prefix . '-' . $page);

        if (!empty($args)) {
            $base_url = add_query_arg($args, $base_url);
        }

        return $base_url;
    }

    /**
     * Get action URL with nonce
     */
    public function getActionUrl($action, $args = array()) {
        $args['action'] = $action;
        $args['_wpnonce'] = wp_create_nonce('osb_admin_action');

        return add_query_arg($args, $_SERVER['REQUEST_URI']);
    }
}