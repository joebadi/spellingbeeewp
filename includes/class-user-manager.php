<?php
/**
 * User Manager Class
 *
 * Handles user matching, merging, and conflict resolution
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class OSB_User_Manager {

    /**
     * Instance of this class
     */
    private static $instance = null;

    /**
     * Database instance
     */
    private $db;

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
    }

    /**
     * Process school representative registration
     * Handles user matching, creation, or merging
     */
    public function processSchoolRepresentative($form_data) {
        $email = sanitize_email($form_data['contact_email']);
        $name = sanitize_text_field($form_data['contact_person']);
        $phone = sanitize_text_field($form_data['phone'] ?? '');

        // Step 1: Check for exact email match
        $existing_user = get_user_by('email', $email);

        if ($existing_user) {
            return $this->handleExistingUser($existing_user, $form_data, 'school_representative');
        }

        // Step 2: Search for potential matches
        $potential_matches = $this->findPotentialMatches($email, $name, $phone);

        if (!empty($potential_matches)) {
            return $this->handlePotentialMatches($potential_matches, $form_data, 'school_representative');
        }

        // Step 3: Create new user
        return $this->createNewUser($form_data, 'school_representative');
    }

    /**
     * Process parent/guardian registration
     */
    public function processParentGuardian($parent_data, $student_id) {
        $email = sanitize_email($parent_data['parent_email']);
        $name = sanitize_text_field($parent_data['parent_name']);
        $phone = sanitize_text_field($parent_data['parent_phone'] ?? '');

        // Step 1: Check for exact email match
        $existing_user = get_user_by('email', $email);

        if ($existing_user) {
            return $this->handleExistingParent($existing_user, $parent_data, $student_id);
        }

        // Step 2: Search for potential matches
        $potential_matches = $this->findPotentialMatches($email, $name, $phone);

        if (!empty($potential_matches)) {
            return $this->handlePotentialParentMatches($potential_matches, $parent_data, $student_id);
        }

        // Step 3: Create new parent user
        return $this->createNewParentUser($parent_data, $student_id);
    }

    /**
     * Process student registration (optional)
     */
    public function processStudent($student_data) {
        if (empty($student_data['email'])) {
            return array('success' => true, 'user_id' => null, 'message' => 'Student account creation skipped - no email provided');
        }

        $email = sanitize_email($student_data['email']);

        // Check if user already exists
        $existing_user = get_user_by('email', $email);

        if ($existing_user) {
            // Add student role if not already present
            $user = new WP_User($existing_user->ID);
            if (!in_array('student', $user->roles)) {
                $user->add_role('student');
            }

            // Update user meta
            $this->updateStudentUserMeta($existing_user->ID, $student_data);

            return array(
                'success' => true,
                'user_id' => $existing_user->ID,
                'message' => 'Existing user updated with student role',
                'action' => 'updated_existing'
            );
        }

        // Create new student user
        return $this->createNewStudentUser($student_data);
    }

    /**
     * Handle existing user found by email
     */
    private function handleExistingUser($existing_user, $form_data, $role) {
        $user = new WP_User($existing_user->ID);

        // Add role if not already present
        if (!in_array($role, $user->roles)) {
            $user->add_role($role);
        }

        // Update user meta with school information
        $this->updateUserMeta($existing_user->ID, $form_data, $role);

        // Check for conflicting data
        $conflicts = $this->detectConflicts($existing_user, $form_data);

        if (!empty($conflicts)) {
            // Create conflict record for admin review
            $conflict_id = $this->createConflictRecord($existing_user->ID, $form_data, $conflicts, 'email_match', 1.0);

            return array(
                'success' => true,
                'user_id' => $existing_user->ID,
                'message' => 'User found but conflicts detected. Admin review required.',
                'action' => 'conflict_detected',
                'conflict_id' => $conflict_id,
                'conflicts' => $conflicts
            );
        }

        return array(
            'success' => true,
            'user_id' => $existing_user->ID,
            'message' => 'Existing user updated successfully',
            'action' => 'updated_existing'
        );
    }

    /**
     * Handle potential user matches
     */
    private function handlePotentialMatches($matches, $form_data, $role) {
        $best_match = null;
        $best_score = 0;

        foreach ($matches as $match) {
            $score = $this->calculateMatchScore($match, $form_data);
            if ($score > $best_score) {
                $best_score = $score;
                $best_match = $match;
            }
        }

        if ($best_score > 0.7) {
            // High confidence match - create conflict record for admin review
            $conflicts = $this->detectConflicts($best_match, $form_data);
            $conflict_id = $this->createConflictRecord($best_match->ID, $form_data, $conflicts, 'name_phone_match', $best_score);

            return array(
                'success' => true,
                'user_id' => null,
                'message' => 'Potential user match found. Admin review required.',
                'action' => 'potential_match',
                'conflict_id' => $conflict_id,
                'match_score' => $best_score
            );
        }

        // Low confidence - create new user
        return $this->createNewUser($form_data, $role);
    }

    /**
     * Create new WordPress user for school representative
     */
    private function createNewUser($form_data, $role) {
        $email = sanitize_email($form_data['contact_email']);
        $name = sanitize_text_field($form_data['contact_person']);

        // Parse name
        $name_parts = explode(' ', $name, 2);
        $first_name = $name_parts[0];
        $last_name = isset($name_parts[1]) ? $name_parts[1] : '';

        // Generate username
        $username = $this->generateUniqueUsername($email, $name);

        // Generate password
        $password = wp_generate_password(12, false);

        $user_data = array(
            'user_login' => $username,
            'user_email' => $email,
            'user_pass' => $password,
            'display_name' => $name,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'role' => $role
        );

        $user_id = wp_insert_user($user_data);

        if (is_wp_error($user_id)) {
            return array(
                'success' => false,
                'message' => 'Failed to create user: ' . $user_id->get_error_message()
            );
        }

        // Update user meta
        $this->updateUserMeta($user_id, $form_data, $role);

        // Send welcome email with password
        $this->sendWelcomeEmail($user_id, $password, $role);

        return array(
            'success' => true,
            'user_id' => $user_id,
            'message' => 'New user created successfully',
            'action' => 'created_new',
            'password' => $password
        );
    }

    /**
     * Create new parent user
     */
    private function createNewParentUser($parent_data, $student_id) {
        $email = sanitize_email($parent_data['parent_email']);
        $name = sanitize_text_field($parent_data['parent_name']);

        // Parse name
        $name_parts = explode(' ', $name, 2);
        $first_name = $name_parts[0];
        $last_name = isset($name_parts[1]) ? $name_parts[1] : '';

        // Generate username
        $username = $this->generateUniqueUsername($email, $name);

        // Generate password
        $password = wp_generate_password(12, false);

        $user_data = array(
            'user_login' => $username,
            'user_email' => $email,
            'user_pass' => $password,
            'display_name' => $name,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'role' => 'parent_guardian'
        );

        $user_id = wp_insert_user($user_data);

        if (is_wp_error($user_id)) {
            return array(
                'success' => false,
                'message' => 'Failed to create parent user: ' . $user_id->get_error_message()
            );
        }

        // Update user meta
        $this->updateParentUserMeta($user_id, $parent_data, $student_id);

        // Send welcome email
        $this->sendParentWelcomeEmail($user_id, $password);

        return array(
            'success' => true,
            'user_id' => $user_id,
            'message' => 'Parent user created successfully',
            'action' => 'created_new',
            'password' => $password
        );
    }

    /**
     * Create new student user (optional)
     */
    private function createNewStudentUser($student_data) {
        $email = sanitize_email($student_data['email']);
        $first_name = sanitize_text_field($student_data['first_name']);
        $last_name = sanitize_text_field($student_data['last_name']);
        $full_name = $first_name . ' ' . $last_name;

        // Generate username
        $username = $this->generateUniqueUsername($email, $full_name);

        // Generate password
        $password = wp_generate_password(12, false);

        $user_data = array(
            'user_login' => $username,
            'user_email' => $email,
            'user_pass' => $password,
            'display_name' => $full_name,
            'first_name' => $first_name,
            'last_name' => $last_name,
            'role' => 'student'
        );

        $user_id = wp_insert_user($user_data);

        if (is_wp_error($user_id)) {
            return array(
                'success' => false,
                'message' => 'Failed to create student user: ' . $user_id->get_error_message()
            );
        }

        // Update user meta
        $this->updateStudentUserMeta($user_id, $student_data);

        return array(
            'success' => true,
            'user_id' => $user_id,
            'message' => 'Student user created successfully',
            'action' => 'created_new'
        );
    }

    /**
     * Find potential user matches
     */
    private function findPotentialMatches($email, $name, $phone = '') {
        return $this->db->findPotentialUserMatches($name, $phone, $email);
    }

    /**
     * Calculate match score between existing user and new data
     */
    private function calculateMatchScore($user, $form_data) {
        $score = 0;

        // Name similarity
        $existing_name = strtolower($user->display_name);
        $new_name = strtolower($form_data['contact_person'] ?? $form_data['parent_name'] ?? '');

        if ($existing_name && $new_name) {
            $similarity = 0;
            similar_text($existing_name, $new_name, $similarity);
            $score += ($similarity / 100) * 0.6; // 60% weight for name
        }

        // Phone similarity (if available)
        $existing_phone = get_user_meta($user->ID, 'phone', true);
        $new_phone = $form_data['phone'] ?? $form_data['parent_phone'] ?? '';

        if ($existing_phone && $new_phone) {
            // Remove all non-numeric characters for comparison
            $existing_phone_clean = preg_replace('/[^0-9]/', '', $existing_phone);
            $new_phone_clean = preg_replace('/[^0-9]/', '', $new_phone);

            if ($existing_phone_clean === $new_phone_clean) {
                $score += 0.4; // 40% weight for phone
            }
        }

        return $score;
    }

    /**
     * Detect conflicts between existing user and new data
     */
    private function detectConflicts($user, $form_data) {
        $conflicts = array();

        // Check phone number conflicts
        $existing_phone = get_user_meta($user->ID, 'phone', true);
        $new_phone = $form_data['phone'] ?? $form_data['parent_phone'] ?? '';

        if ($existing_phone && $new_phone && $existing_phone !== $new_phone) {
            $conflicts['phone'] = array(
                'existing' => $existing_phone,
                'new' => $new_phone
            );
        }

        // Check name conflicts
        $existing_name = $user->display_name;
        $new_name = $form_data['contact_person'] ?? $form_data['parent_name'] ?? '';

        if ($existing_name && $new_name && strtolower($existing_name) !== strtolower($new_name)) {
            $conflicts['name'] = array(
                'existing' => $existing_name,
                'new' => $new_name
            );
        }

        return $conflicts;
    }

    /**
     * Create conflict record for admin review
     */
    private function createConflictRecord($user_id, $form_data, $conflicts, $type, $score) {
        $conflict_data = array(
            'existing_user_id' => $user_id,
            'new_user_data' => json_encode($form_data),
            'conflict_type' => $type,
            'confidence_score' => $score,
            'conflict_fields' => json_encode($conflicts),
            'status' => 'pending'
        );

        return $this->db->createUserConflict($conflict_data);
    }

    /**
     * Update user meta with form data
     */
    private function updateUserMeta($user_id, $form_data, $role) {
        if ($role === 'school_representative') {
            update_user_meta($user_id, 'phone', sanitize_text_field($form_data['phone'] ?? ''));
            update_user_meta($user_id, 'osb_contact_person', sanitize_text_field($form_data['contact_person']));
            update_user_meta($user_id, 'osb_school_type', sanitize_text_field($form_data['school_type'] ?? ''));
            update_user_meta($user_id, 'osb_school_state', sanitize_text_field($form_data['state'] ?? ''));
        }

        // Update registration history
        $history = get_user_meta($user_id, 'osb_registration_history', true);
        if (!$history) {
            $history = array();
        }

        $current_year = date('Y');
        $history[$current_year] = array(
            'role' => $role,
            'registration_date' => current_time('mysql'),
            'data' => $form_data
        );

        update_user_meta($user_id, 'osb_registration_history', $history);
    }

    /**
     * Update parent user meta
     */
    private function updateParentUserMeta($user_id, $parent_data, $student_id) {
        update_user_meta($user_id, 'phone', sanitize_text_field($parent_data['parent_phone'] ?? ''));
        update_user_meta($user_id, 'osb_relationship', sanitize_text_field($parent_data['parent_relationship'] ?? 'parent'));

        // Add student to children array
        $children = get_user_meta($user_id, 'osb_children_students', true);
        if (!is_array($children)) {
            $children = array();
        }
        if (!in_array($student_id, $children)) {
            $children[] = $student_id;
            update_user_meta($user_id, 'osb_children_students', $children);
        }
    }

    /**
     * Update student user meta
     */
    private function updateStudentUserMeta($user_id, $student_data) {
        update_user_meta($user_id, 'phone', sanitize_text_field($student_data['phone'] ?? ''));
        update_user_meta($user_id, 'osb_student_id', intval($student_data['student_id'] ?? 0));
        update_user_meta($user_id, 'osb_school_id', intval($student_data['school_id'] ?? 0));
        update_user_meta($user_id, 'osb_birth_date', sanitize_text_field($student_data['birth_date'] ?? ''));
        update_user_meta($user_id, 'osb_age', intval($student_data['age'] ?? 0));
    }

    /**
     * Generate unique username
     */
    private function generateUniqueUsername($email, $name) {
        $base_username = sanitize_user(strtolower(str_replace(' ', '', $name)));

        if (empty($base_username)) {
            $base_username = sanitize_user(substr($email, 0, strpos($email, '@')));
        }

        $username = $base_username;
        $counter = 1;

        while (username_exists($username)) {
            $username = $base_username . $counter;
            $counter++;
        }

        return $username;
    }

    /**
     * Send welcome email to new user
     */
    private function sendWelcomeEmail($user_id, $password, $role) {
        $user = get_user_by('ID', $user_id);
        if (!$user) return;

        $subject = sprintf(__('Welcome to %s Spelling Bee Competition', 'omafuru-spelling-bee'), get_option('osb_organization_name', 'Omafuru Foundation'));

        $message = sprintf(__('Hello %s,', 'omafuru-spelling-bee'), $user->display_name) . "\n\n";
        $message .= __('Your account has been created for the spelling bee competition.', 'omafuru-spelling-bee') . "\n\n";
        $message .= sprintf(__('Login URL: %s', 'omafuru-spelling-bee'), wp_login_url()) . "\n";
        $message .= sprintf(__('Username: %s', 'omafuru-spelling-bee'), $user->user_login) . "\n";
        $message .= sprintf(__('Password: %s', 'omafuru-spelling-bee'), $password) . "\n\n";
        $message .= __('Please change your password after logging in.', 'omafuru-spelling-bee') . "\n\n";
        $message .= __('Thank you!', 'omafuru-spelling-bee');

        wp_mail($user->user_email, $subject, $message);
    }

    /**
     * Send welcome email to parent
     */
    private function sendParentWelcomeEmail($user_id, $password) {
        // Similar to sendWelcomeEmail but with parent-specific content
        $this->sendWelcomeEmail($user_id, $password, 'parent_guardian');
    }

    /**
     * Handle existing parent user
     */
    private function handleExistingParent($existing_user, $parent_data, $student_id) {
        $user = new WP_User($existing_user->ID);

        // Add parent role if not already present
        if (!in_array('parent_guardian', $user->roles)) {
            $user->add_role('parent_guardian');
        }

        // Update user meta
        $this->updateParentUserMeta($existing_user->ID, $parent_data, $student_id);

        return array(
            'success' => true,
            'user_id' => $existing_user->ID,
            'message' => 'Existing parent user updated successfully',
            'action' => 'updated_existing'
        );
    }

    /**
     * Handle potential parent matches
     */
    private function handlePotentialParentMatches($matches, $parent_data, $student_id) {
        // For simplicity, create new user if no exact match
        // In production, you might want more sophisticated matching
        return $this->createNewParentUser($parent_data, $student_id);
    }
}