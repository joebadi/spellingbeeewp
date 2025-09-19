<?php
/**
 * Students List Template
 *
 * @var array $students
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$admin_menu = OSB_Admin_Menu::getInstance();
$selected_school_id = intval($_GET['school_id'] ?? 0);
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Students', 'spelling-bee-pro'); ?></h1>
    <a href="<?php echo $admin_menu->getAdminUrl('students', array('action' => 'new')); ?>" class="page-title-action">
        <?php _e('Add New Student', 'spelling-bee-pro'); ?>
    </a>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Student deleted successfully.', 'spelling-bee-pro'); ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['saved'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Student saved successfully.', 'spelling-bee-pro'); ?></p>
        </div>
    <?php endif; ?>

    <hr class="wp-header-end">

    <!-- Filters -->
    <div class="osb-filters">
        <div class="osb-filter-bar">
            <select id="osb-school-filter" class="osb-filter-select">
                <option value=""><?php _e('All Schools', 'spelling-bee-pro'); ?></option>
                <?php
                $db = OSB_Database::getInstance();
                $schools = $db->getAllSchools();
                foreach ($schools as $school):
                ?>
                    <option value="<?php echo $school->id; ?>" <?php selected($selected_school_id, $school->id); ?>>
                        <?php echo esc_html($school->school_name); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <select id="osb-grade-filter" class="osb-filter-select">
                <option value=""><?php _e('All Grades', 'spelling-bee-pro'); ?></option>
                <?php for ($i = 1; $i <= 12; $i++): ?>
                    <option value="<?php echo $i; ?>"><?php printf(__('Grade %d', 'spelling-bee-pro'), $i); ?></option>
                <?php endfor; ?>
            </select>

            <select id="osb-gender-filter" class="osb-filter-select">
                <option value=""><?php _e('All Genders', 'spelling-bee-pro'); ?></option>
                <option value="male"><?php _e('Male', 'spelling-bee-pro'); ?></option>
                <option value="female"><?php _e('Female', 'spelling-bee-pro'); ?></option>
                <option value="other"><?php _e('Other', 'spelling-bee-pro'); ?></option>
            </select>

            <input type="search" id="osb-search-students" class="osb-search-input"
                   placeholder="<?php _e('Search students...', 'spelling-bee-pro'); ?>">

            <button type="button" id="osb-clear-filters" class="button">
                <?php _e('Clear Filters', 'spelling-bee-pro'); ?>
            </button>

            <button type="button" id="osb-export-students" class="button">
                <?php _e('Export CSV', 'spelling-bee-pro'); ?>
            </button>
        </div>
    </div>

    <?php if (!empty($students)): ?>
        <div class="osb-students-table-container">
            <table class="wp-list-table widefat fixed striped osb-students-table">
                <thead>
                    <tr>
                        <th scope="col" class="manage-column column-cb check-column">
                            <label class="screen-reader-text" for="cb-select-all-1"><?php _e('Select All', 'spelling-bee-pro'); ?></label>
                            <input id="cb-select-all-1" type="checkbox">
                        </th>
                        <th scope="col" class="manage-column column-name column-primary sortable">
                            <a href="#" class="osb-sort" data-sort="name">
                                <span><?php _e('Student Name', 'spelling-bee-pro'); ?></span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th scope="col" class="manage-column column-school">
                            <?php _e('School', 'spelling-bee-pro'); ?>
                        </th>
                        <th scope="col" class="manage-column column-grade">
                            <?php _e('Grade', 'spelling-bee-pro'); ?>
                        </th>
                        <th scope="col" class="manage-column column-age">
                            <?php _e('Age', 'spelling-bee-pro'); ?>
                        </th>
                        <th scope="col" class="manage-column column-gender">
                            <?php _e('Gender', 'spelling-bee-pro'); ?>
                        </th>
                        <th scope="col" class="manage-column column-parent">
                            <?php _e('Parent/Guardian', 'spelling-bee-pro'); ?>
                        </th>
                        <th scope="col" class="manage-column column-registrations">
                            <?php _e('Registrations', 'spelling-bee-pro'); ?>
                        </th>
                        <th scope="col" class="manage-column column-date">
                            <?php _e('Added', 'spelling-bee-pro'); ?>
                        </th>
                    </tr>
                </thead>

                <tbody id="osb-students-tbody">
                    <?php foreach ($students as $student): ?>
                        <?php
                        // Calculate age
                        $age = '';
                        if (!empty($student->date_of_birth)) {
                            $birthDate = new DateTime($student->date_of_birth);
                            $currentDate = new DateTime();
                            $age = $currentDate->diff($birthDate)->y;
                        }

                        // Get registration count
                        global $wpdb;
                        $registration_count = $wpdb->get_var(
                            $wpdb->prepare(
                                "SELECT COUNT(*) FROM {$wpdb->prefix}osb_registrations r
                                 WHERE r.school_id = %d",
                                $student->school_id
                            )
                        );
                        ?>
                        <tr class="osb-student-row"
                            data-school="<?php echo esc_attr($student->school_id); ?>"
                            data-grade="<?php echo esc_attr($student->grade_level); ?>"
                            data-gender="<?php echo esc_attr($student->gender); ?>">

                            <th scope="row" class="check-column">
                                <input type="checkbox" name="student[]" value="<?php echo $student->id; ?>">
                            </th>

                            <td class="column-name column-primary" data-colname="<?php _e('Student Name', 'spelling-bee-pro'); ?>">
                                <div class="osb-student-info">
                                    <strong>
                                        <a href="<?php echo $admin_menu->getAdminUrl('students', array('action' => 'view', 'student_id' => $student->id)); ?>"
                                           class="row-title">
                                            <?php echo esc_html($student->first_name . ' ' . $student->last_name); ?>
                                        </a>
                                    </strong>

                                    <?php if (!empty($student->student_email)): ?>
                                        <br><a href="mailto:<?php echo esc_attr($student->student_email); ?>" class="osb-student-email">
                                            <?php echo esc_html($student->student_email); ?>
                                        </a>
                                    <?php endif; ?>
                                </div>

                                <div class="row-actions">
                                    <span class="view">
                                        <a href="<?php echo $admin_menu->getAdminUrl('students', array('action' => 'view', 'student_id' => $student->id)); ?>">
                                            <?php _e('View', 'spelling-bee-pro'); ?>
                                        </a> |
                                    </span>
                                    <span class="edit">
                                        <a href="<?php echo $admin_menu->getAdminUrl('students', array('action' => 'edit', 'student_id' => $student->id)); ?>">
                                            <?php _e('Edit', 'spelling-bee-pro'); ?>
                                        </a> |
                                    </span>
                                    <span class="school">
                                        <a href="<?php echo $admin_menu->getAdminUrl('schools', array('action' => 'view', 'school_id' => $student->school_id)); ?>">
                                            <?php _e('View School', 'spelling-bee-pro'); ?>
                                        </a> |
                                    </span>
                                    <span class="delete">
                                        <a href="<?php echo $admin_menu->getActionUrl('delete_student', array('student_id' => $student->id)); ?>"
                                           class="submitdelete"
                                           onclick="return confirm('<?php _e('Are you sure you want to delete this student?', 'spelling-bee-pro'); ?>')">
                                            <?php _e('Delete', 'spelling-bee-pro'); ?>
                                        </a>
                                    </span>
                                </div>

                                <button type="button" class="toggle-row">
                                    <span class="screen-reader-text"><?php _e('Show more details', 'spelling-bee-pro'); ?></span>
                                </button>
                            </td>

                            <td class="column-school" data-colname="<?php _e('School', 'spelling-bee-pro'); ?>">
                                <a href="<?php echo $admin_menu->getAdminUrl('schools', array('action' => 'view', 'school_id' => $student->school_id)); ?>">
                                    <?php echo esc_html($student->school_name); ?>
                                </a>
                            </td>

                            <td class="column-grade" data-colname="<?php _e('Grade', 'spelling-bee-pro'); ?>">
                                <span class="osb-grade-badge osb-grade-<?php echo esc_attr($student->grade_level); ?>">
                                    <?php printf(__('Grade %s', 'spelling-bee-pro'), esc_html($student->grade_level)); ?>
                                </span>
                            </td>

                            <td class="column-age" data-colname="<?php _e('Age', 'spelling-bee-pro'); ?>">
                                <?php if ($age): ?>
                                    <span class="osb-age"><?php printf(__('%d years', 'spelling-bee-pro'), $age); ?></span>
                                    <div class="osb-birthday">
                                        <small><?php echo date('M j, Y', strtotime($student->date_of_birth)); ?></small>
                                    </div>
                                <?php else: ?>
                                    <span class="osb-no-data">—</span>
                                <?php endif; ?>
                            </td>

                            <td class="column-gender" data-colname="<?php _e('Gender', 'spelling-bee-pro'); ?>">
                                <?php if (!empty($student->gender)): ?>
                                    <span class="osb-gender osb-gender-<?php echo esc_attr($student->gender); ?>">
                                        <?php echo esc_html(ucfirst($student->gender)); ?>
                                    </span>
                                <?php else: ?>
                                    <span class="osb-no-data">—</span>
                                <?php endif; ?>
                            </td>

                            <td class="column-parent" data-colname="<?php _e('Parent/Guardian', 'spelling-bee-pro'); ?>">
                                <div class="osb-parent-info">
                                    <?php if (!empty($student->parent_email)): ?>
                                        <strong><?php echo esc_html($student->parent_name ?: 'Parent/Guardian'); ?></strong><br>
                                        <a href="mailto:<?php echo esc_attr($student->parent_email); ?>">
                                            <?php echo esc_html($student->parent_email); ?>
                                        </a>
                                        <?php if (!empty($student->parent_phone)): ?>
                                            <br><span class="osb-phone"><?php echo esc_html($student->parent_phone); ?></span>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="osb-no-data"><?php _e('Not provided', 'spelling-bee-pro'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td class="column-registrations" data-colname="<?php _e('Registrations', 'spelling-bee-pro'); ?>">
                                <span class="osb-registration-count">
                                    <?php echo intval($registration_count); ?>
                                </span>

                                <?php if ($registration_count > 0): ?>
                                    <div class="osb-registration-link">
                                        <a href="<?php echo $admin_menu->getAdminUrl('registrations', array('school_id' => $student->school_id)); ?>">
                                            <?php _e('View', 'spelling-bee-pro'); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </td>

                            <td class="column-date" data-colname="<?php _e('Added', 'spelling-bee-pro'); ?>">
                                <abbr title="<?php echo esc_attr(date('F j, Y g:i a', strtotime($student->created_at))); ?>">
                                    <?php echo date('M j, Y', strtotime($student->created_at)); ?>
                                </abbr>
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
                    <option value="delete"><?php _e('Delete', 'spelling-bee-pro'); ?></option>
                </select>
                <input type="submit" id="doaction2" class="button action" value="<?php _e('Apply', 'spelling-bee-pro'); ?>">
            </div>

            <div class="alignright">
                <span class="displaying-num"><?php printf(_n('%d student', '%d students', count($students), 'spelling-bee-pro'), count($students)); ?></span>
            </div>
        </div>

    <?php else: ?>
        <div class="osb-no-students">
            <div class="osb-empty-state">
                <div class="osb-empty-icon">👨‍🎓</div>
                <h3><?php _e('No Students Found', 'spelling-bee-pro'); ?></h3>
                <p><?php _e('No students have been registered yet.', 'spelling-bee-pro'); ?></p>
                <a href="<?php echo $admin_menu->getAdminUrl('students', array('action' => 'new')); ?>"
                   class="button button-primary button-large">
                    <?php _e('Add First Student', 'spelling-bee-pro'); ?>
                </a>
            </div>
        </div>
    <?php endif; ?>
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

.osb-students-table-container {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
}

.osb-students-table {
    margin: 0;
}

.osb-student-info {
    line-height: 1.5;
}

.osb-student-email {
    color: #666;
    font-size: 0.9em;
}

.osb-grade-badge {
    display: inline-block;
    padding: 3px 8px;
    border-radius: 3px;
    font-size: 0.85em;
    font-weight: 500;
    background: #f0f0f0;
    color: #333;
}

.osb-age {
    font-weight: 500;
}

.osb-birthday {
    margin-top: 2px;
}

.osb-birthday small {
    color: #666;
    font-size: 0.85em;
}

.osb-gender {
    display: inline-block;
    padding: 3px 6px;
    border-radius: 3px;
    font-size: 0.85em;
    font-weight: 500;
}

.osb-gender-male { background: #e8f4fd; color: #0073aa; }
.osb-gender-female { background: #fdf2f6; color: #d63638; }
.osb-gender-other { background: #f6f7f7; color: #50575e; }

.osb-parent-info {
    line-height: 1.4;
    font-size: 0.9em;
}

.osb-parent-info strong {
    color: #0073aa;
}

.osb-phone {
    color: #666;
}

.osb-registration-count {
    display: inline-block;
    background: #f0f0f0;
    padding: 4px 8px;
    border-radius: 3px;
    font-weight: 500;
    color: #0073aa;
}

.osb-registration-link {
    margin-top: 4px;
}

.osb-registration-link a {
    font-size: 0.85em;
    text-decoration: none;
}

.osb-no-data {
    color: #999;
    font-style: italic;
}

.osb-no-students {
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

/* Sortable headers */
.osb-sort {
    text-decoration: none;
    color: inherit;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.osb-sort:hover {
    color: #0073aa;
}

.sorting-indicator {
    width: 10px;
    height: 10px;
    opacity: 0.3;
}

.osb-sort.asc .sorting-indicator::after {
    content: '▲';
}

.osb-sort.desc .sorting-indicator::after {
    content: '▼';
}

/* Hidden class for filtering */
.osb-student-row.hidden {
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

    .column-age,
    .column-gender,
    .column-registrations,
    .column-date {
        display: none;
    }
}

@media (max-width: 600px) {
    .column-grade,
    .column-parent {
        display: none;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Filtering functionality
    function filterStudents() {
        const schoolFilter = $('#osb-school-filter').val();
        const gradeFilter = $('#osb-grade-filter').val();
        const genderFilter = $('#osb-gender-filter').val();
        const searchTerm = $('#osb-search-students').val().toLowerCase();

        $('.osb-student-row').each(function() {
            const $row = $(this);
            const school = $row.data('school').toString();
            const grade = $row.data('grade').toString();
            const gender = $row.data('gender');
            const studentName = $row.find('.row-title').text().toLowerCase();
            const schoolName = $row.find('.column-school a').text().toLowerCase();

            let show = true;

            // School filter
            if (schoolFilter && school !== schoolFilter) {
                show = false;
            }

            // Grade filter
            if (gradeFilter && grade !== gradeFilter) {
                show = false;
            }

            // Gender filter
            if (genderFilter && gender !== genderFilter) {
                show = false;
            }

            // Search filter
            if (searchTerm && !studentName.includes(searchTerm) && !schoolName.includes(searchTerm)) {
                show = false;
            }

            $row.toggleClass('hidden', !show);
        });

        // Update count
        const visibleCount = $('.osb-student-row:not(.hidden)').length;
        $('.displaying-num').text(
            visibleCount === 1
                ? '<?php _e('1 student', 'spelling-bee-pro'); ?>'
                : visibleCount + ' <?php _e('students', 'spelling-bee-pro'); ?>'
        );
    }

    // Bind filter events
    $('#osb-school-filter, #osb-grade-filter, #osb-gender-filter').on('change', filterStudents);
    $('#osb-search-students').on('input', filterStudents);

    // Apply initial school filter if set from URL
    <?php if ($selected_school_id): ?>
        filterStudents();
    <?php endif; ?>

    // Clear filters
    $('#osb-clear-filters').on('click', function() {
        $('#osb-school-filter').val('');
        $('#osb-grade-filter').val('');
        $('#osb-gender-filter').val('');
        $('#osb-search-students').val('');
        filterStudents();
    });

    // Sorting
    $('.osb-sort').on('click', function(e) {
        e.preventDefault();

        const $this = $(this);
        const sortBy = $this.data('sort');
        const isAsc = !$this.hasClass('asc');

        // Remove existing sort classes
        $('.osb-sort').removeClass('asc desc');

        // Add sort class to current
        $this.addClass(isAsc ? 'asc' : 'desc');

        // Sort rows
        const $tbody = $('#osb-students-tbody');
        const $rows = $tbody.find('.osb-student-row').get();

        $rows.sort(function(a, b) {
            let aVal, bVal;

            switch(sortBy) {
                case 'name':
                    aVal = $(a).find('.row-title').text();
                    bVal = $(b).find('.row-title').text();
                    break;
                default:
                    return 0;
            }

            if (isAsc) {
                return aVal.localeCompare(bVal);
            } else {
                return bVal.localeCompare(aVal);
            }
        });

        $tbody.append($rows);
    });

    // Export CSV
    $('#osb-export-students').on('click', function() {
        const params = new URLSearchParams({
            action: 'osb_admin_action',
            sub_action: 'export_students',
            format: 'csv',
            nonce: osb_ajax.nonce
        });

        // Add current filters
        const schoolFilter = $('#osb-school-filter').val();
        if (schoolFilter) {
            params.set('school_id', schoolFilter);
        }

        window.location.href = ajaxurl + '?' + params.toString();
    });

    // Bulk actions
    $('#doaction2').on('click', function(e) {
        const action = $('#bulk-action-selector-bottom').val();
        const selected = $('input[name="student[]"]:checked');

        if (action === '-1') {
            alert('<?php _e('Please select an action.', 'spelling-bee-pro'); ?>');
            e.preventDefault();
            return;
        }

        if (selected.length === 0) {
            alert('<?php _e('Please select at least one student.', 'spelling-bee-pro'); ?>');
            e.preventDefault();
            return;
        }

        if (action === 'delete') {
            if (!confirm('<?php _e('Are you sure you want to delete the selected students?', 'spelling-bee-pro'); ?>')) {
                e.preventDefault();
                return;
            }
        }

        // Process bulk action via AJAX
        e.preventDefault();

        const studentIds = selected.map(function() {
            return $(this).val();
        }).get();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'osb_admin_action',
                sub_action: 'bulk_students_action',
                bulk_action: action,
                student_ids: studentIds,
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
        $('input[name="student[]"]').prop('checked', $(this).is(':checked'));
    });
});
</script>