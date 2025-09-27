<?php
/**
 * Registrations List Template
 *
 * @var array $events
 * @var array $registrations
 * @var int $selected_event_id
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$admin_menu = OSB_Admin_Menu::getInstance();
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Registrations', 'spelling-bee-pro'); ?></h1>

    <?php if (isset($_GET['approved'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Registration approved successfully.', 'spelling-bee-pro'); ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['rejected'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Registration rejected successfully.', 'spelling-bee-pro'); ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Registration deleted successfully.', 'spelling-bee-pro'); ?></p>
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
                <option value="pending"><?php _e('Pending', 'spelling-bee-pro'); ?></option>
                <option value="approved"><?php _e('Approved', 'spelling-bee-pro'); ?></option>
                <option value="rejected"><?php _e('Rejected', 'spelling-bee-pro'); ?></option>
            </select>

            <input type="search" id="osb-search-registrations" class="osb-search-input"
                   placeholder="<?php _e('Search schools...', 'spelling-bee-pro'); ?>">

            <button type="button" id="osb-clear-filters" class="button">
                <?php _e('Clear Filters', 'spelling-bee-pro'); ?>
            </button>

            <button type="button" id="osb-export-registrations" class="button">
                <?php _e('Export CSV', 'spelling-bee-pro'); ?>
            </button>

            <?php if ($selected_event_id): ?>
                <button type="button" id="osb-send-bulk-email" class="button button-primary">
                    <?php _e('Send Bulk Email', 'spelling-bee-pro'); ?>
                </button>
            <?php endif; ?>
        </div>

        <?php if ($selected_event_id): ?>
            <!-- Registration Statistics -->
            <div class="osb-registration-stats">
                <?php
                $stats = array(
                    'total' => 0,
                    'pending' => 0,
                    'approved' => 0,
                    'rejected' => 0
                );

                foreach ($registrations as $reg) {
                    $stats['total']++;
                    $stats[$reg->status]++;
                }
                ?>

                <div class="osb-stat-item">
                    <span class="osb-stat-number"><?php echo $stats['total']; ?></span>
                    <span class="osb-stat-label"><?php _e('Total', 'spelling-bee-pro'); ?></span>
                </div>

                <div class="osb-stat-item osb-stat-pending">
                    <span class="osb-stat-number"><?php echo $stats['pending']; ?></span>
                    <span class="osb-stat-label"><?php _e('Pending', 'spelling-bee-pro'); ?></span>
                </div>

                <div class="osb-stat-item osb-stat-approved">
                    <span class="osb-stat-number"><?php echo $stats['approved']; ?></span>
                    <span class="osb-stat-label"><?php _e('Approved', 'spelling-bee-pro'); ?></span>
                </div>

                <div class="osb-stat-item osb-stat-rejected">
                    <span class="osb-stat-number"><?php echo $stats['rejected']; ?></span>
                    <span class="osb-stat-label"><?php _e('Rejected', 'spelling-bee-pro'); ?></span>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if (!$selected_event_id): ?>
        <div class="osb-no-event-selected">
            <div class="osb-empty-state">
                <div class="osb-empty-icon">📋</div>
                <h3><?php _e('Select an Event', 'spelling-bee-pro'); ?></h3>
                <p><?php _e('Please select an event from the dropdown above to view its registrations.', 'spelling-bee-pro'); ?></p>
            </div>
        </div>

    <?php elseif (!empty($registrations)): ?>
        <div class="osb-registrations-table-container">
            <table class="wp-list-table widefat fixed striped osb-registrations-table">
                <thead>
                    <tr>
                        <th scope="col" class="manage-column column-cb check-column">
                            <label class="screen-reader-text" for="cb-select-all-1"><?php _e('Select All', 'spelling-bee-pro'); ?></label>
                            <input id="cb-select-all-1" type="checkbox">
                        </th>
                        <th scope="col" class="manage-column column-school column-primary">
                            <?php _e('School', 'spelling-bee-pro'); ?>
                        </th>
                        <th scope="col" class="manage-column column-contact">
                            <?php _e('Contact Person', 'spelling-bee-pro'); ?>
                        </th>
                        <th scope="col" class="manage-column column-students">
                            <?php _e('Students', 'spelling-bee-pro'); ?>
                        </th>
                        <th scope="col" class="manage-column column-status">
                            <?php _e('Status', 'spelling-bee-pro'); ?>
                        </th>
                        <th scope="col" class="manage-column column-token">
                            <?php _e('Token', 'spelling-bee-pro'); ?>
                        </th>
                        <th scope="col" class="manage-column column-date">
                            <?php _e('Registered', 'spelling-bee-pro'); ?>
                        </th>
                        <th scope="col" class="manage-column column-actions">
                            <?php _e('Actions', 'spelling-bee-pro'); ?>
                        </th>
                    </tr>
                </thead>

                <tbody id="osb-registrations-tbody">
                    <?php foreach ($registrations as $registration): ?>
                        <?php
                        // Get student count for this school
                        $db = OSB_Database::getInstance();
                        $students = $db->getStudentsBySchool($registration->school_id);
                        $student_count = count($students);
                        ?>
                        <tr class="osb-registration-row"
                            data-status="<?php echo esc_attr($registration->status); ?>"
                            data-school-id="<?php echo esc_attr($registration->school_id); ?>">

                            <th scope="row" class="check-column">
                                <input type="checkbox" name="registration[]" value="<?php echo $registration->id; ?>">
                            </th>

                            <td class="column-school column-primary" data-colname="<?php _e('School', 'spelling-bee-pro'); ?>">
                                <strong>
                                    <a href="<?php echo $admin_menu->getAdminUrl('registrations', array('action' => 'view', 'registration_id' => $registration->id)); ?>"
                                       class="row-title">
                                        <?php echo esc_html($registration->school_name); ?>
                                    </a>
                                </strong>

                                <div class="row-actions">
                                    <span class="view">
                                        <a href="<?php echo $admin_menu->getAdminUrl('registrations', array('action' => 'view', 'registration_id' => $registration->id)); ?>">
                                            <?php _e('View Details', 'spelling-bee-pro'); ?>
                                        </a> |
                                    </span>
                                    <span class="school">
                                        <a href="<?php echo $admin_menu->getAdminUrl('schools', array('action' => 'view', 'school_id' => $registration->school_id)); ?>">
                                            <?php _e('View School', 'spelling-bee-pro'); ?>
                                        </a> |
                                    </span>
                                    <span class="students">
                                        <a href="<?php echo $admin_menu->getAdminUrl('students', array('school_id' => $registration->school_id)); ?>">
                                            <?php _e('View Students', 'spelling-bee-pro'); ?>
                                        </a>
                                        <?php if ($registration->status === 'pending'): ?>
                                            |
                                        </span>
                                        <span class="delete">
                                            <a href="<?php echo $admin_menu->getActionUrl('delete_registration', array('registration_id' => $registration->id)); ?>"
                                               class="submitdelete"
                                               onclick="return confirm('<?php _e('Are you sure you want to delete this registration?', 'spelling-bee-pro'); ?>')">
                                                <?php _e('Delete', 'spelling-bee-pro'); ?>
                                            </a>
                                        <?php endif; ?>
                                    </span>
                                </div>

                                <button type="button" class="toggle-row">
                                    <span class="screen-reader-text"><?php _e('Show more details', 'spelling-bee-pro'); ?></span>
                                </button>
                            </td>

                            <td class="column-contact" data-colname="<?php _e('Contact Person', 'spelling-bee-pro'); ?>">
                                <div class="osb-contact-info">
                                    <strong><?php echo esc_html($registration->contact_person); ?></strong><br>
                                    <a href="mailto:<?php echo esc_attr($registration->contact_email); ?>">
                                        <?php echo esc_html($registration->contact_email); ?>
                                    </a>
                                </div>
                            </td>

                            <td class="column-students" data-colname="<?php _e('Students', 'spelling-bee-pro'); ?>">
                                <span class="osb-student-count">
                                    <a href="<?php echo $admin_menu->getAdminUrl('students', array('school_id' => $registration->school_id)); ?>">
                                        <?php echo intval($student_count); ?>
                                    </a>
                                </span>

                                <?php if ($student_count > 0): ?>
                                    <div class="osb-student-preview">
                                        <?php
                                        $first_few = array_slice($students, 0, 3);
                                        $names = array_map(function($student) {
                                            return $student->first_name . ' ' . $student->last_name;
                                        }, $first_few);

                                        echo esc_html(implode(', ', $names));

                                        if ($student_count > 3) {
                                            echo '<br><small>' . sprintf(__('and %d more...', 'spelling-bee-pro'), $student_count - 3) . '</small>';
                                        }
                                        ?>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td class="column-status" data-colname="<?php _e('Status', 'spelling-bee-pro'); ?>">
                                <span class="osb-status osb-status-<?php echo esc_attr($registration->status); ?>">
                                    <?php echo esc_html(ucfirst($registration->status)); ?>
                                </span>

                                <?php if ($registration->status === 'pending'): ?>
                                    <div class="osb-quick-actions">
                                        <button type="button" class="button button-small button-primary osb-approve-registration"
                                                data-registration-id="<?php echo $registration->id; ?>">
                                            <?php _e('Approve', 'spelling-bee-pro'); ?>
                                        </button>
                                        <button type="button" class="button button-small osb-reject-registration"
                                                data-registration-id="<?php echo $registration->id; ?>">
                                            <?php _e('Reject', 'spelling-bee-pro'); ?>
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td class="column-token" data-colname="<?php _e('Token', 'spelling-bee-pro'); ?>">
                                <code class="osb-token"><?php echo esc_html(substr($registration->registration_token, 0, 8)) . '...'; ?></code>
                                <button type="button" class="button-link osb-copy-token"
                                        data-token="<?php echo esc_attr($registration->registration_token); ?>"
                                        title="<?php _e('Copy full token', 'spelling-bee-pro'); ?>">
                                    📋
                                </button>
                            </td>

                            <td class="column-date" data-colname="<?php _e('Registered', 'spelling-bee-pro'); ?>">
                                <abbr title="<?php echo esc_attr(date('F j, Y g:i a', strtotime($registration->created_at))); ?>">
                                    <?php echo date('M j, Y', strtotime($registration->created_at)); ?>
                                </abbr>
                            </td>

                            <td class="column-actions" data-colname="<?php _e('Actions', 'spelling-bee-pro'); ?>">
                                <div class="osb-action-buttons">
                                    <button type="button" class="button button-small button-primary osb-view-form-data"
                                            data-registration-id="<?php echo $registration->id; ?>"
                                            title="<?php _e('View submitted form data', 'spelling-bee-pro'); ?>">
                                        <?php _e('View Form Data', 'spelling-bee-pro'); ?>
                                    </button>

                                    <button type="button" class="button button-small osb-send-email"
                                            data-registration-id="<?php echo $registration->id; ?>">
                                        <?php _e('Send Email', 'spelling-bee-pro'); ?>
                                    </button>

                                    <?php if ($registration->status !== 'pending'): ?>
                                        <button type="button" class="button button-small osb-view-documents"
                                                data-school-id="<?php echo $registration->school_id; ?>">
                                            <?php _e('Documents', 'spelling-bee-pro'); ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Bulk Actions -->
        <div class="tablenav bottom">
            <div class="alignleft actions bulkactions">
                <label for="bulk-action-selector-bottom" class="screen-reader-text"><?php _e('Select bulk action', 'spelling-bee-pro'); ?></label>
                <select name="action2" id="bulk-action-selector-bottom">
                    <option value="-1"><?php _e('Bulk Actions', 'spelling-bee-pro'); ?></option>
                    <option value="approve"><?php _e('Approve', 'spelling-bee-pro'); ?></option>
                    <option value="reject"><?php _e('Reject', 'spelling-bee-pro'); ?></option>
                    <option value="send_email"><?php _e('Send Email', 'spelling-bee-pro'); ?></option>
                    <option value="delete"><?php _e('Delete', 'spelling-bee-pro'); ?></option>
                </select>
                <input type="submit" id="doaction2" class="button action" value="<?php _e('Apply', 'spelling-bee-pro'); ?>">
            </div>

            <div class="alignright">
                <span class="displaying-num"><?php printf(_n('%d registration', '%d registrations', count($registrations), 'spelling-bee-pro'), count($registrations)); ?></span>
            </div>
        </div>

    <?php else: ?>
        <div class="osb-no-registrations">
            <div class="osb-empty-state">
                <div class="osb-empty-icon">📋</div>
                <h3><?php _e('No Registrations Found', 'spelling-bee-pro'); ?></h3>
                <p><?php _e('No schools have registered for this event yet.', 'spelling-bee-pro'); ?></p>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Email Modal -->
<div id="osb-email-modal" class="osb-modal" style="display: none;">
    <div class="osb-modal-content">
        <div class="osb-modal-header">
            <h3 id="osb-email-modal-title"><?php _e('Send Email', 'spelling-bee-pro'); ?></h3>
            <button type="button" class="osb-modal-close">&times;</button>
        </div>
        <div class="osb-modal-body">
            <form id="osb-email-form">
                <input type="hidden" id="email-registration-ids" name="registration_ids">

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="email-subject"><?php _e('Subject', 'spelling-bee-pro'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="email-subject" name="subject" class="widefat" required>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="email-message"><?php _e('Message', 'spelling-bee-pro'); ?></label>
                        </th>
                        <td>
                            <textarea id="email-message" name="message" rows="8" class="widefat" required></textarea>
                            <p class="description"><?php _e('You can use basic HTML formatting.', 'spelling-bee-pro'); ?></p>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="osb-modal-footer">
            <button type="button" id="send-email-btn" class="button button-primary">
                <?php _e('Send Email', 'spelling-bee-pro'); ?>
            </button>
            <button type="button" class="button osb-modal-close">
                <?php _e('Cancel', 'spelling-bee-pro'); ?>
            </button>
        </div>
    </div>
</div>

<!-- Rejection Reason Modal -->
<div id="osb-reject-modal" class="osb-modal" style="display: none;">
    <div class="osb-modal-content">
        <div class="osb-modal-header">
            <h3><?php _e('Reject Registration', 'spelling-bee-pro'); ?></h3>
            <button type="button" class="osb-modal-close">&times;</button>
        </div>
        <div class="osb-modal-body">
            <p><?php _e('Please provide a reason for rejecting this registration:', 'spelling-bee-pro'); ?></p>
            <textarea id="rejection-reason" rows="4" class="widefat" placeholder="<?php _e('Enter rejection reason...', 'spelling-bee-pro'); ?>"></textarea>
            <input type="hidden" id="reject-registration-id">
        </div>
        <div class="osb-modal-footer">
            <button type="button" id="confirm-reject-btn" class="button button-primary">
                <?php _e('Reject Registration', 'spelling-bee-pro'); ?>
            </button>
            <button type="button" class="button osb-modal-close">
                <?php _e('Cancel', 'spelling-bee-pro'); ?>
            </button>
        </div>
    </div>
</div>

<!-- View Form Data Modal -->
<div id="osb-form-data-modal" class="osb-modal" style="display: none;">
    <div class="osb-modal-content osb-modal-large">
        <div class="osb-modal-header">
            <h3 id="osb-form-data-modal-title"><?php _e('Registration Form Data', 'spelling-bee-pro'); ?></h3>
            <button type="button" class="osb-modal-close">&times;</button>
        </div>
        <div class="osb-modal-body" id="osb-form-data-content">
            <!-- Form data will be loaded here via AJAX -->
            <div class="osb-loading">
                <div class="osb-spinner"></div>
                <p><?php _e('Loading registration data...', 'spelling-bee-pro'); ?></p>
            </div>
        </div>
        <div class="osb-modal-footer">
            <button type="button" class="button osb-modal-close">
                <?php _e('Close', 'spelling-bee-pro'); ?>
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
    margin-bottom: 15px;
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

.osb-registration-stats {
    display: flex;
    gap: 20px;
    padding-top: 15px;
    border-top: 1px solid #ddd;
}

.osb-stat-item {
    text-align: center;
    padding: 15px;
    background: #f9f9f9;
    border-radius: 4px;
    min-width: 80px;
}

.osb-stat-number {
    display: block;
    font-size: 24px;
    font-weight: bold;
    line-height: 1;
}

.osb-stat-label {
    display: block;
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
    margin-top: 4px;
}

.osb-stat-pending .osb-stat-number { color: #ffb900; }
.osb-stat-approved .osb-stat-number { color: #00a32a; }
.osb-stat-rejected .osb-stat-number { color: #d63638; }

.osb-registrations-table-container {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
}

.osb-registrations-table {
    margin: 0;
}

.osb-contact-info {
    line-height: 1.5;
}

.osb-contact-info strong {
    color: #0073aa;
}

.osb-student-count {
    display: inline-block;
    background: #f0f0f0;
    padding: 4px 8px;
    border-radius: 3px;
    font-weight: 500;
}

.osb-student-count a {
    text-decoration: none;
    color: #0073aa;
}

.osb-student-preview {
    margin-top: 8px;
    font-size: 0.9em;
    line-height: 1.4;
}

.osb-student-preview small {
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

.osb-status-pending { background: #ffb900; color: white; }
.osb-status-approved { background: #00a32a; color: white; }
.osb-status-rejected { background: #d63638; color: white; }

.osb-quick-actions {
    margin-top: 8px;
}

.osb-quick-actions .button {
    font-size: 11px;
    height: auto;
    padding: 3px 8px;
    line-height: 1.4;
    margin-right: 4px;
}

.osb-token {
    font-family: monospace;
    background: #f0f0f0;
    padding: 2px 4px;
    border-radius: 2px;
    font-size: 0.85em;
}

.osb-copy-token {
    border: none;
    background: none;
    cursor: pointer;
    padding: 2px 4px;
    font-size: 14px;
}

.osb-copy-token:hover {
    background: #f0f0f0;
    border-radius: 2px;
}

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

.osb-no-event-selected,
.osb-no-registrations {
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

.osb-modal-large {
    max-width: 900px;
    width: 95%;
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

/* Hidden class for filtering */
.osb-registration-row.hidden {
    display: none;
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

    .osb-registration-stats {
        flex-wrap: wrap;
    }

    .column-token,
    .column-date {
        display: none;
    }
}

