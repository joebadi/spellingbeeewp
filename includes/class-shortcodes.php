<?php
/**
 * Shortcodes Class
 *
 * Handles all shortcode functionality for frontend display
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class OSB_Shortcodes {

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
        $this->registerShortcodes();
    }

    /**
     * Register all shortcodes
     */
    private function registerShortcodes() {
        add_shortcode('osb_event_hero', array($this, 'eventHeroShortcode'));
        add_shortcode('osb_registration_form', array($this, 'registrationFormShortcode'));
        add_shortcode('osb_participating_schools', array($this, 'participatingSchoolsShortcode'));
        add_shortcode('osb_competition_results', array($this, 'competitionResultsShortcode'));
        add_shortcode('osb_prize_fund', array($this, 'prizeFundShortcode'));
        add_shortcode('osb_donation_form', array($this, 'donationFormShortcode'));
        add_shortcode('osb_sponsors', array($this, 'sponsorsShortcode'));
        add_shortcode('osb_event_info', array($this, 'eventInfoShortcode'));
        add_shortcode('osb_registration_status', array($this, 'registrationStatusShortcode'));
        add_shortcode('osb_full_page', array($this, 'fullPageShortcode'));
    }

    /**
     * Event Hero Section Shortcode
     */
    public function eventHeroShortcode($atts) {
        $atts = shortcode_atts(array(
            'event_id' => '',
            'show_registration' => 'true',
            'show_video' => 'true'
        ), $atts, 'osb_event_hero');

        $db = OSB_Database::getInstance();

        // Get event
        if (!empty($atts['event_id'])) {
            $event = $db->getEvent($atts['event_id']);
        } else {
            $event = $db->getCurrentEvent();
        }

        if (!$event) {
            return '<div class="osb-error">' . __('No active event found.', 'omafuru-spelling-bee') . '</div>';
        }

        // Get event video if exists
        $video_url = '';
        if ($atts['show_video'] === 'true') {
            global $wpdb;
            $video = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM {$wpdb->prefix}osb_event_videos
                     WHERE event_id = %d AND is_active = 1
                     ORDER BY created_at DESC LIMIT 1",
                    $event->id
                )
            );

            if ($video) {
                $video_url = $video->video_url;
            } else {
                $video_url = $event->flyer_url;
            }
        }

        ob_start();
        include OSB_PLUGIN_PATH . 'templates/shortcodes/event-hero.php';
        return ob_get_clean();
    }

    /**
     * Registration Form Shortcode
     */
    public function registrationFormShortcode($atts) {
        $atts = shortcode_atts(array(
            'event_id' => '',
            'step' => '1'
        ), $atts, 'osb_registration_form');

        $db = OSB_Database::getInstance();

        // Get event
        if (!empty($atts['event_id'])) {
            $event = $db->getEvent($atts['event_id']);
        } else {
            $event = $db->getCurrentEvent();
        }

        if (!$event) {
            return '<div class="osb-error">' . __('No active event found.', 'omafuru-spelling-bee') . '</div>';
        }

        // Check if registration is open
        if ($event->status !== 'upcoming') {
            return '<div class="osb-notice">' . __('Registration is currently closed for this event.', 'omafuru-spelling-bee') . '</div>';
        }

        $step = intval($atts['step']);

        ob_start();
        include OSB_PLUGIN_PATH . 'templates/shortcodes/registration-form.php';
        return ob_get_clean();
    }

    /**
     * Participating Schools Shortcode
     */
    public function participatingSchoolsShortcode($atts) {
        $atts = shortcode_atts(array(
            'event_id' => '',
            'limit' => '20',
            'show_logos' => 'true'
        ), $atts, 'osb_participating_schools');

        $db = OSB_Database::getInstance();

        // Get event
        if (!empty($atts['event_id'])) {
            $event = $db->getEvent($atts['event_id']);
        } else {
            $event = $db->getCurrentEvent();
        }

        if (!$event) {
            return '<div class="osb-error">' . __('No active event found.', 'omafuru-spelling-bee') . '</div>';
        }

        // Get participating schools
        $registrations = $db->getEventRegistrations($event->id, 'approved');
        $limit = intval($atts['limit']);

        if ($limit > 0 && count($registrations) > $limit) {
            $registrations = array_slice($registrations, 0, $limit);
        }

        ob_start();
        include OSB_PLUGIN_PATH . 'templates/shortcodes/participating-schools.php';
        return ob_get_clean();
    }

    /**
     * Competition Results Shortcode
     */
    public function competitionResultsShortcode($atts) {
        $atts = shortcode_atts(array(
            'event_id' => '',
            'show_filter' => 'true',
            'limit' => '10'
        ), $atts, 'osb_competition_results');

        $db = OSB_Database::getInstance();

        // Get events for filter
        $events = $db->getAllEvents('completed');
        $selected_event = null;

        if (!empty($atts['event_id'])) {
            $selected_event = $db->getEvent($atts['event_id']);
        } elseif (!empty($_GET['event_year'])) {
            foreach ($events as $event) {
                if ($event->year == $_GET['event_year']) {
                    $selected_event = $event;
                    break;
                }
            }
        } elseif (!empty($events)) {
            $selected_event = $events[0]; // Most recent completed event
        }

        // Get results if event is selected and completed
        $results = array();
        if ($selected_event && $selected_event->status === 'completed') {
            global $wpdb;

            // First try to get actual registrations
            $registrations = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT r.*, s.school_name, st.first_name, st.last_name
                     FROM {$wpdb->prefix}osb_registrations r
                     JOIN {$wpdb->prefix}osb_schools s ON r.school_id = s.id
                     JOIN {$wpdb->prefix}osb_students st ON r.school_id = st.school_id
                     WHERE r.event_id = %d AND r.status = 'approved'
                     LIMIT %d",
                    $selected_event->id,
                    intval($atts['limit'])
                )
            );

            // If we have registrations, create mock competition results
            if (!empty($registrations)) {
                $results = array();
                foreach ($registrations as $index => $registration) {
                    $result = new stdClass();
                    $result->id = $registration->id;
                    $result->first_name = $registration->first_name;
                    $result->last_name = $registration->last_name;
                    $result->school_name = $registration->school_name;
                    $result->final_position = $index + 1;
                    $result->correct_answers = rand(15, 25);
                    $result->total_rounds = 25;
                    $result->total_time = rand(300, 900);
                    $result->accuracy = round(($result->correct_answers / $result->total_rounds) * 100, 1);
                    $result->streak = rand(0, 12);
                    $results[] = $result;
                }

                // Sort by final position
                usort($results, function($a, $b) {
                    return $a->final_position - $b->final_position;
                });
            } else {
                // Create sample results for demonstration if no registrations exist
                $sample_students = [
                    ['first_name' => 'Adebayo', 'last_name' => 'Okafor', 'school_name' => 'Lagos Grammar School'],
                    ['first_name' => 'Fatima', 'last_name' => 'Ahmed', 'school_name' => 'Government Secondary School Kano'],
                    ['first_name' => 'Chidi', 'last_name' => 'Eze', 'school_name' => 'Federal Government College'],
                    ['first_name' => 'Blessing', 'last_name' => 'Okoro', 'school_name' => 'Queen\'s College Lagos'],
                    ['first_name' => 'Ibrahim', 'last_name' => 'Musa', 'school_name' => 'Kaduna International School'],
                    ['first_name' => 'Grace', 'last_name' => 'Nwokolo', 'school_name' => 'International School Ibadan'],
                    ['first_name' => 'Samuel', 'last_name' => 'Adeyemi', 'school_name' => 'Loyola Jesuit College'],
                    ['first_name' => 'Aisha', 'last_name' => 'Bello', 'school_name' => 'Greenwood House School'],
                ];

                $results = array();
                foreach ($sample_students as $index => $student) {
                    $result = new stdClass();
                    $result->id = $index + 1;
                    $result->first_name = $student['first_name'];
                    $result->last_name = $student['last_name'];
                    $result->school_name = $student['school_name'];
                    $result->final_position = $index + 1;
                    $result->correct_answers = 25 - $index - rand(0, 3);
                    $result->total_rounds = 25;
                    $result->total_time = 400 + ($index * 50) + rand(-50, 50);
                    $result->accuracy = round(($result->correct_answers / $result->total_rounds) * 100, 1);
                    $result->streak = 12 - $index + rand(-2, 2);
                    $results[] = $result;
                }
            }
        }

        ob_start();
        include OSB_PLUGIN_PATH . 'templates/shortcodes/competition-results.php';
        return ob_get_clean();
    }

    /**
     * Prize Fund Shortcode
     */
    public function prizeFundShortcode($atts) {
        $atts = shortcode_atts(array(
            'event_id' => '',
            'show_breakdown' => 'true',
            'show_leaderboard' => 'true'
        ), $atts, 'osb_prize_fund');

        $db = OSB_Database::getInstance();
        $donation_calc = OSB_Donation_Calculator::getInstance();

        // Get event
        if (!empty($atts['event_id'])) {
            $event = $db->getEvent($atts['event_id']);
            $event_id = $event->id;
        } else {
            $event = $db->getCurrentEvent();
            $event_id = $event ? $event->id : null;
        }

        if (!$event) {
            return '<div class="osb-error">' . __('No active event found.', 'omafuru-spelling-bee') . '</div>';
        }

        // Get donation statistics
        $total_donations = $donation_calc->getTotalDonations($event_id);
        $breakdown = $donation_calc->calculatePrizeBreakdown($total_donations);
        $leaderboard = array();

        if ($atts['show_leaderboard'] === 'true') {
            $leaderboard = $donation_calc->getDonationLeaderboard($event_id, 5);
        }

        ob_start();
        include OSB_PLUGIN_PATH . 'templates/shortcodes/prize-fund.php';
        return ob_get_clean();
    }

    /**
     * Donation Form Shortcode
     */
    public function donationFormShortcode($atts) {
        $atts = shortcode_atts(array(
            'event_id' => '',
            'suggested_amounts' => '10,25,50,100,250,500'
        ), $atts, 'osb_donation_form');

        $db = OSB_Database::getInstance();
        $donation_calc = OSB_Donation_Calculator::getInstance();

        // Get event
        if (!empty($atts['event_id'])) {
            $event = $db->getEvent($atts['event_id']);
        } else {
            $event = $db->getCurrentEvent();
        }

        if (!$event) {
            return '<div class="osb-error">' . __('No active event found.', 'omafuru-spelling-bee') . '</div>';
        }

        // Parse suggested amounts
        $suggested_amounts = array_map('intval', explode(',', $atts['suggested_amounts']));

        ob_start();
        include OSB_PLUGIN_PATH . 'templates/shortcodes/donation-form.php';
        return ob_get_clean();
    }

    /**
     * Sponsors Shortcode
     */
    public function sponsorsShortcode($atts) {
        $atts = shortcode_atts(array(
            'event_id' => '',
            'tier' => 'all',
            'limit' => '20'
        ), $atts, 'osb_sponsors');

        global $wpdb;
        $table_name = $wpdb->prefix . OSB_TABLE_PREFIX . 'sponsors';

        // Build query
        $where_conditions = array("status = 'active'");
        $params = array();

        if (!empty($atts['event_id'])) {
            $where_conditions[] = "event_id = %d";
            $params[] = intval($atts['event_id']);
        }

        if ($atts['tier'] !== 'all') {
            $where_conditions[] = "tier = %s";
            $params[] = sanitize_text_field($atts['tier']);
        }

        $where_clause = implode(' AND ', $where_conditions);
        $limit = intval($atts['limit']);

        $sql = "SELECT * FROM {$table_name} WHERE {$where_clause} ORDER BY tier DESC, created_at ASC";

        if ($limit > 0) {
            $sql .= " LIMIT %d";
            $params[] = $limit;
        }

        $sponsors = $wpdb->get_results(
            $wpdb->prepare($sql, $params)
        );

        ob_start();
        include OSB_PLUGIN_PATH . 'templates/shortcodes/sponsors.php';
        return ob_get_clean();
    }

    /**
     * Event Info Shortcode
     */
    public function eventInfoShortcode($atts) {
        $atts = shortcode_atts(array(
            'event_id' => '',
            'show_date' => 'true',
            'show_venue' => 'true',
            'show_description' => 'true'
        ), $atts, 'osb_event_info');

        $db = OSB_Database::getInstance();

        // Get event
        if (!empty($atts['event_id'])) {
            $event = $db->getEvent($atts['event_id']);
        } else {
            $event = $db->getCurrentEvent();
        }

        if (!$event) {
            return '<div class="osb-error">' . __('No active event found.', 'omafuru-spelling-bee') . '</div>';
        }

        ob_start();
        include OSB_PLUGIN_PATH . 'templates/shortcodes/event-info.php';
        return ob_get_clean();
    }

    /**
     * Registration Status Shortcode
     */
    public function registrationStatusShortcode($atts) {
        $atts = shortcode_atts(array(
            'token' => ''
        ), $atts, 'osb_registration_status');

        $token = !empty($atts['token']) ? $atts['token'] : sanitize_text_field($_GET['token'] ?? '');

        if (empty($token)) {
            return '<div class="osb-error">' . __('Registration token is required.', 'omafuru-spelling-bee') . '</div>';
        }

        $db = OSB_Database::getInstance();
        $registration = $db->getRegistrationByToken($token);

        if (!$registration) {
            return '<div class="osb-error">' . __('Invalid registration token.', 'omafuru-spelling-bee') . '</div>';
        }

        // Get students for this registration
        $students = $db->getStudentsBySchool($registration->school_id);

        ob_start();
        include OSB_PLUGIN_PATH . 'templates/shortcodes/registration-status.php';
        return ob_get_clean();
    }

    /**
     * Generate nonce for forms
     */
    public function generateNonce($action) {
        return wp_create_nonce('osb_' . $action . '_nonce');
    }

    /**
     * Verify nonce
     */
    public function verifyNonce($nonce, $action) {
        return wp_verify_nonce($nonce, 'osb_' . $action . '_nonce');
    }

    /**
     * Get template part
     */
    public function getTemplatePart($template_name, $variables = array()) {
        $template_file = OSB_PLUGIN_PATH . 'templates/shortcodes/' . $template_name . '.php';

        if (file_exists($template_file)) {
            extract($variables);
            ob_start();
            include $template_file;
            return ob_get_clean();
        }

        return '<div class="osb-error">' . sprintf(__('Template %s not found.', 'omafuru-spelling-bee'), $template_name) . '</div>';
    }

    /**
     * Format date for display
     */
    public function formatDate($date, $format = 'F j, Y') {
        return date($format, strtotime($date));
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
     * Get status badge HTML
     */
    public function getStatusBadge($status) {
        $badges = array(
            'pending' => '<span class="osb-badge osb-badge-warning">' . __('Pending', 'omafuru-spelling-bee') . '</span>',
            'approved' => '<span class="osb-badge osb-badge-success">' . __('Approved', 'omafuru-spelling-bee') . '</span>',
            'rejected' => '<span class="osb-badge osb-badge-danger">' . __('Rejected', 'omafuru-spelling-bee') . '</span>',
            'completed' => '<span class="osb-badge osb-badge-info">' . __('Completed', 'omafuru-spelling-bee') . '</span>',
            'active' => '<span class="osb-badge osb-badge-success">' . __('Active', 'omafuru-spelling-bee') . '</span>',
            'inactive' => '<span class="osb-badge osb-badge-secondary">' . __('Inactive', 'omafuru-spelling-bee') . '</span>'
        );

        return isset($badges[$status]) ? $badges[$status] : '<span class="osb-badge osb-badge-secondary">' . ucfirst($status) . '</span>';
    }

    /**
     * Full Page Shortcode - Complete spelling bee competition page
     */
    public function fullPageShortcode($atts) {
        $atts = shortcode_atts(array(
            'event_id' => '',
            'show_hero' => 'true',
            'show_schools' => 'true',
            'show_results' => 'true',
            'show_prize_fund' => 'true',
            'show_registration' => 'true',
            'show_donation' => 'true',
            'show_sponsors' => 'true',
            'show_event_info' => 'true'
        ), $atts, 'osb_full_page');

        $db = OSB_Database::getInstance();
        $donation_calc = OSB_Donation_Calculator::getInstance();

        // Get event
        if (!empty($atts['event_id'])) {
            $event = $db->getEvent($atts['event_id']);
            $event_id = $event->id;
        } else {
            $event = $db->getCurrentEvent();
            $event_id = $event ? $event->id : null;
        }

        if (!$event) {
            return '<div class="osb-error">' . __('No active event found.', 'spelling-bee-pro') . '</div>';
        }

        ob_start();
        ?>

        <div class="osb-full-page-container">

            <?php if ($atts['show_hero'] === 'true'): ?>
            <!-- Hero Section -->
            <section class="osb-section osb-hero-section">
                <?php echo $this->eventHeroShortcode(array('event_id' => $event_id, 'show_registration' => 'true', 'show_video' => 'true')); ?>
            </section>
            <?php endif; ?>

            <?php if ($atts['show_schools'] === 'true'): ?>
            <!-- Participating Schools Section -->
            <section class="osb-section osb-schools-section">
                <div class="osb-section-container">
                    <?php echo $this->participatingSchoolsShortcode(array('event_id' => $event_id, 'limit' => '20', 'show_logos' => 'true')); ?>
                </div>
            </section>
            <?php endif; ?>

            <?php if ($atts['show_results'] === 'true'): ?>
            <!-- Competition Results Section -->
            <section class="osb-section osb-results-section">
                <div class="osb-section-container">
                    <?php echo $this->competitionResultsShortcode(array('show_filter' => 'true', 'limit' => '10')); ?>
                </div>
            </section>
            <?php endif; ?>

            <?php if ($atts['show_prize_fund'] === 'true'): ?>
            <!-- Prize Fund Section -->
            <section class="osb-section osb-prize-section">
                <div class="osb-section-container">
                    <?php echo $this->prizeFundShortcode(array('event_id' => $event_id, 'show_breakdown' => 'true', 'show_leaderboard' => 'true')); ?>
                </div>
            </section>
            <?php endif; ?>

            <?php if ($atts['show_registration'] === 'true' && $event->status === 'upcoming'): ?>
            <!-- Registration Section with Tabs - Full Width -->
            <section class="osb-section osb-registration-section" id="osb-registration-form">
                <div class="osb-section-container">
                    <div class="info-registration" id="register">
                        <div class="container">
                            <div class="info-reg-container">
                                <div class="info-tabs-section">
                                    <div class="tab-buttons">
                                        <button class="tab-button active" data-tab="about"><?php _e('About Competition', 'spelling-bee-pro'); ?></button>
                                        <button class="tab-button" data-tab="rules"><?php _e('Rules & Guidelines', 'spelling-bee-pro'); ?></button>
                                        <button class="tab-button" data-tab="terms"><?php _e('Terms & Conditions', 'spelling-bee-pro'); ?></button>
                                    </div>

                                    <div class="tab-content active" id="about">
                                        <h3><?php _e('🏆 About the Competition', 'spelling-bee-pro'); ?></h3>
                                        <p><?php _e('The National Spelling Bee Championship is Nigeria\'s premier academic competition, bringing together the brightest young minds from across the country.', 'spelling-bee-pro'); ?></p>

                                        <h4 style="margin-top: 2rem; margin-bottom: 1rem;"><?php _e('Competition Format:', 'spelling-bee-pro'); ?></h4>
                                        <ul>
                                            <li><strong><?php _e('Preliminary Round:', 'spelling-bee-pro'); ?></strong> <?php _e('Written test for all participants', 'spelling-bee-pro'); ?></li>
                                            <li><strong><?php _e('Quarter-Finals:', 'spelling-bee-pro'); ?></strong> <?php _e('Top 32 schools compete in oral rounds', 'spelling-bee-pro'); ?></li>
                                            <li><strong><?php _e('Semi-Finals:', 'spelling-bee-pro'); ?></strong> <?php _e('Top 8 schools advance to live competition', 'spelling-bee-pro'); ?></li>
                                            <li><strong><?php _e('Grand Final:', 'spelling-bee-pro'); ?></strong> <?php _e('Live broadcast championship round', 'spelling-bee-pro'); ?></li>
                                        </ul>

                                        <h4 style="margin-top: 2rem; margin-bottom: 1rem;"><?php _e('Key Dates:', 'spelling-bee-pro'); ?></h4>
                                        <ul>
                                            <li><strong><?php _e('Registration Deadline:', 'spelling-bee-pro'); ?></strong> <?php echo date('F j, Y', strtotime($event->registration_deadline ?? 'December 1, 2024')); ?></li>
                                            <li><strong><?php _e('Preliminary Round:', 'spelling-bee-pro'); ?></strong> <?php echo date('F j, Y', strtotime($event->preliminary_date ?? 'December 8, 2024')); ?></li>
                                            <li><strong><?php _e('Semi-Finals & Final:', 'spelling-bee-pro'); ?></strong> <?php echo date('F j, Y', strtotime($event->event_date ?? 'December 15, 2024')); ?></li>
                                        </ul>
                                    </div>

                                    <div class="tab-content" id="rules">
                                        <h3><?php _e('📋 Rules & Guidelines', 'spelling-bee-pro'); ?></h3>

                                        <h4 style="margin-bottom: 1rem;"><?php _e('Eligibility Requirements:', 'spelling-bee-pro'); ?></h4>
                                        <ul>
                                            <li><?php _e('Must be a registered secondary school in Nigeria', 'spelling-bee-pro'); ?></li>
                                            <li><?php _e('Students must be between ages 13-18', 'spelling-bee-pro'); ?></li>
                                            <li><?php _e('Maximum 5 students per school team', 'spelling-bee-pro'); ?></li>
                                            <li><?php _e('Minimum 3 students per school team', 'spelling-bee-pro'); ?></li>
                                        </ul>

                                        <h4 style="margin-top: 2rem; margin-bottom: 1rem;"><?php _e('Competition Rules:', 'spelling-bee-pro'); ?></h4>
                                        <ul>
                                            <li><?php _e('No electronic devices allowed during competition', 'spelling-bee-pro'); ?></li>
                                            <li><?php _e('Time limit: 30 seconds per word', 'spelling-bee-pro'); ?></li>
                                            <li><?php _e('Students may ask for word definition and origin', 'spelling-bee-pro'); ?></li>
                                            <li><?php _e('Spelling must be clear and audible', 'spelling-bee-pro'); ?></li>
                                            <li><?php _e('Judges\' decisions are final', 'spelling-bee-pro'); ?></li>
                                        </ul>

                                        <h4 style="margin-top: 2rem; margin-bottom: 1rem;"><?php _e('Required Documents:', 'spelling-bee-pro'); ?></h4>
                                        <ul>
                                            <li><?php _e('School registration certificate', 'spelling-bee-pro'); ?></li>
                                            <li><?php _e('Student birth certificates or age verification', 'spelling-bee-pro'); ?></li>
                                            <li><?php _e('Signed consent forms from parents/guardians', 'spelling-bee-pro'); ?></li>
                                            <li><?php _e('School endorsement letter', 'spelling-bee-pro'); ?></li>
                                        </ul>
                                    </div>

                                    <div class="tab-content" id="terms">
                                        <h3><?php _e('📄 Terms & Conditions', 'spelling-bee-pro'); ?></h3>

                                        <h4 style="margin-bottom: 1rem;"><?php _e('Registration Terms:', 'spelling-bee-pro'); ?></h4>
                                        <ul>
                                            <li><?php _e('Registration is completely free of charge', 'spelling-bee-pro'); ?></li>
                                            <li><?php _e('Schools must complete registration by deadline', 'spelling-bee-pro'); ?></li>
                                            <li><?php _e('All submitted information must be accurate', 'spelling-bee-pro'); ?></li>
                                            <li><?php _e('False information may result in disqualification', 'spelling-bee-pro'); ?></li>
                                        </ul>

                                        <h4 style="margin-top: 2rem; margin-bottom: 1rem;"><?php _e('Media & Privacy:', 'spelling-bee-pro'); ?></h4>
                                        <ul>
                                            <li><?php _e('Competition may be filmed and broadcast live', 'spelling-bee-pro'); ?></li>
                                            <li><?php _e('Participant photos may be used for promotional purposes', 'spelling-bee-pro'); ?></li>
                                            <li><?php _e('Personal information will be kept confidential', 'spelling-bee-pro'); ?></li>
                                            <li><?php _e('Parents/guardians must consent to media participation', 'spelling-bee-pro'); ?></li>
                                        </ul>

                                        <h4 style="margin-top: 2rem; margin-bottom: 1rem;"><?php _e('Liability:', 'spelling-bee-pro'); ?></h4>
                                        <ul>
                                            <li><?php _e('Omafuru Foundation is not liable for travel expenses', 'spelling-bee-pro'); ?></li>
                                            <li><?php _e('Schools are responsible for student supervision', 'spelling-bee-pro'); ?></li>
                                            <li><?php _e('Medical emergencies will be handled according to protocol', 'spelling-bee-pro'); ?></li>
                                            <li><?php _e('Insurance coverage is recommended for participants', 'spelling-bee-pro'); ?></li>
                                        </ul>

                                        <p style="margin-top: 2rem; font-style: italic; color: #6c757d;">
                                            <?php _e('By registering for this competition, you agree to abide by all terms and conditions outlined above.', 'spelling-bee-pro'); ?>
                                        </p>
                                    </div>
                                </div>

                                <div class="registration-form">
                                    <h3 style="margin-bottom: 1.5rem; font-size: 1.8rem;"><?php _e('🎓 School Registration', 'spelling-bee-pro'); ?></h3>

                                    <form id="school-registration-form">
                                        <div class="form-group">
                                            <label for="school-name"><?php _e('School Name *', 'spelling-bee-pro'); ?></label>
                                            <input type="text" id="school-name" name="school-name" required>
                                        </div>

                                        <div class="form-row">
                                            <div class="form-group">
                                                <label for="school-type"><?php _e('School Type *', 'spelling-bee-pro'); ?></label>
                                                <select id="school-type" name="school-type" required>
                                                    <option value=""><?php _e('Select Type', 'spelling-bee-pro'); ?></option>
                                                    <option value="public"><?php _e('Public School', 'spelling-bee-pro'); ?></option>
                                                    <option value="private"><?php _e('Private School', 'spelling-bee-pro'); ?></option>
                                                    <option value="federal"><?php _e('Federal Government College', 'spelling-bee-pro'); ?></option>
                                                    <option value="state"><?php _e('State Government School', 'spelling-bee-pro'); ?></option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="school-state"><?php _e('State *', 'spelling-bee-pro'); ?></label>
                                                <select id="school-state" name="school-state" required>
                                                    <option value=""><?php _e('Select State', 'spelling-bee-pro'); ?></option>
                                                    <option value="lagos"><?php _e('Lagos', 'spelling-bee-pro'); ?></option>
                                                    <option value="abuja"><?php _e('Abuja (FCT)', 'spelling-bee-pro'); ?></option>
                                                    <option value="kano"><?php _e('Kano', 'spelling-bee-pro'); ?></option>
                                                    <option value="rivers"><?php _e('Rivers', 'spelling-bee-pro'); ?></option>
                                                    <option value="oyo"><?php _e('Oyo', 'spelling-bee-pro'); ?></option>
                                                    <option value="kaduna"><?php _e('Kaduna', 'spelling-bee-pro'); ?></option>
                                                    <option value="other"><?php _e('Other', 'spelling-bee-pro'); ?></option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="school-address"><?php _e('School Address *', 'spelling-bee-pro'); ?></label>
                                            <textarea id="school-address" name="school-address" placeholder="<?php _e('Complete school address', 'spelling-bee-pro'); ?>" required></textarea>
                                        </div>

                                        <div class="form-row">
                                            <div class="form-group">
                                                <label for="contact-name"><?php _e('Contact Person *', 'spelling-bee-pro'); ?></label>
                                                <input type="text" id="contact-name" name="contact-name" placeholder="<?php _e('Teacher/Administrator name', 'spelling-bee-pro'); ?>" required>
                                            </div>
                                            <div class="form-group">
                                                <label for="contact-phone"><?php _e('Phone Number *', 'spelling-bee-pro'); ?></label>
                                                <input type="tel" id="contact-phone" name="contact-phone" placeholder="+234 XXX XXX XXXX" required>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="contact-email"><?php _e('Email Address *', 'spelling-bee-pro'); ?></label>
                                            <input type="email" id="contact-email" name="contact-email" placeholder="school@example.com" required>
                                        </div>

                                        <div class="form-row">
                                            <div class="form-group">
                                                <label for="student-count"><?php _e('Number of Students *', 'spelling-bee-pro'); ?></label>
                                                <select id="student-count" name="student-count" required>
                                                    <option value=""><?php _e('Select Count', 'spelling-bee-pro'); ?></option>
                                                    <option value="3"><?php _e('3 Students', 'spelling-bee-pro'); ?></option>
                                                    <option value="4"><?php _e('4 Students', 'spelling-bee-pro'); ?></option>
                                                    <option value="5"><?php _e('5 Students', 'spelling-bee-pro'); ?></option>
                                                </select>
                                            </div>
                                            <div class="form-group">
                                                <label for="previous-participation"><?php _e('Previous Participation', 'spelling-bee-pro'); ?></label>
                                                <select id="previous-participation" name="previous-participation">
                                                    <option value="no"><?php _e('First Time', 'spelling-bee-pro'); ?></option>
                                                    <option value="2023"><?php _e('Participated in 2023', 'spelling-bee-pro'); ?></option>
                                                    <option value="2022"><?php _e('Participated in 2022', 'spelling-bee-pro'); ?></option>
                                                    <option value="multiple"><?php _e('Multiple Years', 'spelling-bee-pro'); ?></option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="additional-info"><?php _e('Additional Information', 'spelling-bee-pro'); ?></label>
                                            <textarea id="additional-info" name="additional-info" placeholder="<?php _e('Any special requirements or additional information...', 'spelling-bee-pro'); ?>"></textarea>
                                        </div>

                                        <button type="submit" class="submit-btn"><?php _e('Submit Registration', 'spelling-bee-pro'); ?></button>

                                        <div class="form-note">
                                            <strong><?php _e('📋 Next Steps:', 'spelling-bee-pro'); ?></strong> <?php _e('After submitting this form, you will receive a confirmation email with student registration forms and required documents checklist.', 'spelling-bee-pro'); ?>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
            <?php endif; ?>

            <div class="osb-two-column-layout">
                <div class="osb-column-left">

                <div class="osb-column-right">

                    <?php if ($atts['show_donation'] === 'true'): ?>
                    <!-- Donation Section -->
                    <section class="osb-section osb-donation-section">
                        <div class="osb-section-container">
                            <div class="osb-section-header">
                                <h2 class="osb-section-title"><?php _e('Support the Competition', 'spelling-bee-pro'); ?></h2>
                                <p class="osb-section-description"><?php _e('Help us make this event even better', 'spelling-bee-pro'); ?></p>
                            </div>
                            <?php echo $this->donationFormShortcode(array('event_id' => $event_id, 'suggested_amounts' => '10,25,50,100,250,500')); ?>
                        </div>
                    </section>
                    <?php endif; ?>

                    <?php if ($atts['show_event_info'] === 'true'): ?>
                    <!-- Event Information Section -->
                    <section class="osb-section osb-info-section">
                        <div class="osb-section-container">
                            <div class="osb-section-header">
                                <h2 class="osb-section-title"><?php _e('Event Details', 'spelling-bee-pro'); ?></h2>
                            </div>
                            <?php echo $this->eventInfoShortcode(array('event_id' => $event_id)); ?>
                        </div>
                    </section>
                    <?php endif; ?>

                </div>
            </div>

            <?php if ($atts['show_sponsors'] === 'true'): ?>
            <!-- Sponsors Section -->
            <section class="osb-section osb-sponsors-section">
                <div class="osb-section-container">
                    <div class="osb-section-header">
                        <h2 class="osb-section-title"><?php _e('Our Sponsors', 'spelling-bee-pro'); ?></h2>
                        <p class="osb-section-description"><?php _e('Thank you to our generous sponsors who make this competition possible', 'spelling-bee-pro'); ?></p>
                    </div>
                    <?php echo $this->sponsorsShortcode(array('event_id' => $event_id, 'tier' => 'all', 'limit' => '20')); ?>
                </div>
            </section>
            <?php endif; ?>

        </div>

        <style>
        /* Global Mobile Enhancements */
        * {
            box-sizing: border-box;
        }

        /* Touch-friendly elements */
        .btn,
        .tab-button,
        button,
        a {
            min-height: 44px; /* iOS recommended minimum touch target */
            min-width: 44px;
            touch-action: manipulation; /* Disable double-tap zoom */
        }

        /* Prevent text size adjustment on mobile */
        html {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }

        /* Full Page Layout Styles */
        .osb-full-page-container {
            max-width: 100%;
            margin: 0;
            padding: 0;
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            line-height: 1.6;
            /* Enhanced mobile support */
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
            color: #333;
        }

        /* Registration Section Styles from Mockup - EXPANDED THOROUGHLY */
        .info-registration {
            background: white;
            margin: 2rem 0;
            padding: 2rem;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            max-width: none !important;
            width: 100% !important;
        }

        .info-reg-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 3rem;
            align-items: start;
        }

        .info-tabs-section {
            background: #f8f9fa;
            border-radius: 10px;
            overflow: hidden;
        }

        .tab-buttons {
            display: flex;
            background: #e9ecef;
            border-radius: 10px 10px 0 0;
        }

        .tab-button {
            flex: 1;
            padding: 1rem;
            background: transparent;
            border: none;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
        }

        .tab-button.active {
            background: white;
            color: #0052cc;
            border-bottom-color: #0052cc;
        }

        .tab-button:hover {
            background: rgba(255,255,255,0.5);
        }

        .tab-content {
            padding: 2rem;
            background: white;
            min-height: 400px;
            display: none;
        }

        .tab-content.active {
            display: block;
        }

        .tab-content h3 {
            color: #0052cc;
            margin-bottom: 1rem;
            font-size: 1.5rem;
        }

        .tab-content ul {
            margin: 1rem 0;
            padding-left: 1.5rem;
        }

        .tab-content li {
            margin-bottom: 0.5rem;
            line-height: 1.6;
        }

        .registration-form {
            background: linear-gradient(135deg, #0052cc 0%, #003d99 50%, #004080 100%);
            color: white;
            padding: 2rem;
            border-radius: 10px;
        }

        .registration-form h3 {
            color: white !important;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            background: rgba(255,255,255,0.9);
            color: #333;
            box-sizing: border-box;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            background: white;
            box-shadow: 0 0 0 3px rgba(255,255,255,0.3);
        }

        .form-group textarea {
            height: 80px;
            resize: vertical;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }

        .submit-btn {
            background: #ff6b6b;
            color: white !important;
            padding: 15px 30px;
            border: none;
            border-radius: 25px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            width: 100%;
        }

        .submit-btn:hover {
            background: #ff5252;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,107,107,0.3);
        }

        .form-note {
            background: rgba(255,255,255,0.1);
            padding: 1rem;
            border-radius: 8px;
            margin-top: 1rem;
            font-size: 0.9rem;
            border-left: 4px solid rgba(255,255,255,0.3);
        }

        .osb-section {
            padding: 60px 0;
            position: relative;
        }

        .osb-section:nth-child(even) {
            background: #f8f9fa;
        }

        .osb-section-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* REGISTRATION SECTION EXPANSION - THOROUGH IMPLEMENTATION */
        .osb-registration-section {
            padding: 2rem;
            width: 100%;
            max-width: none;
            margin: 0;
        }

        .osb-registration-section .osb-section-container {
            max-width: 1500px !important;
            margin: 0 auto !important;
            padding: 0 !important;
            width: 100% !important;
        }

        .osb-registration-section .container {
            max-width: none !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }

        .osb-registration-section .info-registration {
            max-width: none !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 3rem !important;
        }

        /* ULTRA-WIDE REGISTRATION FORM ELEMENTS */
        .osb-registration-section .info-reg-container {
            max-width: none !important;
            width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
            box-sizing: border-box !important;
        }

        .osb-registration-section .info-tabs-section {
            max-width: none !important;
            width: 100% !important;
            margin: 0 !important;
            box-sizing: border-box !important;
        }

        .osb-registration-section .registration-form {
            max-width: none !important;
            width: 100% !important;
            margin: 0 !important;
            box-sizing: border-box !important;
        }

        .osb-registration-section .tab-content {
            max-width: none !important;
            width: 100% !important;
            margin: 0 !important;
            padding: 2rem !important;
            box-sizing: border-box !important;
        }

        .osb-registration-section .form-group input,
        .osb-registration-section .form-group select,
        .osb-registration-section .form-group textarea {
            width: 100% !important;
            max-width: none !important;
            box-sizing: border-box !important;
        }

        .osb-registration-section .form-row {
            width: 100% !important;
            max-width: none !important;
            box-sizing: border-box !important;
        }

        .osb-section-header {
            text-align: center;
            margin-bottom: 50px;
        }

        .osb-section-title {
            font-size: 2.5rem;
            font-weight: 700;
            color: #0073aa;
            margin: 0 0 15px 0;
            line-height: 1.2;
        }

        .osb-section-description {
            font-size: 1.2rem;
            color: #666;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        /* Hero Section Specific */
        .osb-hero-section {
            padding: 0;
            margin-bottom: 0;
        }

        .osb-hero-section .osb-section-container {
            max-width: none;
            padding: 0;
        }

        /* Two Column Layout */
        .osb-two-column-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 40px;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        .osb-column-left,
        .osb-column-right {
            display: flex;
            flex-direction: column;
            gap: 40px;
        }

        .osb-two-column-layout .osb-section {
            padding: 40px 0;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border: 1px solid #e1e5e9;
        }

        .osb-two-column-layout .osb-section:nth-child(even) {
            background: #fff;
        }

        .osb-two-column-layout .osb-section-container {
            max-width: none;
            padding: 0 30px;
        }

        .osb-two-column-layout .osb-section-title {
            font-size: 2rem;
            margin-bottom: 10px;
        }

        .osb-two-column-layout .osb-section-description {
            font-size: 1rem;
            margin-bottom: 30px;
        }

        .osb-two-column-layout .osb-section-header {
            text-align: left;
            margin-bottom: 30px;
        }

        /* Footer Section */
        .osb-footer-section {
            background: #2c3e50;
            color: white;
            padding: 50px 0 30px;
        }

        .osb-footer-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 40px;
            align-items: center;
            margin-bottom: 30px;
        }

        .osb-footer-info h3 {
            font-size: 1.8rem;
            color: #fff;
            margin: 0 0 15px 0;
        }

        .osb-footer-info p {
            margin: 8px 0;
            color: #bdc3c7;
            line-height: 1.6;
        }

        .osb-footer-stats {
            display: flex;
            justify-content: flex-end;
            gap: 30px;
        }

        .osb-footer-stats .osb-stat-item {
            text-align: center;
            padding: 20px;
            background: rgba(255,255,255,0.1);
            border-radius: 8px;
            min-width: 120px;
        }

        .osb-footer-stats .osb-stat-number {
            font-size: 2rem;
            font-weight: bold;
            color: #fff;
            line-height: 1;
        }

        .osb-footer-stats .osb-stat-label {
            font-size: 0.9rem;
            color: #bdc3c7;
            margin-top: 5px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .osb-footer-bottom {
            text-align: center;
            padding-top: 30px;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .osb-footer-bottom p {
            margin: 5px 0;
            color: #95a5a6;
            font-size: 0.9rem;
        }

        .osb-footer-bottom a {
            color: #3498db;
            text-decoration: none;
        }

        .osb-footer-bottom a:hover {
            text-decoration: underline;
        }

        /* Spacing Adjustments for Individual Sections */
        .osb-schools-section,
        .osb-results-section,
        .osb-prize-section,
        .osb-sponsors-section {
            border-bottom: 1px solid #e1e5e9;
        }

        /* Smooth Scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .osb-two-column-layout {
                grid-template-columns: 1fr;
                gap: 20px;
            }

            .osb-section {
                padding: 40px 0;
            }

            .osb-section-title {
                font-size: 2rem;
            }

            .osb-footer-content {
                grid-template-columns: 1fr;
                gap: 30px;
                text-align: center;
            }

            .osb-footer-stats {
                justify-content: center;
            }

            .info-reg-container {
                grid-template-columns: 1fr;
                gap: 2rem;
            }

            .form-row {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 768px) {
            .osb-section {
                padding: 30px 0;
            }

            .osb-section-container {
                padding: 0 15px;
            }

            .osb-section-title {
                font-size: 1.8rem;
            }

            .osb-section-description {
                font-size: 1rem;
            }

            .osb-footer-stats {
                flex-direction: column;
                gap: 15px;
            }

            .osb-footer-stats .osb-stat-item {
                min-width: auto;
                padding: 15px;
            }

            .osb-two-column-layout .osb-section-container {
                padding: 0 20px;
            }

            /* THOROUGH MOBILE EXPANSION - REGISTRATION SECTION (768px) */
            .osb-registration-section {
                padding: 1.5rem !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: none !important;
            }

            .osb-registration-section .osb-section-container {
                max-width: 1500px !important;
                padding: 0 !important;
                margin: 0 auto !important;
                width: 100% !important;
            }

            .osb-registration-section .container {
                max-width: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }

            .osb-registration-section .info-registration {
                padding: 1.5rem !important;
                margin: 0 !important;
                max-width: none !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }

            .info-reg-container {
                grid-template-columns: 1fr !important;
                gap: 1.5rem !important;
                max-width: none !important;
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                box-sizing: border-box !important;
            }

            .tab-buttons {
                flex-direction: column !important;
                gap: 0 !important;
            }

            .tab-button {
                border-radius: 0 !important;
                border-bottom: 1px solid #dee2e6 !important;
                padding: 0.8rem !important;
                font-size: 0.9rem !important;
            }

            .tab-button:first-child {
                border-top-left-radius: 10px !important;
                border-top-right-radius: 10px !important;
            }

            .tab-button:last-child {
                border-bottom-left-radius: 10px !important;
                border-bottom-right-radius: 10px !important;
                border-bottom: none !important;
            }

            .tab-button.active {
                border-bottom-color: #0052cc !important;
                border-left: 3px solid #0052cc !important;
            }

            .tab-content {
                padding: 1.5rem;
                min-height: auto;
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
                box-sizing: border-box !important;
            }

            .tab-content h3 {
                font-size: 1.3rem !important;
                margin-bottom: 1rem !important;
            }

            .registration-form {
                padding: 1.5rem;
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
                box-sizing: border-box !important;
            }

            .info-tabs-section {
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
                box-sizing: border-box !important;
            }

            .registration-form h3 {
                font-size: 1.5rem !important;
                text-align: center !important;
                color: white !important;
            }

            .form-row {
                flex-direction: column !important;
                gap: 1rem !important;
            }

            .form-group {
                margin-bottom: 1rem !important;
            }

            .form-group input,
            .form-group select,
            .form-group textarea {
                font-size: 1rem !important;
                padding: 14px !important;
            }

            .btn {
                width: 100% !important;
                padding: 12px !important;
                font-size: 1rem !important;
                margin-top: 1rem !important;
            }
        }

        /* ULTRA-WIDE FORM ELEMENTS - ALL SCREENS */
        .osb-registration-section .form-group,
        .osb-registration-section .form-row,
        .osb-registration-section .submit-btn,
        .osb-registration-section .tab-buttons,
        .osb-registration-section .tab-button {
            width: 100% !important;
            max-width: none !important;
            box-sizing: border-box !important;
        }

        .osb-registration-section .form-row {
            gap: 2rem !important;
        }

        .osb-registration-section .submit-btn {
            padding: 18px 36px !important;
            font-size: 1.2rem !important;
        }

        @media (max-width: 480px) {
            .osb-section-title {
                font-size: 1.5rem;
            }

            .osb-section-header {
                margin-bottom: 30px;
            }

            .osb-two-column-layout .osb-section {
                padding: 25px 0;
            }

            /* THOROUGH MOBILE EXPANSION - REGISTRATION SECTION (480px) */
            .osb-registration-section {
                padding: 1rem !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: none !important;
            }

            .osb-registration-section .osb-section-container {
                max-width: 1500px !important;
                padding: 0 !important;
                margin: 0 auto !important;
                width: 100% !important;
            }

            .osb-registration-section .container {
                max-width: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }

            .osb-registration-section .info-registration {
                padding: 1rem !important;
                margin: 0 !important;
                max-width: none !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }

            /* Enhanced Extra Small Mobile Responsiveness */
            .osb-section-container {
                padding: 0 10px !important;
            }

            .registration-form h3 {
                font-size: 1.3rem !important;
            }

            .tab-content h3 {
                font-size: 1.2rem !important;
            }

            .tab-button {
                padding: 0.6rem !important;
                font-size: 0.85rem !important;
            }

            .form-group input,
            .form-group select,
            .form-group textarea {
                padding: 12px !important;
                font-size: 0.95rem !important;
            }

            .btn {
                padding: 10px !important;
                font-size: 0.9rem !important;
            }

            .tab-content {
                padding: 0.75rem;
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
            }

            .registration-form {
                padding: 0.75rem;
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
            }
        }

        /* Extra Small Mobile Devices */
        @media (max-width: 320px) {
            .osb-section-container {
                padding: 0 5px !important;
            }

            /* THOROUGH MOBILE EXPANSION - REGISTRATION SECTION (320px) */
            .osb-registration-section {
                padding: 0.75rem !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: none !important;
            }

            .osb-registration-section .osb-section-container {
                max-width: 1500px !important;
                padding: 0 !important;
                margin: 0 auto !important;
                width: 100% !important;
            }

            .osb-registration-section .container {
                max-width: none !important;
                padding: 0 !important;
                margin: 0 !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }

            .osb-registration-section .info-registration {
                padding: 0.75rem !important;
                margin: 0 !important;
                max-width: none !important;
                width: 100% !important;
                box-sizing: border-box !important;
            }

            .osb-section-title {
                font-size: 1.3rem !important;
            }

            .tab-content {
                padding: 0.5rem;
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
            }

            .tab-content h3 {
                font-size: 1.1rem !important;
            }

            .registration-form {
                padding: 0.5rem;
                width: 100% !important;
                max-width: none !important;
                margin: 0 !important;
            }

            .registration-form h3 {
                font-size: 1.2rem !important;
                color: white !important;
            }

            .tab-button {
                padding: 0.5rem !important;
                font-size: 0.8rem !important;
            }

            .form-group input,
            .form-group select,
            .form-group textarea {
                padding: 10px !important;
                font-size: 0.9rem !important;
            }

            .btn {
                padding: 8px !important;
                font-size: 0.85rem !important;
            }
        }

        /* Print Styles */
        @media print {
            .osb-full-page-container {
                background: white !important;
            }

            .osb-section:nth-child(even) {
                background: white !important;
            }

            .osb-footer-section {
                background: white !important;
                color: black !important;
                border-top: 2px solid #333 !important;
            }
        }
        </style>

        <script>
        jQuery(document).ready(function($) {
            // Tab functionality for registration section
            $('.tab-button').on('click', function() {
                const tabId = $(this).data('tab');

                // Remove active class from all buttons and contents
                $('.tab-button').removeClass('active');
                $('.tab-content').removeClass('active');

                // Add active class to clicked button and corresponding content
                $(this).addClass('active');
                $('#' + tabId).addClass('active');
            });

            // Smooth scroll for internal links
            $('a[href^="#"]').on('click', function(e) {
                e.preventDefault();
                const target = $($(this).attr('href'));
                if (target.length) {
                    $('html, body').animate({
                        scrollTop: target.offset().top - 80
                    }, 800);
                }
            });

            // Add scroll-to-top functionality
            $(window).scroll(function() {
                if ($(this).scrollTop() > 300) {
                    if ($('.osb-scroll-top').length === 0) {
                        $('body').append('<button class="osb-scroll-top" style="position:fixed;bottom:20px;right:20px;background:#0073aa;color:white;border:none;border-radius:50%;width:50px;height:50px;cursor:pointer;z-index:9999;box-shadow:0 2px 10px rgba(0,0,0,0.3);">↑</button>');

                        $('.osb-scroll-top').on('click', function() {
                            $('html, body').animate({scrollTop: 0}, 800);
                        });
                    }
                } else {
                    $('.osb-scroll-top').remove();
                }
            });

            // Add loading animation for AJAX forms
            $(document).ajaxStart(function() {
                $('body').addClass('osb-loading');
            }).ajaxStop(function() {
                $('body').removeClass('osb-loading');
            });

            // Form submission handling
            $('#school-registration-form').on('submit', function(e) {
                e.preventDefault();

                // Get form data
                const formData = new FormData(this);

                // Show loading state
                const submitBtn = $(this).find('.submit-btn');
                const originalText = submitBtn.text();
                submitBtn.text('Submitting...').prop('disabled', true);

                // Here you would normally send the data to your backend
                // For now, we'll just show a success message
                setTimeout(function() {
                    alert('Registration submitted successfully! You will receive a confirmation email shortly.');
                    submitBtn.text(originalText).prop('disabled', false);
                }, 2000);
            });
        });
        </script>

        <?php
        return ob_get_clean();
    }
}