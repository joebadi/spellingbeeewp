<?php
/**
 * Reports Admin Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Variables passed from menu class
$events = isset($events) ? $events : array();
$selected_event_id = isset($selected_event_id) ? $selected_event_id : 0;
$report_data = isset($report_data) ? $report_data : array();
?>

<div class="wrap osb-admin-page">
    <div class="osb-header">
        <h1 class="wp-heading-inline">
            <span class="dashicons dashicons-chart-bar"></span>
            Reports & Analytics
        </h1>

        <div class="osb-header-actions">
            <button type="button" class="button" id="export-report">
                <span class="dashicons dashicons-download"></span>
                Export Report
            </button>
            <button type="button" class="button button-primary" id="generate-pdf">
                <span class="dashicons dashicons-pdf"></span>
                Generate PDF
            </button>
        </div>
    </div>

    <hr class="wp-header-end">

    <!-- Event Selection -->
    <div class="osb-filters">
        <div class="osb-filter-bar">
            <label for="event-filter"><?php _e('Select Event:', 'spelling-bee-pro'); ?></label>
            <select id="event-filter" class="osb-filter-select">
                <option value=""><?php _e('Choose an event...', 'spelling-bee-pro'); ?></option>
                <?php foreach ($events as $event): ?>
                    <option value="<?php echo $event->id; ?>" <?php selected($selected_event_id, $event->id); ?>>
                        <?php echo esc_html($event->title . ' (' . $event->year . ')'); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select id="report-type" class="osb-filter-select">
                <option value="overview"><?php _e('Overview Report', 'spelling-bee-pro'); ?></option>
                <option value="registrations"><?php _e('Registrations Report', 'spelling-bee-pro'); ?></option>
                <option value="donations"><?php _e('Donations Report', 'spelling-bee-pro'); ?></option>
                <option value="financial"><?php _e('Financial Report', 'spelling-bee-pro'); ?></option>
                <option value="participation"><?php _e('Participation Report', 'spelling-bee-pro'); ?></option>
            </select>

            <input type="date" id="date-from" class="osb-filter-input" placeholder="From Date">
            <input type="date" id="date-to" class="osb-filter-input" placeholder="To Date">

            <button type="button" id="apply-filters" class="button">
                <?php _e('Apply Filters', 'spelling-bee-pro'); ?>
            </button>
        </div>
    </div>

    <?php if (!$selected_event_id): ?>
        <div class="osb-no-selection">
            <div class="osb-empty-state">
                <div class="osb-empty-icon">📊</div>
                <h3><?php _e('Select an Event', 'spelling-bee-pro'); ?></h3>
                <p><?php _e('Please select an event from the dropdown above to generate reports.', 'spelling-bee-pro'); ?></p>
            </div>
        </div>

    <?php elseif (!empty($report_data)): ?>

        <!-- Report Content -->
        <div class="osb-report-content">

            <!-- Overview Stats -->
            <div class="osb-stats-grid">
                <div class="osb-stat-card osb-primary">
                    <div class="osb-stat-icon">🏫</div>
                    <div class="osb-stat-content">
                        <div class="osb-stat-number"><?php echo count($report_data['registrations']); ?></div>
                        <div class="osb-stat-label"><?php _e('Schools Registered', 'spelling-bee-pro'); ?></div>
                    </div>
                </div>

                <div class="osb-stat-card osb-success">
                    <div class="osb-stat-icon">👥</div>
                    <div class="osb-stat-content">
                        <div class="osb-stat-number">
                            <?php
                            $total_students = 0;
                            foreach ($report_data['registrations'] as $reg) {
                                if ($reg->status === 'approved') {
                                    global $wpdb;
                                    $student_count = $wpdb->get_var($wpdb->prepare(
                                        "SELECT COUNT(*) FROM {$wpdb->prefix}osb_students WHERE school_id = %d",
                                        $reg->school_id
                                    ));
                                    $total_students += intval($student_count);
                                }
                            }
                            echo number_format($total_students);
                            ?>
                        </div>
                        <div class="osb-stat-label"><?php _e('Total Students', 'spelling-bee-pro'); ?></div>
                    </div>
                </div>

                <div class="osb-stat-card osb-info">
                    <div class="osb-stat-icon">💰</div>
                    <div class="osb-stat-content">
                        <div class="osb-stat-number">
                            $<?php echo number_format($report_data['donations']['total_amount'] ?? 0, 0); ?>
                        </div>
                        <div class="osb-stat-label"><?php _e('Total Donations', 'spelling-bee-pro'); ?></div>
                    </div>
                </div>

                <div class="osb-stat-card osb-warning">
                    <div class="osb-stat-icon">📋</div>
                    <div class="osb-stat-content">
                        <div class="osb-stat-number">
                            <?php
                            $pending_count = 0;
                            foreach ($report_data['registrations'] as $reg) {
                                if ($reg->status === 'pending') $pending_count++;
                            }
                            echo $pending_count;
                            ?>
                        </div>
                        <div class="osb-stat-label"><?php _e('Pending Approvals', 'spelling-bee-pro'); ?></div>
                    </div>
                </div>
            </div>

            <!-- Registration Status Chart -->
            <div class="osb-report-section">
                <h3><?php _e('Registration Status Breakdown', 'spelling-bee-pro'); ?></h3>
                <div class="osb-chart-container">
                    <canvas id="registration-chart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Monthly Registration Trends -->
            <div class="osb-report-section">
                <h3><?php _e('Registration Timeline', 'spelling-bee-pro'); ?></h3>
                <div class="osb-chart-container">
                    <canvas id="timeline-chart" width="400" height="200"></canvas>
                </div>
            </div>

            <!-- Top Performing Schools -->
            <div class="osb-report-section">
                <h3><?php _e('Registered Schools by Region', 'spelling-bee-pro'); ?></h3>
                <div class="osb-schools-table">
                    <table class="wp-list-table widefat fixed striped">
                        <thead>
                            <tr>
                                <th><?php _e('School Name', 'spelling-bee-pro'); ?></th>
                                <th><?php _e('Location', 'spelling-bee-pro'); ?></th>
                                <th><?php _e('Students', 'spelling-bee-pro'); ?></th>
                                <th><?php _e('Status', 'spelling-bee-pro'); ?></th>
                                <th><?php _e('Registration Date', 'spelling-bee-pro'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($report_data['registrations'] as $registration): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo esc_html($registration->school_name); ?></strong>
                                    </td>
                                    <td><?php echo esc_html($registration->city . ', ' . $registration->state); ?></td>
                                    <td>
                                        <?php
                                        $student_count = $wpdb->get_var($wpdb->prepare(
                                            "SELECT COUNT(*) FROM {$wpdb->prefix}osb_students WHERE school_id = %d",
                                            $registration->school_id
                                        ));
                                        echo intval($student_count);
                                        ?>
                                    </td>
                                    <td>
                                        <span class="osb-status osb-status-<?php echo esc_attr($registration->status); ?>">
                                            <?php echo esc_html(ucfirst($registration->status)); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($registration->created_at)); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Donation Summary -->
            <?php if (!empty($report_data['donations'])): ?>
                <div class="osb-report-section">
                    <h3><?php _e('Donation Summary', 'spelling-bee-pro'); ?></h3>
                    <div class="osb-donation-stats">
                        <div class="osb-donation-summary">
                            <div class="osb-summary-item">
                                <label><?php _e('Total Amount:', 'spelling-bee-pro'); ?></label>
                                <span class="osb-amount">$<?php echo number_format($report_data['donations']['total_amount'], 2); ?></span>
                            </div>
                            <div class="osb-summary-item">
                                <label><?php _e('Number of Donors:', 'spelling-bee-pro'); ?></label>
                                <span><?php echo number_format($report_data['donations']['donor_count']); ?></span>
                            </div>
                            <div class="osb-summary-item">
                                <label><?php _e('Average Donation:', 'spelling-bee-pro'); ?></label>
                                <span>$<?php echo number_format($report_data['donations']['average_amount'], 2); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Export Actions -->
            <div class="osb-report-actions">
                <div class="osb-action-buttons">
                    <button type="button" class="button button-large" id="export-csv">
                        <span class="dashicons dashicons-media-spreadsheet"></span>
                        <?php _e('Export as CSV', 'spelling-bee-pro'); ?>
                    </button>
                    <button type="button" class="button button-large" id="export-excel">
                        <span class="dashicons dashicons-media-spreadsheet"></span>
                        <?php _e('Export as Excel', 'spelling-bee-pro'); ?>
                    </button>
                    <button type="button" class="button button-primary button-large" id="print-report">
                        <span class="dashicons dashicons-printer"></span>
                        <?php _e('Print Report', 'spelling-bee-pro'); ?>
                    </button>
                </div>
            </div>
        </div>

    <?php else: ?>
        <div class="osb-no-data">
            <div class="osb-empty-state">
                <div class="osb-empty-icon">📈</div>
                <h3><?php _e('No Data Available', 'spelling-bee-pro'); ?></h3>
                <p><?php _e('There is no data available for the selected event yet.', 'spelling-bee-pro'); ?></p>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
/* Reports Page Styles */
.osb-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 15px;
}

