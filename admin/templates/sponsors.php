<?php
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

$event_id = isset($_GET['event_id']) ? intval($_GET['event_id']) : 0;
$events = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}osb_events ORDER BY created_at DESC");
$current_event = null;
if ($event_id) {
    $current_event = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}osb_events WHERE id = %d", $event_id));
}

$sponsors = [];
if ($event_id) {
    $sponsors = $wpdb->get_results($wpdb->prepare("
        SELECT s.*, e.name as event_name
        FROM {$wpdb->prefix}osb_sponsors s
        LEFT JOIN {$wpdb->prefix}osb_events e ON s.event_id = e.id
        WHERE s.event_id = %d
        ORDER BY s.sponsorship_level DESC, s.amount DESC, s.created_at DESC
    ", $event_id));
}

$sponsor_stats = [];
if ($event_id) {
    $sponsor_stats = $wpdb->get_row($wpdb->prepare("
        SELECT
            COUNT(*) as total_sponsors,
            COALESCE(SUM(amount), 0) as total_amount,
            SUM(CASE WHEN sponsorship_level = 'platinum' THEN 1 ELSE 0 END) as platinum_sponsors,
            SUM(CASE WHEN sponsorship_level = 'gold' THEN 1 ELSE 0 END) as gold_sponsors,
            SUM(CASE WHEN sponsorship_level = 'silver' THEN 1 ELSE 0 END) as silver_sponsors,
            SUM(CASE WHEN sponsorship_level = 'bronze' THEN 1 ELSE 0 END) as bronze_sponsors,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_sponsors,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_sponsors
        FROM {$wpdb->prefix}osb_sponsors
        WHERE event_id = %d
    ", $event_id), ARRAY_A);
}

$sponsorship_levels = [
    'platinum' => ['min_amount' => 10000, 'color' => '#e5e4e2', 'benefits' => 'Logo on all materials, VIP seating, opening ceremony mention'],
    'gold' => ['min_amount' => 5000, 'color' => '#ffd700', 'benefits' => 'Logo on select materials, premium seating'],
    'silver' => ['min_amount' => 2500, 'color' => '#c0c0c0', 'benefits' => 'Logo on website and programs'],
    'bronze' => ['min_amount' => 1000, 'color' => '#cd7f32', 'benefits' => 'Logo on website']
];
?>

<div class="wrap osb-admin-page">
    <div class="osb-header">
        <h1 class="wp-heading-inline">
            <span class="dashicons dashicons-star-filled"></span>
            Sponsors Management
        </h1>

        <div class="osb-header-actions">
            <button type="button" class="button button-primary" id="add-sponsor-btn">
                <span class="dashicons dashicons-plus"></span>
                Add Sponsor
            </button>
            <button type="button" class="button" id="export-sponsors">
                <span class="dashicons dashicons-download"></span>
                Export
            </button>
            <button type="button" class="button" id="generate-report">
                <span class="dashicons dashicons-chart-bar"></span>
                Sponsorship Report
            </button>
        </div>
    </div>

    <!-- Event Selection -->
    <div class="osb-event-selector">
        <label for="event-select">Select Event:</label>
        <select id="event-select" onchange="filterByEvent(this.value)">
            <option value="">All Events</option>
            <?php foreach ($events as $event): ?>
                <option value="<?php echo $event->id; ?>" <?php selected($event_id, $event->id); ?>>
                    <?php echo esc_html($event->name); ?> (<?php echo date('Y', strtotime($event->start_date)); ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if ($event_id && $current_event): ?>
        <!-- Statistics Cards -->
        <div class="osb-stats-grid">
            <div class="osb-stat-card total">
                <div class="osb-stat-icon">
                    <span class="dashicons dashicons-groups"></span>
                </div>
                <div class="osb-stat-content">
                    <div class="osb-stat-number"><?php echo number_format($sponsor_stats['total_sponsors'] ?? 0); ?></div>
                    <div class="osb-stat-label">Total Sponsors</div>
                </div>
            </div>

            <div class="osb-stat-card revenue">
                <div class="osb-stat-icon">
                    <span class="dashicons dashicons-money-alt"></span>
                </div>
                <div class="osb-stat-content">
                    <div class="osb-stat-number">$<?php echo number_format($sponsor_stats['total_amount'] ?? 0); ?></div>
                    <div class="osb-stat-label">Total Sponsorship</div>
                </div>
            </div>

            <div class="osb-stat-card active">
                <div class="osb-stat-icon">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <div class="osb-stat-content">
                    <div class="osb-stat-number"><?php echo number_format($sponsor_stats['active_sponsors'] ?? 0); ?></div>
                    <div class="osb-stat-label">Active Sponsors</div>
                </div>
            </div>

            <div class="osb-stat-card pending">
                <div class="osb-stat-icon">
                    <span class="dashicons dashicons-clock"></span>
                </div>
                <div class="osb-stat-content">
                    <div class="osb-stat-number"><?php echo number_format($sponsor_stats['pending_sponsors'] ?? 0); ?></div>
                    <div class="osb-stat-label">Pending</div>
                </div>
            </div>
        </div>

        <!-- Sponsorship Levels Overview -->
        <div class="sponsorship-levels-overview">
            <h3>Sponsorship Levels</h3>
            <div class="levels-grid">
                <?php foreach ($sponsorship_levels as $level => $details): ?>
                    <div class="level-card level-<?php echo $level; ?>">
                        <div class="level-header" style="background-color: <?php echo $details['color']; ?>">
                            <h4><?php echo ucfirst($level); ?> Sponsor</h4>
                            <div class="level-count"><?php echo number_format($sponsor_stats[$level . '_sponsors'] ?? 0); ?> sponsors</div>
                        </div>
                        <div class="level-details">
                            <div class="min-amount">Min: $<?php echo number_format($details['min_amount']); ?></div>
                            <div class="benefits"><?php echo esc_html($details['benefits']); ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Filters -->
        <div class="osb-filters">
            <div class="osb-filter-group">
                <label>Sponsorship Level:</label>
                <select id="level-filter" onchange="filterSponsors()">
                    <option value="">All Levels</option>
                    <option value="platinum">Platinum</option>
                    <option value="gold">Gold</option>
                    <option value="silver">Silver</option>
                    <option value="bronze">Bronze</option>
                </select>
            </div>

            <div class="osb-filter-group">
                <label>Status:</label>
                <select id="status-filter" onchange="filterSponsors()">
                    <option value="">All Status</option>
                    <option value="active">Active</option>
                    <option value="pending">Pending</option>
                    <option value="inactive">Inactive</option>
                </select>
            </div>

            <div class="osb-filter-group">
                <label>Amount Range:</label>
                <select id="amount-filter" onchange="filterSponsors()">
                    <option value="">All Amounts</option>
                    <option value="10000+">$10,000+</option>
                    <option value="5000-9999">$5,000 - $9,999</option>
                    <option value="2500-4999">$2,500 - $4,999</option>
                    <option value="1000-2499">$1,000 - $2,499</option>
                    <option value="0-999">Under $1,000</option>
                </select>
            </div>

            <div class="osb-filter-group">
                <input type="text" id="search-filter" placeholder="Search sponsors..." onkeyup="filterSponsors()">
            </div>

            <button type="button" class="button" onclick="clearFilters()">Clear Filters</button>
        </div>

        <!-- Sponsors Table -->
        <?php if (!empty($sponsors)): ?>
            <div class="osb-table-container">
                <table class="wp-list-table widefat fixed striped" id="sponsors-table">
                    <thead>
                        <tr>
                            <th scope="col" class="check-column">
                                <input type="checkbox" id="select-all-sponsors">
                            </th>
                            <th scope="col" class="sortable" data-sort="company_name">
                                Sponsor Details
                                <span class="sorting-indicator"></span>
                            </th>
                            <th scope="col" class="sortable" data-sort="sponsorship_level">
                                Level
                                <span class="sorting-indicator"></span>
                            </th>
                            <th scope="col" class="sortable" data-sort="amount">
                                Amount
                                <span class="sorting-indicator"></span>
                            </th>
                            <th scope="col">Contact</th>
                            <th scope="col" class="sortable" data-sort="status">
                                Status
                                <span class="sorting-indicator"></span>
                            </th>
                            <th scope="col" class="sortable" data-sort="created_at">
                                Date Added
                                <span class="sorting-indicator"></span>
                            </th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sponsors as $sponsor): ?>
                            <tr class="sponsor-row"
                                data-level="<?php echo esc_attr($sponsor->sponsorship_level); ?>"
                                data-status="<?php echo esc_attr($sponsor->status); ?>"
                                data-amount="<?php echo esc_attr($sponsor->amount); ?>">
                                <td class="check-column">
                                    <input type="checkbox" name="sponsor_ids[]" value="<?php echo $sponsor->id; ?>" class="sponsor-checkbox">
                                </td>

                                <td class="sponsor-details">
                                    <div class="sponsor-info">
                                        <?php if ($sponsor->logo_url): ?>
                                            <img src="<?php echo esc_url($sponsor->logo_url); ?>" alt="<?php echo esc_attr($sponsor->company_name); ?>" class="sponsor-logo">
                                        <?php endif; ?>
                                        <div class="sponsor-text">
                                            <strong class="company-name"><?php echo esc_html($sponsor->company_name); ?></strong>
                                            <?php if ($sponsor->website): ?>
                                                <div class="sponsor-website">
                                                    <a href="<?php echo esc_url($sponsor->website); ?>" target="_blank">
                                                        <?php echo esc_html($sponsor->website); ?>
                                                    </a>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($sponsor->description): ?>
                                                <div class="sponsor-description"><?php echo esc_html($sponsor->description); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </td>

                                <td class="level-column">
                                    <span class="level-badge level-<?php echo esc_attr($sponsor->sponsorship_level); ?>">
                                        <?php echo esc_html(ucfirst($sponsor->sponsorship_level)); ?>
                                    </span>
                                </td>

                                <td class="amount-column">
                                    <div class="amount-display">$<?php echo number_format($sponsor->amount); ?></div>
                                    <?php if ($sponsor->payment_status): ?>
                                        <div class="payment-status payment-<?php echo esc_attr($sponsor->payment_status); ?>">
                                            <?php echo esc_html(ucfirst($sponsor->payment_status)); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td class="contact-info">
                                    <div class="contact-name"><?php echo esc_html($sponsor->contact_name); ?></div>
                                    <div class="contact-email">
                                        <a href="mailto:<?php echo esc_attr($sponsor->contact_email); ?>">
                                            <?php echo esc_html($sponsor->contact_email); ?>
                                        </a>
                                    </div>
                                    <?php if ($sponsor->contact_phone): ?>
                                        <div class="contact-phone"><?php echo esc_html($sponsor->contact_phone); ?></div>
                                    <?php endif; ?>
                                </td>

                                <td class="status-column">
                                    <span class="status-badge status-<?php echo esc_attr($sponsor->status); ?>">
                                        <?php echo esc_html(ucfirst($sponsor->status)); ?>
                                    </span>
                                </td>

                                <td class="date-column">
                                    <div class="created-date"><?php echo date('M j, Y', strtotime($sponsor->created_at)); ?></div>
                                    <div class="created-time"><?php echo date('g:i A', strtotime($sponsor->created_at)); ?></div>
                                </td>

                                <td class="actions-column">
                                    <div class="action-buttons">
                                        <button type="button" class="button button-small edit-sponsor"
                                                data-id="<?php echo $sponsor->id; ?>">
                                            <span class="dashicons dashicons-edit"></span>
                                            Edit
                                        </button>

                                        <?php if ($sponsor->status === 'pending'): ?>
                                            <button type="button" class="button button-primary button-small activate-sponsor"
                                                    data-id="<?php echo $sponsor->id; ?>">
                                                <span class="dashicons dashicons-yes"></span>
                                                Activate
                                            </button>
                                        <?php elseif ($sponsor->status === 'active'): ?>
                                            <button type="button" class="button button-small deactivate-sponsor"
                                                    data-id="<?php echo $sponsor->id; ?>">
                                                <span class="dashicons dashicons-pause"></span>
                                                Deactivate
                                            </button>
                                        <?php endif; ?>

                                        <button type="button" class="button button-small send-email"
                                                data-id="<?php echo $sponsor->id; ?>"
                                                data-email="<?php echo esc_attr($sponsor->contact_email); ?>">
                                            <span class="dashicons dashicons-email"></span>
                                            Email
                                        </button>

                                        <button type="button" class="button button-link-delete button-small delete-sponsor"
                                                data-id="<?php echo $sponsor->id; ?>">
                                            <span class="dashicons dashicons-trash"></span>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="osb-empty-state">
                <div class="osb-empty-icon">
                    <span class="dashicons dashicons-star-filled"></span>
                </div>
                <h3>No Sponsors Found</h3>
                <p>No sponsors have been added for this event yet.</p>
                <button type="button" class="button button-primary" id="add-first-sponsor">
                    <span class="dashicons dashicons-plus"></span>
                    Add First Sponsor
                </button>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="osb-empty-state">
            <div class="osb-empty-icon">
                <span class="dashicons dashicons-admin-settings"></span>
            </div>
            <h3>Select an Event</h3>
            <p>Please select an event to view and manage sponsors.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Add/Edit Sponsor Modal -->
<div id="sponsor-modal" class="osb-modal" style="display: none;">
    <div class="osb-modal-content sponsor-modal-content">
        <div class="osb-modal-header">
            <h3 id="sponsor-modal-title">Add New Sponsor</h3>
            <button type="button" class="osb-modal-close">&times;</button>
        </div>
        <div class="osb-modal-body">
            <form id="sponsor-form" enctype="multipart/form-data">
                <input type="hidden" id="sponsor-id" name="sponsor_id">
                <input type="hidden" id="sponsor-event-id" name="event_id" value="<?php echo $event_id; ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="company_name">Company Name *</label>
                        <input type="text" id="company_name" name="company_name" required>
                    </div>
                    <div class="form-group">
                        <label for="website">Website</label>
                        <input type="url" id="website" name="website" placeholder="https://">
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="3" placeholder="Brief description of the sponsor..."></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="sponsorship_level">Sponsorship Level *</label>
                        <select id="sponsorship_level" name="sponsorship_level" required onchange="updateMinAmount()">
                            <option value="">Select Level</option>
                            <option value="platinum">Platinum ($10,000+)</option>
                            <option value="gold">Gold ($5,000+)</option>
                            <option value="silver">Silver ($2,500+)</option>
                            <option value="bronze">Bronze ($1,000+)</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="amount">Sponsorship Amount *</label>
                        <input type="number" id="amount" name="amount" min="0" step="0.01" required>
                        <div id="amount-guidance" class="field-help"></div>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="contact_name">Contact Name *</label>
                        <input type="text" id="contact_name" name="contact_name" required>
                    </div>
                    <div class="form-group">
                        <label for="contact_email">Contact Email *</label>
                        <input type="email" id="contact_email" name="contact_email" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="contact_phone">Contact Phone</label>
                        <input type="tel" id="contact_phone" name="contact_phone">
                    </div>
                    <div class="form-group">
                        <label for="payment_status">Payment Status</label>
                        <select id="payment_status" name="payment_status">
                            <option value="pending">Pending</option>
                            <option value="paid">Paid</option>
                            <option value="partial">Partial</option>
                            <option value="overdue">Overdue</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="logo_upload">Sponsor Logo</label>
                    <input type="file" id="logo_upload" name="logo_upload" accept="image/*">
                    <div class="field-help">Upload a high-quality logo (PNG, JPG, SVG). Recommended size: 300x150px</div>
                    <div id="current-logo" style="display: none;">
                        <img id="current-logo-img" src="" alt="Current logo" style="max-width: 150px; margin-top: 10px;">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status">
                            <option value="pending">Pending</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="button" onclick="closeModal('sponsor-modal')">Cancel</button>
                    <button type="submit" class="button button-primary">Save Sponsor</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Email Modal -->
<div id="email-modal" class="osb-modal" style="display: none;">
    <div class="osb-modal-content">
        <div class="osb-modal-header">
            <h3>Send Email to Sponsor</h3>
            <button type="button" class="osb-modal-close">&times;</button>
        </div>
        <div class="osb-modal-body">
            <form id="email-form">
                <input type="hidden" id="email-sponsor-id">
                <div class="form-group">
                    <label for="email-to">To:</label>
                    <input type="email" id="email-to" readonly>
                </div>
                <div class="form-group">
                    <label for="email-subject">Subject:</label>
                    <input type="text" id="email-subject" required>
                </div>
                <div class="form-group">
                    <label for="email-message">Message:</label>
                    <textarea id="email-message" rows="8" required></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="button" onclick="closeModal('email-modal')">Cancel</button>
                    <button type="submit" class="button button-primary">Send Email</button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
.osb-admin-page {
    max-width: 1200px;
    margin: 20px 0;
}

.osb-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 10px;
    border-bottom: 1px solid #ddd;
}

.osb-header h1 {
    display: flex;
    align-items: center;
    gap: 10px;
    margin: 0;
}

.osb-header-actions {
    display: flex;
    gap: 10px;
}

.osb-event-selector {
    background: #fff;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 20px;
}

.osb-event-selector label {
    font-weight: 600;
    margin-right: 10px;
}

.osb-event-selector select {
    min-width: 250px;
}

.osb-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.osb-stat-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.osb-stat-card.total {
    border-left: 4px solid #0073aa;
}

.osb-stat-card.revenue {
    border-left: 4px solid #5cb85c;
}

.osb-stat-card.active {
    border-left: 4px solid #5cb85c;
}

.osb-stat-card.pending {
    border-left: 4px solid #f0ad4e;
}

.osb-stat-icon {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.osb-stat-number {
    font-size: 24px;
    font-weight: bold;
    color: #333;
}

.osb-stat-label {
    color: #666;
    font-size: 12px;
    text-transform: uppercase;
    margin-top: 5px;
}

.sponsorship-levels-overview {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 30px;
}

.sponsorship-levels-overview h3 {
    margin-top: 0;
    margin-bottom: 15px;
}

.levels-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.level-card {
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
}

.level-header {
    padding: 15px;
    color: #333;
    font-weight: bold;
}

.level-header h4 {
    margin: 0 0 5px 0;
    font-size: 16px;
}

.level-count {
    font-size: 12px;
    opacity: 0.8;
}

.level-details {
    padding: 15px;
    background: #f9f9f9;
}

.min-amount {
    font-weight: 600;
    margin-bottom: 8px;
}

.benefits {
    font-size: 12px;
    color: #666;
    line-height: 1.4;
}

.osb-filters {
    background: #fff;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 20px;
    display: flex;
    gap: 20px;
    align-items: end;
    flex-wrap: wrap;
}

.osb-filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.osb-filter-group label {
    font-weight: 600;
    font-size: 12px;
}

.osb-table-container {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow-x: auto;
}

.sponsor-details {
    max-width: 300px;
}

.sponsor-info {
    display: flex;
    align-items: flex-start;
    gap: 10px;
}

.sponsor-logo {
    width: 40px;
    height: 40px;
    object-fit: contain;
    border: 1px solid #ddd;
    border-radius: 2px;
}

.sponsor-text .company-name {
    display: block;
    margin-bottom: 3px;
}

.sponsor-website {
    font-size: 12px;
    margin-bottom: 3px;
}

.sponsor-website a {
    color: #0073aa;
    text-decoration: none;
}

.sponsor-description {
    font-size: 11px;
    color: #666;
    line-height: 1.3;
    max-width: 200px;
}

.level-badge {
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: #333;
}

.level-platinum {
    background: #e5e4e2;
}

.level-gold {
    background: #ffd700;
}

.level-silver {
    background: #c0c0c0;
}

.level-bronze {
    background: #cd7f32;
    color: #fff;
}

.amount-display {
    font-weight: 600;
    font-size: 16px;
}

.payment-status {
    font-size: 11px;
    margin-top: 3px;
}

.payment-paid {
    color: #5cb85c;
}

.payment-pending {
    color: #f0ad4e;
}

.payment-partial {
    color: #5bc0de;
}

.payment-overdue {
    color: #d9534f;
}

.contact-info .contact-name {
    font-weight: 600;
    margin-bottom: 3px;
}

.contact-info .contact-email {
    font-size: 12px;
    margin-bottom: 2px;
}

.contact-info .contact-phone {
    font-size: 12px;
    color: #666;
}

.status-badge {
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-active {
    background: #d4edda;
    color: #155724;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-inactive {
    background: #f8d7da;
    color: #721c24;
}

.date-column .created-date {
    font-weight: 600;
}

.date-column .created-time {
    font-size: 11px;
    color: #666;
}

.action-buttons {
    display: flex;
    gap: 5px;
    flex-wrap: wrap;
}

.osb-empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.osb-empty-icon {
    font-size: 48px;
    color: #ccc;
    margin-bottom: 20px;
}

.osb-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.sponsor-modal-content {
    min-width: 600px;
    max-width: 90vw;
}

.osb-modal-content {
    background: #fff;
    border-radius: 4px;
    max-height: 90vh;
    overflow: auto;
}

.osb-modal-header {
    padding: 20px;
    border-bottom: 1px solid #ddd;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.osb-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
}

.osb-modal-body {
    padding: 20px;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
    margin-bottom: 15px;
}

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
}

.field-help {
    font-size: 11px;
    color: #666;
    margin-top: 3px;
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px solid #ddd;
}

@media (max-width: 768px) {
    .osb-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .osb-filters {
        flex-direction: column;
        align-items: stretch;
    }

    .form-row {
        grid-template-columns: 1fr;
    }

    .action-buttons {
        flex-direction: column;
    }

    .sponsor-modal-content {
        min-width: auto;
        width: 95vw;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Add/Edit sponsor modal
    $('#add-sponsor-btn, #add-first-sponsor').on('click', function() {
        resetSponsorForm();
        $('#sponsor-modal-title').text('Add New Sponsor');
        $('#sponsor-modal').show();
    });

    // Edit sponsor
    $(document).on('click', '.edit-sponsor', function() {
        const sponsorId = $(this).data('id');
        loadSponsorData(sponsorId);
    });

    // Submit sponsor form
    $('#sponsor-form').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('action', 'save_sponsor');
        formData.append('_wpnonce', osb_admin.nonce);

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('Error: ' + response.data.message);
                }
            }
        });
    });

    // Activate/Deactivate sponsor
    $(document).on('click', '.activate-sponsor, .deactivate-sponsor', function() {
        const sponsorId = $(this).data('id');
        const newStatus = $(this).hasClass('activate-sponsor') ? 'active' : 'inactive';
        const action = newStatus === 'active' ? 'activate' : 'deactivate';

        if (confirm(`Are you sure you want to ${action} this sponsor?`)) {
            updateSponsorStatus(sponsorId, newStatus);
        }
    });

    // Send email
    $(document).on('click', '.send-email', function() {
        const sponsorId = $(this).data('id');
        const email = $(this).data('email');

        $('#email-sponsor-id').val(sponsorId);
        $('#email-to').val(email);
        $('#email-subject').val('');
        $('#email-message').val('');
        $('#email-modal').show();
    });

    // Submit email form
    $('#email-form').on('submit', function(e) {
        e.preventDefault();

        const sponsorId = $('#email-sponsor-id').val();
        const subject = $('#email-subject').val();
        const message = $('#email-message').val();

        $.post(ajaxurl, {
            action: 'send_sponsor_email',
            sponsor_id: sponsorId,
            subject: subject,
            message: message,
            _wpnonce: osb_admin.nonce
        }, function(response) {
            if (response.success) {
                alert('Email sent successfully!');
                closeModal('email-modal');
            } else {
                alert('Error: ' + response.data.message);
            }
        });
    });

    // Delete sponsor
    $(document).on('click', '.delete-sponsor', function() {
        const sponsorId = $(this).data('id');

        if (confirm('Are you sure you want to delete this sponsor? This action cannot be undone.')) {
            deleteSponsor(sponsorId);
        }
    });

    // Export sponsors
    $('#export-sponsors').on('click', function() {
        const eventId = $('#event-select').val();
        if (!eventId) {
            alert('Please select an event first.');
            return;
        }

        window.location.href = `${ajaxurl}?action=export_sponsors&event_id=${eventId}&_wpnonce=${osb_admin.nonce}`;
    });

    // Generate report
    $('#generate-report').on('click', function() {
        const eventId = $('#event-select').val();
        if (!eventId) {
            alert('Please select an event first.');
            return;
        }

        window.open(`${ajaxurl}?action=generate_sponsor_report&event_id=${eventId}&_wpnonce=${osb_admin.nonce}`, '_blank');
    });

    // Close modal
    $(document).on('click', '.osb-modal-close, .osb-modal', function(e) {
        if (e.target === this) {
            $(this).closest('.osb-modal').hide();
        }
    });
});

