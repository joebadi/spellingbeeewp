<?php
/**
 * Donation Calculator Class
 *
 * Handles prize fund calculations and donation management
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class OSB_Donation_Calculator {

    /**
     * Instance of this class
     */
    private static $instance = null;

    /**
     * Prize distribution percentages
     */
    private $prize_distribution;

    /**
     * Minimum donation amount
     */
    private $min_donation = 5.00;

    /**
     * Maximum donation amount
     */
    private $max_donation = 10000.00;

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
        $this->loadPrizeDistribution();
        $this->setupHooks();
    }

    /**
     * Load prize distribution from options
     */
    private function loadPrizeDistribution() {
        $default_distribution = array(
            'first_place' => 40,      // 40% to 1st place
            'second_place' => 25,     // 25% to 2nd place
            'third_place' => 15,      // 15% to 3rd place
            'participation' => 10,     // 10% for participation awards
            'organization' => 10       // 10% for organization expenses
        );

        $saved_distribution = get_option('osb_prize_distribution', $default_distribution);
        $this->prize_distribution = wp_parse_args($saved_distribution, $default_distribution);
    }

    /**
     * Setup WordPress hooks
     */
    private function setupHooks() {
        add_action('osb_process_donation', array($this, 'processDonation'), 10, 2);
        add_action('osb_refund_donation', array($this, 'processDonationRefund'), 10, 2);
        add_filter('osb_donation_amounts', array($this, 'getSuggestedAmounts'));
    }

    /**
     * Calculate prize breakdown from total fund
     */
    public function calculatePrizeBreakdown($total_fund) {
        $breakdown = array();
        $total_fund = floatval($total_fund);

        foreach ($this->prize_distribution as $category => $percentage) {
            $percentage = floatval($percentage);
            $amount = ($total_fund * $percentage) / 100;
            $breakdown[$category] = array(
                'percentage' => $percentage,
                'amount' => round($amount, 2),
                'formatted' => $this->formatCurrency($amount)
            );
        }

        return $breakdown;
    }

    /**
     * Get current total donations for an event
     */
    public function getTotalDonations($event_id = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . OSB_TABLE_PREFIX . 'donations';

        if ($event_id) {
            $total = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT SUM(amount) FROM {$table_name} WHERE event_id = %d AND status = 'completed'",
                    $event_id
                )
            );
        } else {
            // Get total for current active event
            $db = OSB_Database::getInstance();
            $current_event = $db->getCurrentEvent();

            if ($current_event) {
                $total = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT SUM(amount) FROM {$table_name} WHERE event_id = %d AND status = 'completed'",
                        $current_event->id
                    )
                );
            } else {
                $total = 0;
            }
        }

        return floatval($total);
    }

    /**
     * Get donation statistics
     */
    public function getDonationStats($event_id = null) {
        global $wpdb;
        $table_name = $wpdb->prefix . OSB_TABLE_PREFIX . 'donations';

        $where_clause = $event_id ? $wpdb->prepare("WHERE event_id = %d", $event_id) : "";

        $stats = array(
            'total_amount' => 0,
            'total_donations' => 0,
            'average_donation' => 0,
            'by_status' => array(),
            'recent_donations' => array()
        );

        // Get total and count
        $totals = $wpdb->get_row(
            "SELECT
                SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_amount,
                COUNT(*) as total_donations,
                AVG(CASE WHEN status = 'completed' THEN amount ELSE NULL END) as average_donation
             FROM {$table_name} {$where_clause}"
        );

        if ($totals) {
            $stats['total_amount'] = floatval($totals->total_amount);
            $stats['total_donations'] = intval($totals->total_donations);
            $stats['average_donation'] = floatval($totals->average_donation);
        }

        // Get breakdown by status
        $status_breakdown = $wpdb->get_results(
            "SELECT status, COUNT(*) as count, SUM(amount) as total
             FROM {$table_name} {$where_clause}
             GROUP BY status",
            OBJECT_K
        );

        foreach ($status_breakdown as $status => $data) {
            $stats['by_status'][$status] = array(
                'count' => intval($data->count),
                'total' => floatval($data->total),
                'formatted' => $this->formatCurrency($data->total)
            );
        }

        // Get recent donations
        $stats['recent_donations'] = $wpdb->get_results(
            "SELECT donor_name, donor_email, amount, status, donated_at
             FROM {$table_name} {$where_clause}
             ORDER BY donated_at DESC
             LIMIT 10"
        );

        return $stats;
    }

    /**
     * Process a donation
     */
    public function processDonation($donation_data, $payment_info = array()) {
        global $wpdb;

        // Validate donation amount
        if (!$this->isValidDonationAmount($donation_data['amount'])) {
            return new WP_Error('invalid_amount', __('Invalid donation amount.', 'omafuru-spelling-bee'));
        }

        // Get current event if not specified
        if (empty($donation_data['event_id'])) {
            $db = OSB_Database::getInstance();
            $current_event = $db->getCurrentEvent();
            if (!$current_event) {
                return new WP_Error('no_active_event', __('No active event found.', 'omafuru-spelling-bee'));
            }
            $donation_data['event_id'] = $current_event->id;
        }

        // Generate transaction reference
        $transaction_ref = $this->generateTransactionReference();

        // Prepare donation data
        $insert_data = array(
            'event_id' => intval($donation_data['event_id']),
            'donor_name' => sanitize_text_field($donation_data['donor_name']),
            'donor_email' => sanitize_email($donation_data['donor_email']),
            'donor_phone' => sanitize_text_field($donation_data['donor_phone']),
            'amount' => floatval($donation_data['amount']),
            'currency' => isset($donation_data['currency']) ? $donation_data['currency'] : 'USD',
            'payment_method' => sanitize_text_field($payment_info['method'] ?? 'online'),
            'transaction_reference' => $transaction_ref,
            'status' => 'pending',
            'donor_message' => sanitize_textarea_field($donation_data['donor_message'] ?? ''),
            'is_anonymous' => isset($donation_data['is_anonymous']) ? 1 : 0,
            'created_at' => current_time('mysql')
        );

        // Insert donation record
        $table_name = $wpdb->prefix . OSB_TABLE_PREFIX . 'donations';
        $result = $wpdb->insert($table_name, $insert_data);

        if ($result === false) {
            return new WP_Error('database_error', __('Failed to save donation record.', 'omafuru-spelling-bee'));
        }

        $donation_id = $wpdb->insert_id;

        // Process payment (this would integrate with payment gateway)
        $payment_result = $this->processPayment($donation_id, $donation_data, $payment_info);

        if (is_wp_error($payment_result)) {
            // Update status to failed
            $wpdb->update(
                $table_name,
                array('status' => 'failed', 'updated_at' => current_time('mysql')),
                array('id' => $donation_id),
                array('%s', '%s'),
                array('%d')
            );

            return $payment_result;
        }

        // Send confirmation email
        $this->sendDonationConfirmation($donation_id);

        return array(
            'donation_id' => $donation_id,
            'transaction_reference' => $transaction_ref,
            'status' => 'pending',
            'message' => __('Donation processed successfully. You will receive a confirmation email shortly.', 'omafuru-spelling-bee')
        );
    }

    /**
     * Process payment (placeholder for payment gateway integration)
     */
    private function processPayment($donation_id, $donation_data, $payment_info) {
        // This is a placeholder for actual payment processing
        // In a real implementation, you would integrate with payment gateways
        // like PayPal, Stripe, or local payment providers

        global $wpdb;
        $table_name = $wpdb->prefix . OSB_TABLE_PREFIX . 'donations';

        // For demo purposes, simulate successful payment
        $payment_successful = true; // This would be the actual payment result

        if ($payment_successful) {
            // Update donation status to completed
            $wpdb->update(
                $table_name,
                array(
                    'status' => 'completed',
                    'payment_date' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('id' => $donation_id),
                array('%s', '%s', '%s'),
                array('%d')
            );

            return true;
        } else {
            return new WP_Error('payment_failed', __('Payment processing failed.', 'omafuru-spelling-bee'));
        }
    }

    /**
     * Process donation refund
     */
    public function processDonationRefund($donation_id, $reason = '') {
        global $wpdb;
        $table_name = $wpdb->prefix . OSB_TABLE_PREFIX . 'donations';

        // Get donation record
        $donation = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE id = %d",
                $donation_id
            )
        );

        if (!$donation) {
            return new WP_Error('donation_not_found', __('Donation record not found.', 'omafuru-spelling-bee'));
        }

        if ($donation->status !== 'completed') {
            return new WP_Error('invalid_status', __('Only completed donations can be refunded.', 'omafuru-spelling-bee'));
        }

        // Process refund (integrate with payment gateway)
        $refund_successful = true; // Placeholder

        if ($refund_successful) {
            $wpdb->update(
                $table_name,
                array(
                    'status' => 'refunded',
                    'refund_reason' => $reason,
                    'refund_date' => current_time('mysql'),
                    'updated_at' => current_time('mysql')
                ),
                array('id' => $donation_id),
                array('%s', '%s', '%s', '%s'),
                array('%d')
            );

            // Send refund notification
            $this->sendRefundNotification($donation_id);

            return true;
        }

        return new WP_Error('refund_failed', __('Refund processing failed.', 'omafuru-spelling-bee'));
    }

    /**
     * Get suggested donation amounts
     */
    public function getSuggestedAmounts($amounts = array()) {
        if (empty($amounts)) {
            $amounts = array(10, 25, 50, 100, 250, 500);
        }

        return apply_filters('osb_suggested_donation_amounts', $amounts);
    }

    /**
     * Validate donation amount
     */
    private function isValidDonationAmount($amount) {
        $amount = floatval($amount);
        return $amount >= $this->min_donation && $amount <= $this->max_donation;
    }

    /**
     * Generate transaction reference
     */
    private function generateTransactionReference() {
        return 'OSB_' . date('Ymd') . '_' . wp_generate_password(8, false, false);
    }

    /**
     * Format currency
     */
    private function formatCurrency($amount, $currency = 'USD') {
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
     * Send donation confirmation email
     */
    private function sendDonationConfirmation($donation_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . OSB_TABLE_PREFIX . 'donations';

        $donation = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT d.*, e.title as event_title
                 FROM {$table_name} d
                 LEFT JOIN {$wpdb->prefix}osb_events e ON d.event_id = e.id
                 WHERE d.id = %d",
                $donation_id
            )
        );

        if (!$donation) {
            return false;
        }

        $email_handler = OSB_Email_Handler::getInstance();

        $template_data = array(
            'donor_name' => $donation->donor_name,
            'amount' => $this->formatCurrency($donation->amount, $donation->currency),
            'event_title' => $donation->event_title,
            'transaction_reference' => $donation->transaction_reference,
            'donation_date' => date('F j, Y', strtotime($donation->created_at)),
            'organization_name' => get_option('osb_organization_name', 'Omafuru Foundation'),
        );

        $subject = sprintf(
            __('Thank you for your donation - %s', 'omafuru-spelling-bee'),
            $template_data['organization_name']
        );

        $message = sprintf(
            __("Dear %s,\n\nThank you for your generous donation of %s to support %s.\n\nDonation Details:\n- Amount: %s\n- Event: %s\n- Transaction Reference: %s\n- Date: %s\n\nYour contribution helps make this competition possible and supports educational excellence.\n\nBest regards,\n%s Team", 'omafuru-spelling-bee'),
            $template_data['donor_name'],
            $template_data['amount'],
            $template_data['event_title'],
            $template_data['amount'],
            $template_data['event_title'],
            $template_data['transaction_reference'],
            $template_data['donation_date'],
            $template_data['organization_name']
        );

        return $email_handler->sendEmail($donation->donor_email, $subject, $message);
    }

    /**
     * Send refund notification
     */
    private function sendRefundNotification($donation_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . OSB_TABLE_PREFIX . 'donations';

        $donation = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT d.*, e.title as event_title
                 FROM {$table_name} d
                 LEFT JOIN {$wpdb->prefix}osb_events e ON d.event_id = e.id
                 WHERE d.id = %d",
                $donation_id
            )
        );

        if (!$donation) {
            return false;
        }

        $email_handler = OSB_Email_Handler::getInstance();

        $template_data = array(
            'donor_name' => $donation->donor_name,
            'amount' => $this->formatCurrency($donation->amount, $donation->currency),
            'transaction_reference' => $donation->transaction_reference,
            'refund_reason' => $donation->refund_reason,
            'organization_name' => get_option('osb_organization_name', 'Omafuru Foundation'),
        );

        $subject = sprintf(
            __('Donation Refund Processed - %s', 'omafuru-spelling-bee'),
            $template_data['organization_name']
        );

        $message = sprintf(
            __("Dear %s,\n\nYour donation refund has been processed.\n\nRefund Details:\n- Amount: %s\n- Transaction Reference: %s\n- Reason: %s\n\nThe refund will appear in your original payment method within 3-5 business days.\n\nThank you for your understanding.\n\nBest regards,\n%s Team", 'omafuru-spelling-bee'),
            $template_data['donor_name'],
            $template_data['amount'],
            $template_data['transaction_reference'],
            $template_data['refund_reason'],
            $template_data['organization_name']
        );

        return $email_handler->sendEmail($donation->donor_email, $subject, $message);
    }

    /**
     * Get donation leaderboard
     */
    public function getDonationLeaderboard($event_id = null, $limit = 10) {
        global $wpdb;
        $table_name = $wpdb->prefix . OSB_TABLE_PREFIX . 'donations';

        $where_clause = '';
        $params = array();

        if ($event_id) {
            $where_clause = "WHERE event_id = %d AND status = 'completed' AND is_anonymous = 0";
            $params[] = $event_id;
        } else {
            $where_clause = "WHERE status = 'completed' AND is_anonymous = 0";
        }

        $sql = "SELECT donor_name, SUM(amount) as total_donated, COUNT(*) as donation_count
                FROM {$table_name}
                {$where_clause}
                GROUP BY donor_name, donor_email
                ORDER BY total_donated DESC
                LIMIT %d";

        $params[] = $limit;

        return $wpdb->get_results(
            $wpdb->prepare($sql, $params)
        );
    }

    /**
     * Update prize distribution
     */
    public function updatePrizeDistribution($distribution) {
        // Validate that percentages add up to 100
        $total_percentage = array_sum($distribution);

        if ($total_percentage != 100) {
            return new WP_Error('invalid_distribution', __('Prize distribution percentages must add up to 100%.', 'omafuru-spelling-bee'));
        }

        update_option('osb_prize_distribution', $distribution);
        $this->prize_distribution = $distribution;

        return true;
    }

    /**
     * Get prize distribution
     */
    public function getPrizeDistribution() {
        return $this->prize_distribution;
    }
}