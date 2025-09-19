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

$conflicts = [];
if ($event_id) {
    $conflicts = $wpdb->get_results($wpdb->prepare("
        SELECT c.*,
               s1.name as school1_name, s2.name as school2_name,
               st1.first_name as student1_first, st1.last_name as student1_last, st1.student_id as student1_id,
               st2.first_name as student2_first, st2.last_name as student2_last, st2.student_id as student2_id,
               u1.display_name as reporter_name, u2.display_name as resolver_name
        FROM {$wpdb->prefix}osb_conflicts c
        LEFT JOIN {$wpdb->prefix}osb_schools s1 ON c.school1_id = s1.id
        LEFT JOIN {$wpdb->prefix}osb_schools s2 ON c.school2_id = s2.id
        LEFT JOIN {$wpdb->prefix}osb_students st1 ON c.student1_id = st1.id
        LEFT JOIN {$wpdb->prefix}osb_students st2 ON c.student2_id = st2.id
        LEFT JOIN {$wpdb->prefix}users u1 ON c.reported_by = u1.ID
        LEFT JOIN {$wpdb->prefix}users u2 ON c.resolved_by = u2.ID
        WHERE c.event_id = %d
        ORDER BY c.status ASC, c.severity DESC, c.reported_at DESC
    ", $event_id));
}

$conflict_stats = [];
if ($event_id) {
    $conflict_stats = $wpdb->get_row($wpdb->prepare("
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'investigating' THEN 1 ELSE 0 END) as investigating,
            SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) as resolved,
            SUM(CASE WHEN severity = 'high' THEN 1 ELSE 0 END) as high_severity,
            SUM(CASE WHEN severity = 'medium' THEN 1 ELSE 0 END) as medium_severity,
            SUM(CASE WHEN severity = 'low' THEN 1 ELSE 0 END) as low_severity
        FROM {$wpdb->prefix}osb_conflicts
        WHERE event_id = %d
    ", $event_id), ARRAY_A);
}

$conflict_types = [
    'duplicate_registration' => 'Duplicate Registration',
    'age_discrepancy' => 'Age Discrepancy',
    'school_mismatch' => 'School Mismatch',
    'document_conflict' => 'Document Conflict',
    'identity_verification' => 'Identity Verification',
    'eligibility_dispute' => 'Eligibility Dispute',
    'technical_issue' => 'Technical Issue',
    'other' => 'Other'
];

$severity_levels = [
    'low' => ['color' => '#28a745', 'label' => 'Low'],
    'medium' => ['color' => '#ffc107', 'label' => 'Medium'],
    'high' => ['color' => '#dc3545', 'label' => 'High']
];
?>

<div class="wrap osb-admin-page">
    <div class="osb-header">
        <h1 class="wp-heading-inline">
            <span class="dashicons dashicons-warning"></span>
            Conflicts Resolution
        </h1>

        <div class="osb-header-actions">
            <button type="button" class="button button-primary" id="add-conflict-btn">
                <span class="dashicons dashicons-plus"></span>
                Report Conflict
            </button>
            <button type="button" class="button" id="export-conflicts">
                <span class="dashicons dashicons-download"></span>
                Export
            </button>
            <button type="button" class="button" id="conflict-summary">
                <span class="dashicons dashicons-chart-pie"></span>
                Summary Report
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
                    <span class="dashicons dashicons-warning"></span>
                </div>
                <div class="osb-stat-content">
                    <div class="osb-stat-number"><?php echo number_format($conflict_stats['total'] ?? 0); ?></div>
                    <div class="osb-stat-label">Total Conflicts</div>
                </div>
            </div>

            <div class="osb-stat-card pending">
                <div class="osb-stat-icon">
                    <span class="dashicons dashicons-clock"></span>
                </div>
                <div class="osb-stat-content">
                    <div class="osb-stat-number"><?php echo number_format($conflict_stats['pending'] ?? 0); ?></div>
                    <div class="osb-stat-label">Pending</div>
                </div>
            </div>

            <div class="osb-stat-card investigating">
                <div class="osb-stat-icon">
                    <span class="dashicons dashicons-search"></span>
                </div>
                <div class="osb-stat-content">
                    <div class="osb-stat-number"><?php echo number_format($conflict_stats['investigating'] ?? 0); ?></div>
                    <div class="osb-stat-label">Investigating</div>
                </div>
            </div>

            <div class="osb-stat-card resolved">
                <div class="osb-stat-icon">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <div class="osb-stat-content">
                    <div class="osb-stat-number"><?php echo number_format($conflict_stats['resolved'] ?? 0); ?></div>
                    <div class="osb-stat-label">Resolved</div>
                </div>
            </div>
        </div>

        <!-- Severity Overview -->
        <div class="severity-overview">
            <h3>Conflicts by Severity</h3>
            <div class="severity-grid">
                <?php foreach ($severity_levels as $level => $details): ?>
                    <div class="severity-card severity-<?php echo $level; ?>">
                        <div class="severity-header" style="background-color: <?php echo $details['color']; ?>">
                            <h4><?php echo $details['label']; ?> Severity</h4>
                            <div class="severity-count"><?php echo number_format($conflict_stats[$level . '_severity'] ?? 0); ?> conflicts</div>
                        </div>
                        <div class="severity-percentage">
                            <?php
                            $total = $conflict_stats['total'] ?? 0;
                            $count = $conflict_stats[$level . '_severity'] ?? 0;
                            $percentage = $total > 0 ? round(($count / $total) * 100, 1) : 0;
                            echo $percentage . '%';
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Filters -->
        <div class="osb-filters">
            <div class="osb-filter-group">
                <label>Status:</label>
                <select id="status-filter" onchange="filterConflicts()">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="investigating">Investigating</option>
                    <option value="resolved">Resolved</option>
                </select>
            </div>

            <div class="osb-filter-group">
                <label>Severity:</label>
                <select id="severity-filter" onchange="filterConflicts()">
                    <option value="">All Severities</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>

            <div class="osb-filter-group">
                <label>Conflict Type:</label>
                <select id="type-filter" onchange="filterConflicts()">
                    <option value="">All Types</option>
                    <?php foreach ($conflict_types as $type => $label): ?>
                        <option value="<?php echo $type; ?>"><?php echo $label; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="osb-filter-group">
                <input type="text" id="search-filter" placeholder="Search conflicts..." onkeyup="filterConflicts()">
            </div>

            <button type="button" class="button" onclick="clearFilters()">Clear Filters</button>
        </div>

        <!-- Conflicts Table -->
        <?php if (!empty($conflicts)): ?>
            <div class="osb-table-container">
                <table class="wp-list-table widefat fixed striped" id="conflicts-table">
                    <thead>
                        <tr>
                            <th scope="col" class="check-column">
                                <input type="checkbox" id="select-all-conflicts">
                            </th>
                            <th scope="col" class="sortable" data-sort="conflict_type">
                                Conflict Details
                                <span class="sorting-indicator"></span>
                            </th>
                            <th scope="col">Entities Involved</th>
                            <th scope="col" class="sortable" data-sort="severity">
                                Severity
                                <span class="sorting-indicator"></span>
                            </th>
                            <th scope="col" class="sortable" data-sort="status">
                                Status
                                <span class="sorting-indicator"></span>
                            </th>
                            <th scope="col" class="sortable" data-sort="reported_at">
                                Reported
                                <span class="sorting-indicator"></span>
                            </th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($conflicts as $conflict): ?>
                            <tr class="conflict-row"
                                data-status="<?php echo esc_attr($conflict->status); ?>"
                                data-severity="<?php echo esc_attr($conflict->severity); ?>"
                                data-type="<?php echo esc_attr($conflict->conflict_type); ?>">
                                <td class="check-column">
                                    <input type="checkbox" name="conflict_ids[]" value="<?php echo $conflict->id; ?>" class="conflict-checkbox">
                                </td>

                                <td class="conflict-details">
                                    <div class="conflict-type">
                                        <strong><?php echo esc_html($conflict_types[$conflict->conflict_type] ?? ucfirst($conflict->conflict_type)); ?></strong>
                                        <span class="conflict-id">#<?php echo $conflict->id; ?></span>
                                    </div>
                                    <div class="conflict-description"><?php echo esc_html($conflict->description); ?></div>
                                    <?php if ($conflict->resolution_notes): ?>
                                        <div class="resolution-notes">
                                            <strong>Resolution:</strong> <?php echo esc_html($conflict->resolution_notes); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td class="entities-involved">
                                    <?php if ($conflict->school1_id || $conflict->school2_id): ?>
                                        <div class="schools-involved">
                                            <?php if ($conflict->school1_name): ?>
                                                <div class="school-entity"><?php echo esc_html($conflict->school1_name); ?></div>
                                            <?php endif; ?>
                                            <?php if ($conflict->school2_name): ?>
                                                <div class="school-entity"><?php echo esc_html($conflict->school2_name); ?></div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($conflict->student1_id || $conflict->student2_id): ?>
                                        <div class="students-involved">
                                            <?php if ($conflict->student1_first): ?>
                                                <div class="student-entity">
                                                    <?php echo esc_html($conflict->student1_first . ' ' . $conflict->student1_last); ?>
                                                    <span class="student-id">(ID: <?php echo esc_html($conflict->student1_id); ?>)</span>
                                                </div>
                                            <?php endif; ?>
                                            <?php if ($conflict->student2_first): ?>
                                                <div class="student-entity">
                                                    <?php echo esc_html($conflict->student2_first . ' ' . $conflict->student2_last); ?>
                                                    <span class="student-id">(ID: <?php echo esc_html($conflict->student2_id); ?>)</span>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td class="severity-column">
                                    <span class="severity-badge severity-<?php echo esc_attr($conflict->severity); ?>">
                                        <?php echo esc_html(ucfirst($conflict->severity)); ?>
                                    </span>
                                </td>

                                <td class="status-column">
                                    <span class="status-badge status-<?php echo esc_attr($conflict->status); ?>">
                                        <?php echo esc_html(ucfirst(str_replace('_', ' ', $conflict->status))); ?>
                                    </span>
                                    <?php if ($conflict->status === 'resolved' && $conflict->resolved_at): ?>
                                        <div class="resolved-date">
                                            Resolved: <?php echo date('M j, Y', strtotime($conflict->resolved_at)); ?>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td class="reported-info">
                                    <div class="reported-date"><?php echo date('M j, Y', strtotime($conflict->reported_at)); ?></div>
                                    <div class="reported-time"><?php echo date('g:i A', strtotime($conflict->reported_at)); ?></div>
                                    <?php if ($conflict->reporter_name): ?>
                                        <div class="reported-by">by <?php echo esc_html($conflict->reporter_name); ?></div>
                                    <?php endif; ?>
                                </td>

                                <td class="actions-column">
                                    <div class="action-buttons">
                                        <button type="button" class="button button-small view-conflict"
                                                data-id="<?php echo $conflict->id; ?>">
                                            <span class="dashicons dashicons-visibility"></span>
                                            View
                                        </button>

                                        <?php if ($conflict->status === 'pending'): ?>
                                            <button type="button" class="button button-primary button-small start-investigation"
                                                    data-id="<?php echo $conflict->id; ?>">
                                                <span class="dashicons dashicons-search"></span>
                                                Investigate
                                            </button>
                                        <?php elseif ($conflict->status === 'investigating'): ?>
                                            <button type="button" class="button button-primary button-small resolve-conflict"
                                                    data-id="<?php echo $conflict->id; ?>">
                                                <span class="dashicons dashicons-yes"></span>
                                                Resolve
                                            </button>
                                        <?php endif; ?>

                                        <button type="button" class="button button-small edit-conflict"
                                                data-id="<?php echo $conflict->id; ?>">
                                            <span class="dashicons dashicons-edit"></span>
                                            Edit
                                        </button>

                                        <button type="button" class="button button-small add-note"
                                                data-id="<?php echo $conflict->id; ?>">
                                            <span class="dashicons dashicons-sticky"></span>
                                            Note
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
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <h3>No Conflicts Found</h3>
                <p>Great! No conflicts have been reported for this event.</p>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="osb-empty-state">
            <div class="osb-empty-icon">
                <span class="dashicons dashicons-admin-settings"></span>
            </div>
            <h3>Select an Event</h3>
            <p>Please select an event to view and manage conflicts.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Add/Edit Conflict Modal -->
<div id="conflict-modal" class="osb-modal" style="display: none;">
    <div class="osb-modal-content conflict-modal-content">
        <div class="osb-modal-header">
            <h3 id="conflict-modal-title">Report New Conflict</h3>
            <button type="button" class="osb-modal-close">&times;</button>
        </div>
        <div class="osb-modal-body">
            <form id="conflict-form">
                <input type="hidden" id="conflict-id" name="conflict_id">
                <input type="hidden" id="conflict-event-id" name="event_id" value="<?php echo $event_id; ?>">

                <div class="form-row">
                    <div class="form-group">
                        <label for="conflict_type">Conflict Type *</label>
                        <select id="conflict_type" name="conflict_type" required>
                            <option value="">Select Type</option>
                            <?php foreach ($conflict_types as $type => $label): ?>
                                <option value="<?php echo $type; ?>"><?php echo $label; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="severity">Severity *</label>
                        <select id="severity" name="severity" required>
                            <option value="">Select Severity</option>
                            <option value="low">Low - Minor issue, no immediate impact</option>
                            <option value="medium">Medium - Moderate issue, requires attention</option>
                            <option value="high">High - Critical issue, immediate attention required</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="description">Description *</label>
                    <textarea id="description" name="description" rows="4" placeholder="Describe the conflict in detail..." required></textarea>
                </div>

                <div class="entities-section">
                    <h4>Entities Involved</h4>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="school1_id">School 1</label>
                            <select id="school1_id" name="school1_id">
                                <option value="">Select School</option>
                                <?php
                                $schools = $wpdb->get_results($wpdb->prepare("
                                    SELECT id, name FROM {$wpdb->prefix}osb_schools
                                    WHERE event_id = %d ORDER BY name
                                ", $event_id));
                                foreach ($schools as $school):
                                ?>
                                    <option value="<?php echo $school->id; ?>"><?php echo esc_html($school->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="school2_id">School 2</label>
                            <select id="school2_id" name="school2_id">
                                <option value="">Select School</option>
                                <?php foreach ($schools as $school): ?>
                                    <option value="<?php echo $school->id; ?>"><?php echo esc_html($school->name); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="student1_id">Student 1</label>
                            <select id="student1_id" name="student1_id">
                                <option value="">Select Student</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="student2_id">Student 2</label>
                            <select id="student2_id" name="student2_id">
                                <option value="">Select Student</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="resolution_notes">Resolution Notes</label>
                    <textarea id="resolution_notes" name="resolution_notes" rows="3" placeholder="Add resolution notes or action taken..."></textarea>
                </div>

                <div class="form-actions">
                    <button type="button" class="button" onclick="closeModal('conflict-modal')">Cancel</button>
                    <button type="submit" class="button button-primary">Save Conflict</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Conflict Detail Modal -->
<div id="conflict-detail-modal" class="osb-modal" style="display: none;">
    <div class="osb-modal-content">
        <div class="osb-modal-header">
            <h3>Conflict Details</h3>
            <button type="button" class="osb-modal-close">&times;</button>
        </div>
        <div class="osb-modal-body">
            <div id="conflict-detail-content"></div>
        </div>
    </div>
</div>

<!-- Resolution Modal -->
<div id="resolution-modal" class="osb-modal" style="display: none;">
    <div class="osb-modal-content">
        <div class="osb-modal-header">
            <h3>Resolve Conflict</h3>
            <button type="button" class="osb-modal-close">&times;</button>
        </div>
        <div class="osb-modal-body">
            <form id="resolution-form">
                <input type="hidden" id="resolve-conflict-id">
                <div class="form-group">
                    <label for="resolution_notes_modal">Resolution Notes *</label>
                    <textarea id="resolution_notes_modal" rows="5" placeholder="Describe how this conflict was resolved..." required></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="button" onclick="closeModal('resolution-modal')">Cancel</button>
                    <button type="submit" class="button button-primary">Mark as Resolved</button>
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
    border-left: 4px solid #6c757d;
}

.osb-stat-card.pending {
    border-left: 4px solid #ffc107;
}

.osb-stat-card.investigating {
    border-left: 4px solid #17a2b8;
}

.osb-stat-card.resolved {
    border-left: 4px solid #28a745;
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

.severity-overview {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
    margin-bottom: 30px;
}

.severity-overview h3 {
    margin-top: 0;
    margin-bottom: 15px;
}

.severity-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 15px;
}

.severity-card {
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
}

.severity-header {
    padding: 15px;
    color: #fff;
    font-weight: bold;
}

.severity-header h4 {
    margin: 0 0 5px 0;
    font-size: 16px;
}

.severity-count {
    font-size: 12px;
    opacity: 0.9;
}

.severity-percentage {
    padding: 15px;
    background: #f9f9f9;
    text-align: center;
    font-weight: 600;
    font-size: 18px;
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

.conflict-details .conflict-type {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 5px;
}

.conflict-id {
    font-size: 11px;
    color: #666;
    background: #f0f0f0;
    padding: 2px 6px;
    border-radius: 3px;
}

.conflict-description {
    font-size: 13px;
    color: #333;
    line-height: 1.4;
    margin-bottom: 5px;
}

.resolution-notes {
    font-size: 12px;
    color: #666;
    background: #f8f9fa;
    padding: 8px;
    border-left: 3px solid #28a745;
    margin-top: 8px;
}

.entities-involved .school-entity,
.entities-involved .student-entity {
    padding: 4px 8px;
    background: #f8f9fa;
    border-radius: 3px;
    margin-bottom: 3px;
    font-size: 12px;
}

.student-id {
    color: #666;
    font-size: 11px;
}

.severity-badge {
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    color: #fff;
}

.severity-low {
    background: #28a745;
}

.severity-medium {
    background: #ffc107;
    color: #333;
}

.severity-high {
    background: #dc3545;
}

.status-badge {
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
}

.status-pending {
    background: #fff3cd;
    color: #856404;
}

.status-investigating {
    background: #d1ecf1;
    color: #0c5460;
}

.status-resolved {
    background: #d4edda;
    color: #155724;
}

.resolved-date {
    font-size: 11px;
    color: #666;
    margin-top: 3px;
}

.reported-info .reported-date {
    font-weight: 600;
}

.reported-info .reported-time {
    font-size: 11px;
    color: #666;
}

.reported-info .reported-by {
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
    color: #28a745;
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

.conflict-modal-content {
    min-width: 700px;
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

.entities-section {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
    margin: 20px 0;
    background: #f9f9f9;
}

.entities-section h4 {
    margin-top: 0;
    margin-bottom: 15px;
    color: #333;
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

    .conflict-modal-content {
        min-width: auto;
        width: 95vw;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Add/Edit conflict modal
    $('#add-conflict-btn').on('click', function() {
        resetConflictForm();
        $('#conflict-modal-title').text('Report New Conflict');
        $('#conflict-modal').show();
    });

    // Edit conflict
    $(document).on('click', '.edit-conflict', function() {
        const conflictId = $(this).data('id');
        loadConflictData(conflictId);
    });

    // View conflict details
    $(document).on('click', '.view-conflict', function() {
        const conflictId = $(this).data('id');
        loadConflictDetails(conflictId);
    });

    // Start investigation
    $(document).on('click', '.start-investigation', function() {
        const conflictId = $(this).data('id');

        if (confirm('Start investigating this conflict?')) {
            updateConflictStatus(conflictId, 'investigating');
        }
    });

    // Resolve conflict
    $(document).on('click', '.resolve-conflict', function() {
        const conflictId = $(this).data('id');

        $('#resolve-conflict-id').val(conflictId);
        $('#resolution-modal').show();
    });

    // Submit conflict form
    $('#conflict-form').on('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        formData.append('action', 'save_conflict');
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

    // Submit resolution form
    $('#resolution-form').on('submit', function(e) {
        e.preventDefault();

        const conflictId = $('#resolve-conflict-id').val();
        const notes = $('#resolution_notes_modal').val();

        $.post(ajaxurl, {
            action: 'resolve_conflict',
            conflict_id: conflictId,
            resolution_notes: notes,
            _wpnonce: osb_admin.nonce
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Error: ' + response.data.message);
            }
        });
    });

    // School selection changes - update student lists
    $('#school1_id, #school2_id').on('change', function() {
        const schoolId = $(this).val();
        const studentSelect = $(this).attr('id') === 'school1_id' ? '#student1_id' : '#student2_id';

        if (schoolId) {
            loadStudentsBySchool(schoolId, studentSelect);
        } else {
            $(studentSelect).html('<option value="">Select Student</option>');
        }
    });

    // Export conflicts
    $('#export-conflicts').on('click', function() {
        const eventId = $('#event-select').val();
        if (!eventId) {
            alert('Please select an event first.');
            return;
        }

        window.location.href = `${ajaxurl}?action=export_conflicts&event_id=${eventId}&_wpnonce=${osb_admin.nonce}`;
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

function filterConflicts() {
    const statusFilter = document.getElementById('status-filter').value.toLowerCase();
    const severityFilter = document.getElementById('severity-filter').value.toLowerCase();
    const typeFilter = document.getElementById('type-filter').value.toLowerCase();
    const searchFilter = document.getElementById('search-filter').value.toLowerCase();

    const rows = document.querySelectorAll('.conflict-row');

    rows.forEach(row => {
        const status = row.dataset.status.toLowerCase();
        const severity = row.dataset.severity.toLowerCase();
        const type = row.dataset.type.toLowerCase();
        const text = row.textContent.toLowerCase();

        const statusMatch = !statusFilter || status === statusFilter;
        const severityMatch = !severityFilter || severity === severityFilter;
        const typeMatch = !typeFilter || type === typeFilter;
        const searchMatch = !searchFilter || text.includes(searchFilter);

        row.style.display = (statusMatch && severityMatch && typeMatch && searchMatch) ? '' : 'none';
    });
}

function clearFilters() {
    document.getElementById('status-filter').value = '';
    document.getElementById('severity-filter').value = '';
    document.getElementById('type-filter').value = '';
    document.getElementById('search-filter').value = '';
    filterConflicts();
}

function resetConflictForm() {
    document.getElementById('conflict-form').reset();
    document.getElementById('conflict-id').value = '';
    document.getElementById('student1_id').innerHTML = '<option value="">Select Student</option>';
    document.getElementById('student2_id').innerHTML = '<option value="">Select Student</option>';
}

function loadConflictData(conflictId) {
    jQuery.post(ajaxurl, {
        action: 'get_conflict_data',
        conflict_id: conflictId,
        _wpnonce: osb_admin.nonce
    }, function(response) {
        if (response.success) {
            const conflict = response.data;

            document.getElementById('conflict-id').value = conflict.id;
            document.getElementById('conflict_type').value = conflict.conflict_type;
            document.getElementById('severity').value = conflict.severity;
            document.getElementById('description').value = conflict.description;
            document.getElementById('school1_id').value = conflict.school1_id || '';
            document.getElementById('school2_id').value = conflict.school2_id || '';
            document.getElementById('resolution_notes').value = conflict.resolution_notes || '';

            // Load students for selected schools
            if (conflict.school1_id) {
                loadStudentsBySchool(conflict.school1_id, '#student1_id', conflict.student1_id);
            }
            if (conflict.school2_id) {
                loadStudentsBySchool(conflict.school2_id, '#student2_id', conflict.student2_id);
            }

            document.getElementById('conflict-modal-title').textContent = 'Edit Conflict';
            document.getElementById('conflict-modal').style.display = 'block';
        } else {
            alert('Error loading conflict data: ' + response.data.message);
        }
    });
}

function loadConflictDetails(conflictId) {
    jQuery.post(ajaxurl, {
        action: 'get_conflict_details',
        conflict_id: conflictId,
        _wpnonce: osb_admin.nonce
    }, function(response) {
        if (response.success) {
            document.getElementById('conflict-detail-content').innerHTML = response.data.html;
            document.getElementById('conflict-detail-modal').style.display = 'block';
        } else {
            alert('Error loading conflict details: ' + response.data.message);
        }
    });
}

function loadStudentsBySchool(schoolId, selectElement, selectedValue = '') {
    jQuery.post(ajaxurl, {
        action: 'get_students_by_school',
        school_id: schoolId,
        _wpnonce: osb_admin.nonce
    }, function(response) {
        if (response.success) {
            let options = '<option value="">Select Student</option>';
            response.data.students.forEach(student => {
                const selected = student.id == selectedValue ? 'selected' : '';
                options += `<option value="${student.id}" ${selected}>${student.first_name} ${student.last_name} (ID: ${student.student_id})</option>`;
            });
            jQuery(selectElement).html(options);
        }
    });
}

function updateConflictStatus(conflictId, status) {
    jQuery.post(ajaxurl, {
        action: 'update_conflict_status',
        conflict_id: conflictId,
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

function closeModal(modalId) {
    document.getElementById(modalId).style.display = 'none';
    if (modalId === 'resolution-modal') {
        document.getElementById('resolution_notes_modal').value = '';
    }
}
</script>