@media (max-width: 600px) {
    .column-students,
    .column-actions {
        display: none;
    }

    .osb-modal-content {
        width: 95%;
        margin: 20px;
    }
}

/* Form Data Modal Styles */
.osb-loading {
    text-align: center;
    padding: 40px;
}

.osb-spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #0073aa;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    animation: spin 1s linear infinite;
    margin: 0 auto 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.osb-form-data-container {
    padding: 0;
}

/* Tab Navigation Styles */
.osb-tab-navigation {
    display: flex;
    background: #f1f1f1;
    border-bottom: 2px solid #0073aa;
    margin: -20px -20px 0 -20px;
    padding: 0;
    border-radius: 8px 8px 0 0;
    overflow: hidden;
}

.osb-tab-button {
    flex: 1;
    background: #f1f1f1;
    border: none;
    padding: 15px 20px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    font-size: 14px;
    font-weight: 500;
    color: #666;
    border-right: 1px solid #ddd;
}

.osb-tab-button:last-child {
    border-right: none;
}

.osb-tab-button:hover {
    background: #e8e8e8;
    color: #0073aa;
}

.osb-tab-button.active {
    background: #0073aa;
    color: white;
    box-shadow: inset 0 -3px 0 #005a87;
}

.osb-tab-button.active:hover {
    background: #005a87;
    color: white;
}

