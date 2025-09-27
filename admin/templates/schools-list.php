<?php
/**
 * Schools List Template
 *
 * @var array $schools
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$admin_menu = OSB_Admin_Menu::getInstance();
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Schools', 'spelling-bee-pro'); ?></h1>
    <a href="<?php echo $admin_menu->getAdminUrl('schools', array('action' => 'new')); ?>" class="page-title-action">
        <?php _e('Add New School', 'spelling-bee-pro'); ?>
    </a>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('School deleted successfully.', 'spelling-bee-pro'); ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['saved'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('School saved successfully.', 'spelling-bee-pro'); ?></p>
        </div>
    <?php endif; ?>

    <hr class="wp-header-end">

    <!-- Filters -->
    <div class="osb-filters">
        <div class="osb-filter-bar">
            <select id="osb-status-filter" class="osb-filter-select">
                <option value=""><?php _e('All Statuses', 'spelling-bee-pro'); ?></option>
                <option value="active"><?php _e('Active', 'spelling-bee-pro'); ?></option>
                <option value="pending"><?php _e('Pending', 'spelling-bee-pro'); ?></option>
                <option value="inactive"><?php _e('Inactive', 'spelling-bee-pro'); ?></option>
            </select>

            <select id="osb-type-filter" class="osb-filter-select">
                <option value=""><?php _e('All Types', 'spelling-bee-pro'); ?></option>
                <option value="public"><?php _e('Public', 'spelling-bee-pro'); ?></option>
                <option value="private"><?php _e('Private', 'spelling-bee-pro'); ?></option>
                <option value="charter"><?php _e('Charter', 'spelling-bee-pro'); ?></option>
                <option value="homeschool"><?php _e('Homeschool', 'spelling-bee-pro'); ?></option>
            </select>

            <input type="search" id="osb-search-schools" class="osb-search-input"
                   placeholder="<?php _e('Search schools...', 'spelling-bee-pro'); ?>">

            <button type="button" id="osb-clear-filters" class="button">
                <?php _e('Clear Filters', 'spelling-bee-pro'); ?>
            </button>

            <button type="button" id="osb-export-schools" class="button">
                <?php _e('Export CSV', 'spelling-bee-pro'); ?>
            </button>
        </div>
    </div>

    <?php if (!empty($schools)): ?>
        <div class="osb-schools-table-container">
            <table class="wp-list-table widefat fixed striped osb-schools-table">
                <thead>
                    <tr>
                        <th scope="col" class="manage-column column-cb check-column">
                            <label class="screen-reader-text" for="cb-select-all-1"><?php _e('Select All', 'spelling-bee-pro'); ?></label>
                            <input id="cb-select-all-1" type="checkbox">
                        </th>
                        <th scope="col" class="manage-column column-name column-primary sortable">
                            <a href="#" class="osb-sort" data-sort="school_name">
                                <span><?php _e('School Name', 'spelling-bee-pro'); ?></span>
                                <span class="sorting-indicator"></span>
                            </a>
                        </th>
                        <th scope="col" class="manage-column column-type">
                            <?php _e('Type', 'spelling-bee-pro'); ?>
                        </th>
                        <th scope="col" class="manage-column column-contact">
                            <?php _e('Contact Person', 'spelling-bee-pro'); ?>
                        </th>
                        <th scope="col" class="manage-column column-location">
                            <?php _e('Location', 'spelling-bee-pro'); ?>
                        </th>
                        <th scope="col" class="manage-column column-students">
                            <?php _e('Students', 'spelling-bee-pro'); ?>
                        </th>
                        <th scope="col" class="manage-column column-token">
                            <?php _e('Token', 'spelling-bee-pro'); ?>
                        </th>
                        <th scope="col" class="manage-column column-status">
                            <?php _e('Status', 'spelling-bee-pro'); ?>
                        </th>
                        <th scope="col" class="manage-column column-date">
                            <?php _e('Registered', 'spelling-bee-pro'); ?>
                        </th>
                    </tr>
                </thead>

                <tbody id="osb-schools-tbody">
                    <?php foreach ($schools as $school): ?>
                        <tr class="osb-school-row" data-status="<?php echo esc_attr($school->status); ?>" data-type="<?php echo esc_attr($school->school_type); ?>">
                            <th scope="row" class="check-column">
                                <input type="checkbox" name="school[]" value="<?php echo $school->id; ?>">
                            </th>

                            <td class="column-name column-primary" data-colname="<?php _e('School Name', 'spelling-bee-pro'); ?>">
                                <strong>
                                    <a href="<?php echo $admin_menu->getAdminUrl('schools', array('action' => 'view', 'school_id' => $school->id)); ?>"
                                       class="row-title">
                                        <?php echo esc_html($school->school_name); ?>
                                    </a>
                                </strong>

                                <div class="row-actions">
                                    <span class="view">
                                        <a href="<?php echo $admin_menu->getAdminUrl('schools', array('action' => 'view', 'school_id' => $school->id)); ?>">
                                            <?php _e('View', 'spelling-bee-pro'); ?>
                                        </a> |
                                    </span>
                                    <span class="edit">
                                        <a href="<?php echo $admin_menu->getAdminUrl('schools', array('action' => 'edit', 'school_id' => $school->id)); ?>">
                                            <?php _e('Edit', 'spelling-bee-pro'); ?>
                                        </a> |
                                    </span>
                                    <span class="students">
                                        <a href="<?php echo $admin_menu->getAdminUrl('students', array('school_id' => $school->id)); ?>">
                                            <?php _e('Students', 'spelling-bee-pro'); ?>
                                        </a> |
                                    </span>
                                    <span class="registrations">
                                        <a href="<?php echo $admin_menu->getAdminUrl('registrations', array('school_id' => $school->id)); ?>">
                                            <?php _e('Registrations', 'spelling-bee-pro'); ?>
                                        </a> |
                                    </span>
                                    <span class="delete">
                                        <a href="<?php echo $admin_menu->getActionUrl('delete_school', array('school_id' => $school->id)); ?>"
                                           class="submitdelete"
                                           onclick="return confirm('<?php _e('Are you sure you want to delete this school?', 'spelling-bee-pro'); ?>')">
                                            <?php _e('Delete', 'spelling-bee-pro'); ?>
                                        </a>
                                    </span>
                                </div>

                                <button type="button" class="toggle-row">
                                    <span class="screen-reader-text"><?php _e('Show more details', 'spelling-bee-pro'); ?></span>
                                </button>
                            </td>

                            <td class="column-type" data-colname="<?php _e('Type', 'spelling-bee-pro'); ?>">
                                <span class="osb-school-type osb-type-<?php echo esc_attr($school->school_type); ?>">
                                    <?php echo esc_html(ucfirst($school->school_type)); ?>
                                </span>
                            </td>

                            <td class="column-contact" data-colname="<?php _e('Contact Person', 'spelling-bee-pro'); ?>">
                                <div class="osb-contact-info">
                                    <strong><?php echo esc_html($school->contact_person_name ?: $school->contact_person); ?></strong><br>
                                    <a href="mailto:<?php echo esc_attr($school->user_email ?: $school->contact_email); ?>">
                                        <?php echo esc_html($school->user_email ?: $school->contact_email); ?>
                                    </a>
                                    <?php if (!empty($school->contact_phone)): ?>
                                        <br><span class="osb-phone"><?php echo esc_html($school->contact_phone); ?></span>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td class="column-location" data-colname="<?php _e('Location', 'spelling-bee-pro'); ?>">
                                <div class="osb-location-info">
                                    <?php if (!empty($school->address)): ?>
                                        <div class="osb-address"><?php echo esc_html($school->address); ?></div>
                                    <?php endif; ?>
                                    <?php if (!empty($school->city) || !empty($school->state)): ?>
                                        <div class="osb-city-state">
                                            <?php if (!empty($school->city)): ?>
                                                <span class="osb-city"><?php echo esc_html($school->city); ?></span>
                                            <?php endif; ?>
                                            <?php if (!empty($school->state)): ?>
                                                <?php if (!empty($school->city)): ?>, <?php endif; ?>
                                                <span class="osb-state"><?php echo esc_html($school->state); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td class="column-students" data-colname="<?php _e('Students', 'spelling-bee-pro'); ?>">
                                <?php
                                global $wpdb;
                                $student_count = $wpdb->get_var(
                                    $wpdb->prepare(
                                        "SELECT COUNT(*) FROM {$wpdb->prefix}osb_students WHERE school_id = %d",
                                        $school->id
                                    )
                                );
                                ?>
                                <span class="osb-student-count">
                                    <a href="<?php echo $admin_menu->getAdminUrl('students', array('school_id' => $school->id)); ?>">
                                        <?php echo intval($student_count); ?>
                                    </a>
                                </span>
                            </td>

                            <td class="column-token" data-colname="<?php _e('Token', 'spelling-bee-pro'); ?>">
                                <?php
                                // Get the latest registration token for this school
                                global $wpdb;
                                $latest_token = $wpdb->get_var(
                                    $wpdb->prepare(
                                        "SELECT registration_token FROM {$wpdb->prefix}osb_registrations
                                         WHERE school_id = %d
                                         ORDER BY created_at DESC LIMIT 1",
                                        $school->id
                                    )
                                );
                                ?>
                                <?php if ($latest_token): ?>
                                    <code class="osb-token" title="<?php echo esc_attr($latest_token); ?>">
                                        <?php echo esc_html(substr($latest_token, 0, 8) . '...'); ?>
                                    </code>
                                    <button type="button" class="button button-small osb-copy-token"
                                            data-token="<?php echo esc_attr($latest_token); ?>"
                                            title="<?php _e('Copy full token', 'spelling-bee-pro'); ?>">
                                        📋
                                    </button>
                                <?php else: ?>
                                    <span class="osb-no-token">—</span>
                                <?php endif; ?>
                            </td>

                            <td class="column-status" data-colname="<?php _e('Status', 'spelling-bee-pro'); ?>">
                                <?php
                                // Convert pending status to active for display logic
                                $display_status = ($school->status === 'pending') ? 'active' : $school->status;
                                $is_active = ($display_status === 'active');
                                ?>
                                <span class="osb-status osb-status-<?php echo esc_attr($display_status); ?>">
                                    <?php echo esc_html(ucfirst($display_status)); ?>
                                </span>

                                <div class="osb-quick-actions">
                                    <?php if ($is_active): ?>
                                        <button type="button" class="button button-small osb-deactivate-school"
                                                data-school-id="<?php echo $school->id; ?>"
                                                title="<?php _e('Deactivate this school', 'spelling-bee-pro'); ?>">
                                            <?php _e('Deactivate', 'spelling-bee-pro'); ?>
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="button button-small button-primary osb-activate-school"
                                                data-school-id="<?php echo $school->id; ?>"
                                                title="<?php _e('Activate this school', 'spelling-bee-pro'); ?>">
                                            <?php _e('Activate', 'spelling-bee-pro'); ?>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>

                            <td class="column-date" data-colname="<?php _e('Registered', 'spelling-bee-pro'); ?>">
                                <abbr title="<?php echo esc_attr(date('F j, Y g:i a', strtotime($school->created_at))); ?>">
                                    <?php echo date('M j, Y', strtotime($school->created_at)); ?>
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
                    <option value="approve"><?php _e('Approve', 'spelling-bee-pro'); ?></option>
                    <option value="deactivate"><?php _e('Deactivate', 'spelling-bee-pro'); ?></option>
                    <option value="delete"><?php _e('Delete', 'spelling-bee-pro'); ?></option>
                </select>
                <input type="submit" id="doaction2" class="button action" value="<?php _e('Apply', 'spelling-bee-pro'); ?>">
            </div>

            <div class="alignright">
                <span class="displaying-num"><?php printf(_n('%d school', '%d schools', count($schools), 'spelling-bee-pro'), count($schools)); ?></span>
            </div>
        </div>

    <?php else: ?>
        <div class="osb-no-schools">
            <div class="osb-empty-state">
                <div class="osb-empty-icon">🏫</div>
                <h3><?php _e('No Schools Found', 'spelling-bee-pro'); ?></h3>
                <p><?php _e('No schools have registered yet.', 'spelling-bee-pro'); ?></p>
                <a href="<?php echo $admin_menu->getAdminUrl('schools', array('action' => 'new')); ?>"
                   class="button button-primary button-large">
                    <?php _e('Add First School', 'spelling-bee-pro'); ?>
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

.osb-schools-table-container {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
}

.osb-schools-table {
    margin: 0;
}

.osb-contact-info {
    line-height: 1.5;
}

.osb-contact-info strong {
    color: #0073aa;
}

.osb-phone {
    color: #666;
    font-size: 0.9em;
}

.osb-location-info {
    line-height: 1.4;
    font-size: 0.9em;
}

.osb-city {
    font-weight: 500;
}

.osb-state, .osb-country {
    color: #666;
}

.osb-school-type {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 0.85em;
    font-weight: 500;
    text-transform: uppercase;
}

.osb-type-public { background: #e8f4fd; color: #0073aa; }
.osb-type-private { background: #fff2e8; color: #d63638; }
.osb-type-charter { background: #f0f6fc; color: #2c3338; }
.osb-type-homeschool { background: #f6fff0; color: #00a32a; }

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

.osb-status {
    display: inline-block;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 0.85em;
    font-weight: 600;
    text-transform: uppercase;
}

.osb-status-active { background: #00a32a; color: white; }
.osb-status-pending { background: #ffb900; color: white; }
.osb-status-inactive { background: #646970; color: white; }

.osb-quick-actions {
    margin-top: 8px;
}

.osb-quick-actions .button {
    font-size: 11px;
    height: auto;
    padding: 3px 8px;
    line-height: 1.4;
}

/* Token column styles */
.osb-token {
    background: #f1f1f1;
    color: #666;
    padding: 4px 6px;
    border-radius: 3px;
    font-family: monospace;
    font-size: 11px;
    display: inline-block;
    margin-right: 5px;
}

