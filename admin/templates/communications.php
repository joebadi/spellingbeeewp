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

$communications = [];
if ($event_id) {
    $communications = $wpdb->get_results($wpdb->prepare("
        SELECT c.*, u.display_name as sender_name
        FROM {$wpdb->prefix}osb_communications c
        LEFT JOIN {$wpdb->prefix}users u ON c.sent_by = u.ID
        WHERE c.event_id = %d
        ORDER BY c.sent_at DESC
    ", $event_id));
}

$comm_stats = [];
if ($event_id) {
    $comm_stats = $wpdb->get_row($wpdb->prepare("
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN status = 'sent' THEN 1 ELSE 0 END) as sent,
            SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled,
            SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft,
            SUM(CASE WHEN comm_type = 'email' THEN 1 ELSE 0 END) as emails,
            SUM(CASE WHEN comm_type = 'sms' THEN 1 ELSE 0 END) as sms
        FROM {$wpdb->prefix}osb_communications
        WHERE event_id = %d
    ", $event_id), ARRAY_A);
}

$email_templates = [
    'welcome' => 'Welcome to Competition',
    'reminder' => 'Registration Reminder',
    'confirmation' => 'Registration Confirmation',
    'document_request' => 'Document Request',
    'approval' => 'Registration Approved',
    'rejection' => 'Registration Rejected',
    'event_update' => 'Event Update',
    'results' => 'Competition Results',
    'thank_you' => 'Thank You Message'
];
?>

<div class="wrap osb-admin-page">
    <div class="osb-header">
        <h1 class="wp-heading-inline">
            <span class="dashicons dashicons-email"></span>
            Communications Center
        </h1>

        <div class="osb-header-actions">
            <button type="button" class="button button-primary" id="compose-email-btn">
                <span class="dashicons dashicons-plus"></span>
                Compose Email
            </button>
            <button type="button" class="button" id="bulk-email-btn">
                <span class="dashicons dashicons-groups"></span>
                Bulk Email
            </button>
            <button type="button" class="button" id="email-templates-btn">
                <span class="dashicons dashicons-text-page"></span>
                Templates
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
                    <span class="dashicons dashicons-email"></span>
                </div>
                <div class="osb-stat-content">
                    <div class="osb-stat-number"><?php echo number_format($comm_stats['total'] ?? 0); ?></div>
                    <div class="osb-stat-label">Total Communications</div>
                </div>
            </div>

            <div class="osb-stat-card sent">
                <div class="osb-stat-icon">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <div class="osb-stat-content">
                    <div class="osb-stat-number"><?php echo number_format($comm_stats['sent'] ?? 0); ?></div>
                    <div class="osb-stat-label">Sent</div>
                </div>
            </div>

            <div class="osb-stat-card scheduled">
                <div class="osb-stat-icon">
                    <span class="dashicons dashicons-clock"></span>
                </div>
                <div class="osb-stat-content">
                    <div class="osb-stat-number"><?php echo number_format($comm_stats['scheduled'] ?? 0); ?></div>
                    <div class="osb-stat-label">Scheduled</div>
                </div>
            </div>

            <div class="osb-stat-card draft">
                <div class="osb-stat-icon">
                    <span class="dashicons dashicons-edit"></span>
                </div>
                <div class="osb-stat-content">
                    <div class="osb-stat-number"><?php echo number_format($comm_stats['draft'] ?? 0); ?></div>
                    <div class="osb-stat-label">Drafts</div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
            <h3>Quick Send</h3>
            <div class="quick-action-buttons">
                <button type="button" class="button quick-action" data-template="welcome">
                    <span class="dashicons dashicons-welcome-view-site"></span>
                    Welcome New Registrations
                </button>
                <button type="button" class="button quick-action" data-template="reminder">
                    <span class="dashicons dashicons-bell"></span>
                    Send Reminders
                </button>
                <button type="button" class="button quick-action" data-template="event_update">
                    <span class="dashicons dashicons-megaphone"></span>
                    Event Updates
                </button>
                <button type="button" class="button quick-action" data-template="results">
                    <span class="dashicons dashicons-awards"></span>
                    Competition Results
                </button>
            </div>
        </div>

        <!-- Filters -->
        <div class="osb-filters">
            <div class="osb-filter-group">
                <label>Status:</label>
                <select id="status-filter" onchange="filterCommunications()">
                    <option value="">All Status</option>
                    <option value="sent">Sent</option>
                    <option value="scheduled">Scheduled</option>
                    <option value="draft">Draft</option>
                    <option value="failed">Failed</option>
                </select>
            </div>

            <div class="osb-filter-group">
                <label>Type:</label>
                <select id="type-filter" onchange="filterCommunications()">
                    <option value="">All Types</option>
                    <option value="email">Email</option>
                    <option value="sms">SMS</option>
                </select>
            </div>

            <div class="osb-filter-group">
                <label>Template:</label>
                <select id="template-filter" onchange="filterCommunications()">
                    <option value="">All Templates</option>
                    <?php foreach ($email_templates as $template => $label): ?>
                        <option value="<?php echo $template; ?>"><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="osb-filter-group">
                <input type="text" id="search-filter" placeholder="Search communications..." onkeyup="filterCommunications()">
            </div>

            <button type="button" class="button" onclick="clearFilters()">Clear Filters</button>
        </div>

        <!-- Communications Table -->
        <?php if (!empty($communications)): ?>
            <div class="osb-table-container">
                <table class="wp-list-table widefat fixed striped" id="communications-table">
                    <thead>
                        <tr>
                            <th scope="col" class="check-column">
                                <input type="checkbox" id="select-all-comms">
                            </th>
                            <th scope="col" class="sortable" data-sort="subject">
                                Subject/Content
                                <span class="sorting-indicator"></span>
                            </th>
                            <th scope="col" class="sortable" data-sort="comm_type">
                                Type
                                <span class="sorting-indicator"></span>
                            </th>
                            <th scope="col">Recipients</th>
                            <th scope="col" class="sortable" data-sort="status">
                                Status
                                <span class="sorting-indicator"></span>
                            </th>
                            <th scope="col" class="sortable" data-sort="sent_at">
                                Date/Time
                                <span class="sorting-indicator"></span>
                            </th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($communications as $comm): ?>
                            <tr class="comm-row"
                                data-status="<?php echo esc_attr($comm->status); ?>"
                                data-type="<?php echo esc_attr($comm->comm_type); ?>"
                                data-template="<?php echo esc_attr($comm->template_type); ?>">
                                <td class="check-column">
                                    <input type="checkbox" name="comm_ids[]" value="<?php echo $comm->id; ?>" class="comm-checkbox">
                                </td>

                                <td class="comm-content">
                                    <div class="comm-subject">
                                        <strong><?php echo esc_html($comm->subject); ?></strong>
                                        <?php if ($comm->template_type): ?>
                                            <span class="template-badge"><?php echo esc_html($email_templates[$comm->template_type] ?? ucfirst($comm->template_type)); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="comm-preview">
                                        <?php echo esc_html(wp_trim_words(strip_tags($comm->message), 15)); ?>
                                    </div>
                                </td>

                                <td class="type-column">
                                    <span class="type-badge type-<?php echo esc_attr($comm->comm_type); ?>">
                                        <?php if ($comm->comm_type === 'email'): ?>
                                            <span class="dashicons dashicons-email"></span>
                                        <?php else: ?>
                                            <span class="dashicons dashicons-smartphone"></span>
                                        <?php endif; ?>
                                        <?php echo esc_html(strtoupper($comm->comm_type)); ?>
                                    </span>
                                </td>

                                <td class="recipients-column">
                                    <div class="recipient-count">
                                        <?php echo number_format($comm->recipient_count); ?> recipients
                                    </div>
                                    <?php if ($comm->recipient_type): ?>
                                        <div class="recipient-type"><?php echo esc_html(ucfirst(str_replace('_', ' ', $comm->recipient_type))); ?></div>
                                    <?php endif; ?>
                                </td>

                                <td class="status-column">
                                    <span class="status-badge status-<?php echo esc_attr($comm->status); ?>">
                                        <?php echo esc_html(ucfirst($comm->status)); ?>
                                    </span>
                                    <?php if ($comm->status === 'failed' && $comm->error_message): ?>
                                        <div class="error-indicator" title="<?php echo esc_attr($comm->error_message); ?>">
                                            <span class="dashicons dashicons-warning"></span>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td class="date-column">
                                    <?php if ($comm->sent_at): ?>
                                        <div class="sent-date"><?php echo date('M j, Y', strtotime($comm->sent_at)); ?></div>
                                        <div class="sent-time"><?php echo date('g:i A', strtotime($comm->sent_at)); ?></div>
                                    <?php elseif ($comm->scheduled_at): ?>
                                        <div class="scheduled-date">Scheduled: <?php echo date('M j, Y g:i A', strtotime($comm->scheduled_at)); ?></div>
                                    <?php else: ?>
                                        <div class="created-date">Created: <?php echo date('M j, Y', strtotime($comm->created_at)); ?></div>
                                    <?php endif; ?>
                                    <?php if ($comm->sender_name): ?>
                                        <div class="sender-name">by <?php echo esc_html($comm->sender_name); ?></div>
                                    <?php endif; ?>
                                </td>

                                <td class="actions-column">
                                    <div class="action-buttons">
                                        <button type="button" class="button button-small view-comm"
                                                data-id="<?php echo $comm->id; ?>">
                                            <span class="dashicons dashicons-visibility"></span>
                                            View
                                        </button>

                                        <?php if ($comm->status === 'draft'): ?>
                                            <button type="button" class="button button-primary button-small send-comm"
                                                    data-id="<?php echo $comm->id; ?>">
                                                <span class="dashicons dashicons-email-alt"></span>
                                                Send
                                            </button>
                                            <button type="button" class="button button-small edit-comm"
                                                    data-id="<?php echo $comm->id; ?>">
                                                <span class="dashicons dashicons-edit"></span>
                                                Edit
                                            </button>
                                        <?php elseif ($comm->status === 'scheduled'): ?>
                                            <button type="button" class="button button-small cancel-schedule"
                                                    data-id="<?php echo $comm->id; ?>">
                                                <span class="dashicons dashicons-no"></span>
                                                Cancel
                                            </button>
                                        <?php elseif ($comm->status === 'sent'): ?>
                                            <button type="button" class="button button-small duplicate-comm"
                                                    data-id="<?php echo $comm->id; ?>">
                                                <span class="dashicons dashicons-admin-page"></span>
                                                Duplicate
                                            </button>
                                        <?php endif; ?>

                                        <button type="button" class="button button-link-delete button-small delete-comm"
                                                data-id="<?php echo $comm->id; ?>">
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
                    <span class="dashicons dashicons-email"></span>
                </div>
                <h3>No Communications Yet</h3>
                <p>Start communicating with your participants by composing your first email.</p>
                <button type="button" class="button button-primary" id="compose-first-email">
                    <span class="dashicons dashicons-plus"></span>
                    Compose First Email
                </button>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="osb-empty-state">
            <div class="osb-empty-icon">
                <span class="dashicons dashicons-admin-settings"></span>
            </div>
            <h3>Select an Event</h3>
            <p>Please select an event to manage communications.</p>
        </div>
    <?php endif; ?>
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

.osb-stat-card.sent {
    border-left: 4px solid #28a745;
}

.osb-stat-card.scheduled {
    border-left: 4px solid #ffc107;
}

.osb-stat-card.draft {
    border-left: 4px solid #6c757d;
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

.quick-actions {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 30px;
}

.quick-actions h3 {
    margin-top: 0;
    margin-bottom: 15px;
}

.quick-action-buttons {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 10px;
}

.quick-action {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 16px;
    text-align: left;
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

.comm-content .comm-subject {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 5px;
}

.template-badge {
    font-size: 11px;
    background: #e9ecef;
    color: #495057;
    padding: 2px 6px;
    border-radius: 3px;
}

.comm-preview {
    font-size: 12px;
    color: #666;
    line-height: 1.3;
}

.type-badge {
    display: flex;
    align-items: center;
    gap: 5px;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
}

.type-email {
    background: #e3f2fd;
    color: #1976d2;
}

.type-sms {
    background: #e8f5e8;
    color: #388e3c;
}

.recipient-count {
    font-weight: 600;
    margin-bottom: 3px;
}

.recipient-type {
    font-size: 11px;
    color: #666;
}

.status-badge {
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-sent {
    background: #d4edda;
    color: #155724;
}

.status-scheduled {
    background: #fff3cd;
    color: #856404;
}

.status-draft {
    background: #f8f9fa;
    color: #6c757d;
}

.status-failed {
    background: #f8d7da;
    color: #721c24;
}

.error-indicator {
    margin-top: 5px;
    color: #dc3545;
    cursor: help;
}

.date-column .sent-date,
.date-column .scheduled-date,
.date-column .created-date {
    font-weight: 600;
}

.date-column .sent-time {
    font-size: 11px;
    color: #666;
}

.date-column .sender-name {
    font-size: 11px;
    color: #999;
    margin-top: 2px;
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

    .quick-action-buttons {
        grid-template-columns: 1fr;
    }

    .action-buttons {
        flex-direction: column;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Compose email
    $('#compose-email-btn, #compose-first-email').on('click', function() {
        // Open compose email modal (to be implemented)
        alert('Compose email functionality would open here');
    });

    // Quick actions
    $('.quick-action').on('click', function() {
        const template = $(this).data('template');
        // Open quick send modal with template (to be implemented)
        alert(`Quick send ${template} template would open here`);
    });

    // View communication
    $(document).on('click', '.view-comm', function() {
        const commId = $(this).data('id');
        // Open communication details modal (to be implemented)
        alert(`View communication ${commId} details would open here`);
    });

    // Send draft
    $(document).on('click', '.send-comm', function() {
        const commId = $(this).data('id');

        if (confirm('Are you sure you want to send this communication?')) {
            sendCommunication(commId);
        }
    });

    // Delete communication
    $(document).on('click', '.delete-comm', function() {
        const commId = $(this).data('id');

        if (confirm('Are you sure you want to delete this communication?')) {
            deleteCommunication(commId);
        }
    });

    // Duplicate communication
    $(document).on('click', '.duplicate-comm', function() {
        const commId = $(this).data('id');
        duplicateCommunication(commId);
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

function filterCommunications() {
    const statusFilter = document.getElementById('status-filter').value.toLowerCase();
    const typeFilter = document.getElementById('type-filter').value.toLowerCase();
    const templateFilter = document.getElementById('template-filter').value.toLowerCase();
    const searchFilter = document.getElementById('search-filter').value.toLowerCase();

    const rows = document.querySelectorAll('.comm-row');

    rows.forEach(row => {
        const status = row.dataset.status.toLowerCase();
        const type = row.dataset.type.toLowerCase();
        const template = row.dataset.template.toLowerCase();
        const text = row.textContent.toLowerCase();

        const statusMatch = !statusFilter || status === statusFilter;
        const typeMatch = !typeFilter || type === typeFilter;
        const templateMatch = !templateFilter || template === templateFilter;
        const searchMatch = !searchFilter || text.includes(searchFilter);

        row.style.display = (statusMatch && typeMatch && templateMatch && searchMatch) ? '' : 'none';
    });
}

function clearFilters() {
    document.getElementById('status-filter').value = '';
    document.getElementById('type-filter').value = '';
    document.getElementById('template-filter').value = '';
    document.getElementById('search-filter').value = '';
    filterCommunications();
}

function sendCommunication(commId) {
    jQuery.post(ajaxurl, {
        action: 'send_communication',
        comm_id: commId,
        _wpnonce: osb_admin.nonce
    }, function(response) {
        if (response.success) {
            location.reload();
        } else {
            alert('Error: ' + response.data.message);
        }
    });
}

function deleteCommunication(commId) {
    jQuery.post(ajaxurl, {
        action: 'delete_communication',
        comm_id: commId,
        _wpnonce: osb_admin.nonce
    }, function(response) {
        if (response.success) {
            location.reload();
        } else {
            alert('Error: ' + response.data.message);
        }
    });
}

function duplicateCommunication(commId) {
    jQuery.post(ajaxurl, {
        action: 'duplicate_communication',
        comm_id: commId,
        _wpnonce: osb_admin.nonce
    }, function(response) {
        if (response.success) {
            location.reload();
        } else {
            alert('Error: ' + response.data.message);
        }
    });
}
</script>