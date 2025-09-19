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

$documents = [];
if ($event_id) {
    $documents = $wpdb->get_results($wpdb->prepare("
        SELECT d.*, s.name as school_name, st.first_name, st.last_name, st.student_id,
               u.display_name as uploaded_by_name
        FROM {$wpdb->prefix}osb_documents d
        LEFT JOIN {$wpdb->prefix}osb_schools s ON d.school_id = s.id
        LEFT JOIN {$wpdb->prefix}osb_students st ON d.student_id = st.id
        LEFT JOIN {$wpdb->prefix}users u ON d.uploaded_by = u.ID
        WHERE d.event_id = %d
        ORDER BY d.uploaded_at DESC
    ", $event_id));
}

$document_stats = [];
if ($event_id) {
    $document_stats = $wpdb->get_row($wpdb->prepare("
        SELECT
            COUNT(*) as total,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected
        FROM {$wpdb->prefix}osb_documents
        WHERE event_id = %d
    ", $event_id), ARRAY_A);
}
?>

<div class="wrap osb-admin-page">
    <div class="osb-header">
        <h1 class="wp-heading-inline">
            <span class="dashicons dashicons-media-document"></span>
            Documents Management
        </h1>

        <div class="osb-header-actions">
            <button type="button" class="button button-primary" id="bulk-approve-docs">
                <span class="dashicons dashicons-yes"></span>
                Bulk Approve
            </button>
            <button type="button" class="button" id="export-documents">
                <span class="dashicons dashicons-download"></span>
                Export
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
            <div class="osb-stat-card">
                <div class="osb-stat-icon">
                    <span class="dashicons dashicons-media-document"></span>
                </div>
                <div class="osb-stat-content">
                    <div class="osb-stat-number"><?php echo number_format($document_stats['total'] ?? 0); ?></div>
                    <div class="osb-stat-label">Total Documents</div>
                </div>
            </div>

            <div class="osb-stat-card pending">
                <div class="osb-stat-icon">
                    <span class="dashicons dashicons-clock"></span>
                </div>
                <div class="osb-stat-content">
                    <div class="osb-stat-number"><?php echo number_format($document_stats['pending'] ?? 0); ?></div>
                    <div class="osb-stat-label">Pending Review</div>
                </div>
            </div>

            <div class="osb-stat-card approved">
                <div class="osb-stat-icon">
                    <span class="dashicons dashicons-yes-alt"></span>
                </div>
                <div class="osb-stat-content">
                    <div class="osb-stat-number"><?php echo number_format($document_stats['approved'] ?? 0); ?></div>
                    <div class="osb-stat-label">Approved</div>
                </div>
            </div>

            <div class="osb-stat-card rejected">
                <div class="osb-stat-icon">
                    <span class="dashicons dashicons-dismiss"></span>
                </div>
                <div class="osb-stat-content">
                    <div class="osb-stat-number"><?php echo number_format($document_stats['rejected'] ?? 0); ?></div>
                    <div class="osb-stat-label">Rejected</div>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="osb-filters">
            <div class="osb-filter-group">
                <label>Document Type:</label>
                <select id="type-filter" onchange="filterDocuments()">
                    <option value="">All Types</option>
                    <option value="student_photo">Student Photo</option>
                    <option value="birth_certificate">Birth Certificate</option>
                    <option value="school_enrollment">School Enrollment</option>
                    <option value="identification">Identification</option>
                    <option value="medical_form">Medical Form</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="osb-filter-group">
                <label>Status:</label>
                <select id="status-filter" onchange="filterDocuments()">
                    <option value="">All Status</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Approved</option>
                    <option value="rejected">Rejected</option>
                </select>
            </div>

            <div class="osb-filter-group">
                <label>School:</label>
                <select id="school-filter" onchange="filterDocuments()">
                    <option value="">All Schools</option>
                    <?php
                    $schools = $wpdb->get_results($wpdb->prepare("
                        SELECT DISTINCT s.id, s.name
                        FROM {$wpdb->prefix}osb_schools s
                        INNER JOIN {$wpdb->prefix}osb_documents d ON s.id = d.school_id
                        WHERE d.event_id = %d
                        ORDER BY s.name
                    ", $event_id));
                    foreach ($schools as $school):
                    ?>
                        <option value="<?php echo $school->id; ?>"><?php echo esc_html($school->name); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="osb-filter-group">
                <input type="text" id="search-filter" placeholder="Search documents..." onkeyup="filterDocuments()">
            </div>

            <button type="button" class="button" onclick="clearFilters()">Clear Filters</button>
        </div>

        <!-- Documents Table -->
        <?php if (!empty($documents)): ?>
            <div class="osb-table-container">
                <table class="wp-list-table widefat fixed striped" id="documents-table">
                    <thead>
                        <tr>
                            <th scope="col" class="check-column">
                                <input type="checkbox" id="select-all-docs">
                            </th>
                            <th scope="col" class="sortable" data-sort="document_type">
                                Document Type
                                <span class="sorting-indicator"></span>
                            </th>
                            <th scope="col" class="sortable" data-sort="school_name">
                                School/Student
                                <span class="sorting-indicator"></span>
                            </th>
                            <th scope="col">File</th>
                            <th scope="col" class="sortable" data-sort="status">
                                Status
                                <span class="sorting-indicator"></span>
                            </th>
                            <th scope="col" class="sortable" data-sort="uploaded_at">
                                Uploaded
                                <span class="sorting-indicator"></span>
                            </th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($documents as $document): ?>
                            <tr class="document-row"
                                data-type="<?php echo esc_attr($document->document_type); ?>"
                                data-status="<?php echo esc_attr($document->status); ?>"
                                data-school="<?php echo esc_attr($document->school_id); ?>">
                                <td class="check-column">
                                    <input type="checkbox" name="document_ids[]" value="<?php echo $document->id; ?>" class="document-checkbox">
                                </td>

                                <td class="document-type">
                                    <strong><?php echo esc_html(ucwords(str_replace('_', ' ', $document->document_type))); ?></strong>
                                    <?php if ($document->description): ?>
                                        <div class="document-description"><?php echo esc_html($document->description); ?></div>
                                    <?php endif; ?>
                                </td>

                                <td class="entity-info">
                                    <div class="school-name"><?php echo esc_html($document->school_name); ?></div>
                                    <?php if ($document->student_id): ?>
                                        <div class="student-name">
                                            <?php echo esc_html($document->first_name . ' ' . $document->last_name); ?>
                                            <span class="student-id">(ID: <?php echo esc_html($document->student_id); ?>)</span>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td class="file-info">
                                    <div class="file-details">
                                        <a href="<?php echo esc_url($document->file_path); ?>" target="_blank" class="file-link">
                                            <span class="dashicons dashicons-media-default"></span>
                                            <?php echo esc_html(basename($document->file_path)); ?>
                                        </a>
                                        <div class="file-meta">
                                            Size: <?php echo esc_html($document->file_size ? size_format($document->file_size) : 'Unknown'); ?>
                                        </div>
                                    </div>
                                </td>

                                <td class="status-column">
                                    <span class="status-badge status-<?php echo esc_attr($document->status); ?>">
                                        <?php echo esc_html(ucfirst($document->status)); ?>
                                    </span>
                                    <?php if ($document->status === 'rejected' && $document->rejection_reason): ?>
                                        <div class="rejection-reason" title="<?php echo esc_attr($document->rejection_reason); ?>">
                                            <span class="dashicons dashicons-info"></span>
                                        </div>
                                    <?php endif; ?>
                                </td>

                                <td class="upload-info">
                                    <div class="upload-date"><?php echo date('M j, Y', strtotime($document->uploaded_at)); ?></div>
                                    <div class="upload-time"><?php echo date('g:i A', strtotime($document->uploaded_at)); ?></div>
                                    <?php if ($document->uploaded_by_name): ?>
                                        <div class="uploaded-by">by <?php echo esc_html($document->uploaded_by_name); ?></div>
                                    <?php endif; ?>
                                </td>

                                <td class="actions-column">
                                    <div class="action-buttons">
                                        <button type="button" class="button button-small view-document"
                                                data-id="<?php echo $document->id; ?>"
                                                data-url="<?php echo esc_url($document->file_path); ?>">
                                            <span class="dashicons dashicons-visibility"></span>
                                            View
                                        </button>

                                        <?php if ($document->status === 'pending'): ?>
                                            <button type="button" class="button button-primary button-small approve-document"
                                                    data-id="<?php echo $document->id; ?>">
                                                <span class="dashicons dashicons-yes"></span>
                                                Approve
                                            </button>
                                            <button type="button" class="button button-small reject-document"
                                                    data-id="<?php echo $document->id; ?>">
                                                <span class="dashicons dashicons-no"></span>
                                                Reject
                                            </button>
                                        <?php elseif ($document->status === 'approved'): ?>
                                            <button type="button" class="button button-small reject-document"
                                                    data-id="<?php echo $document->id; ?>">
                                                <span class="dashicons dashicons-no"></span>
                                                Reject
                                            </button>
                                        <?php elseif ($document->status === 'rejected'): ?>
                                            <button type="button" class="button button-primary button-small approve-document"
                                                    data-id="<?php echo $document->id; ?>">
                                                <span class="dashicons dashicons-yes"></span>
                                                Approve
                                            </button>
                                        <?php endif; ?>

                                        <button type="button" class="button button-link-delete button-small delete-document"
                                                data-id="<?php echo $document->id; ?>">
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
                    <span class="dashicons dashicons-media-document"></span>
                </div>
                <h3>No Documents Found</h3>
                <p>No documents have been uploaded for this event yet.</p>
            </div>
        <?php endif; ?>
    <?php else: ?>
        <div class="osb-empty-state">
            <div class="osb-empty-icon">
                <span class="dashicons dashicons-admin-settings"></span>
            </div>
            <h3>Select an Event</h3>
            <p>Please select an event to view and manage documents.</p>
        </div>
    <?php endif; ?>
</div>

<!-- Document Viewer Modal -->
<div id="document-viewer-modal" class="osb-modal" style="display: none;">
    <div class="osb-modal-content">
        <div class="osb-modal-header">
            <h3>Document Viewer</h3>
            <button type="button" class="osb-modal-close">&times;</button>
        </div>
        <div class="osb-modal-body">
            <div id="document-viewer-content"></div>
        </div>
    </div>
</div>

<!-- Rejection Reason Modal -->
<div id="rejection-modal" class="osb-modal" style="display: none;">
    <div class="osb-modal-content">
        <div class="osb-modal-header">
            <h3>Reject Document</h3>
            <button type="button" class="osb-modal-close">&times;</button>
        </div>
        <div class="osb-modal-body">
            <form id="rejection-form">
                <input type="hidden" id="reject-document-id">
                <div class="form-group">
                    <label for="rejection-reason">Reason for Rejection:</label>
                    <textarea id="rejection-reason" rows="4" placeholder="Please provide a reason for rejecting this document..." required></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="button" onclick="closeModal('rejection-modal')">Cancel</button>
                    <button type="submit" class="button button-primary">Reject Document</button>
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

.osb-stat-card.pending {
    border-left: 4px solid #f0ad4e;
}

.osb-stat-card.approved {
    border-left: 4px solid #5cb85c;
}

.osb-stat-card.rejected {
    border-left: 4px solid #d9534f;
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

.document-type .document-description {
    font-size: 12px;
    color: #666;
    margin-top: 3px;
}

.entity-info .school-name {
    font-weight: 600;
    margin-bottom: 3px;
}

.entity-info .student-name {
    font-size: 12px;
    color: #666;
}

.entity-info .student-id {
    color: #999;
}

.file-info .file-link {
    display: flex;
    align-items: center;
    gap: 5px;
    text-decoration: none;
}

.file-info .file-meta {
    font-size: 11px;
    color: #999;
    margin-top: 3px;
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

.status-approved {
    background: #d4edda;
    color: #155724;
}

.status-rejected {
    background: #f8d7da;
    color: #721c24;
}

.rejection-reason {
    margin-top: 5px;
    cursor: help;
}

.upload-info .upload-date {
    font-weight: 600;
}

.upload-info .upload-time {
    font-size: 11px;
    color: #666;
}

.upload-info .uploaded-by {
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

.osb-modal-content {
    background: #fff;
    border-radius: 4px;
    min-width: 500px;
    max-width: 90vw;
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

.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
}

.form-group textarea {
    width: 100%;
    resize: vertical;
}

.form-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
    margin-top: 20px;
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

    .action-buttons {
        flex-direction: column;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Select all functionality
    $('#select-all-docs').on('change', function() {
        $('.document-checkbox').prop('checked', this.checked);
    });

    // Individual checkbox change
    $('.document-checkbox').on('change', function() {
        if (!this.checked) {
            $('#select-all-docs').prop('checked', false);
        } else if ($('.document-checkbox:checked').length === $('.document-checkbox').length) {
            $('#select-all-docs').prop('checked', true);
        }
    });

    // Approve document
    $(document).on('click', '.approve-document', function() {
        const documentId = $(this).data('id');

        if (confirm('Are you sure you want to approve this document?')) {
            updateDocumentStatus(documentId, 'approved');
        }
    });

    // Reject document
    $(document).on('click', '.reject-document', function() {
        const documentId = $(this).data('id');

        $('#reject-document-id').val(documentId);
        $('#rejection-modal').show();
    });

    // Submit rejection
    $('#rejection-form').on('submit', function(e) {
        e.preventDefault();

        const documentId = $('#reject-document-id').val();
        const reason = $('#rejection-reason').val();

        updateDocumentStatus(documentId, 'rejected', reason);
        closeModal('rejection-modal');
    });

    // Bulk approve
    $('#bulk-approve-docs').on('click', function() {
        const checkedBoxes = $('.document-checkbox:checked');

        if (checkedBoxes.length === 0) {
            alert('Please select documents to approve.');
            return;
        }

        if (confirm(`Are you sure you want to approve ${checkedBoxes.length} document(s)?`)) {
            const documentIds = [];
            checkedBoxes.each(function() {
                documentIds.push($(this).val());
            });

            bulkUpdateDocuments(documentIds, 'approved');
        }
    });

    // View document
    $(document).on('click', '.view-document', function() {
        const documentUrl = $(this).data('url');
        const fileExtension = documentUrl.split('.').pop().toLowerCase();

        let content = '';
        if (['jpg', 'jpeg', 'png', 'gif'].includes(fileExtension)) {
            content = `<img src="${documentUrl}" style="max-width: 100%; height: auto;">`;
        } else if (fileExtension === 'pdf') {
            content = `<iframe src="${documentUrl}" style="width: 100%; height: 500px;"></iframe>`;
        } else {
            content = `<p>Preview not available. <a href="${documentUrl}" target="_blank">Download file</a></p>`;
        }

        $('#document-viewer-content').html(content);
        $('#document-viewer-modal').show();
    });

    // Delete document
    $(document).on('click', '.delete-document', function() {
        const documentId = $(this).data('id');

        if (confirm('Are you sure you want to delete this document? This action cannot be undone.')) {
            deleteDocument(documentId);
        }
    });

    // Export documents
    $('#export-documents').on('click', function() {
        const eventId = $('#event-select').val();
        if (!eventId) {
            alert('Please select an event first.');
            return;
        }

        window.location.href = `${ajaxurl}?action=export_documents&event_id=${eventId}&_wpnonce=${osb_admin.nonce}`;
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

function filterDocuments() {
    const typeFilter = document.getElementById('type-filter').value.toLowerCase();
    const statusFilter = document.getElementById('status-filter').value.toLowerCase();
    const schoolFilter = document.getElementById('school-filter').value;
    const searchFilter = document.getElementById('search-filter').value.toLowerCase();

    const rows = document.querySelectorAll('.document-row');

    rows.forEach(row => {
        const type = row.dataset.type.toLowerCase();
        const status = row.dataset.status.toLowerCase();
        const school = row.dataset.school;
        const text = row.textContent.toLowerCase();

        const typeMatch = !typeFilter || type.includes(typeFilter);
        const statusMatch = !statusFilter || status === statusFilter;
        const schoolMatch = !schoolFilter || school === schoolFilter;
        const searchMatch = !searchFilter || text.includes(searchFilter);

        row.style.display = (typeMatch && statusMatch && schoolMatch && searchMatch) ? '' : 'none';
    });
}

function clearFilters() {
    document.getElementById('type-filter').value = '';
    document.getElementById('status-filter').value = '';
    document.getElementById('school-filter').value = '';
    document.getElementById('search-filter').value = '';
    filterDocuments();
}

function updateDocumentStatus(documentId, status, reason = '') {
    jQuery.post(ajaxurl, {
        action: 'update_document_status',
        document_id: documentId,
        status: status,
        reason: reason,
        _wpnonce: osb_admin.nonce
    }, function(response) {
        if (response.success) {
            location.reload();
        } else {
            alert('Error: ' + response.data.message);
        }
    });
}

function bulkUpdateDocuments(documentIds, status) {
    jQuery.post(ajaxurl, {
        action: 'bulk_update_documents',
        document_ids: documentIds,
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

function deleteDocument(documentId) {
    jQuery.post(ajaxurl, {
        action: 'delete_document',
        document_id: documentId,
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
    if (modalId === 'rejection-modal') {
        document.getElementById('rejection-reason').value = '';
    }
}
</script>