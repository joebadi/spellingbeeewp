<?php
/**
 * Schools View Template
 *
 * @var object $school
 * @var array $students
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$admin_menu = OSB_Admin_Menu::getInstance();
$db = OSB_Database::getInstance();

// Get additional data
$registrations = $db->getRegistrationsBySchool($school->id);
$user = $school->wp_user_id ? get_user_by('ID', $school->wp_user_id) : null;

// Status badge classes
$status_classes = [
    'pending' => 'osb-status-pending',
    'documents_submitted' => 'osb-status-submitted',
    'under_review' => 'osb-status-review',
    'approved' => 'osb-status-approved',
    'rejected' => 'osb-status-rejected',
    'confirmed' => 'osb-status-confirmed'
];

$status_class = isset($status_classes[$school->status]) ? $status_classes[$school->status] : 'osb-status-default';
?>

<div class="wrap">
    <div class="osb-view-header">
        <div class="osb-view-title">
            <h1><?php echo esc_html($school->school_name); ?></h1>
            <span class="osb-status-badge <?php echo esc_attr($status_class); ?>">
                <?php echo esc_html(ucwords(str_replace('_', ' ', $school->status))); ?>
            </span>
        </div>

        <div class="osb-view-actions">
            <a href="<?php echo $admin_menu->getAdminUrl('schools', array('action' => 'edit', 'school_id' => $school->id)); ?>"
               class="button button-primary">
                <?php _e('Edit School', 'spelling-bee-pro'); ?>
            </a>
            <a href="<?php echo $admin_menu->getAdminUrl('schools'); ?>"
               class="button">
                <?php _e('Back to Schools', 'spelling-bee-pro'); ?>
            </a>
        </div>
    </div>

    <div class="osb-view-content">
        <!-- School Information -->
        <div class="osb-info-card">
            <h2><?php _e('School Information', 'spelling-bee-pro'); ?></h2>

            <div class="osb-info-grid">
                <div class="osb-info-item">
                    <label><?php _e('School Name', 'spelling-bee-pro'); ?></label>
                    <div class="osb-info-value"><?php echo esc_html($school->school_name); ?></div>
                </div>

                <div class="osb-info-item">
                    <label><?php _e('School Type', 'spelling-bee-pro'); ?></label>
                    <div class="osb-info-value"><?php echo esc_html(ucfirst($school->school_type)); ?></div>
                </div>

                <div class="osb-info-item">
                    <label><?php _e('State', 'spelling-bee-pro'); ?></label>
                    <div class="osb-info-value"><?php echo esc_html($school->state); ?></div>
                </div>

                <div class="osb-info-item">
                    <label><?php _e('Status', 'spelling-bee-pro'); ?></label>
                    <div class="osb-info-value">
                        <span class="osb-status-badge <?php echo esc_attr($status_class); ?>">
                            <?php echo esc_html(ucwords(str_replace('_', ' ', $school->status))); ?>
                        </span>
                    </div>
                </div>

                <div class="osb-info-item osb-info-full">
                    <label><?php _e('Address', 'spelling-bee-pro'); ?></label>
                    <div class="osb-info-value"><?php echo esc_html($school->address); ?></div>
                </div>
            </div>
        </div>

        <!-- Contact Information -->
        <div class="osb-info-card">
            <h2><?php _e('Contact Information', 'spelling-bee-pro'); ?></h2>

            <div class="osb-info-grid">
                <div class="osb-info-item">
                    <label><?php _e('Contact Person', 'spelling-bee-pro'); ?></label>
                    <div class="osb-info-value"><?php echo esc_html($school->contact_person); ?></div>
                </div>

                <div class="osb-info-item">
                    <label><?php _e('Email', 'spelling-bee-pro'); ?></label>
                    <div class="osb-info-value">
                        <a href="mailto:<?php echo esc_attr($school->contact_email); ?>">
                            <?php echo esc_html($school->contact_email); ?>
                        </a>
                    </div>
                </div>

                <?php if ($school->phone): ?>
                <div class="osb-info-item">
                    <label><?php _e('Phone', 'spelling-bee-pro'); ?></label>
                    <div class="osb-info-value">
                        <a href="tel:<?php echo esc_attr($school->phone); ?>">
                            <?php echo esc_html($school->phone); ?>
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ($user): ?>
                <div class="osb-info-item">
                    <label><?php _e('WordPress User', 'spelling-bee-pro'); ?></label>
                    <div class="osb-info-value">
                        <a href="<?php echo get_edit_user_link($user->ID); ?>" target="_blank">
                            <?php echo esc_html($user->display_name); ?> (<?php echo esc_html($user->user_email); ?>)
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Students -->
        <div class="osb-info-card">
            <div class="osb-card-header">
                <h2><?php _e('Students', 'spelling-bee-pro'); ?></h2>
                <span class="osb-count-badge"><?php echo count($students); ?></span>
            </div>

            <?php if (empty($students)): ?>
                <div class="osb-empty-state">
                    <p><?php _e('No students registered for this school yet.', 'spelling-bee-pro'); ?></p>
                </div>
            <?php else: ?>
                <div class="osb-table-container">
                    <table class="osb-table">
                        <thead>
                            <tr>
                                <th><?php _e('Name', 'spelling-bee-pro'); ?></th>
                                <th><?php _e('Grade', 'spelling-bee-pro'); ?></th>
                                <th><?php _e('Age', 'spelling-bee-pro'); ?></th>
                                <th><?php _e('Email', 'spelling-bee-pro'); ?></th>
                                <th><?php _e('Actions', 'spelling-bee-pro'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($students as $student): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($student->first_name . ' ' . $student->last_name); ?></strong>
                                </td>
                                <td><?php echo esc_html($student->grade_level ?? '-'); ?></td>
                                <td><?php echo esc_html($student->age ?? '-'); ?></td>
                                <td>
                                    <?php if ($student->email): ?>
                                        <a href="mailto:<?php echo esc_attr($student->email); ?>">
                                            <?php echo esc_html($student->email); ?>
                                        </a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo $admin_menu->getAdminUrl('students', array('action' => 'view', 'student_id' => $student->id)); ?>"
                                       class="button button-small">
                                        <?php _e('View', 'spelling-bee-pro'); ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Registrations -->
        <div class="osb-info-card">
            <div class="osb-card-header">
                <h2><?php _e('Event Registrations', 'spelling-bee-pro'); ?></h2>
                <span class="osb-count-badge"><?php echo count($registrations); ?></span>
            </div>

            <?php if (empty($registrations)): ?>
                <div class="osb-empty-state">
                    <p><?php _e('No event registrations found for this school.', 'spelling-bee-pro'); ?></p>
                </div>
            <?php else: ?>
                <div class="osb-table-container">
                    <table class="osb-table">
                        <thead>
                            <tr>
                                <th><?php _e('Event', 'spelling-bee-pro'); ?></th>
                                <th><?php _e('Registration Date', 'spelling-bee-pro'); ?></th>
                                <th><?php _e('Status', 'spelling-bee-pro'); ?></th>
                                <th><?php _e('Actions', 'spelling-bee-pro'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($registrations as $registration): ?>
                            <?php $event = $db->getEvent($registration->event_id); ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($event ? $event->title : 'Unknown Event'); ?></strong>
                                </td>
                                <td><?php echo esc_html(date('M j, Y', strtotime($registration->registration_date))); ?></td>
                                <td>
                                    <span class="osb-status-badge osb-status-<?php echo esc_attr($registration->status); ?>">
                                        <?php echo esc_html(ucfirst($registration->status)); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="<?php echo $admin_menu->getAdminUrl('registrations', array('action' => 'view', 'registration_id' => $registration->id)); ?>"
                                       class="button button-small">
                                        <?php _e('View', 'spelling-bee-pro'); ?>
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <!-- Timestamps -->
        <div class="osb-info-card">
            <h2><?php _e('Record Information', 'spelling-bee-pro'); ?></h2>

            <div class="osb-info-grid">
                <div class="osb-info-item">
                    <label><?php _e('Created', 'spelling-bee-pro'); ?></label>
                    <div class="osb-info-value"><?php echo esc_html(date('F j, Y \a\t g:i A', strtotime($school->created_at))); ?></div>
                </div>

                <div class="osb-info-item">
                    <label><?php _e('Last Updated', 'spelling-bee-pro'); ?></label>
                    <div class="osb-info-value"><?php echo esc_html(date('F j, Y \a\t g:i A', strtotime($school->updated_at))); ?></div>
                </div>

                <div class="osb-info-item">
                    <label><?php _e('School ID', 'spelling-bee-pro'); ?></label>
                    <div class="osb-info-value"><?php echo esc_html($school->id); ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.osb-view-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 20px;
    border-bottom: 1px solid #ccd0d4;
    padding-bottom: 15px;
}

.osb-view-title h1 {
    margin: 0;
    display: inline-block;
    margin-right: 15px;
}

.osb-status-badge {
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.osb-status-pending { background: #f7f3cd; color: #8a6d3b; }
.osb-status-submitted { background: #d9edf7; color: #31708f; }
.osb-status-review { background: #fcf8e3; color: #8a6d3b; }
.osb-status-approved { background: #dff0d8; color: #3c763d; }
.osb-status-rejected { background: #f2dede; color: #a94442; }
.osb-status-confirmed { background: #d4edda; color: #155724; }

.osb-view-actions {
    display: flex;
    gap: 10px;
}

.osb-info-card {
    background: #fff;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
    margin-bottom: 20px;
}

.osb-info-card h2 {
    background: #f7f7f7;
    border-bottom: 1px solid #ccd0d4;
    margin: 0;
    padding: 15px 20px;
    font-size: 16px;
}

.osb-card-header {
    background: #f7f7f7;
    border-bottom: 1px solid #ccd0d4;
    margin: 0;
    padding: 15px 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.osb-card-header h2 {
    background: none;
    border: none;
    margin: 0;
    padding: 0;
}

.osb-count-badge {
    background: #0073aa;
    color: white;
    padding: 2px 8px;
    border-radius: 10px;
    font-size: 12px;
    font-weight: 600;
}

.osb-info-grid {
    padding: 20px;
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.osb-info-full {
    grid-column: span 2;
}

.osb-info-item label {
    font-weight: 600;
    color: #555;
    display: block;
    margin-bottom: 5px;
}

.osb-info-value {
    color: #333;
}

.osb-table-container {
    padding: 20px;
}

.osb-table {
    width: 100%;
    border-collapse: collapse;
}

.osb-table th,
.osb-table td {
    padding: 12px;
    text-align: left;
    border-bottom: 1px solid #eee;
}

.osb-table th {
    background: #f9f9f9;
    font-weight: 600;
}

.osb-empty-state {
    padding: 40px 20px;
    text-align: center;
    color: #666;
}

@media (max-width: 768px) {
    .osb-view-header {
        flex-direction: column;
        gap: 15px;
    }

    .osb-info-grid {
        grid-template-columns: 1fr;
    }

    .osb-info-full {
        grid-column: span 1;
    }
}
</style>