.osb-header h1 {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.8rem;
    margin: 0;
}

.osb-header-actions {
    display: flex;
    gap: 10px;
}

.osb-filters {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    margin-bottom: 30px;
}

.osb-filter-bar {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.osb-filter-bar label {
    font-weight: 600;
    color: #333;
}

.osb-filter-select,
.osb-filter-input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.osb-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.osb-stat-card {
    background: #fff;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    display: flex;
    align-items: center;
    gap: 20px;
    border-left: 4px solid #ddd;
    transition: all 0.3s ease;
}

.osb-stat-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.osb-primary { border-left-color: #0073aa; }
.osb-success { border-left-color: #28a745; }
.osb-info { border-left-color: #17a2b8; }
.osb-warning { border-left-color: #ffc107; }

.osb-stat-icon {
    font-size: 2.5rem;
    opacity: 0.8;
}

.osb-stat-content {
    flex: 1;
}

.osb-stat-number {
    font-size: 2rem;
    font-weight: bold;
    color: #333;
    line-height: 1;
    margin-bottom: 5px;
}

.osb-stat-label {
    font-size: 0.9rem;
    color: #666;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.osb-report-section {
    background: #fff;
    border-radius: 12px;
    padding: 30px;
    margin-bottom: 30px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.osb-report-section h3 {
    margin: 0 0 20px 0;
    font-size: 1.4rem;
    color: #333;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 10px;
}

.osb-chart-container {
    position: relative;
    height: 300px;
    margin: 20px 0;
}

.osb-schools-table {
    overflow-x: auto;
}

.osb-status {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
}

.osb-status-approved { background: #d4edda; color: #155724; }
.osb-status-pending { background: #fff3cd; color: #856404; }
.osb-status-rejected { background: #f8d7da; color: #721c24; }

.osb-donation-stats {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
}

.osb-donation-summary {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.osb-summary-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.osb-summary-item label {
    font-weight: 600;
    color: #666;
    font-size: 0.9rem;
}

.osb-amount {
    font-size: 1.5rem;
    font-weight: bold;
    color: #28a745;
}

.osb-report-actions {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 30px;
    text-align: center;
    margin-top: 40px;
}

.osb-action-buttons {
    display: flex;
    justify-content: center;
    gap: 15px;
    flex-wrap: wrap;
}

.osb-action-buttons .button {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
}

.osb-no-selection,
.osb-no-data {
    margin: 40px 0;
}

.osb-empty-state {
    text-align: center;
    padding: 80px 20px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.osb-empty-icon {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.7;
}

.osb-empty-state h3 {
    color: #333;
    margin-bottom: 10px;
    font-size: 1.5rem;
}

.osb-empty-state p {
    color: #666;
    font-size: 1.1rem;
    line-height: 1.6;
}

/* Print Styles */
@media print {
    .osb-header-actions,
    .osb-filters,
    .osb-report-actions {
        display: none !important;
    }

    .osb-report-section {
        break-inside: avoid;
        box-shadow: none;
        border: 1px solid #ddd;
    }
}

/* Responsive */
@media (max-width: 768px) {
    .osb-header {
        flex-direction: column;
        align-items: stretch;
    }

    .osb-filter-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .osb-stats-grid {
        grid-template-columns: 1fr;
    }

    .osb-stat-card {
        flex-direction: column;
        text-align: center;
    }

    .osb-donation-summary {
        grid-template-columns: 1fr;
    }

    .osb-action-buttons {
        flex-direction: column;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Event filter change
    $('#event-filter').on('change', function() {
        const eventId = $(this).val();
        if (eventId) {
            const url = new URL(window.location);
            url.searchParams.set('event_id', eventId);
            window.location.href = url.toString();
        }
    });

    // Print report
    $('#print-report').on('click', function() {
        window.print();
    });

    // Export functions
    $('#export-csv, #export-excel').on('click', function() {
        const format = $(this).attr('id').replace('export-', '');
        const eventId = $('#event-filter').val();

        if (!eventId) {
            alert('<?php _e('Please select an event first.', 'spelling-bee-pro'); ?>');
            return;
        }

        const params = new URLSearchParams({
            action: 'osb_admin_action',
            sub_action: 'export_report',
            event_id: eventId,
            format: format,
            nonce: osb_ajax.nonce
        });

        window.location.href = ajaxurl + '?' + params.toString();
    });

    // Generate PDF
    $('#generate-pdf').on('click', function() {
        const eventId = $('#event-filter').val();

        if (!eventId) {
            alert('<?php _e('Please select an event first.', 'spelling-bee-pro'); ?>');
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).text('<?php _e('Generating...', 'spelling-bee-pro'); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'osb_admin_action',
                sub_action: 'generate_pdf_report',
                event_id: eventId,
                nonce: osb_ajax.nonce
            },
            success: function(response) {
                if (response.success && response.data.download_url) {
                    window.location.href = response.data.download_url;
                } else {
                    alert('<?php _e('Error generating PDF:', 'spelling-bee-pro'); ?> ' + (response.data || 'Unknown error'));
                }
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-pdf"></span> <?php _e('Generate PDF', 'spelling-bee-pro'); ?>');
            },
            error: function() {
                alert('<?php _e('Network error. Please try again.', 'spelling-bee-pro'); ?>');
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-pdf"></span> <?php _e('Generate PDF', 'spelling-bee-pro'); ?>');
            }
        });
    });

    // Initialize charts if data is available
    <?php if (!empty($report_data)): ?>
    // Registration status chart
    const registrationData = {
        labels: ['Approved', 'Pending', 'Rejected'],
        datasets: [{
            data: [
                <?php
                $approved = $pending = $rejected = 0;
                foreach ($report_data['registrations'] as $reg) {
                    switch ($reg->status) {
                        case 'approved': $approved++; break;
                        case 'pending': $pending++; break;
                        case 'rejected': $rejected++; break;
                    }
                }
                echo "$approved, $pending, $rejected";
                ?>
            ],
            backgroundColor: ['#28a745', '#ffc107', '#dc3545']
        }]
    };

    if (typeof Chart !== 'undefined') {
        new Chart($('#registration-chart')[0], {
            type: 'doughnut',
            data: registrationData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    }
    <?php endif; ?>
});
</script>