function filterByEvent(eventId) {
    const url = new URL(window.location);
    if (eventId) {
        url.searchParams.set('event_id', eventId);
    } else {
        url.searchParams.delete('event_id');
    }
    window.location.href = url.toString();
}

function filterSponsors() {
    const levelFilter = document.getElementById('level-filter').value.toLowerCase();
    const statusFilter = document.getElementById('status-filter').value.toLowerCase();
    const amountFilter = document.getElementById('amount-filter').value;
    const searchFilter = document.getElementById('search-filter').value.toLowerCase();

    const rows = document.querySelectorAll('.sponsor-row');

    rows.forEach(row => {
        const level = row.dataset.level.toLowerCase();
        const status = row.dataset.status.toLowerCase();
        const amount = parseFloat(row.dataset.amount);
        const text = row.textContent.toLowerCase();

        const levelMatch = !levelFilter || level === levelFilter;
        const statusMatch = !statusFilter || status === statusFilter;
        const searchMatch = !searchFilter || text.includes(searchFilter);

        let amountMatch = true;
        if (amountFilter) {
            if (amountFilter === '10000+') {
                amountMatch = amount >= 10000;
            } else if (amountFilter === '5000-9999') {
                amountMatch = amount >= 5000 && amount <= 9999;
            } else if (amountFilter === '2500-4999') {
                amountMatch = amount >= 2500 && amount <= 4999;
            } else if (amountFilter === '1000-2499') {
                amountMatch = amount >= 1000 && amount <= 2499;
            } else if (amountFilter === '0-999') {
                amountMatch = amount < 1000;
            }
        }

        row.style.display = (levelMatch && statusMatch && amountMatch && searchMatch) ? '' : 'none';
    });
}

