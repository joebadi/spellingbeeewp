<?php
/**
 * Donations Admin Template
 *
 * @var array $stats
 * @var array $events
 * @var int $selected_event_id
 * @var float $total_donations
 * @var array $prize_breakdown
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$admin_menu = OSB_Admin_Menu::getInstance();
$donation_calc = OSB_Donation_Calculator::getInstance();
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Donations & Prize Fund', 'spelling-bee-pro'); ?></h1>

    <?php if (isset($_GET['refunded'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Donation refunded successfully.', 'spelling-bee-pro'); ?></p>
        </div>
    <?php endif; ?>

    <hr class="wp-header-end">

    <!-- Event Selection and Filters -->
    <div class="osb-filters">
        <div class="osb-filter-bar">
            <select id="osb-event-filter" class="osb-filter-select">
                <option value=""><?php _e('Select Event', 'spelling-bee-pro'); ?></option>
                <?php foreach ($events as $event): ?>
                    <option value="<?php echo $event->id; ?>" <?php selected($selected_event_id, $event->id); ?>>
                        <?php echo esc_html($event->title . ' (' . $event->year . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select id="osb-status-filter" class="osb-filter-select">
                <option value=""><?php _e('All Statuses', 'spelling-bee-pro'); ?></option>
                <option value="completed"><?php _e('Completed', 'spelling-bee-pro'); ?></option>
                <option value="pending"><?php _e('Pending', 'spelling-bee-pro'); ?></option>
                <option value="failed"><?php _e('Failed', 'spelling-bee-pro'); ?></option>
                <option value="refunded"><?php _e('Refunded', 'spelling-bee-pro'); ?></option>
            </select>

            <input type="search" id="osb-search-donations" class="osb-search-input"
                   placeholder="<?php _e('Search donors...', 'spelling-bee-pro'); ?>">

            <button type="button" id="osb-clear-filters" class="button">
                <?php _e('Clear Filters', 'spelling-bee-pro'); ?>
            </button>

            <button type="button" id="osb-export-donations" class="button">
                <?php _e('Export CSV', 'spelling-bee-pro'); ?>
            </button>

            <?php if ($selected_event_id): ?>
                <button type="button" id="osb-manual-donation" class="button button-primary">
                    <?php _e('Add Manual Donation', 'spelling-bee-pro'); ?>
                </button>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$selected_event_id): ?>
        <div class="osb-no-event-selected">
            <div class="osb-empty-state">
                <div class="osb-empty-icon">💰</div>
                <h3><?php _e('Select an Event', 'spelling-bee-pro'); ?></h3>
                <p><?php _e('Please select an event from the dropdown above to view its donations and prize fund.', 'spelling-bee-pro'); ?></p>
            </div>
        </div>

    <?php else: ?>
        <!-- Prize Fund Overview -->
        <div class="osb-prize-fund-overview">
            <div class="osb-fund-summary">
                <div class="osb-total-fund">
                    <div class="osb-fund-amount">$<?php echo number_format($total_donations, 2); ?></div>
                    <div class="osb-fund-label"><?php _e('Total Prize Fund', 'spelling-bee-pro'); ?></div>
                </div>

                <div class="osb-fund-goal">
                    <?php
                    $current_event = null;
                    foreach ($events as $event) {
                        if ($event->id == $selected_event_id) {
                            $current_event = $event;
                            break;
                        }
                    }
                    ?>
                    <?php if ($current_event && $current_event->prize_fund_goal > 0): ?>
                        <?php
                        $goal = floatval($current_event->prize_fund_goal);
                        $percentage = ($total_donations / $goal) * 100;
                        $percentage = min(100, $percentage);
                        ?>
                        <div class="osb-goal-progress">
                            <div class="osb-progress-bar">
                                <div class="osb-progress-fill" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                            <div class="osb-goal-text">
                                <?php printf(__('%s%% of $%s goal', 'spelling-bee-pro'),
                                    number_format($percentage, 1),
                                    number_format($goal, 2)
                                ); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="osb-no-goal">
                            <?php _e('No fund goal set', 'spelling-bee-pro'); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Prize Breakdown -->
            <?php if (!empty($prize_breakdown) && $total_donations > 0): ?>
                <div class="osb-prize-breakdown">
                    <h3><?php _e('Prize Distribution', 'spelling-bee-pro'); ?></h3>
                    <div class="osb-breakdown-grid">
                        <?php foreach ($prize_breakdown as $category => $data): ?>
                            <div class="osb-breakdown-item osb-breakdown-<?php echo esc_attr($category); ?>">
                                <div class="osb-breakdown-amount"><?php echo esc_html($data['formatted']); ?></div>
                                <div class="osb-breakdown-label">
                                    <?php
                                    switch ($category) {
                                        case 'first_place':
                                            _e('1st Place', 'spelling-bee-pro');
                                            break;
                                        case 'second_place':
                                            _e('2nd Place', 'spelling-bee-pro');
                                            break;
                                        case 'third_place':
                                            _e('3rd Place', 'spelling-bee-pro');
                                            break;
                                        case 'participation':
                                            _e('Participation', 'spelling-bee-pro');
                                            break;
                                        case 'organization':
                                            _e('Organization', 'spelling-bee-pro');
                                            break;
                                        default:
                                            echo esc_html(ucfirst(str_replace('_', ' ', $category)));
                                    }
                                    ?>
                                </div>
                                <div class="osb-breakdown-percentage"><?php echo $data['percentage']; ?>%</div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Donation Statistics -->
        <?php if (!empty($stats)): ?>
            <div class="osb-donation-stats">
                <div class="osb-stats-grid">
                    <div class="osb-stat-card">
                        <div class="osb-stat-number"><?php echo intval($stats['total_donations']); ?></div>
                        <div class="osb-stat-label"><?php _e('Total Donations', 'spelling-bee-pro'); ?></div>
                    </div>

                    <div class="osb-stat-card">
                        <div class="osb-stat-number">$<?php echo number_format($stats['average_donation'], 2); ?></div>
                        <div class="osb-stat-label"><?php _e('Average Donation', 'spelling-bee-pro'); ?></div>
                    </div>

                    <?php if (!empty($stats['by_status']['completed'])): ?>
                        <div class="osb-stat-card osb-stat-completed">
                            <div class="osb-stat-number"><?php echo intval($stats['by_status']['completed']['count']); ?></div>
                            <div class="osb-stat-label"><?php _e('Completed', 'spelling-bee-pro'); ?></div>
                            <div class="osb-stat-amount"><?php echo esc_html($stats['by_status']['completed']['formatted']); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($stats['by_status']['pending'])): ?>
                        <div class="osb-stat-card osb-stat-pending">
                            <div class="osb-stat-number"><?php echo intval($stats['by_status']['pending']['count']); ?></div>
                            <div class="osb-stat-label"><?php _e('Pending', 'spelling-bee-pro'); ?></div>
                            <div class="osb-stat-amount"><?php echo esc_html($stats['by_status']['pending']['formatted']); ?></div>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($stats['by_status']['refunded'])): ?>
                        <div class="osb-stat-card osb-stat-refunded">
                            <div class="osb-stat-number"><?php echo intval($stats['by_status']['refunded']['count']); ?></div>
                            <div class="osb-stat-label"><?php _e('Refunded', 'spelling-bee-pro'); ?></div>
                            <div class="osb-stat-amount"><?php echo esc_html($stats['by_status']['refunded']['formatted']); ?></div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Donations List -->
        <?php if (!empty($stats['recent_donations'])): ?>
            <div class="osb-donations-table-container">
                <h3><?php _e('Recent Donations', 'spelling-bee-pro'); ?></h3>
                <table class="wp-list-table widefat fixed striped osb-donations-table">
                    <thead>
                        <tr>
                            <th scope="col" class="manage-column column-cb check-column">
                                <label class="screen-reader-text" for="cb-select-all-1"><?php _e('Select All', 'spelling-bee-pro'); ?></label>
                                <input id="cb-select-all-1" type="checkbox">
                            </th>
                            <th scope="col" class="manage-column column-donor column-primary">
                                <?php _e('Donor', 'spelling-bee-pro'); ?>
                            </th>
                            <th scope="col" class="manage-column column-amount">
                                <?php _e('Amount', 'spelling-bee-pro'); ?>
                            </th>
                            <th scope="col" class="manage-column column-status">
                                <?php _e('Status', 'spelling-bee-pro'); ?>
                            </th>
                            <th scope="col" class="manage-column column-method">
                                <?php _e('Method', 'spelling-bee-pro'); ?>
                            </th>
                            <th scope="col" class="manage-column column-date">
                                <?php _e('Date', 'spelling-bee-pro'); ?>
                            </th>
                            <th scope="col" class="manage-column column-actions">
                                <?php _e('Actions', 'spelling-bee-pro'); ?>
                            </th>
                        </tr>
                    </thead>

                    <tbody id="osb-donations-tbody">
                        <?php foreach ($stats['recent_donations'] as $donation): ?>
                            <tr class="osb-donation-row" data-status="<?php echo esc_attr($donation->status); ?>">
                                <th scope="row" class="check-column">
                                    <input type="checkbox" name="donation[]" value="<?php echo $donation->id; ?>">
                                </th>

                                <td class="column-donor column-primary" data-colname="<?php _e('Donor', 'spelling-bee-pro'); ?>">
                                    <strong>
                                        <?php echo $donation->is_anonymous ? __('Anonymous Donor', 'spelling-bee-pro') : esc_html($donation->donor_name); ?>
                                    </strong>
                                    <?php if (!$donation->is_anonymous && !empty($donation->donor_email)): ?>
                                        <br><a href="mailto:<?php echo esc_attr($donation->donor_email); ?>">
                                            <?php echo esc_html($donation->donor_email); ?>
                                        </a>
                                    <?php endif; ?>

                                    <div class="row-actions">
                                        <span class="view">
                                            <a href="#" class="osb-view-donation" data-donation-id="<?php echo $donation->id; ?>">
                                                <?php _e('View Details', 'spelling-bee-pro'); ?>
                                            </a>
                                        </span>
                                        <?php if ($donation->status === 'completed'): ?>
                                            |
                                            <span class="refund">
                                                <a href="#" class="osb-refund-donation" data-donation-id="<?php echo $donation->id; ?>">
                                                    <?php _e('Refund', 'spelling-bee-pro'); ?>
                                                </a>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <button type="button" class="toggle-row">
                                        <span class="screen-reader-text"><?php _e('Show more details', 'spelling-bee-pro'); ?></span>
                                    </button>
                                </td>

                                <td class="column-amount" data-colname="<?php _e('Amount', 'spelling-bee-pro'); ?>">
                                    <span class="osb-amount osb-amount-<?php echo esc_attr($donation->status); ?>">
                                        $<?php echo number_format($donation->amount, 2); ?>
                                    </span>
                                    <?php if (!empty($donation->currency) && $donation->currency !== 'USD'): ?>
                                        <br><small class="osb-currency"><?php echo esc_html($donation->currency); ?></small>
                                    <?php endif; ?>
                                </td>

                                <td class="column-status" data-colname="<?php _e('Status', 'spelling-bee-pro'); ?>">
                                    <span class="osb-status osb-status-<?php echo esc_attr($donation->status); ?>">
                                        <?php echo esc_html(ucfirst($donation->status)); ?>
                                    </span>
                                </td>

                                <td class="column-method" data-colname="<?php _e('Method', 'spelling-bee-pro'); ?>">
                                    <?php echo esc_html(ucfirst($donation->payment_method)); ?>
                                </td>

                                <td class="column-date" data-colname="<?php _e('Date', 'spelling-bee-pro'); ?>">
                                    <abbr title="<?php echo esc_attr(date('F j, Y g:i a', strtotime($donation->created_at))); ?>">
                                        <?php echo date('M j, Y', strtotime($donation->created_at)); ?>
                                    </abbr>
                                </td>

                                <td class="column-actions" data-colname="<?php _e('Actions', 'spelling-bee-pro'); ?>">
                                    <div class="osb-action-buttons">
                                        <button type="button" class="button button-small osb-send-receipt"
                                                data-donation-id="<?php echo $donation->id; ?>">
                                            <?php _e('Send Receipt', 'spelling-bee-pro'); ?>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Donation Leaderboard -->
            <?php
            $leaderboard = $donation_calc->getDonationLeaderboard($selected_event_id, 10);
            if (!empty($leaderboard)):
            ?>
                <div class="osb-donor-leaderboard">
                    <h3><?php _e('Top Donors', 'spelling-bee-pro'); ?></h3>
                    <div class="osb-leaderboard-list">
                        <?php foreach ($leaderboard as $index => $donor): ?>
                            <div class="osb-leaderboard-item osb-rank-<?php echo $index + 1; ?>">
                                <div class="osb-donor-rank">#<?php echo $index + 1; ?></div>
                                <div class="osb-donor-info">
                                    <div class="osb-donor-name"><?php echo esc_html($donor->donor_name); ?></div>
                                    <div class="osb-donor-stats">
                                        <span class="osb-donation-amount">$<?php echo number_format($donor->total_donated, 2); ?></span>
                                        <span class="osb-donation-count"><?php printf(_n('%d donation', '%d donations', $donor->donation_count, 'spelling-bee-pro'), $donor->donation_count); ?></span>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

        <?php else: ?>
            <div class="osb-no-donations">
                <div class="osb-empty-state">
                    <div class="osb-empty-icon">💰</div>
                    <h3><?php _e('No Donations Yet', 'spelling-bee-pro'); ?></h3>
                    <p><?php _e('No donations have been received for this event yet.', 'spelling-bee-pro'); ?></p>
                    <button type="button" id="osb-manual-donation-empty" class="button button-primary button-large">
                        <?php _e('Add Manual Donation', 'spelling-bee-pro'); ?>
                    </button>
                </div>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- Manual Donation Modal -->
<div id="osb-donation-modal" class="osb-modal" style="display: none;">
    <div class="osb-modal-content">
        <div class="osb-modal-header">
            <h3><?php _e('Add Manual Donation', 'spelling-bee-pro'); ?></h3>
            <button type="button" class="osb-modal-close">&times;</button>
        </div>
        <div class="osb-modal-body">
            <form id="osb-donation-form">
                <input type="hidden" name="event_id" value="<?php echo intval($selected_event_id); ?>">

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="donor-name"><?php _e('Donor Name', 'spelling-bee-pro'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="donor-name" name="donor_name" class="widefat" required>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="donor-email"><?php _e('Donor Email', 'spelling-bee-pro'); ?></label>
                        </th>
                        <td>
                            <input type="email" id="donor-email" name="donor_email" class="widefat">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="donor-phone"><?php _e('Donor Phone', 'spelling-bee-pro'); ?></label>
                        </th>
                        <td>
                            <input type="tel" id="donor-phone" name="donor_phone" class="widefat">
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="donation-amount"><?php _e('Amount', 'spelling-bee-pro'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="donation-amount" name="amount" class="widefat"
                                   min="1" step="0.01" required>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="payment-method"><?php _e('Payment Method', 'spelling-bee-pro'); ?></label>
                        </th>
                        <td>
                            <select id="payment-method" name="payment_method" class="widefat">
                                <option value="cash"><?php _e('Cash', 'spelling-bee-pro'); ?></option>
                                <option value="check"><?php _e('Check', 'spelling-bee-pro'); ?></option>
                                <option value="bank_transfer"><?php _e('Bank Transfer', 'spelling-bee-pro'); ?></option>
                                <option value="other"><?php _e('Other', 'spelling-bee-pro'); ?></option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="donor-message"><?php _e('Message (Optional)', 'spelling-bee-pro'); ?></label>
                        </th>
                        <td>
                            <textarea id="donor-message" name="donor_message" rows="3" class="widefat"></textarea>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"></th>
                        <td>
                            <label>
                                <input type="checkbox" id="is-anonymous" name="is_anonymous" value="1">
                                <?php _e('Anonymous donation', 'spelling-bee-pro'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="osb-modal-footer">
            <button type="button" id="add-donation-btn" class="button button-primary">
                <?php _e('Add Donation', 'spelling-bee-pro'); ?>
            </button>
            <button type="button" class="button osb-modal-close">
                <?php _e('Cancel', 'spelling-bee-pro'); ?>
            </button>
        </div>
    </div>
</div>

<!-- Refund Modal -->
<div id="osb-refund-modal" class="osb-modal" style="display: none;">
    <div class="osb-modal-content">
        <div class="osb-modal-header">
            <h3><?php _e('Process Refund', 'spelling-bee-pro'); ?></h3>
            <button type="button" class="osb-modal-close">&times;</button>
        </div>
        <div class="osb-modal-body">
            <p><?php _e('Please provide a reason for this refund:', 'spelling-bee-pro'); ?></p>
            <textarea id="refund-reason" rows="4" class="widefat" placeholder="<?php _e('Enter refund reason...', 'spelling-bee-pro'); ?>"></textarea>
            <input type="hidden" id="refund-donation-id">
        </div>
        <div class="osb-modal-footer">
            <button type="button" id="process-refund-btn" class="button button-primary">
                <?php _e('Process Refund', 'spelling-bee-pro'); ?>
            </button>
            <button type="button" class="button osb-modal-close">
                <?php _e('Cancel', 'spelling-bee-pro'); ?>
            </button>
        </div>
    </div>
</div>

<style>
.osb-filters {
    margin: 20px 0;
    padding: 15px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.osb-filter-bar {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.osb-filter-select,
.osb-search-input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.osb-search-input {
    min-width: 200px;
    flex: 1;
}

.osb-prize-fund-overview {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 30px;
    margin: 20px 0;
}

.osb-fund-summary {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
}

.osb-total-fund {
    text-align: center;
}

.osb-fund-amount {
    font-size: 3rem;
    font-weight: bold;
    color: #0073aa;
    line-height: 1;
}

.osb-fund-label {
    font-size: 1.2rem;
    color: #666;
    margin-top: 10px;
}

.osb-fund-goal {
    flex: 1;
    max-width: 400px;
    margin-left: 40px;
}

.osb-progress-bar {
    background: #f0f0f0;
    border-radius: 10px;
    height: 20px;
    overflow: hidden;
    margin-bottom: 10px;
}

.osb-progress-fill {
    background: linear-gradient(90deg, #00a32a, #46b450);
    height: 100%;
    border-radius: 10px;
    transition: width 0.3s ease;
}

.osb-goal-text {
    text-align: center;
    color: #666;
    font-weight: 500;
}

.osb-no-goal {
    text-align: center;
    color: #999;
    font-style: italic;
}

.osb-prize-breakdown {
    border-top: 1px solid #ddd;
    padding-top: 30px;
}

.osb-breakdown-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.osb-breakdown-item {
    text-align: center;
    padding: 20px 15px;
    background: #f9f9f9;
    border-radius: 8px;
    border-left: 4px solid #ccc;
}

.osb-breakdown-first_place { border-left-color: #ffd700; background: #fffbf0; }
.osb-breakdown-second_place { border-left-color: #c0c0c0; background: #f8f8f8; }
.osb-breakdown-third_place { border-left-color: #cd7f32; background: #faf6f0; }
.osb-breakdown-participation { border-left-color: #00a32a; background: #f0f8f0; }
.osb-breakdown-organization { border-left-color: #0073aa; background: #f0f4f8; }

.osb-breakdown-amount {
    font-size: 1.5rem;
    font-weight: bold;
    color: #333;
}

.osb-breakdown-label {
    font-size: 0.9rem;
    color: #666;
    margin: 8px 0 4px;
}

.osb-breakdown-percentage {
    font-size: 0.85rem;
    color: #999;
}

.osb-donation-stats {
    margin: 20px 0;
}

.osb-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.osb-stat-card {
    background: #fff;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 8px;
    text-align: center;
}

.osb-stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #0073aa;
    line-height: 1;
}

.osb-stat-label {
    font-size: 0.9rem;
    color: #666;
    margin-top: 8px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.osb-stat-amount {
    font-size: 0.85rem;
    color: #999;
    margin-top: 4px;
}

.osb-stat-completed .osb-stat-number { color: #00a32a; }
.osb-stat-pending .osb-stat-number { color: #ffb900; }
.osb-stat-refunded .osb-stat-number { color: #d63638; }

.osb-donations-table-container {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
    margin: 20px 0;
}

.osb-donations-table-container h3 {
    padding: 15px 20px;
    margin: 0;
    background: #f9f9f9;
    border-bottom: 1px solid #ddd;
}

.osb-donations-table {
    margin: 0;
}

.osb-amount {
    font-weight: bold;
    font-size: 1.1em;
}

.osb-amount-completed { color: #00a32a; }
.osb-amount-pending { color: #ffb900; }
.osb-amount-refunded { color: #d63638; }

.osb-currency {
    color: #666;
}

.osb-status {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 0.85em;
    font-weight: 600;
    text-transform: uppercase;
}

.osb-status-completed { background: #00a32a; color: white; }
.osb-status-pending { background: #ffb900; color: white; }
.osb-status-failed { background: #d63638; color: white; }
.osb-status-refunded { background: #666; color: white; }

.osb-action-buttons {
    display: flex;
    flex-direction: column;
    gap: 4px;
}

.osb-action-buttons .button {
    font-size: 11px;
    height: auto;
    padding: 3px 8px;
    line-height: 1.4;
}

.osb-donor-leaderboard {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin: 20px 0;
}

.osb-donor-leaderboard h3 {
    margin: 0 0 20px 0;
    padding-bottom: 15px;
    border-bottom: 1px solid #ddd;
}

.osb-leaderboard-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.osb-leaderboard-item {
    display: flex;
    align-items: center;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 6px;
    border-left: 4px solid #ddd;
}

.osb-rank-1 { border-left-color: #ffd700; background: #fffbf0; }
.osb-rank-2 { border-left-color: #c0c0c0; background: #f8f8f8; }
.osb-rank-3 { border-left-color: #cd7f32; background: #faf6f0; }

.osb-donor-rank {
    font-size: 1.5rem;
    font-weight: bold;
    color: #666;
    margin-right: 15px;
    min-width: 40px;
}

.osb-rank-1 .osb-donor-rank { color: #ffd700; }
.osb-rank-2 .osb-donor-rank { color: #c0c0c0; }
.osb-rank-3 .osb-donor-rank { color: #cd7f32; }

.osb-donor-info {
    flex: 1;
}

.osb-donor-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.osb-donor-stats {
    display: flex;
    gap: 15px;
    font-size: 0.9rem;
}

.osb-donation-amount {
    color: #00a32a;
    font-weight: bold;
}

.osb-donation-count {
    color: #666;
}

.osb-no-event-selected,
.osb-no-donations {
    margin: 40px 0;
}

.osb-empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
}

.osb-empty-icon {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.3;
}

.osb-empty-state h3 {
    color: #666;
    margin-bottom: 10px;
}

.osb-empty-state p {
    color: #999;
    margin-bottom: 20px;
}

/* Modal Styles */
.osb-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.osb-modal-content {
    background: #fff;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
}

