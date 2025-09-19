<?php
/**
 * Automation & Workflow Management Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$notification_stats = $stats['notifications'] ?? null;
$recent_notifications = $stats['recent'] ?? array();
?>

<div class="wrap">
    <h1><?php _e('Workflow Automation', 'omafuru-spelling-bee'); ?></h1>

    <!-- Summary Cards -->
    <div class="osb-automation-summary">
        <div class="osb-summary-cards">
            <div class="osb-summary-card">
                <h3><?php _e('Total Notifications', 'omafuru-spelling-bee'); ?></h3>
                <div class="osb-card-number"><?php echo intval($notification_stats->total ?? 0); ?></div>
            </div>
            <div class="osb-summary-card osb-card-warning">
                <h3><?php _e('Unread', 'omafuru-spelling-bee'); ?></h3>
                <div class="osb-card-number"><?php echo intval($notification_stats->unread ?? 0); ?></div>
            </div>
            <div class="osb-summary-card osb-card-danger">
                <h3><?php _e('High Priority', 'omafuru-spelling-bee'); ?></h3>
                <div class="osb-card-number"><?php echo intval($notification_stats->high_priority ?? 0); ?></div>
            </div>
        </div>
    </div>

    <!-- Automation Controls -->
    <div class="osb-automation-controls">
        <h2><?php _e('Automation Controls', 'omafuru-spelling-bee'); ?></h2>

        <div class="osb-control-section">
            <h3><?php _e('Bulk Operations', 'omafuru-spelling-bee'); ?></h3>
            <p><?php _e('Perform bulk operations on multiple registrations:', 'omafuru-spelling-bee'); ?></p>

            <form id="osb-bulk-operations-form" method="post">
                <?php wp_nonce_field('osb_bulk_operation', 'bulk_nonce'); ?>

                <div class="osb-form-row">
                    <label for="bulk-action"><?php _e('Select Action:', 'omafuru-spelling-bee'); ?></label>
                    <select id="bulk-action" name="bulk_action">
                        <option value=""><?php _e('Choose an action...', 'omafuru-spelling-bee'); ?></option>
                        <option value="approve_all"><?php _e('Approve All Selected', 'omafuru-spelling-bee'); ?></option>
                        <option value="mark_documents_submitted"><?php _e('Mark as Documents Submitted', 'omafuru-spelling-bee'); ?></option>
                        <option value="send_reminder"><?php _e('Send Reminder Emails', 'omafuru-spelling-bee'); ?></option>
                    </select>
                </div>

                <div class="osb-form-row">
                    <label for="registration-filter"><?php _e('Filter Registrations:', 'omafuru-spelling-bee'); ?></label>
                    <select id="registration-filter" name="registration_filter">
                        <option value="pending"><?php _e('Pending Registrations', 'omafuru-spelling-bee'); ?></option>
                        <option value="documents_submitted"><?php _e('Documents Submitted', 'omafuru-spelling-bee'); ?></option>
                        <option value="under_review"><?php _e('Under Review', 'omafuru-spelling-bee'); ?></option>
                    </select>
                </div>

                <button type="button" id="preview-bulk-action" class="button button-secondary">
                    <?php _e('Preview Affected Registrations', 'omafuru-spelling-bee'); ?>
                </button>

                <button type="submit" class="button button-primary" disabled>
                    <?php _e('Execute Bulk Operation', 'omafuru-spelling-bee'); ?>
                </button>
            </form>
        </div>
    </div>

    <!-- Recent Notifications -->
    <div class="osb-recent-notifications">
        <h2><?php _e('Recent Notifications', 'omafuru-spelling-bee'); ?></h2>

        <?php if (empty($recent_notifications)): ?>
            <p><?php _e('No notifications yet. Automation will generate notifications as events occur.', 'omafuru-spelling-bee'); ?></p>
        <?php else: ?>
            <div class="osb-notifications-list">
                <?php foreach ($recent_notifications as $notification): ?>
                    <div class="osb-notification-item <?php echo $notification->is_read ? 'read' : 'unread'; ?> priority-<?php echo esc_attr($notification->priority); ?>">
                        <div class="osb-notification-header">
                            <div class="osb-notification-title">
                                <?php echo esc_html($notification->title); ?>
                                <?php if (!$notification->is_read): ?>
                                    <span class="osb-unread-indicator">●</span>
                                <?php endif; ?>
                            </div>
                            <div class="osb-notification-time">
                                <?php echo human_time_diff(strtotime($notification->created_at), current_time('timestamp')) . ' ' . __('ago', 'omafuru-spelling-bee'); ?>
                            </div>
                        </div>
                        <div class="osb-notification-message">
                            <?php echo esc_html($notification->message); ?>
                        </div>
                        <?php if (!$notification->is_read): ?>
                            <div class="osb-notification-actions">
                                <button type="button" class="button-link mark-as-read" data-notification-id="<?php echo $notification->id; ?>">
                                    <?php _e('Mark as Read', 'omafuru-spelling-bee'); ?>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Automation Status -->
    <div class="osb-automation-status">
        <h2><?php _e('Automation Status', 'omafuru-spelling-bee'); ?></h2>

        <div class="osb-status-grid">
            <div class="osb-status-item">
                <div class="osb-status-icon osb-status-active">✓</div>
                <div class="osb-status-text">
                    <strong><?php _e('Email Automation', 'omafuru-spelling-bee'); ?></strong>
                    <p><?php _e('Document checklists and reminders are being sent automatically', 'omafuru-spelling-bee'); ?></p>
                </div>
            </div>

            <div class="osb-status-item">
                <div class="osb-status-icon osb-status-active">✓</div>
                <div class="osb-status-text">
                    <strong><?php _e('School Classification', 'omafuru-spelling-bee'); ?></strong>
                    <p><?php _e('Schools are automatically classified based on participation history', 'omafuru-spelling-bee'); ?></p>
                </div>
            </div>

            <div class="osb-status-item">
                <div class="osb-status-icon osb-status-active">✓</div>
                <div class="osb-status-text">
                    <strong><?php _e('Progress Tracking', 'omafuru-spelling-bee'); ?></strong>
                    <p><?php _e('Registration progress is automatically saved and can be resumed', 'omafuru-spelling-bee'); ?></p>
                </div>
            </div>

            <div class="osb-status-item">
                <div class="osb-status-icon osb-status-active">✓</div>
                <div class="osb-status-text">
                    <strong><?php _e('File Upload Optimization', 'omafuru-spelling-bee'); ?></strong>
                    <p><?php _e('Mobile uploads are compressed and uploaded progressively', 'omafuru-spelling-bee'); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Automation JavaScript -->
<script>
jQuery(document).ready(function($) {
    // Mark notification as read
    $('.mark-as-read').on('click', function() {
        const notificationId = $(this).data('notification-id');
        const notificationItem = $(this).closest('.osb-notification-item');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'osb_mark_notification_read',
                notification_id: notificationId,
                nonce: '<?php echo wp_create_nonce('osb_admin_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    notificationItem.removeClass('unread').addClass('read');
                    notificationItem.find('.osb-unread-indicator').remove();
                    notificationItem.find('.osb-notification-actions').remove();

                    // Update unread count
                    const currentUnread = parseInt($('.osb-card-warning .osb-card-number').text());
                    $('.osb-card-warning .osb-card-number').text(Math.max(0, currentUnread - 1));
                }
            }
        });
    });

    // Preview bulk operations
    $('#preview-bulk-action').on('click', function() {
        const action = $('#bulk-action').val();
        const filter = $('#registration-filter').val();

        if (!action) {
            alert('<?php _e("Please select an action first.", "omafuru-spelling-bee"); ?>');
            return;
        }

        $(this).prop('disabled', true).text('<?php _e("Loading...", "omafuru-spelling-bee"); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'osb_admin_ajax',
                sub_action: 'preview_bulk_operation',
                bulk_action: action,
                registration_filter: filter,
                nonce: '<?php echo wp_create_nonce('osb_admin_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    const data = response.data;
                    let previewHtml = '<div class="osb-bulk-preview">';
                    previewHtml += '<h3><?php _e("Bulk Operation Preview", "omafuru-spelling-bee"); ?></h3>';
                    previewHtml += '<p><strong><?php _e("Action:", "omafuru-spelling-bee"); ?></strong> ' + data.action_description + '</p>';
                    previewHtml += '<p><strong><?php _e("Affected Registrations:", "omafuru-spelling-bee"); ?></strong> ' + data.affected_count + '</p>';

                    if (data.registrations.length > 0) {
                        previewHtml += '<h4><?php _e("Sample Affected Records:", "omafuru-spelling-bee"); ?></h4>';
                        previewHtml += '<table class="widefat"><thead><tr><th><?php _e("ID", "omafuru-spelling-bee"); ?></th><th><?php _e("School", "omafuru-spelling-bee"); ?></th><th><?php _e("Status", "omafuru-spelling-bee"); ?></th></tr></thead><tbody>';

                        data.registrations.forEach(function(reg) {
                            previewHtml += '<tr><td>' + reg.id + '</td><td>' + reg.school_name + '</td><td>' + reg.status + '</td></tr>';
                        });

                        previewHtml += '</tbody></table>';
                        if (data.total_found > data.registrations.length) {
                            previewHtml += '<p><em><?php _e("... and", "omafuru-spelling-bee"); ?> ' + (data.total_found - data.registrations.length) + ' <?php _e("more registrations", "omafuru-spelling-bee"); ?></em></p>';
                        }
                    }

                    previewHtml += '</div>';

                    // Show preview in a modal-like div
                    if ($('#bulk-preview-container').length === 0) {
                        $('<div id="bulk-preview-container" style="background:#f9f9f9;border:1px solid #ddd;padding:15px;margin:15px 0;border-radius:4px;"></div>').insertAfter('#osb-bulk-operations-form');
                    }
                    $('#bulk-preview-container').html(previewHtml);

                    // Enable the execute button
                    $('button[type="submit"]').prop('disabled', false);
                } else {
                    alert('<?php _e("Error:", "omafuru-spelling-bee"); ?> ' + response.data);
                }
            },
            error: function() {
                alert('<?php _e("An error occurred while previewing the bulk operation.", "omafuru-spelling-bee"); ?>');
            },
            complete: function() {
                $('#preview-bulk-action').prop('disabled', false).text('<?php _e("Preview Affected Registrations", "omafuru-spelling-bee"); ?>');
            }
        });
    });

    // Handle bulk operation form
    $('#osb-bulk-operations-form').on('submit', function(e) {
        e.preventDefault();

        const action = $('#bulk-action').val();
        const filter = $('#registration-filter').val();

        if (!confirm('<?php _e("Are you sure you want to execute this bulk operation?", "omafuru-spelling-bee"); ?>')) {
            return;
        }

        const $submitButton = $('button[type="submit"]');
        $submitButton.prop('disabled', true).text('<?php _e("Processing...", "omafuru-spelling-bee"); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'osb_admin_ajax',
                sub_action: 'execute_bulk_operation',
                bulk_action: action,
                registration_filter: filter,
                bulk_nonce: $('input[name="bulk_nonce"]').val(),
                nonce: '<?php echo wp_create_nonce('osb_admin_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php _e("Success:", "omafuru-spelling-bee"); ?> ' + response.data.message);

                    // Clear the preview
                    $('#bulk-preview-container').remove();

                    // Reset form
                    $('#bulk-action').val('');
                    $('#registration-filter').val('pending');
                    $submitButton.prop('disabled', true).text('<?php _e("Execute Bulk Operation", "omafuru-spelling-bee"); ?>');

                    // Reload page to show updated notifications
                    window.location.reload();
                } else {
                    alert('<?php _e("Error:", "omafuru-spelling-bee"); ?> ' + response.data);
                }
            },
            error: function() {
                alert('<?php _e("An error occurred while executing the bulk operation.", "omafuru-spelling-bee"); ?>');
            },
            complete: function() {
                if (!response || !response.success) {
                    $submitButton.prop('disabled', false).text('<?php _e("Execute Bulk Operation", "omafuru-spelling-bee"); ?>');
                }
            }
        });
    });
});
</script>

<!-- Automation Styles -->
<style>
.osb-automation-summary {
    margin-bottom: 30px;
}

.osb-summary-cards {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.osb-summary-card {
    background: #fff;
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    padding: 20px;
    text-align: center;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
}

.osb-summary-card h3 {
    margin: 0 0 10px 0;
    font-size: 14px;
    color: #646970;
    text-transform: uppercase;
}

.osb-card-number {
    font-size: 32px;
    font-weight: 600;
    color: #1d2327;
}

.osb-card-warning .osb-card-number {
    color: #dba617;
}

.osb-card-danger .osb-card-number {
    color: #d63638;
}

.osb-automation-controls,
.osb-recent-notifications,
.osb-automation-status {
    background: #fff;
    border: 1px solid #c3c4c7;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
    padding: 20px;
    margin-bottom: 20px;
}

.osb-form-row {
    margin-bottom: 15px;
}

.osb-form-row label {
    display: inline-block;
    width: 150px;
    font-weight: 600;
}

.osb-form-row select {
    min-width: 250px;
}

.osb-notifications-list {
    max-height: 500px;
    overflow-y: auto;
}

.osb-notification-item {
    border: 1px solid #c3c4c7;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 10px;
    background: #fff;
}

.osb-notification-item.unread {
    border-left: 4px solid #2271b1;
    background: #f6f7f7;
}

.osb-notification-item.priority-high {
    border-left-color: #d63638;
}

.osb-notification-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 8px;
}

.osb-notification-title {
    font-weight: 600;
    color: #1d2327;
}

.osb-unread-indicator {
    color: #2271b1;
    margin-left: 5px;
}

.osb-notification-time {
    color: #646970;
    font-size: 12px;
}

.osb-notification-message {
    color: #646970;
    line-height: 1.4;
}

.osb-notification-actions {
    margin-top: 10px;
    text-align: right;
}

.osb-status-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
}

.osb-status-item {
    display: flex;
    align-items: flex-start;
    gap: 15px;
}

.osb-status-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    color: white;
    flex-shrink: 0;
}

.osb-status-active {
    background: #00a32a;
}

.osb-status-text strong {
    display: block;
    margin-bottom: 5px;
    color: #1d2327;
}

.osb-status-text p {
    margin: 0;
    color: #646970;
    font-size: 13px;
    line-height: 1.4;
}
</style>