function clearFilters() {
    document.getElementById('level-filter').value = '';
    document.getElementById('status-filter').value = '';
    document.getElementById('amount-filter').value = '';
    document.getElementById('search-filter').value = '';
    filterSponsors();
}

function updateMinAmount() {
    const level = document.getElementById('sponsorship_level').value;
    const amountField = document.getElementById('amount');
    const guidance = document.getElementById('amount-guidance');

    const minimums = {
        'platinum': 10000,
        'gold': 5000,
        'silver': 2500,
        'bronze': 1000
    };

    if (level && minimums[level]) {
        amountField.min = minimums[level];
        guidance.textContent = `Minimum amount for ${level} level: $${minimums[level].toLocaleString()}`;

        if (parseFloat(amountField.value) < minimums[level]) {
            amountField.value = minimums[level];
        }
    } else {
        amountField.min = 0;
        guidance.textContent = '';
    }
}

function resetSponsorForm() {
    document.getElementById('sponsor-form').reset();
    document.getElementById('sponsor-id').value = '';
    document.getElementById('current-logo').style.display = 'none';
    document.getElementById('amount-guidance').textContent = '';
}

function loadSponsorData(sponsorId) {
    jQuery.post(ajaxurl, {
        action: 'get_sponsor_data',
        sponsor_id: sponsorId,
        _wpnonce: osb_admin.nonce
    }, function(response) {
        if (response.success) {
            const sponsor = response.data;

            document.getElementById('sponsor-id').value = sponsor.id;
            document.getElementById('company_name').value = sponsor.company_name;
            document.getElementById('website').value = sponsor.website || '';
            document.getElementById('description').value = sponsor.description || '';
            document.getElementById('sponsorship_level').value = sponsor.sponsorship_level;
            document.getElementById('amount').value = sponsor.amount;
            document.getElementById('contact_name').value = sponsor.contact_name;
            document.getElementById('contact_email').value = sponsor.contact_email;
            document.getElementById('contact_phone').value = sponsor.contact_phone || '';
            document.getElementById('payment_status').value = sponsor.payment_status || 'pending';
            document.getElementById('status').value = sponsor.status;

            if (sponsor.logo_url) {
                document.getElementById('current-logo-img').src = sponsor.logo_url;
                document.getElementById('current-logo').style.display = 'block';
            }

            updateMinAmount();

            document.getElementById('sponsor-modal-title').textContent = 'Edit Sponsor';
            document.getElementById('sponsor-modal').style.display = 'block';
        } else {
            alert('Error loading sponsor data: ' + response.data.message);
        }
    });
}

function updateSponsorStatus(sponsorId, status) {
    jQuery.post(ajaxurl, {
        action: 'update_sponsor_status',
        sponsor_id: sponsorId,
        status: status,
        _wpnonce: osb_admin.nonce
    }, function(response) {
        if (response.success) {
            location.reload();
        } else {
            alert('Error: ' + response.data.message);
        }
    });
}

function deleteSponsor(sponsorId) {
    jQuery.post(ajaxurl, {
        action: 'delete_sponsor',
        sponsor_id: sponsorId,
        _wpnonce: osb_admin.nonce
    }, function(response) {
        if (response.success) {
            location.reload();
        } else {
            alert('Error: ' + response.data.message);
        }
    });
}

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
}
</script>