.osb-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #ddd;
}

.osb-modal-header h3 {
    margin: 0;
}

.osb-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.osb-modal-body {
    padding: 20px;
}

.osb-modal-footer {
    padding: 20px;
    border-top: 1px solid #ddd;
    text-align: right;
}

.osb-modal-footer .button {
    margin-left: 10px;
}

/* Responsive */
@media (max-width: 768px) {
    .osb-filter-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .osb-filter-select,
    .osb-search-input {
        width: 100%;
    }

    .osb-fund-summary {
        flex-direction: column;
        gap: 20px;
        text-align: center;
    }

    .osb-fund-goal {
        margin-left: 0;
    }

    .osb-breakdown-grid {
        grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
    }

    .osb-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .column-method,
    .column-date {
        display: none;
    }
}

@media (max-width: 600px) {
    .osb-stats-grid {
        grid-template-columns: 1fr;
    }

    .column-actions {
        display: none;
    }

    .osb-modal-content {
        width: 95%;
        margin: 20px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Event filter change
    $('#osb-event-filter').on('change', function() {
        const eventId = $(this).val();
        if (eventId) {
            window.location.href = '<?php echo admin_url('admin.php?page=spelling-bee-donations'); ?>&event_id=' + eventId;
        } else {
            window.location.href = '<?php echo admin_url('admin.php?page=spelling-bee-donations'); ?>';
        }
    });

    // Manual donation modals
    $('#osb-manual-donation, #osb-manual-donation-empty').on('click', function() {
        $('#osb-donation-modal').show();
    });

    // Add manual donation
    $('#add-donation-btn').on('click', function() {
        const formData = {
            action: 'osb_admin_action',
            sub_action: 'process_donation',
            nonce: osb_ajax.nonce
        };

        $('#osb-donation-form').find('input, select, textarea').each(function() {
            formData[$(this).attr('name')] = $(this).val();
        });

        if ($('#is-anonymous').is(':checked')) {
            formData['is_anonymous'] = 1;
        }

        const $button = $(this);
        $button.prop('disabled', true).text('<?php _e('Adding...', 'spelling-bee-pro'); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    alert('<?php _e('Donation added successfully!', 'spelling-bee-pro'); ?>');
                    location.reload();
                } else {
                    alert('<?php _e('Error:', 'spelling-bee-pro'); ?> ' + response.data);
                    $button.prop('disabled', false).text('<?php _e('Add Donation', 'spelling-bee-pro'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Network error. Please try again.', 'spelling-bee-pro'); ?>');
                $button.prop('disabled', false).text('<?php _e('Add Donation', 'spelling-bee-pro'); ?>');
            }
        });
    });

    // Refund donation
    $('.osb-refund-donation').on('click', function(e) {
        e.preventDefault();
        const donationId = $(this).data('donation-id');
        $('#refund-donation-id').val(donationId);
        $('#osb-refund-modal').show();
    });

    // Process refund
    $('#process-refund-btn').on('click', function() {
        const donationId = $('#refund-donation-id').val();
        const reason = $('#refund-reason').val();

        if (!reason.trim()) {
            alert('<?php _e('Please provide a reason for the refund.', 'spelling-bee-pro'); ?>');
            return;
        }

        const $button = $(this);
        $button.prop('disabled', true).text('<?php _e('Processing...', 'spelling-bee-pro'); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'osb_admin_action',
                sub_action: 'process_refund',
                donation_id: donationId,
                reason: reason,
                nonce: osb_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php _e('Refund processed successfully!', 'spelling-bee-pro'); ?>');
                    location.reload();
                } else {
                    alert('<?php _e('Error:', 'spelling-bee-pro'); ?> ' + response.data);
                    $button.prop('disabled', false).text('<?php _e('Process Refund', 'spelling-bee-pro'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Network error. Please try again.', 'spelling-bee-pro'); ?>');
                $button.prop('disabled', false).text('<?php _e('Process Refund', 'spelling-bee-pro'); ?>');
            }
        });
    });

    // Send receipt
    $('.osb-send-receipt').on('click', function() {
        const donationId = $(this).data('donation-id');
        const $button = $(this);
        $button.prop('disabled', true).text('<?php _e('Sending...', 'spelling-bee-pro'); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'osb_admin_action',
                sub_action: 'send_donation_receipt',
                donation_id: donationId,
                nonce: osb_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php _e('Receipt sent successfully!', 'spelling-bee-pro'); ?>');
                } else {
                    alert('<?php _e('Error:', 'spelling-bee-pro'); ?> ' + response.data);
                }
                $button.prop('disabled', false).text('<?php _e('Send Receipt', 'spelling-bee-pro'); ?>');
            },
            error: function() {
                alert('<?php _e('Network error. Please try again.', 'spelling-bee-pro'); ?>');
                $button.prop('disabled', false).text('<?php _e('Send Receipt', 'spelling-bee-pro'); ?>');
            }
        });
    });

    // Export donations
    $('#osb-export-donations').on('click', function() {
        const params = new URLSearchParams({
            action: 'osb_admin_action',
            sub_action: 'export_donations',
            event_id: <?php echo intval($selected_event_id ?: 0); ?>,
            format: 'csv',
            nonce: osb_ajax.nonce
        });

        window.location.href = ajaxurl + '?' + params.toString();
    });

    // Modal close
    $('.osb-modal-close').on('click', function() {
        $('.osb-modal').hide();
        $('#osb-donation-form')[0].reset();
        $('#refund-reason').val('');
    });

    // Filtering functionality
    function filterDonations() {
        const statusFilter = $('#osb-status-filter').val();
        const searchTerm = $('#osb-search-donations').val().toLowerCase();

        $('.osb-donation-row').each(function() {
            const $row = $(this);
            const status = $row.data('status');
            const donorName = $row.find('.column-donor strong').text().toLowerCase();

            let show = true;

            if (statusFilter && status !== statusFilter) {
                show = false;
            }

            if (searchTerm && !donorName.includes(searchTerm)) {
                show = false;
            }

            $row.toggle(show);
        });
    }

    // Bind filter events
    $('#osb-status-filter').on('change', filterDonations);
    $('#osb-search-donations').on('input', filterDonations);

    // Clear filters
    $('#osb-clear-filters').on('click', function() {
        $('#osb-status-filter').val('');
        $('#osb-search-donations').val('');
        filterDonations();
    });
});
</script>