.osb-tab-icon {
    font-size: 16px;
}

.osb-tab-label {
    font-weight: 600;
}

/* Tab Content Styles */
.osb-tab-content {
    padding: 20px;
    min-height: 400px;
}

.osb-tab-pane {
    display: none;
}

.osb-tab-pane.active {
    display: block;
    animation: fadeIn 0.3s ease-in-out;
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.osb-form-section {
    margin-bottom: 25px;
    border: 1px solid #e1e1e1;
    border-radius: 8px;
    overflow: hidden;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.osb-form-section-header {
    background: linear-gradient(135deg, #f7f7f7 0%, #f1f1f1 100%);
    padding: 15px 20px;
    border-bottom: 1px solid #e1e1e1;
    font-weight: 600;
    color: #0073aa;
    font-size: 15px;
}

.osb-form-section-content {
    padding: 20px;
    background: #fff;
}

.osb-data-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 15px;
}

.osb-data-item {
    display: flex;
    flex-direction: column;
    margin-bottom: 15px;
}

.osb-data-item.osb-data-full-width {
    grid-column: 1 / -1;
}

.osb-data-label {
    font-weight: 600;
    color: #555;
    margin-bottom: 8px;
    font-size: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.osb-data-value {
    color: #333;
    padding: 12px 15px;
    background: #f9f9f9;
    border-radius: 6px;
    min-height: 20px;
    line-height: 1.5;
    border: 1px solid #e8e8e8;
    transition: all 0.2s ease;
}

.osb-data-value:hover {
    background: #f5f5f5;
    border-color: #ddd;
}

.osb-data-value.empty {
    color: #999;
    font-style: italic;
}

.osb-data-value code {
    background: #e8e8e8;
    padding: 4px 8px;
    border-radius: 4px;
    font-family: 'Courier New', monospace;
    font-size: 13px;
}

.osb-data-value a {
    color: #0073aa;
    text-decoration: none;
}

.osb-data-value a:hover {
    text-decoration: underline;
}

/* Students Styles */
.osb-students-list {
    margin-top: 0;
}

.osb-student-card {
    background: #fff;
    border: 1px solid #e1e1e1;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 15px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    transition: all 0.2s ease;
}

.osb-student-card:hover {
    box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    border-color: #0073aa;
}

.osb-student-header {
    display: flex;
    align-items: center;
    gap: 12px;
    font-weight: 600;
    color: #0073aa;
    margin-bottom: 15px;
    padding-bottom: 10px;
    border-bottom: 2px solid #f1f1f1;
}

.osb-student-number {
    background: #0073aa;
    color: white;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    min-width: 30px;
    text-align: center;
}

.osb-student-name {
    font-size: 16px;
}

/* Documents Styles */
.osb-documents-list {
    margin-top: 0;
}

.osb-document-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 15px;
    background: #f9f9f9;
    border: 1px solid #e8e8e8;
    border-radius: 8px;
    margin-bottom: 12px;
    transition: all 0.2s ease;
}

.osb-document-item:hover {
    background: #f5f5f5;
    border-color: #0073aa;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.osb-document-info {
    flex: 1;
}

.osb-document-name {
    font-weight: 600;
    color: #0073aa;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.osb-document-icon {
    font-size: 18px;
}

.osb-document-meta {
    font-size: 12px;
    color: #666;
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.osb-document-meta span {
    background: #e8e8e8;
    padding: 2px 8px;
    border-radius: 12px;
    font-weight: 500;
}

.osb-document-actions {
    display: flex;
    gap: 8px;
    flex-shrink: 0;
}

.osb-document-actions .button {
    font-size: 12px;
    padding: 6px 12px;
    height: auto;
    line-height: 1.4;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Status Badges */
.osb-status {
    display: inline-flex;
    align-items: center;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.osb-status-pending {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.osb-status-approved {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.osb-status-rejected {
    background: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

/* Responsive Design */
@media (max-width: 768px) {
    .osb-tab-navigation {
        flex-direction: column;
    }

    .osb-tab-button {
        border-right: none;
        border-bottom: 1px solid #ddd;
    }

    .osb-tab-button:last-child {
        border-bottom: none;
    }

    .osb-data-grid {
        grid-template-columns: 1fr;
    }

    .osb-document-item {
        flex-direction: column;
        gap: 15px;
        align-items: stretch;
    }

    .osb-document-actions {
        justify-content: center;
    }

    .osb-student-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Event filter change
    $('#osb-event-filter').on('change', function() {
        const eventId = $(this).val();
        if (eventId) {
            window.location.href = '<?php echo admin_url('admin.php?page=spelling-bee-registrations'); ?>&event_id=' + eventId;
        } else {
            window.location.href = '<?php echo admin_url('admin.php?page=spelling-bee-registrations'); ?>';
        }
    });

    // Filtering functionality
    function filterRegistrations() {
        const statusFilter = $('#osb-status-filter').val();
        const searchTerm = $('#osb-search-registrations').val().toLowerCase();

        $('.osb-registration-row').each(function() {
            const $row = $(this);
            const status = $row.data('status');
            const schoolName = $row.find('.row-title').text().toLowerCase();
            const contactName = $row.find('.osb-contact-info strong').text().toLowerCase();

            let show = true;

            // Status filter
            if (statusFilter && status !== statusFilter) {
                show = false;
            }

            // Search filter
            if (searchTerm && !schoolName.includes(searchTerm) && !contactName.includes(searchTerm)) {
                show = false;
            }

            $row.toggleClass('hidden', !show);
        });

        // Update count
        const visibleCount = $('.osb-registration-row:not(.hidden)').length;
        $('.displaying-num').text(
            visibleCount === 1
                ? '<?php _e('1 registration', 'spelling-bee-pro'); ?>'
                : visibleCount + ' <?php _e('registrations', 'spelling-bee-pro'); ?>'
        );
    }

    // Bind filter events
    $('#osb-status-filter').on('change', filterRegistrations);
    $('#osb-search-registrations').on('input', filterRegistrations);

    // Clear filters
    $('#osb-clear-filters').on('click', function() {
        $('#osb-status-filter').val('');
        $('#osb-search-registrations').val('');
        filterRegistrations();
    });

    // Copy token to clipboard
    $('.osb-copy-token').on('click', function() {
        const token = $(this).data('token');

        if (navigator.clipboard) {
            navigator.clipboard.writeText(token).then(function() {
                alert('<?php _e('Token copied to clipboard!', 'spelling-bee-pro'); ?>');
            });
        } else {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = token;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            alert('<?php _e('Token copied to clipboard!', 'spelling-bee-pro'); ?>');
        }
    });

    // Approve registration
    $('.osb-approve-registration').on('click', function() {
        const registrationId = $(this).data('registration-id');

        if (!confirm('<?php _e('Are you sure you want to approve this registration?', 'spelling-bee-pro'); ?>')) {
            return;
        }

        const $button = $(this);
        $button.prop('disabled', true).text('<?php _e('Approving...', 'spelling-bee-pro'); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'osb_admin_action',
                sub_action: 'update_registration_status',
                registration_id: registrationId,
                status: 'approved',
                nonce: osb_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('<?php _e('Error:', 'spelling-bee-pro'); ?> ' + response.data);
                    $button.prop('disabled', false).text('<?php _e('Approve', 'spelling-bee-pro'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Network error. Please try again.', 'spelling-bee-pro'); ?>');
                $button.prop('disabled', false).text('<?php _e('Approve', 'spelling-bee-pro'); ?>');
            }
        });
    });

    // Reject registration
    $('.osb-reject-registration').on('click', function() {
        const registrationId = $(this).data('registration-id');
        $('#reject-registration-id').val(registrationId);
        $('#osb-reject-modal').show();
    });

    // Confirm rejection
    $('#confirm-reject-btn').on('click', function() {
        const registrationId = $('#reject-registration-id').val();
        const reason = $('#rejection-reason').val();

        if (!reason.trim()) {
            alert('<?php _e('Please provide a reason for rejection.', 'spelling-bee-pro'); ?>');
            return;
        }

        const $button = $(this);
        $button.prop('disabled', true).text('<?php _e('Rejecting...', 'spelling-bee-pro'); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'osb_admin_action',
                sub_action: 'update_registration_status',
                registration_id: registrationId,
                status: 'rejected',
                reason: reason,
                nonce: osb_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('<?php _e('Error:', 'spelling-bee-pro'); ?> ' + response.data);
                    $button.prop('disabled', false).text('<?php _e('Reject Registration', 'spelling-bee-pro'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Network error. Please try again.', 'spelling-bee-pro'); ?>');
                $button.prop('disabled', false).text('<?php _e('Reject Registration', 'spelling-bee-pro'); ?>');
            }
        });
    });

    // Send email
    $('.osb-send-email').on('click', function() {
        const registrationId = $(this).data('registration-id');
        $('#email-registration-ids').val(registrationId);
        $('#osb-email-modal-title').text('<?php _e('Send Email', 'spelling-bee-pro'); ?>');
        $('#osb-email-modal').show();
    });

    // Send bulk email
    $('#osb-send-bulk-email').on('click', function() {
        $('#email-registration-ids').val('all');
        $('#osb-email-modal-title').text('<?php _e('Send Bulk Email', 'spelling-bee-pro'); ?>');
        $('#osb-email-modal').show();
    });

    // View Form Data
    $('.osb-view-form-data').on('click', function() {
        const registrationId = $(this).data('registration-id');

        // Show modal with loading state
        $('#osb-form-data-modal').show();
        $('#osb-form-data-content').html(`
            <div class="osb-loading">
                <div class="osb-spinner"></div>
                <p><?php _e('Loading registration data...', 'spelling-bee-pro'); ?></p>
            </div>
        `);

        // Load form data via AJAX
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'osb_admin_action',
                sub_action: 'get_registration_form_data',
                registration_id: registrationId,
                nonce: osb_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#osb-form-data-content').html(response.data.html);
                    // Initialize tabs after content is loaded
                    initFormDataTabs();
                } else {
                    $('#osb-form-data-content').html(`
                        <div class="osb-error">
                            <p><strong><?php _e('Error:', 'spelling-bee-pro'); ?></strong> ${response.data}</p>
                        </div>
                    `);
                }
            },
            error: function() {
                $('#osb-form-data-content').html(`
                    <div class="osb-error">
                        <p><strong><?php _e('Network error:', 'spelling-bee-pro'); ?></strong> <?php _e('Unable to load registration data.', 'spelling-bee-pro'); ?></p>
                    </div>
                `);
            }
        });
    });

    // Initialize tab functionality for form data modal
    function initFormDataTabs() {
        // Tab switching functionality
        $(document).off('click', '.osb-tab-button').on('click', '.osb-tab-button', function() {
            const targetTab = $(this).data('tab');

            // Remove active class from all buttons and panes
            $('.osb-tab-button').removeClass('active');
            $('.osb-tab-pane').removeClass('active');

            // Add active class to clicked button and corresponding pane
            $(this).addClass('active');
            $('#osb-tab-' + targetTab).addClass('active');
        });
    }

    // Send email action
    $('#send-email-btn').on('click', function() {
        const registrationIds = $('#email-registration-ids').val();
        const subject = $('#email-subject').val();
        const message = $('#email-message').val();

        if (!subject.trim() || !message.trim()) {
            alert('<?php _e('Please fill in all fields.', 'spelling-bee-pro'); ?>');
            return;
        }

        const $button = $(this);
        $button.prop('disabled', true).text('<?php _e('Sending...', 'spelling-bee-pro'); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'osb_admin_action',
                sub_action: 'send_bulk_email',
                registration_ids: registrationIds,
                event_id: <?php echo intval($selected_event_id); ?>,
                subject: subject,
                message: message,
                nonce: osb_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php _e('Emails sent successfully!', 'spelling-bee-pro'); ?>');
                    $('#osb-email-modal').hide();
                    $('#osb-email-form')[0].reset();
                } else {
                    alert('<?php _e('Error:', 'spelling-bee-pro'); ?> ' + response.data);
                }
                $button.prop('disabled', false).text('<?php _e('Send Email', 'spelling-bee-pro'); ?>');
            },
            error: function() {
                alert('<?php _e('Network error. Please try again.', 'spelling-bee-pro'); ?>');
                $button.prop('disabled', false).text('<?php _e('Send Email', 'spelling-bee-pro'); ?>');
            }
        });
    });

    // Modal close
    $('.osb-modal-close').on('click', function() {
        $('.osb-modal').hide();
        $('#rejection-reason').val('');
        $('#osb-email-form')[0].reset();
    });

    // Export CSV
    $('#osb-export-registrations').on('click', function() {
        const params = new URLSearchParams({
            action: 'osb_admin_action',
            sub_action: 'export_registrations',
            event_id: <?php echo intval($selected_event_id); ?>,
            format: 'csv',
            nonce: osb_ajax.nonce
        });

        window.location.href = ajaxurl + '?' + params.toString();
    });

    // Bulk actions
    $('#doaction2').on('click', function(e) {
        const action = $('#bulk-action-selector-bottom').val();
        const selected = $('input[name="registration[]"]:checked');

        if (action === '-1') {
            alert('<?php _e('Please select an action.', 'spelling-bee-pro'); ?>');
            e.preventDefault();
            return;
        }

        if (selected.length === 0) {
            alert('<?php _e('Please select at least one registration.', 'spelling-bee-pro'); ?>');
            e.preventDefault();
            return;
        }

        if (action === 'delete') {
            if (!confirm('<?php _e('Are you sure you want to delete the selected registrations?', 'spelling-bee-pro'); ?>')) {
                e.preventDefault();
                return;
            }
        }

        if (action === 'send_email') {
            e.preventDefault();
            const registrationIds = selected.map(function() {
                return $(this).val();
            }).get();
            $('#email-registration-ids').val(registrationIds.join(','));
            $('#osb-email-modal-title').text('<?php _e('Send Bulk Email', 'spelling-bee-pro'); ?>');
            $('#osb-email-modal').show();
            return;
        }

        // Process other bulk actions via AJAX
        e.preventDefault();

        const registrationIds = selected.map(function() {
            return $(this).val();
        }).get();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'osb_admin_action',
                sub_action: 'bulk_registrations_action',
                bulk_action: action,
                registration_ids: registrationIds,
                nonce: osb_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('<?php _e('Error:', 'spelling-bee-pro'); ?> ' + response.data);
                }
            },
            error: function() {
                alert('<?php _e('Network error. Please try again.', 'spelling-bee-pro'); ?>');
            }
        });
    });

    // Select all checkbox
    $('#cb-select-all-1').on('change', function() {
        $('input[name="registration[]"]').prop('checked', $(this).is(':checked'));
    });
});
</script>