.osb-copy-token {
    font-size: 10px;
    padding: 2px 4px;
    height: auto;
    line-height: 1;
    vertical-align: middle;
}

.osb-copy-token.copied {
    background: #00a32a;
    color: white;
}

.osb-no-token {
    color: #999;
    font-style: italic;
}

.osb-no-schools {
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
.osb-school-row.hidden {
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

    .column-type,
    .column-location,
    .column-date {
        display: none;
    }
}

@media (max-width: 600px) {
    .column-students {
        display: none;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Filtering functionality
    function filterSchools() {
        const statusFilter = $('#osb-status-filter').val();
        const typeFilter = $('#osb-type-filter').val();
        const searchTerm = $('#osb-search-schools').val().toLowerCase();

        $('.osb-school-row').each(function() {
            const $row = $(this);
            const status = $row.data('status');
            const type = $row.data('type');
            const schoolName = $row.find('.row-title').text().toLowerCase();
            const contactName = $row.find('.osb-contact-info strong').text().toLowerCase();

            let show = true;

            // Status filter
            if (statusFilter && status !== statusFilter) {
                show = false;
            }

            // Type filter
            if (typeFilter && type !== typeFilter) {
                show = false;
            }

            // Search filter
            if (searchTerm && !schoolName.includes(searchTerm) && !contactName.includes(searchTerm)) {
                show = false;
            }

            $row.toggleClass('hidden', !show);
        });

        // Update count
        const visibleCount = $('.osb-school-row:not(.hidden)').length;
        $('.displaying-num').text(
            visibleCount === 1
                ? '<?php _e('1 school', 'spelling-bee-pro'); ?>'
                : visibleCount + ' <?php _e('schools', 'spelling-bee-pro'); ?>'
        );
    }

    // Bind filter events
    $('#osb-status-filter, #osb-type-filter').on('change', filterSchools);
    $('#osb-search-schools').on('input', filterSchools);

    // Clear filters
    $('#osb-clear-filters').on('click', function() {
        $('#osb-status-filter').val('');
        $('#osb-type-filter').val('');
        $('#osb-search-schools').val('');
        filterSchools();
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
        const $tbody = $('#osb-schools-tbody');
        const $rows = $tbody.find('.osb-school-row').get();

        $rows.sort(function(a, b) {
            let aVal, bVal;

            switch(sortBy) {
                case 'school_name':
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

    // Quick approve
    $('.osb-approve-school').on('click', function() {
        const schoolId = $(this).data('school-id');

        if (!confirm('<?php _e('Are you sure you want to approve this school?', 'spelling-bee-pro'); ?>')) {
            return;
        }

        const $button = $(this);
        $button.prop('disabled', true).text('<?php _e('Approving...', 'spelling-bee-pro'); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'osb_admin_action',
                sub_action: 'approve_school',
                school_id: schoolId,
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

    // Export CSV
    $('#osb-export-schools').on('click', function() {
        const params = new URLSearchParams({
            action: 'osb_admin_action',
            sub_action: 'export_schools',
            format: 'csv',
            nonce: osb_ajax.nonce
        });

        window.location.href = ajaxurl + '?' + params.toString();
    });

    // Bulk actions
    $('#doaction2').on('click', function(e) {
        const action = $('#bulk-action-selector-bottom').val();
        const selected = $('input[name="school[]"]:checked');

        if (action === '-1') {
            alert('<?php _e('Please select an action.', 'spelling-bee-pro'); ?>');
            e.preventDefault();
            return;
        }

        if (selected.length === 0) {
            alert('<?php _e('Please select at least one school.', 'spelling-bee-pro'); ?>');
            e.preventDefault();
            return;
        }

        if (action === 'delete') {
            if (!confirm('<?php _e('Are you sure you want to delete the selected schools?', 'spelling-bee-pro'); ?>')) {
                e.preventDefault();
                return;
            }
        }

        // Process bulk action via AJAX
        e.preventDefault();

        const schoolIds = selected.map(function() {
            return $(this).val();
        }).get();

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'osb_admin_action',
                sub_action: 'bulk_schools_action',
                bulk_action: action,
                school_ids: schoolIds,
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
        $('input[name="school[]"]').prop('checked', $(this).is(':checked'));
    });

    // Copy token functionality
    $('.osb-copy-token').on('click', function() {
        const token = $(this).data('token');
        const $button = $(this);

        // Copy to clipboard
        navigator.clipboard.writeText(token).then(function() {
            $button.text('✓').addClass('copied');
            setTimeout(function() {
                $button.text('📋').removeClass('copied');
            }, 2000);
        }).catch(function() {
            // Fallback for older browsers
            const textArea = document.createElement('textarea');
            textArea.value = token;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);

            $button.text('✓').addClass('copied');
            setTimeout(function() {
                $button.text('📋').removeClass('copied');
            }, 2000);
        });
    });

    // Activate school
    $('.osb-activate-school').on('click', function() {
        const schoolId = $(this).data('school-id');

        if (!confirm('<?php _e('Are you sure you want to activate this school?', 'spelling-bee-pro'); ?>')) {
            return;
        }

        const $button = $(this);
        $button.prop('disabled', true).text('<?php _e('Activating...', 'spelling-bee-pro'); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'osb_admin_action',
                sub_action: 'activate_school',
                school_id: schoolId,
                nonce: osb_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('<?php _e('Error:', 'spelling-bee-pro'); ?> ' + response.data);
                    $button.prop('disabled', false).text('<?php _e('Activate', 'spelling-bee-pro'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Network error. Please try again.', 'spelling-bee-pro'); ?>');
                $button.prop('disabled', false).text('<?php _e('Activate', 'spelling-bee-pro'); ?>');
            }
        });
    });

    // Deactivate school
    $('.osb-deactivate-school').on('click', function() {
        const schoolId = $(this).data('school-id');

        if (!confirm('<?php _e('Are you sure you want to deactivate this school?', 'spelling-bee-pro'); ?>')) {
            return;
        }

        const $button = $(this);
        $button.prop('disabled', true).text('<?php _e('Deactivating...', 'spelling-bee-pro'); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'osb_admin_action',
                sub_action: 'deactivate_school',
                school_id: schoolId,
                nonce: osb_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                } else {
                    alert('<?php _e('Error:', 'spelling-bee-pro'); ?> ' + response.data);
                    $button.prop('disabled', false).text('<?php _e('Deactivate', 'spelling-bee-pro'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Network error. Please try again.', 'spelling-bee-pro'); ?>');
                $button.prop('disabled', false).text('<?php _e('Deactivate', 'spelling-bee-pro'); ?>');
            }
        });
    });
});
</script>