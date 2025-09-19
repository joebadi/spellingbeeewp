<?php
/**
 * School Classifier Class
 *
 * Handles school classification and returning school recognition
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class OSB_School_Classifier {

    /**
     * Instance of this class
     */
    private static $instance = null;

    /**
     * Database instance
     */
    private $db;

    /**
     * School classification types
     */
    const CLASSIFICATION_NEW = 'new';
    const CLASSIFICATION_RETURNING = 'returning';
    const CLASSIFICATION_VERIFIED = 'verified';
    const CLASSIFICATION_PREMIUM = 'premium';

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
     * Classify school based on history and performance
     */
    public function classifySchool($school_id) {
        $school = $this->db->getSchool($school_id);

        if (!$school) {
            return self::CLASSIFICATION_NEW;
        }

        $participation_history = $this->getParticipationHistory($school);
        $registration_history = $this->getRegistrationHistory($school_id);

        // Classification logic
        if (empty($participation_history) && empty($registration_history)) {
            return self::CLASSIFICATION_NEW;
        }

        if ($this->isPremiumSchool($school, $participation_history, $registration_history)) {
            return self::CLASSIFICATION_PREMIUM;
        }

        if ($this->isVerifiedSchool($school, $participation_history, $registration_history)) {
            return self::CLASSIFICATION_VERIFIED;
        }

        if (!empty($participation_history) || !empty($registration_history)) {
            return self::CLASSIFICATION_RETURNING;
        }

        return self::CLASSIFICATION_NEW;
    }

    /**
     * Get detailed school profile with classification
     */
    public function getSchoolProfile($school_id) {
        $school = $this->db->getSchool($school_id);

        if (!$school) {
            return null;
        }

        $classification = $this->classifySchool($school_id);
        $participation_history = $this->getParticipationHistory($school);
        $registration_history = $this->getRegistrationHistory($school_id);
        $performance_stats = $this->getPerformanceStats($school_id);

        return array(
            'school' => $school,
            'classification' => $classification,
            'classification_label' => $this->getClassificationLabel($classification),
            'classification_benefits' => $this->getClassificationBenefits($classification),
            'participation_history' => $participation_history,
            'registration_history' => $registration_history,
            'performance_stats' => $performance_stats,
            'total_participations' => count($participation_history) + count($registration_history),
            'last_participation' => $this->getLastParticipation($participation_history, $registration_history),
            'reputation_score' => $this->calculateReputationScore($school, $participation_history, $registration_history)
        );
    }

    /**
     * Get participation history from JSON field
     */
    private function getParticipationHistory($school) {
        if (empty($school->previous_participation)) {
            return array();
        }

        $history = json_decode($school->previous_participation, true);
        return is_array($history) ? $history : array();
    }

    /**
     * Get registration history from database
     */
    private function getRegistrationHistory($school_id) {
        global $wpdb;
        $table_prefix = $wpdb->prefix . OSB_TABLE_PREFIX;

        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT r.*, e.title as event_title, e.event_date, e.status as event_status
                 FROM {$table_prefix}registrations r
                 LEFT JOIN {$table_prefix}events e ON r.event_id = e.id
                 WHERE r.school_id = %d
                 ORDER BY e.event_date DESC",
                $school_id
            )
        );
    }

    /**
     * Calculate performance statistics
     */
    private function getPerformanceStats($school_id) {
        $registration_history = $this->getRegistrationHistory($school_id);

        $stats = array(
            'total_registrations' => count($registration_history),
            'completed_registrations' => 0,
            'approved_registrations' => 0,
            'confirmed_registrations' => 0,
            'on_time_submissions' => 0,
            'document_completion_rate' => 0,
            'average_student_count' => 0
        );

        if (empty($registration_history)) {
            return $stats;
        }

        $total_students = 0;
        foreach ($registration_history as $registration) {
            // Count completion metrics
            if ($registration->status === 'confirmed') {
                $stats['completed_registrations']++;
                $stats['confirmed_registrations']++;
            }
            if ($registration->status === 'approved') {
                $stats['approved_registrations']++;
            }

            // Check submission timing
            if ($this->isOnTimeSubmission($registration)) {
                $stats['on_time_submissions']++;
            }

            $total_students += intval($registration->student_count);
        }

        // Calculate rates
        $stats['completion_rate'] = round(($stats['completed_registrations'] / $stats['total_registrations']) * 100, 1);
        $stats['approval_rate'] = round(($stats['approved_registrations'] / $stats['total_registrations']) * 100, 1);
        $stats['on_time_rate'] = round(($stats['on_time_submissions'] / $stats['total_registrations']) * 100, 1);
        $stats['average_student_count'] = round($total_students / $stats['total_registrations'], 1);

        return $stats;
    }

    /**
     * Determine if school qualifies as Premium
     */
    private function isPremiumSchool($school, $participation_history, $registration_history) {
        $total_participations = count($participation_history) + count($registration_history);

        // Must have participated at least 3 times
        if ($total_participations < 3) {
            return false;
        }

        $stats = $this->getPerformanceStats($school->id);

        // Premium criteria: high performance metrics
        return $stats['completion_rate'] >= 90 &&
               $stats['on_time_rate'] >= 85 &&
               $stats['average_student_count'] >= 3;
    }

    /**
     * Determine if school qualifies as Verified
     */
    private function isVerifiedSchool($school, $participation_history, $registration_history) {
        $total_participations = count($participation_history) + count($registration_history);

        // Must have participated at least 2 times
        if ($total_participations < 2) {
            return false;
        }

        $stats = $this->getPerformanceStats($school->id);

        // Verified criteria: good performance metrics
        return $stats['completion_rate'] >= 70 &&
               $stats['on_time_rate'] >= 60;
    }

    /**
     * Calculate reputation score (0-100)
     */
    private function calculateReputationScore($school, $participation_history, $registration_history) {
        $stats = $this->getPerformanceStats($school->id);
        $total_participations = count($participation_history) + count($registration_history);

        $score = 0;

        // Base score for participation (40 points max)
        $score += min($total_participations * 8, 40);

        // Performance metrics (60 points max)
        $score += ($stats['completion_rate'] * 0.3); // 30 points max
        $score += ($stats['on_time_rate'] * 0.2);    // 20 points max
        $score += min($stats['average_student_count'] * 2, 10); // 10 points max

        return min(round($score), 100);
    }

    /**
     * Get classification label
     */
    public function getClassificationLabel($classification) {
        $labels = array(
            self::CLASSIFICATION_NEW => __('New School', 'omafuru-spelling-bee'),
            self::CLASSIFICATION_RETURNING => __('Returning School', 'omafuru-spelling-bee'),
            self::CLASSIFICATION_VERIFIED => __('Verified School', 'omafuru-spelling-bee'),
            self::CLASSIFICATION_PREMIUM => __('Premium School', 'omafuru-spelling-bee')
        );

        return $labels[$classification] ?? $labels[self::CLASSIFICATION_NEW];
    }

    /**
     * Get classification benefits
     */
    public function getClassificationBenefits($classification) {
        $benefits = array(
            self::CLASSIFICATION_NEW => array(
                __('Welcome package with guidelines', 'omafuru-spelling-bee'),
                __('Step-by-step registration assistance', 'omafuru-spelling-bee'),
                __('Access to tutorial resources', 'omafuru-spelling-bee')
            ),
            self::CLASSIFICATION_RETURNING => array(
                __('Pre-filled registration forms', 'omafuru-spelling-bee'),
                __('Previous student data available', 'omafuru-spelling-bee'),
                __('Streamlined document upload', 'omafuru-spelling-bee'),
                __('Registration history access', 'omafuru-spelling-bee')
            ),
            self::CLASSIFICATION_VERIFIED => array(
                __('Priority review process', 'omafuru-spelling-bee'),
                __('Auto-filled school information', 'omafuru-spelling-bee'),
                __('Dedicated support contact', 'omafuru-spelling-bee'),
                __('Advanced registration analytics', 'omafuru-spelling-bee'),
                __('Early access to new features', 'omafuru-spelling-bee')
            ),
            self::CLASSIFICATION_PREMIUM => array(
                __('Express registration approval', 'omafuru-spelling-bee'),
                __('Automatic document pre-validation', 'omafuru-spelling-bee'),
                __('Premium support priority', 'omafuru-spelling-bee'),
                __('Advanced analytics dashboard', 'omafuru-spelling-bee'),
                __('Beta feature access', 'omafuru-spelling-bee'),
                __('Personalized success manager', 'omafuru-spelling-bee')
            )
        );

        return $benefits[$classification] ?? $benefits[self::CLASSIFICATION_NEW];
    }

    /**
     * Get last participation date
     */
    private function getLastParticipation($participation_history, $registration_history) {
        $dates = array();

        // Add historical participation dates
        foreach ($participation_history as $participation) {
            if (isset($participation['year'])) {
                $dates[] = $participation['year'] . '-01-01';
            }
        }

        // Add registration dates
        foreach ($registration_history as $registration) {
            if ($registration->event_date) {
                $dates[] = $registration->event_date;
            }
        }

        if (empty($dates)) {
            return null;
        }

        sort($dates);
        return end($dates);
    }

    /**
     * Check if submission was on time
     */
    private function isOnTimeSubmission($registration) {
        if (!$registration->submitted_at || !$registration->event_date) {
            return false;
        }

        $submission_date = strtotime($registration->submitted_at);
        $event_date = strtotime($registration->event_date);

        // Consider on-time if submitted at least 7 days before event
        $deadline = $event_date - (7 * 24 * 60 * 60);

        return $submission_date <= $deadline;
    }

    /**
     * Update school participation history
     */
    public function updateParticipationHistory($school_id, $event_year, $performance_data = array()) {
        $school = $this->db->getSchool($school_id);

        if (!$school) {
            return false;
        }

        $history = $this->getParticipationHistory($school);

        // Add new participation record
        $history[] = array(
            'year' => $event_year,
            'date_added' => current_time('mysql'),
            'performance' => $performance_data
        );

        // Update school record
        return $this->db->updateSchool($school_id, array(
            'previous_participation' => wp_json_encode($history)
        ));
    }

    /**
     * Get school recognition summary for display
     */
    public function getSchoolRecognitionSummary($school_id) {
        $profile = $this->getSchoolProfile($school_id);

        if (!$profile) {
            return null;
        }

        return array(
            'classification' => $profile['classification'],
            'classification_label' => $profile['classification_label'],
            'total_participations' => $profile['total_participations'],
            'reputation_score' => $profile['reputation_score'],
            'last_participation' => $profile['last_participation'],
            'key_benefits' => array_slice($profile['classification_benefits'], 0, 3) // Top 3 benefits
        );
    }
}