<?php
/**
 * Database Management Class
 *
 * Handles all database operations and queries
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class OSB_Database {

    /**
     * Instance of this class
     */
    private static $instance = null;

    /**
     * WordPress database object
     */
    private $wpdb;

    /**
     * Table prefix for plugin tables
     */
    private $table_prefix;

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
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;
    }

    /**
     * Get table name with prefix
     */
    public function getTableName($table) {
        return $this->table_prefix . $table;
    }

    /* ===========================================
       EVENT METHODS
       =========================================== */

    /**
     * Create a new event
     */
    public function createEvent($data) {
        $defaults = array(
            'status' => 'upcoming',
            'max_students_per_school' => 5,
            'created_by' => get_current_user_id()
        );

        $data = wp_parse_args($data, $defaults);

        $result = $this->wpdb->insert(
            $this->getTableName('events'),
            $data,
            array('%s', '%d', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%f', '%f', '%f', '%f', '%d')
        );

        return $result ? $this->wpdb->insert_id : false;
    }

    /**
     * Get event by ID
     */
    public function getEvent($event_id) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->getTableName('events')} WHERE id = %d",
                $event_id
            )
        );
    }

    /**
     * Get current active event
     */
    public function getCurrentEvent() {
        return $this->wpdb->get_row(
            "SELECT * FROM {$this->getTableName('events')}
             WHERE status IN ('upcoming', 'live')
             ORDER BY event_date DESC LIMIT 1"
        );
    }

    /**
     * Get all events
     */
    public function getAllEvents($status = null) {
        $sql = "SELECT * FROM {$this->getTableName('events')}";

        if ($status) {
            $sql .= $this->wpdb->prepare(" WHERE status = %s", $status);
        }

        $sql .= " ORDER BY year DESC";

        return $this->wpdb->get_results($sql);
    }

    /**
     * Update event
     */
    public function updateEvent($event_id, $data) {
        return $this->wpdb->update(
            $this->getTableName('events'),
            $data,
            array('id' => $event_id),
            null,
            array('%d')
        );
    }

    /* ===========================================
       SCHOOL METHODS
       =========================================== */

    /**
     * Create a new school
     */
    public function createSchool($data) {
        // Log the data being inserted for debugging
        error_log('OSB Database: createSchool called with data: ' . print_r($data, true));
        error_log('OSB Database: Data count: ' . count($data));

        // Add missing fields with defaults if needed
        $defaults = array(
            'status' => 'pending',
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
            'logo_url' => null,
            'previous_participation' => null
        );

        $data = wp_parse_args($data, $defaults);

        // Ensure we have the correct field mapping for the schools table
        $school_data = array(
            'wp_user_id' => intval($data['wp_user_id']),
            'school_name' => sanitize_text_field($data['school_name']),
            'school_type' => sanitize_text_field($data['school_type']),
            'state' => sanitize_text_field($data['state']),
            'address' => sanitize_textarea_field($data['address']),
            'phone' => sanitize_text_field($data['contact_phone'] ?? $data['phone'] ?? ''),
            'contact_person' => sanitize_text_field($data['contact_person']),
            'contact_email' => sanitize_email($data['contact_email']),
            'logo_url' => $data['logo_url'],
            'previous_participation' => $data['previous_participation'],
            'status' => sanitize_text_field($data['status']),
            'created_at' => $data['created_at'],
            'updated_at' => $data['updated_at']
        );

        error_log('OSB Database: Final school data: ' . print_r($school_data, true));

        $result = $this->wpdb->insert(
            $this->getTableName('schools'),
            $school_data,
            array('%d', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')
        );

        if (!$result) {
            error_log('OSB Database: Insert failed. MySQL Error: ' . $this->wpdb->last_error);
            error_log('OSB Database: Query: ' . $this->wpdb->last_query);
        } else {
            error_log('OSB Database: School created successfully with ID: ' . $this->wpdb->insert_id);
        }

        return $result ? $this->wpdb->insert_id : false;
    }

    /**
     * Get school by ID
     */
    public function getSchool($school_id) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT s.*, u.display_name as contact_person_name, u.user_email as user_email
                 FROM {$this->getTableName('schools')} s
                 LEFT JOIN {$this->wpdb->users} u ON s.wp_user_id = u.ID
                 WHERE s.id = %d",
                $school_id
            )
        );
    }

    /**
     * Get school by user ID
     */
    public function getSchoolByUserId($user_id) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT * FROM {$this->getTableName('schools')} WHERE wp_user_id = %d",
                $user_id
            )
        );
    }

    /**
     * Get school by email
     */
    public function getSchoolByEmail($email) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT s.*, u.display_name as contact_person_name
                 FROM {$this->getTableName('schools')} s
                 LEFT JOIN {$this->wpdb->users} u ON s.wp_user_id = u.ID
                 WHERE s.contact_email = %s OR u.user_email = %s",
                $email, $email
            )
        );
    }

    /**
     * Get school by temporary token
     */
    public function getSchoolByTempToken($temp_token) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT s.*, u.display_name as contact_person_name
                 FROM {$this->getTableName('schools')} s
                 LEFT JOIN {$this->wpdb->users} u ON s.wp_user_id = u.ID
                 WHERE s.temp_token = %s",
                $temp_token
            )
        );
    }

    /**
     * Get all schools
     */
    public function getAllSchools($status = null) {
        $sql = "SELECT s.*, u.display_name as contact_person_name, u.user_email as user_email
                FROM {$this->getTableName('schools')} s
                LEFT JOIN {$this->wpdb->users} u ON s.wp_user_id = u.ID";

        if ($status) {
            $sql .= $this->wpdb->prepare(" WHERE s.status = %s", $status);
        }

        $sql .= " ORDER BY s.school_name ASC";

        return $this->wpdb->get_results($sql);
    }

    /**
     * Get all schools (alias for getAllSchools)
     */
    public function getSchools($status = null) {
        return $this->getAllSchools($status);
    }

    /**
     * Update school
     */
    public function updateSchool($school_id, $data) {
        return $this->wpdb->update(
            $this->getTableName('schools'),
            $data,
            array('id' => $school_id),
            null,
            array('%d')
        );
    }

    /* ===========================================
       STUDENT METHODS
       =========================================== */

    /**
     * Create a new student
     */
    public function createStudent($data) {
        $result = $this->wpdb->insert(
            $this->getTableName('students'),
            $data
        );

        return $result ? $this->wpdb->insert_id : false;
    }

    /**
     * Get student by ID
     */
    public function getStudent($student_id) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT st.*, s.school_name, u.user_email as student_email, pu.user_email as parent_wp_email
                 FROM {$this->getTableName('students')} st
                 LEFT JOIN {$this->getTableName('schools')} s ON st.school_id = s.id
                 LEFT JOIN {$this->wpdb->users} u ON st.wp_user_id = u.ID
                 LEFT JOIN {$this->wpdb->users} pu ON st.parent_wp_user_id = pu.ID
                 WHERE st.id = %d",
                $student_id
            )
        );
    }

    /**
     * Get students by school ID
     */
    public function getStudentsBySchool($school_id) {
        return $this->wpdb->get_results(
            $this->wpdb->prepare(
                "SELECT st.*, u.user_email as student_email, pu.user_email as parent_email
                 FROM {$this->getTableName('students')} st
                 LEFT JOIN {$this->wpdb->users} u ON st.wp_user_id = u.ID
                 LEFT JOIN {$this->wpdb->users} pu ON st.parent_wp_user_id = pu.ID
                 WHERE st.school_id = %d
                 ORDER BY st.first_name ASC, st.last_name ASC",
                $school_id
            )
        );
    }

    /**
     * Get student by email
     */
    public function getStudentByEmail($email) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT st.*, s.school_name
                 FROM {$this->getTableName('students')} st
                 LEFT JOIN {$this->getTableName('schools')} s ON st.school_id = s.id
                 WHERE st.email = %s OR st.parent_email = %s",
                $email, $email
            )
        );
    }

    /**
     * Update student
     */
    public function updateStudent($student_id, $data) {
        return $this->wpdb->update(
            $this->getTableName('students'),
            $data,
            array('id' => $student_id),
            null,
            array('%d')
        );
    }

    /**
     * Delete student and related data
     */
    public function deleteStudent($student_id) {
        // Start transaction
        $this->wpdb->query('START TRANSACTION');

        try {
            // First, delete related documents
            $documents_deleted = $this->wpdb->delete(
                $this->getTableName('documents'),
                array('student_id' => $student_id),
                array('%d')
            );

            // Delete the student record
            $student_deleted = $this->wpdb->delete(
                $this->getTableName('students'),
                array('id' => $student_id),
                array('%d')
            );

            if ($student_deleted === false) {
                throw new Exception('Failed to delete student record');
            }

            // Commit transaction
            $this->wpdb->query('COMMIT');

            return $student_deleted;

        } catch (Exception $e) {
            // Rollback transaction
            $this->wpdb->query('ROLLBACK');
            error_log("[OSB] Error deleting student: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Add document record
     */
    public function addDocument($data) {
        $result = $this->wpdb->insert(
            $this->getTableName('documents'),
            $data
        );

        if ($result === false) {
            return false;
        }

        return $this->wpdb->insert_id;
    }

    /* ===========================================
       REGISTRATION METHODS
       =========================================== */

    /**
     * Create a new registration
     */
    public function createRegistration($data) {
        // Generate unique token
        if (!isset($data['registration_token'])) {
            $data['registration_token'] = wp_generate_password(32, false);
        }

        $result = $this->wpdb->insert(
            $this->getTableName('registrations'),
            $data
        );

        return $result ? $this->wpdb->insert_id : false;
    }

    /**
     * Get registration by token
     */
    public function getRegistrationByToken($token) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT r.*, s.school_name, e.title as event_title, e.year
                 FROM {$this->getTableName('registrations')} r
                 LEFT JOIN {$this->getTableName('schools')} s ON r.school_id = s.id
                 LEFT JOIN {$this->getTableName('events')} e ON r.event_id = e.id
                 WHERE r.registration_token = %s",
                $token
            )
        );
    }

    /**
     * Get registration by event and school
     */
    public function getRegistration($event_id, $school_id) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT r.*, s.school_name, e.title as event_title
                 FROM {$this->getTableName('registrations')} r
                 LEFT JOIN {$this->getTableName('schools')} s ON r.school_id = s.id
                 LEFT JOIN {$this->getTableName('events')} e ON r.event_id = e.id
                 WHERE r.event_id = %d AND r.school_id = %d",
                $event_id, $school_id
            )
        );
    }

    /**
     * Get registration by ID
     */
    public function getRegistrationById($registration_id) {
        return $this->wpdb->get_row(
            $this->wpdb->prepare(
                "SELECT r.*, s.school_name, e.title as event_title
                 FROM {$this->getTableName('registrations')} r
                 LEFT JOIN {$this->getTableName('schools')} s ON r.school_id = s.id
                 LEFT JOIN {$this->getTableName('events')} e ON r.event_id = e.id
                 WHERE r.id = %d",
                $registration_id
            )
        );
    }

    /**
     * Get all registrations for an event
     */
    public function getEventRegistrations($event_id, $status = null) {
        $sql = "SELECT r.*, s.school_name, s.contact_person, s.contact_email
                FROM {$this->getTableName('registrations')} r
                LEFT JOIN {$this->getTableName('schools')} s ON r.school_id = s.id
                WHERE r.event_id = %d";

        $params = array($event_id);

        if ($status) {
            $sql .= " AND r.status = %s";
            $params[] = $status;
        }

        $sql .= " ORDER BY r.created_at DESC";

        return $this->wpdb->get_results(
            $this->wpdb->prepare($sql, $params)
        );
    }

    /**
     * Update registration
     */
    public function updateRegistration($registration_id, $data) {
        return $this->wpdb->update(
            $this->getTableName('registrations'),
            $data,
            array('id' => $registration_id),
            null,
            array('%d')
        );
    }

    /**
     * Get registrations by school
     */
    public function getRegistrationsBySchool($school_id, $status = null) {
        $sql = "SELECT r.*, e.title as event_title, e.event_date
                FROM {$this->getTableName('registrations')} r
                LEFT JOIN {$this->getTableName('events')} e ON r.event_id = e.id
                WHERE r.school_id = %d";

        $params = array($school_id);

        if ($status) {
            $sql .= " AND r.status = %s";
            $params[] = $status;
        }

        $sql .= " ORDER BY r.created_at DESC";

        return $this->wpdb->get_results(
            $this->wpdb->prepare($sql, $params)
        );
    }

    /* ===========================================
       USER CONFLICT METHODS
       =========================================== */

    /**
     * Create user conflict record
     */
    public function createUserConflict($data) {
        $result = $this->wpdb->insert(
            $this->getTableName('user_conflicts'),
            $data
        );

        return $result ? $this->wpdb->insert_id : false;
    }

    /**
     * Get pending user conflicts
     */
    public function getPendingUserConflicts() {
        return $this->wpdb->get_results(
            "SELECT uc.*, u.display_name as existing_user_name, u.user_email as existing_user_email
             FROM {$this->getTableName('user_conflicts')} uc
             LEFT JOIN {$this->wpdb->users} u ON uc.existing_user_id = u.ID
             WHERE uc.status = 'pending'
             ORDER BY uc.confidence_score DESC, uc.created_at DESC"
        );
    }

    /**
     * Update user conflict
     */
    public function updateUserConflict($conflict_id, $data) {
        return $this->wpdb->update(
            $this->getTableName('user_conflicts'),
            $data,
            array('id' => $conflict_id),
            null,
            array('%d')
        );
    }

    /* ===========================================
       UTILITY METHODS
       =========================================== */

    /**
     * Find potential user matches by name and phone
     */
    public function findPotentialUserMatches($name, $phone = null, $email = null) {
        $name_parts = explode(' ', $name);
        $first_name = $name_parts[0];
        $last_name = isset($name_parts[1]) ? $name_parts[1] : '';

        $sql = "SELECT u.*, um1.meta_value as first_name_meta, um2.meta_value as last_name_meta
                FROM {$this->wpdb->users} u
                LEFT JOIN {$this->wpdb->usermeta} um1 ON u.ID = um1.user_id AND um1.meta_key = 'first_name'
                LEFT JOIN {$this->wpdb->usermeta} um2 ON u.ID = um2.user_id AND um2.meta_key = 'last_name'
                WHERE 1=1";

        $params = array();

        // Add name matching
        if ($first_name) {
            $sql .= " AND (u.display_name LIKE %s OR um1.meta_value LIKE %s)";
            $params[] = '%' . $first_name . '%';
            $params[] = '%' . $first_name . '%';
        }

        // Add email exclusion if provided
        if ($email) {
            $sql .= " AND u.user_email != %s";
            $params[] = $email;
        }

        $sql .= " LIMIT 10";

        if (empty($params)) {
            return array();
        }

        return $this->wpdb->get_results(
            $this->wpdb->prepare($sql, $params)
        );
    }

    /**
     * Get plugin statistics
     */
    public function getStatistics() {
        $stats = array();

        // Count events
        $stats['total_events'] = $this->wpdb->get_var(
            "SELECT COUNT(*) FROM {$this->getTableName('events')}"
        );

        // Count schools
        $stats['total_schools'] = $this->wpdb->get_var(
            "SELECT COUNT(*) FROM {$this->getTableName('schools')}"
        );

        // Count students
        $stats['total_students'] = $this->wpdb->get_var(
            "SELECT COUNT(*) FROM {$this->getTableName('students')}"
        );

        // Count registrations by status
        $stats['registrations'] = $this->wpdb->get_results(
            "SELECT status, COUNT(*) as count
             FROM {$this->getTableName('registrations')}
             GROUP BY status",
            OBJECT_K
        );

        // Total donations
        $stats['total_donations'] = $this->wpdb->get_var(
            "SELECT SUM(amount) FROM {$this->getTableName('donations')} WHERE status = 'completed'"
        );

        // Pending conflicts
        $stats['pending_conflicts'] = $this->wpdb->get_var(
            "SELECT COUNT(*) FROM {$this->getTableName('user_conflicts')} WHERE status = 'pending'"
        );

        return $stats;
    }

    /**
     * Execute custom query
     */
    public function query($sql) {
        return $this->wpdb->query($sql);
    }

    /**
     * Get results from custom query
     */
    public function getResults($sql) {
        return $this->wpdb->get_results($sql);
    }

    /**
     * Prepare SQL query
     */
    public function prepare($sql, ...$args) {
        return $this->wpdb->prepare($sql, $args);
    }
}