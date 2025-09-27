<?php
/**
 * New Step-by-Step SpellingBee Dashboard Template
 * Step 1: Competition Selection
 * Step 2: Expression of Interest
 * Step 3: Student Registration
 * Step 4: Final Submission
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Variables passed from shortcode
$registration = isset($registration) ? $registration : null;
$students = isset($students) ? $students : array();
$school = isset($school) ? $school : null;

// Get database instance for events
$db = OSB_Database::getInstance();
$upcoming_events = $db->getAllEvents('upcoming');

// Get current event data for student limits
$current_event = null;
if ($registration && !empty($registration->event_id)) {
    $current_event = $db->getEvent($registration->event_id);
}

// Determine current step
$current_step = 1;

// Auto-determine step based on data first
if ($registration || $school) {
    if ($registration) {
        // Check if registration has an event selected
        if (!empty($registration->event_id)) {
            $current_step = 2; // Has event selected, proceed to EOI

            if (!empty($registration->expression_of_interest)) {
                $current_step = 3; // Has EOI, proceed to student registration
            }

            // Step 4 navigation is handled by URL override logic below

            if ($registration->status === 'submitted' || $registration->status === 'approved') {
                $current_step = 'completed';
            }
        }
        // If registration exists but no event_id, stay at step 1 to select competition
    } else {
        // Only school exists (temp token), start at step 1
        $current_step = 1;
    }
}

// Check for URL step override
if (isset($_GET['step']) && is_numeric($_GET['step'])) {
    $url_step = (int) $_GET['step'];

    if ($url_step >= 1 && $url_step <= 4) {
        // Allow going backwards to any previous step
        if ($url_step < $current_step) {
            $current_step = $url_step;
        }
        // Allow going forward to step 4 if minimum requirements are met
        elseif ($url_step == 4 && $current_step >= 3 && !empty($students) && $current_event) {
            $min_students = (int) ($current_event->min_students_per_school ?? get_option('osb_min_students_per_school', 3));
            if (count($students) >= $min_students) {
                $current_step = 4;
            }
        }
    }
}

if (!($registration || $school)) {
    // No valid authentication
    $current_step = 'login';
}
?>

<div class="osb-new-dashboard">
    <!-- Enhanced Header Section with Progress -->
    <div class="osb-dashboard-header">
        <div class="osb-header-content">
            <h1 class="osb-dashboard-title">🏆 Spelling Bee Competition Registration</h1>
            <p class="osb-dashboard-subtitle">Complete your school registration step by step</p>

            <?php if ($registration): ?>
                <div class="osb-school-info-mini">
                    <strong><?php echo esc_html($registration->school_name); ?></strong>
                    <span class="osb-status-badge osb-status-<?php echo esc_attr($registration->status); ?>">
                        <?php echo esc_html(ucfirst($registration->status)); ?>
                    </span>
                </div>
            <?php elseif ($school): ?>
                <div class="osb-school-info-mini">
                    <strong><?php echo esc_html($school->school_name); ?></strong>
                    <span class="osb-status-badge osb-status-pending">
                        Ready to Register
                    </span>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modern Arrow Progress Bar -->
    <div class="osb-arrow-progress-container">
        <div class="osb-arrow-progress">
            <div class="osb-arrow-step <?php echo $current_step >= 1 ? 'completed' : ''; ?> <?php echo $current_step == 1 ? 'active' : ''; ?>">
                <span class="osb-step-number">1</span>
                <span class="osb-step-text">Select Competition</span>
            </div>
            <div class="osb-arrow-step <?php echo $current_step >= 2 ? 'completed' : ''; ?> <?php echo $current_step == 2 ? 'active' : ''; ?>">
                <span class="osb-step-number">2</span>
                <span class="osb-step-text">Expression of Interest</span>
            </div>
            <div class="osb-arrow-step <?php echo $current_step >= 3 ? 'completed' : ''; ?> <?php echo $current_step == 3 ? 'active' : ''; ?>">
                <span class="osb-step-number">3</span>
                <span class="osb-step-text">Student Registration</span>
            </div>
            <div class="osb-arrow-step <?php echo $current_step >= 4 ? 'completed' : ''; ?> <?php echo $current_step == 4 ? 'active' : ''; ?>">
                <span class="osb-step-number">4</span>
                <span class="osb-step-text">Final Submission</span>
            </div>
        </div>
    </div>

    <!-- Step Content -->
    <div class="osb-step-content">

        <?php if ($current_step === 1): ?>
            <!-- Step 1: Competition Selection -->
            <div class="osb-step-panel" id="step-1">
                <div class="osb-step-header">
                    <h2>🎯 Step 1: Select a Competition</h2>
                    <p>Choose which spelling bee competition you would like to register for.</p>
                </div>

                <?php if (!empty($upcoming_events)): ?>
                    <div class="osb-competition-grid">
                        <?php foreach ($upcoming_events as $event): ?>
                            <div class="osb-competition-card" data-event-id="<?php echo esc_attr($event->id); ?>">
                                <div class="osb-competition-header">
                                    <h3><?php echo esc_html($event->title); ?></h3>
                                    <div class="osb-competition-year"><?php echo esc_html($event->year); ?></div>
                                </div>

                                <div class="osb-competition-details">
                                    <div class="osb-detail-item">
                                        <span class="osb-detail-label">📅 Event Date:</span>
                                        <span class="osb-detail-value"><?php echo date('F j, Y', strtotime($event->event_date)); ?></span>
                                    </div>

                                    <?php if (!empty($event->venue_name)): ?>
                                        <div class="osb-detail-item">
                                            <span class="osb-detail-label">📍 Venue:</span>
                                            <span class="osb-detail-value"><?php echo esc_html($event->venue_name); ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if (!empty($event->registration_deadline)): ?>
                                        <div class="osb-detail-item">
                                            <span class="osb-detail-label">⏰ Registration Deadline:</span>
                                            <span class="osb-detail-value"><?php echo date('F j, Y', strtotime($event->registration_deadline)); ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <div class="osb-detail-item">
                                        <span class="osb-detail-label">👥 Max Students:</span>
                                        <span class="osb-detail-value"><?php echo esc_html($event->max_students_per_school); ?> per school</span>
                                    </div>
                                </div>

                                <?php if (!empty($event->description)): ?>
                                    <div class="osb-competition-description">
                                        <?php echo wp_kses_post($event->description); ?>
                                    </div>
                                <?php endif; ?>

                                <div class="osb-competition-actions">
                                    <button class="osb-btn osb-btn-primary osb-select-competition" data-event-id="<?php echo esc_attr($event->id); ?>">
                                        Select This Competition
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="osb-no-events">
                        <div class="osb-no-events-icon">📅</div>
                        <h3>No Upcoming Competitions</h3>
                        <p>There are currently no open competitions available for registration. Please check back later or contact the organizers for more information.</p>
                    </div>
                <?php endif; ?>
            </div>

        <?php elseif ($current_step === 2): ?>
            <!-- Step 2: Enhanced Expression of Interest -->
            <div class="osb-step-panel" id="step-2">
                <?php
                // Include the enhanced EOI form
                $event = $current_event;
                include OSB_PLUGIN_PATH . 'templates/shortcodes/enhanced-eoi-form.php';
                ?>
            </div>

        <?php elseif ($current_step === 3): ?>
            <!-- Step 3: Student Registration -->
            <div class="osb-step-panel" id="step-3">
                <div class="osb-step-header">
                    <div class="osb-step-badge">
                        <span class="osb-step-number">3</span>
                        <span class="osb-step-title">Student Registration</span>
                    </div>
                    <p class="osb-step-description">Register your students for the competition. Add all students before proceeding to the final step.</p>
                </div>

                <div class="osb-step-content">
                    <!-- Registration Requirements Info -->
                    <div class="osb-section-card osb-requirements-info">
                        <div class="osb-card-header">
                            <h3 class="osb-card-title">
                                <span class="osb-icon">📋</span>
                                Registration Requirements
                            </h3>
                        </div>
                        <div class="osb-card-content">
                            <?php if ($current_event): ?>
                                <?php
                                $current_student_count = count($students);
                                $min_students = intval($current_event->min_students_per_school);
                                $max_students = intval($current_event->max_students_per_school);
                                $can_add_more = $current_student_count < $max_students;
                                $has_minimum = $current_student_count >= $min_students;
                                ?>
                                <div class="osb-requirements-grid">
                                    <div class="osb-requirement-card">
                                        <div class="osb-requirement-icon">👥</div>
                                        <div class="osb-requirement-info">
                                            <div class="osb-requirement-title">Student Count</div>
                                            <div class="osb-requirement-desc">
                                                <strong><?php echo $current_student_count; ?></strong> of
                                                <span class="osb-range"><?php echo $min_students; ?>-<?php echo $max_students; ?></span> students
                                            </div>
                                            <div class="osb-requirement-status <?php echo $has_minimum ? 'osb-status-complete' : 'osb-status-incomplete'; ?>">
                                                <?php if ($has_minimum): ?>
                                                    ✅ Minimum requirement met
                                                <?php else: ?>
                                                    ⚠️ Need <?php echo ($min_students - $current_student_count); ?> more student<?php echo ($min_students - $current_student_count) > 1 ? 's' : ''; ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="osb-requirement-card">
                                        <div class="osb-requirement-icon">📄</div>
                                        <div class="osb-requirement-info">
                                            <div class="osb-requirement-title">Required Documents</div>
                                            <div class="osb-requirement-desc">Birth certificate & Parental consent for each student</div>
                                            <div class="osb-requirement-status osb-status-info">
                                                📝 Upload during student registration
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Multi-Student Registration Form -->
                    <div class="osb-section-card osb-multi-student-form">
                        <div class="osb-card-header">
                            <h3 class="osb-card-title">
                                <span class="osb-icon">👤</span>
                                Add Students
                            </h3>
                            <div class="osb-card-subtitle">Complete information for all participating students</div>
                        </div>
                        <div class="osb-card-content">
                            <!-- Students Container -->
                            <div id="osb-students-container" class="osb-students-container">
                                <!-- Student forms will be added here dynamically -->
                            </div>

                            <!-- Add Student Controls -->
                            <?php if ($current_event && $can_add_more): ?>
                                <div class="osb-add-student-controls">
                                    <button type="button" id="osb-add-student-btn" class="osb-btn osb-btn-outline osb-btn-large">
                                        <span class="osb-btn-icon">➕</span>
                                        Add Student
                                        <span class="osb-btn-count">(<?php echo $current_student_count; ?>/<?php echo $max_students; ?>)</span>
                                    </button>
                                </div>
                            <?php elseif ($current_event && !$can_add_more): ?>
                                <div class="osb-limit-notice">
                                    <span class="osb-notice-icon">⚠️</span>
                                    <span class="osb-notice-text">Maximum student limit reached (<?php echo $max_students; ?> students)</span>
                                </div>
                            <?php endif; ?>

                            <!-- Batch Submit Controls -->
                            <div id="osb-batch-submit-controls" class="osb-batch-submit-controls" style="display: none;">
                                <div class="osb-submit-info">
                                    <h4>Ready to Register Students?</h4>
                                    <p>Review all student information and submit to add them to your registration.</p>
                                </div>
                                <div class="osb-submit-actions">
                                    <button type="button" id="osb-clear-all-btn" class="osb-btn osb-btn-secondary">
                                        <span class="osb-btn-icon">🗑️</span>
                                        Clear All
                                    </button>
                                    <button type="button" id="osb-submit-students-btn" class="osb-btn osb-btn-primary osb-btn-large">
                                        <span class="osb-btn-icon">💾</span>
                                        Register All Students
                                        <span class="osb-btn-loading" style="display: none;">
                                            <span class="osb-spinner"></span>
                                            Processing...
                                        </span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                        <?php if ($current_event && $can_add_more): ?>
                        <form id="osb-add-student-form" class="osb-professional-form">
                            <!-- Student Information Card -->
                            <div class="osb-form-section">
                                <div class="osb-form-section-header">
                                    <h4>📝 Student Information</h4>
                                </div>
                                <div class="osb-form-grid">
                                    <div class="osb-form-group">
                                        <label for="student_first_name">
                                            <span class="osb-label-text">First Name</span>
                                            <span class="osb-required">*</span>
                                        </label>
                                        <input type="text" id="student_first_name" name="first_name" class="osb-form-input" required
                                               placeholder="Enter student's first name">
                                    </div>

                                    <div class="osb-form-group">
                                        <label for="student_last_name">
                                            <span class="osb-label-text">Last Name</span>
                                            <span class="osb-required">*</span>
                                        </label>
                                        <input type="text" id="student_last_name" name="last_name" class="osb-form-input" required
                                               placeholder="Enter student's last name">
                                    </div>

                                    <div class="osb-form-group">
                                        <label for="student_grade">
                                            <span class="osb-label-text">Grade/Class</span>
                                            <span class="osb-required">*</span>
                                        </label>
                                        <select id="student_grade" name="grade" class="osb-form-select" required>
                                            <option value="">Select Grade</option>
                                            <option value="Primary 1">Primary 1</option>
                                            <option value="Primary 2">Primary 2</option>
                                            <option value="Primary 3">Primary 3</option>
                                            <option value="Primary 4">Primary 4</option>
                                            <option value="Primary 5">Primary 5</option>
                                            <option value="Primary 6">Primary 6</option>
                                            <option value="JSS1">JSS1</option>
                                            <option value="JSS2">JSS2</option>
                                            <option value="JSS3">JSS3</option>
                                            <option value="SS1">SS1</option>
                                            <option value="SS2">SS2</option>
                                            <option value="SS3">SS3</option>
                                        </select>
                                    </div>

                                    <div class="osb-form-group">
                                        <label for="student_dob">
                                            <span class="osb-label-text">Date of Birth</span>
                                            <span class="osb-required">*</span>
                                        </label>
                                        <input type="date" id="student_dob" name="date_of_birth" class="osb-form-input" required>
                                    </div>

                                    <div class="osb-form-group">
                                        <label for="parent_name">
                                            <span class="osb-label-text">Parent/Guardian Name</span>
                                            <span class="osb-required">*</span>
                                        </label>
                                        <input type="text" id="parent_name" name="parent_name" class="osb-form-input" required
                                               placeholder="Full name of parent or guardian">
                                    </div>

                                    <div class="osb-form-group">
                                        <label for="parent_email">
                                            <span class="osb-label-text">Parent/Guardian Email</span>
                                            <span class="osb-required">*</span>
                                        </label>
                                        <input type="email" id="parent_email" name="parent_email" class="osb-form-input" required
                                               placeholder="parent@example.com">
                                    </div>
                                </div>
                            </div>

                            <!-- Required Documents Card -->
                            <div class="osb-form-section">
                                <div class="osb-form-section-header">
                                    <h4>📋 Required Documents</h4>
                                    <p class="osb-section-description">Upload clear, legible copies of all required documents</p>
                                </div>

                                <div class="osb-documents-grid">
                                    <!-- Birth Certificate -->
                                    <div class="osb-document-card">
                                        <div class="osb-document-info">
                                            <div class="osb-document-icon">🎂</div>
                                            <div class="osb-document-details">
                                                <h5>Student Birth Certificate <span class="osb-required">*</span></h5>
                                                <p>Official birth certificate showing student's full name and date of birth</p>
                                            </div>
                                        </div>
                                        <div class="osb-upload-zone-mini" onclick="triggerFileInput('birth_certificate')">
                                            <div class="osb-upload-content-mini">
                                                <div class="osb-upload-icon-mini">📄</div>
                                                <div class="osb-upload-text-mini">
                                                    <strong>Click to upload</strong>
                                                    <small>PDF, JPG, PNG • Max 5MB</small>
                                                </div>
                                            </div>
                                            <input type="file" id="birth_certificate" name="birth_certificate"
                                                   accept=".pdf,.jpg,.jpeg,.png" required style="display: none;">
                                        </div>
                                        <div id="birth_certificate_preview" class="osb-file-preview" style="display: none;"></div>
                                    </div>

                                    <!-- Parental Consent -->
                                    <div class="osb-document-card">
                                        <div class="osb-document-info">
                                            <div class="osb-document-icon">✍️</div>
                                            <div class="osb-document-details">
                                                <h5>Parental Consent Form <span class="osb-required">*</span></h5>
                                                <p>Completed and signed parental consent form</p>
                                                <a href="#" class="osb-download-link" data-template="parental-consent">
                                                    📥 Download Template
                                                </a>
                                            </div>
                                        </div>
                                        <div class="osb-upload-zone-mini" onclick="triggerFileInput('parental_consent')">
                                            <div class="osb-upload-content-mini">
                                                <div class="osb-upload-icon-mini">📝</div>
                                                <div class="osb-upload-text-mini">
                                                    <strong>Click to upload</strong>
                                                    <small>PDF only • Max 5MB</small>
                                                </div>
                                            </div>
                                            <input type="file" id="parental_consent" name="parental_consent"
                                                   accept=".pdf" required style="display: none;">
                                        </div>
                                        <div id="parental_consent_preview" class="osb-file-preview" style="display: none;"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="osb-form-actions-section">
                                <button type="submit" class="osb-btn osb-btn-primary osb-btn-large">
                                    <span class="osb-btn-icon">👤</span>
                                    Add Student to Registration
                                </button>
                            </div>
                        </form>
                        <?php else: ?>
                        <div class="osb-form-disabled-message">
                            <div class="osb-limit-reached-card">
                                <div class="osb-limit-icon">🚫</div>
                                <h4>Maximum Students Registered</h4>
                                <p>You have reached the maximum limit of <?php echo $max_students; ?> students per school for this event.</p>
                                <p>If you need to make changes, please remove an existing student before adding a new one.</p>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                    <!-- Registered Students Section -->
                    <?php if (!empty($students)): ?>
                    <div class="osb-section-card osb-students-overview-section">
                        <div class="osb-section-header">
                            <div class="osb-section-icon">
                                <span class="osb-icon-circle">👥</span>
                            </div>
                            <div class="osb-section-content">
                                <h3>Registered Students (<?php echo count($students); ?>)</h3>
                                <p>Review and manage your registered students</p>
                            </div>
                        </div>

                        <div class="osb-students-showcase">
                            <?php foreach ($students as $student): ?>
                                <div class="osb-student-showcase-card">
                                    <div class="osb-student-avatar">
                                        <span class="osb-avatar-initials">
                                            <?php echo esc_html(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)); ?>
                                        </span>
                                    </div>
                                    <div class="osb-student-showcase-info">
                                        <h4><?php echo esc_html($student->first_name . ' ' . $student->last_name); ?></h4>
                                        <div class="osb-student-meta">
                                            <span class="osb-meta-item">
                                                <span class="osb-meta-icon">🎓</span>
                                                Grade <?php echo esc_html($student->grade); ?>
                                            </span>
                                            <span class="osb-meta-item">
                                                <span class="osb-meta-icon">🎂</span>
                                                Age <?php echo esc_html($student->age); ?>
                                            </span>
                                        </div>
                                        <div class="osb-parent-info">
                                            <span class="osb-meta-icon">👤</span>
                                            Parent: <?php echo esc_html($student->parent_name); ?>
                                        </div>
                                        <div class="osb-document-status">
                                            <span class="osb-status-badge osb-status-complete">
                                                <span class="osb-status-icon">✅</span>
                                                Documents Complete
                                            </span>
                                        </div>
                                    </div>
                                    <div class="osb-student-showcase-actions">
                                        <button class="osb-btn osb-btn-small osb-btn-outline" onclick="editStudent(<?php echo $student->id; ?>)">
                                            <span class="osb-btn-icon">✏️</span>
                                            Edit
                                        </button>
                                        <button class="osb-btn osb-btn-small osb-btn-danger-outline" onclick="removeStudent(<?php echo $student->id; ?>)">
                                            <span class="osb-btn-icon">🗑️</span>
                                            Remove
                                        </button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Navigation Section -->
                    <div class="osb-section-card osb-navigation-section">
                        <div class="osb-navigation-content">
                            <div class="osb-nav-info">
                                <?php if ($current_event): ?>
                                    <?php
                                    $min_students = intval($current_event->min_students_per_school ?? get_option('osb_min_students_per_school', 3));
                                    $max_students = intval($current_event->max_students_per_school);
                                    $current_count = count($students);
                                    $has_minimum_students = $current_count >= $min_students;
                                    $within_max_limit = $current_count <= $max_students;
                                    ?>
                                    <?php if ($has_minimum_students && $within_max_limit): ?>
                                        <h4>✅ Ready to continue!</h4>
                                        <p>You have registered <?php echo $current_count; ?> student<?php echo $current_count != 1 ? 's' : ''; ?>
                                           (minimum: <?php echo $min_students; ?>, maximum: <?php echo $max_students; ?>). You can now proceed to final submission.</p>
                                    <?php else: ?>
                                        <h4>⚠️ Registration Requirements</h4>
                                        <p>You need at least <?php echo $min_students; ?> students and no more than <?php echo $max_students; ?> students to complete registration.
                                           You currently have <?php echo $current_count; ?> student<?php echo $current_count != 1 ? 's' : ''; ?> registered.</p>
                                        <div class="osb-requirement-status">
                                            <span class="osb-requirement-item">
                                                <span class="osb-requirement-number"><?php echo $current_count; ?>/<?php echo $min_students; ?>-<?php echo $max_students; ?></span>
                                                <span class="osb-requirement-text">Students Required Range</span>
                                            </span>
                                        </div>
                                    <?php endif; ?>
                                <?php else: ?>
                                    <h4>Ready to continue?</h4>
                                    <p>Make sure you've added all participating students before proceeding to the final submission.</p>
                                <?php endif; ?>
                            </div>
                            <div class="osb-navigation-actions">
                                <button type="button" class="osb-btn osb-btn-secondary osb-btn-large" onclick="goBackToStep(2)">
                                    <span class="osb-btn-icon">⬅️</span>
                                    Back to Expression of Interest
                                </button>
                                <button class="osb-btn osb-btn-primary osb-btn-large"
                                        onclick="proceedToFinalStep()"
                                        <?php if ($current_event && (!$has_minimum_students || !$within_max_limit)): ?>disabled<?php endif; ?>>
                                    <span class="osb-btn-icon">📋</span>
                                    Proceed to Final Submission
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($current_step === 4): ?>
            <!-- Step 4: Final Submission -->
            <div class="osb-step-panel" id="step-4">
                <div class="osb-step-header">
                    <div class="osb-step-badge">
                        <span class="osb-step-number">4</span>
                        <span class="osb-step-title">Final Submission</span>
                    </div>
                    <p class="osb-step-description">Review your registration details carefully and submit for admin approval.</p>
                </div>

                <div class="osb-step-content">
                    <!-- Registration Summary Card -->
                    <div class="osb-section-card osb-review-summary">
                        <div class="osb-card-header">
                            <h3 class="osb-card-title">
                                <span class="osb-icon">📋</span>
                                Registration Summary
                            </h3>
                            <div class="osb-card-subtitle">Review all details before submission</div>
                        </div>

                        <div class="osb-card-content">
                            <!-- Competition Details -->
                            <div class="osb-summary-section">
                                <h4 class="osb-summary-title">
                                    <span class="osb-icon">🏆</span>
                                    Competition Details
                                </h4>
                                <div class="osb-summary-grid">
                                    <div class="osb-summary-item">
                                        <div class="osb-summary-label">Competition</div>
                                        <div class="osb-summary-value"><?php echo esc_html($current_event->title); ?></div>
                                    </div>
                                    <div class="osb-summary-item">
                                        <div class="osb-summary-label">Event Date</div>
                                        <div class="osb-summary-value"><?php echo date('F j, Y', strtotime($current_event->event_date)); ?></div>
                                    </div>
                                    <div class="osb-summary-item">
                                        <div class="osb-summary-label">Venue</div>
                                        <div class="osb-summary-value"><?php echo esc_html($current_event->venue_name ?: 'TBD'); ?></div>
                                    </div>
                                    <div class="osb-summary-item">
                                        <div class="osb-summary-label">School</div>
                                        <div class="osb-summary-value"><?php echo esc_html($registration->school_name); ?></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Students Summary -->
                            <div class="osb-summary-section">
                                <h4 class="osb-summary-title">
                                    <span class="osb-icon">👨‍🎓</span>
                                    Registered Students (<?php echo count($students); ?>/<?php echo $current_event->max_students_per_school; ?>)
                                </h4>
                                <div class="osb-students-preview">
                                    <?php foreach ($students as $index => $student): ?>
                                        <div class="osb-student-preview-card">
                                            <div class="osb-student-preview-header">
                                                <span class="osb-student-number"><?php echo ($index + 1); ?></span>
                                                <div class="osb-student-info">
                                                    <div class="osb-student-name"><?php echo esc_html($student->first_name . ' ' . $student->last_name); ?></div>
                                                    <div class="osb-student-details"><?php echo esc_html($student->grade_level); ?> • <?php echo date('M j, Y', strtotime($student->birth_date)); ?></div>
                                                </div>
                                            </div>
                                            <div class="osb-student-status">
                                                <span class="osb-status-badge osb-status-ready">✅ Ready</span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Requirements Check -->
                            <div class="osb-summary-section">
                                <h4 class="osb-summary-title">
                                    <span class="osb-icon">✅</span>
                                    Requirements Checklist
                                </h4>
                                <div class="osb-requirements-checklist">
                                    <div class="osb-requirement-item osb-requirement-completed">
                                        <span class="osb-requirement-icon">✅</span>
                                        <span class="osb-requirement-text">Competition selected</span>
                                    </div>
                                    <div class="osb-requirement-item osb-requirement-completed">
                                        <span class="osb-requirement-icon">✅</span>
                                        <span class="osb-requirement-text">Expression of interest submitted</span>
                                    </div>
                                    <div class="osb-requirement-item osb-requirement-completed">
                                        <span class="osb-requirement-icon">✅</span>
                                        <span class="osb-requirement-text">Minimum <?php echo ($current_event->min_students_per_school ?? get_option('osb_min_students_per_school', 3)); ?> students registered</span>
                                    </div>
                                    <div class="osb-requirement-item osb-requirement-completed">
                                        <span class="osb-requirement-icon">✅</span>
                                        <span class="osb-requirement-text">All student documents uploaded</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Final Agreement and Submission -->
                    <div class="osb-section-card osb-submission-card">
                        <div class="osb-card-header">
                            <h3 class="osb-card-title">
                                <span class="osb-icon">📝</span>
                                Final Agreement
                            </h3>
                            <div class="osb-card-subtitle">Please read and agree to submit your registration</div>
                        </div>

                        <div class="osb-card-content">
                            <div class="osb-agreement-box">
                                <div class="osb-agreement-content">
                                    <h4>Terms & Conditions</h4>
                                    <div class="osb-agreement-text">
                                        <p>By submitting this registration, I confirm that:</p>
                                        <ul>
                                            <li>All information provided is accurate and complete</li>
                                            <li>All student documents are genuine and valid</li>
                                            <li>I understand that false information may result in disqualification</li>
                                            <li>I agree to abide by the competition rules and regulations</li>
                                            <li>I authorize the use of student photos/videos for promotional purposes</li>
                                        </ul>
                                    </div>
                                </div>

                                <div class="osb-agreement-checkbox">
                                    <label class="osb-checkbox-container">
                                        <input type="checkbox" id="final-agreement" required>
                                        <span class="osb-checkmark"></span>
                                        <span class="osb-checkbox-text">
                                            I have read and agree to the terms and conditions above. I confirm that all information is accurate and complete.
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="osb-submission-actions">
                                <button type="button" class="osb-btn osb-btn-secondary osb-btn-large" onclick="goBackToStep(3)">
                                    <span class="osb-btn-icon">←</span>
                                    Back to Students
                                </button>
                                <button class="osb-btn osb-btn-primary osb-btn-large osb-btn-submit" id="submit-final-registration" disabled>
                                    <span class="osb-btn-icon">🚀</span>
                                    Submit for Approval
                                    <span class="osb-btn-loading" style="display: none;">
                                        <span class="osb-spinner"></span>
                                        Submitting...
                                    </span>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php elseif ($current_step === 'completed'): ?>
            <!-- Completed State -->
            <div class="osb-step-panel" id="step-completed">
                <div class="osb-completion-message">
                    <div class="osb-completion-icon">🎉</div>
                    <h2>Registration Submitted!</h2>
                    <p>Your registration has been submitted and is now under review by the administrators.</p>

                    <div class="osb-completion-details">
                        <div class="osb-detail-grid">
                            <div class="osb-detail-item">
                                <label>Submission Date:</label>
                                <span><?php echo date('F j, Y g:i A', strtotime($registration->created_at)); ?></span>
                            </div>
                            <div class="osb-detail-item">
                                <label>Registration Status:</label>
                                <span class="osb-status-badge osb-status-<?php echo esc_attr($registration->status); ?>">
                                    <?php echo esc_html(ucfirst($registration->status)); ?>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="osb-next-steps">
                        <h3>What happens next?</h3>
                        <ul>
                            <li>✅ Your registration has been received</li>
                            <li>⏳ Our team will review your submission and documents</li>
                            <li>📧 You'll receive an email notification with the decision</li>
                            <li>🏆 If approved, you'll get competition details and instructions</li>
                        </ul>
                    </div>
                </div>
            </div>

        <?php elseif ($current_step === 'login'): ?>
            <!-- Login/Authentication Required -->
            <div class="osb-step-panel" id="step-login">
                <div class="osb-login-message">
                    <div class="osb-login-icon">🔐</div>
                    <h2>Authentication Required</h2>
                    <p>Please provide a valid access token to continue with your registration.</p>

                    <div class="osb-login-info">
                        <h3>How to access your dashboard:</h3>
                        <ul>
                            <li>📧 Check your email for the dashboard access link</li>
                            <li>🔗 Click the link in the email to access your dashboard</li>
                            <li>📞 Contact support if you need assistance</li>
                        </ul>
                    </div>

                    <div class="osb-login-actions">
                        <a href="<?php echo home_url('/register/'); ?>" class="osb-btn osb-btn-primary">
                            🏫 Register Your School
                        </a>
                        <a href="mailto:<?php echo get_option('osb_contact_email', get_option('admin_email')); ?>" class="osb-btn osb-btn-secondary">
                            📧 Contact Support
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
/* Enhanced Dashboard Styles - Professional Blue Theme */
.osb-new-dashboard {
    max-width: 1400px;
    margin: 0 auto;
    padding: 2rem;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
    background: linear-gradient(135deg, #f1f7ff 0%, #e6f3ff 100%);
    border-radius: 20px;
    min-height: 100vh;
}

/* Modern Arrow Progress Bar */
.osb-arrow-progress-container {
    margin: 2rem 0;
    display: flex;
    justify-content: center;
    padding: 1rem;
}

.osb-arrow-progress {
    display: flex;
    align-items: center;
    position: relative;
    background: white;
    border-radius: 8px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    overflow: hidden;
}

.osb-arrow-step {
    position: relative;
    display: flex;
    align-items: center;
    padding: 1rem 2rem;
    background: #e9ecef;
    color: #6c757d;
    font-weight: 600;
    transition: all 0.3s ease;
    z-index: 1;
    min-width: 150px;
    justify-content: center;
    gap: 0.5rem;
}

.osb-arrow-step:not(:last-child)::after {
    content: '';
    position: absolute;
    top: 0;
    right: -20px;
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 30px 0 30px 20px;
    border-color: transparent transparent transparent #e9ecef;
    z-index: 2;
    transition: border-left-color 0.3s ease;
}

.osb-arrow-step:not(:first-child) {
    margin-left: -20px;
    padding-left: 2.5rem;
}

.osb-arrow-step.completed {
    background: #28a745;
    color: white;
}

.osb-arrow-step.completed:not(:last-child)::after {
    border-left-color: #28a745;
}

.osb-arrow-step.active {
    background: #0052cc;
    color: white;
    box-shadow: 0 4px 15px rgba(0,82,204,0.3);
}

.osb-arrow-step.active:not(:last-child)::after {
    border-left-color: #0052cc;
}

.osb-step-number {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 24px;
    height: 24px;
    border-radius: 50%;
    background: rgba(255,255,255,0.2);
    font-weight: 700;
    font-size: 0.9rem;
}

.osb-step-text {
    font-size: 0.9rem;
    font-weight: 600;
}

/* Professional Step Panels */
.osb-step-panel {
    background: white;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.08);
    overflow: hidden;
    margin-bottom: 2rem;
}

.osb-step-header {
    padding: 2rem;
    background: linear-gradient(135deg, #0052cc 0%, #0066ff 100%);
    border-bottom: 1px solid #0044aa;
    color: white;
}

.osb-step-header h2 {
    color: white;
    margin: 0 0 1rem 0;
}

.osb-step-header p {
    color: rgba(255,255,255,0.9);
    margin: 0;
}

.osb-step-badge {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.osb-step-number {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0052cc 0%, #0066ff 100%);
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 800;
    font-size: 1.5rem;
    box-shadow: 0 4px 16px rgba(0,82,204,0.3);
}

.osb-step-title {
    font-size: 1.8rem;
    font-weight: 700;
    color: #2c3e50;
}

.osb-step-description {
    font-size: 1.1rem;
    color: #6c757d;
    margin: 0;
    line-height: 1.5;
}

/* EOI Workflow Design */
.osb-eoi-workflow {
    padding: 2rem;
    display: flex;
    align-items: center;
    gap: 1.5rem;
    flex-wrap: wrap;
    justify-content: center;
}

.osb-workflow-step {
    flex: 1;
    min-width: 280px;
    max-width: 350px;
    background: white;
    border-radius: 12px;
    padding: 1.5rem;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
    position: relative;
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}

.osb-workflow-step:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border-color: #0052cc;
}

.osb-workflow-header {
    display: flex;
    align-items: flex-start;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.osb-workflow-icon {
    flex-shrink: 0;
}

.osb-icon-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0052cc 0%, #0066ff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    box-shadow: 0 4px 12px rgba(0,82,204,0.3);
}

.osb-workflow-content h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1.2rem;
    font-weight: 700;
    color: #2c3e50;
}

.osb-workflow-content p {
    margin: 0;
    color: #6c757d;
    font-size: 0.95rem;
    line-height: 1.4;
}

.osb-workflow-action {
    display: flex;
    justify-content: center;
}

.osb-workflow-arrow {
    font-size: 2rem;
    color: #0052cc;
    font-weight: bold;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 40px;
    height: 40px;
    background: white;
    border-radius: 50%;
    border: 3px solid #0052cc;
    box-shadow: 0 4px 12px rgba(0,82,204,0.2);
    flex-shrink: 0;
}

/* Enhanced Buttons */
.osb-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.75rem 1.5rem;
    border: none;
    border-radius: 8px;
    font-size: 0.95rem;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.osb-btn-download {
    background: linear-gradient(135deg, #17a2b8 0%, #138496 100%);
    color: white;
    border: 2px solid #17a2b8;
}

.osb-btn-download:hover {
    background: linear-gradient(135deg, #138496 0%, #117a8b 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(23,162,184,0.3);
}

.osb-btn-primary {
    background: linear-gradient(135deg, #0052cc 0%, #0066ff 100%);
    color: white;
    border: 2px solid #0052cc;
}

.osb-btn-primary:hover:not(:disabled) {
    background: linear-gradient(135deg, #0066ff 0%, #0052cc 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,82,204,0.3);
}

.osb-btn-secondary {
    background: #f8f9fa;
    color: #495057;
    border: 2px solid #dee2e6;
}

.osb-btn-secondary:hover:not(:disabled) {
    background: #e9ecef;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.osb-upload-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

.osb-submission-actions {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

.osb-proceed-section {
    display: flex;
    gap: 1rem;
    align-items: center;
    flex-wrap: wrap;
}

.osb-btn-large {
    padding: 1rem 2rem;
    font-size: 1.1rem;
}

.osb-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none !important;
    box-shadow: none !important;
}

.osb-instruction-badge {
    background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%);
    color: #856404;
    padding: 0.75rem 1.5rem;
    border-radius: 25px;
    font-weight: 600;
    border: 2px solid #ffc107;
    font-size: 0.9rem;
}

/* Modern File Upload */
.osb-file-upload-modern {
    width: 100%;
}

.osb-upload-zone {
    border: 3px dashed #dee2e6;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f8f9fa;
    margin-bottom: 1rem;
}

.osb-upload-zone:hover {
    border-color: #0052cc;
    background: #f0f8ff;
    transform: translateY(-2px);
}

.osb-upload-zone.osb-dragover {
    border-color: #28a745;
    background: #f0fff4;
    transform: scale(1.02);
}

.osb-upload-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 1rem;
}

.osb-upload-icon {
    font-size: 3rem;
    color: #6c757d;
}

.osb-upload-text {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.osb-upload-text strong {
    font-size: 1.1rem;
    color: #2c3e50;
}

.osb-upload-text small {
    color: #6c757d;
    font-size: 0.85rem;
}

.osb-file-info {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #e8f5e8;
    border: 2px solid #28a745;
    border-radius: 8px;
    padding: 1rem;
    margin-bottom: 1rem;
}

.osb-file-details {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.osb-file-name {
    font-weight: 600;
    color: #2c3e50;
}

.osb-file-size {
    font-size: 0.85rem;
    color: #6c757d;
}

.osb-remove-file {
    background: #dc3545;
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    cursor: pointer;
    font-weight: bold;
    transition: all 0.3s ease;
}

.osb-remove-file:hover {
    background: #c82333;
    transform: scale(1.1);
}

.osb-upload-actions {
    display: flex;
    justify-content: center;
    margin-top: 1rem;
}

/* Enhanced Dashboard Header */
.osb-dashboard-header {
    background: linear-gradient(135deg, #0052cc 0%, #003d99 50%, #004080 100%);
    color: white;
    padding: 3rem 2rem;
    border-radius: 20px;
    margin-bottom: 2rem;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 40px rgba(0, 82, 204, 0.3);
}

.osb-dashboard-header::before {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 200 200"><defs><radialGradient id="grad1" cx="50%" cy="50%" r="50%"><stop offset="0%" style="stop-color:rgba(255,255,255,0.15);stop-opacity:1" /><stop offset="100%" style="stop-color:rgba(255,255,255,0);stop-opacity:0" /></radialGradient></defs><circle cx="30" cy="40" r="15" fill="url(%23grad1)"/><circle cx="170" cy="60" r="20" fill="rgba(255,255,255,0.08)"/><circle cx="80" cy="180" r="12" fill="rgba(255,255,255,0.1)"/><circle cx="150" cy="150" r="8" fill="rgba(255,255,255,0.12)"/><polygon points="20,20 40,10 50,35 25,40" fill="rgba(255,255,255,0.05)"/><polygon points="160,30 180,20 185,45 165,50" fill="rgba(255,255,255,0.07)"/></svg>');
    pointer-events: none;
}

.osb-dashboard-header::after {
    content: '';
    position: absolute;
    top: -50%;
    right: -50%;
    width: 100%;
    height: 100%;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
    animation: float 6s ease-in-out infinite;
    pointer-events: none;
}

@keyframes float {
    0%, 100% { transform: translateY(0px) rotate(0deg); }
    50% { transform: translateY(-10px) rotate(5deg); }
}

.osb-header-content {
    position: relative;
    z-index: 2;
    text-align: center;
    margin-bottom: 2rem;
}

.osb-dashboard-title {
    font-size: 3rem;
    font-weight: 800;
    margin: 0 0 0.5rem 0;
    color: white !important;
    text-shadow: 0 4px 15px rgba(0,0,0,0.2);
}

.osb-dashboard-subtitle {
    font-size: 1.2rem;
    opacity: 0.9;
    margin: 0 0 1.5rem 0;
    font-weight: 400;
    color: white;
}

.osb-school-info-mini {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 15px;
    color: white;
    margin-top: 1rem;
}

.osb-school-info-mini strong {
    color: white;
    font-size: 1.1rem;
}

.osb-status-badge {
    padding: 8px 16px;
    border-radius: 25px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: uppercase;
    background: rgba(255,255,255,0.2);
    color: white;
    border: 2px solid rgba(255,255,255,0.3);
    backdrop-filter: blur(10px);
}

.osb-status-pending { background: #ffc107; color: #856404; }
.osb-status-approved { background: #28a745; color: white; }
.osb-status-submitted { background: #17a2b8; color: white; }

/* Step Panels - Updated to match blue theme */
.osb-step-panel {
    background: white;
    border-radius: 16px;
    box-shadow: 0 8px 32px rgba(0,0,0,0.08);
    overflow: hidden;
    margin-bottom: 2rem;
}

/* Competition Selection */
.osb-competition-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
    gap: 30px;
    padding: 30px;
}

.osb-competition-card {
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 25px;
    transition: all 0.3s ease;
    cursor: pointer;
}

.osb-competition-card:hover {
    border-color: #0052cc;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,82,204,0.15);
    background: linear-gradient(135deg, #f8fcff 0%, #f0f8ff 100%);
}

.osb-competition-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.osb-competition-header h3 {
    margin: 0;
    color: #333;
    font-size: 1.4rem;
}

.osb-competition-year {
    background: linear-gradient(135deg, #0052cc 0%, #0066ff 100%);
    color: white;
    padding: 6px 14px;
    border-radius: 20px;
    font-weight: bold;
    box-shadow: 0 2px 8px rgba(0,82,204,0.2);
}

.osb-competition-details {
    margin-bottom: 20px;
}

.osb-detail-item {
    display: flex;
    margin-bottom: 10px;
}

.osb-detail-label {
    min-width: 140px;
    font-weight: 600;
    color: #666;
}

.osb-detail-value {
    color: #333;
}

.osb-competition-description {
    background: linear-gradient(135deg, #f8fcff 0%, #f0f8ff 100%);
    border: 1px solid #e3f2fd;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 20px;
    color: #666;
    line-height: 1.6;
    box-shadow: 0 2px 8px rgba(0,82,204,0.05);
}

/* Updated Button Styles - Professional Blue Theme */
.osb-btn {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    position: relative;
    overflow: hidden;
}

.osb-btn-primary {
    background: linear-gradient(135deg, #0052cc 0%, #0066ff 100%);
    color: white;
    border: 2px solid #0052cc;
}

.osb-btn-primary:hover:not(:disabled) {
    background: linear-gradient(135deg, #0066ff 0%, #0052cc 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,82,204,0.3);
}

.osb-btn-secondary {
    background: #f8f9fa;
    color: #495057;
    border: 2px solid #dee2e6;
}

.osb-btn-secondary:hover:not(:disabled) {
    background: #e9ecef;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.osb-btn-large {
    padding: 16px 32px;
    font-size: 16px;
}

.osb-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
}

/* Form Styles */
.osb-form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.osb-form-group {
    display: flex;
    flex-direction: column;
}

.osb-form-group label {
    margin-bottom: 5px;
    font-weight: 600;
    color: #333;
}

.osb-form-group input,
.osb-form-group select {
    padding: 10px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.required {
    color: #dc3545;
}

/* File Upload Styles */
.osb-upload-dropzone {
    border: 2px dashed #ddd;
    border-radius: 8px;
    padding: 40px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
}

.osb-upload-dropzone:hover {
    border-color: #0052cc;
    background: linear-gradient(135deg, #f8fcff 0%, #f0f8ff 100%);
    transform: translateY(-2px);
}

.osb-upload-icon {
    font-size: 3rem;
    margin-bottom: 15px;
}

/* Student Cards */
.osb-students-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.osb-student-card {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.osb-student-card:hover {
    border-color: #0052cc;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,82,204,0.1);
}

.osb-student-info h4 {
    margin: 0 0 5px 0;
    color: #333;
}

.osb-student-info p {
    margin: 0 0 5px 0;
    color: #666;
}

.osb-student-actions {
    display: flex;
    gap: 10px;
}

.osb-btn-small {
    padding: 6px 12px;
    font-size: 12px;
}

/* Enhanced Mobile Responsiveness */
@media (max-width: 768px) {
    .osb-new-dashboard {
        padding: 1rem;
    }

    .osb-dashboard-header {
        padding: 2rem 1rem;
        border-radius: 15px;
    }

    .osb-dashboard-title {
        font-size: 2rem;
    }

    .osb-dashboard-subtitle {
        font-size: 1rem;
    }

    .osb-arrow-progress-container {
        margin: 1rem 0;
        padding: 0.5rem;
    }

    .osb-arrow-progress {
        flex-direction: column;
        gap: 0.5rem;
        box-shadow: none;
        background: transparent;
    }

    .osb-arrow-step {
        min-width: auto;
        width: 100%;
        padding: 0.75rem 1rem;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        margin-left: 0 !important;
        justify-content: flex-start;
    }

    .osb-arrow-step:not(:last-child)::after {
        display: none;
    }

    .osb-step-text {
        font-size: 0.8rem;
    }

    /* Step 2 Mobile Responsive */
    .osb-eoi-workflow {
        flex-direction: column;
        padding: 1rem;
        gap: 1rem;
    }

    .osb-workflow-step {
        min-width: auto;
        max-width: none;
    }

    .osb-workflow-arrow {
        transform: rotate(90deg);
        width: 30px;
        height: 30px;
        font-size: 1.5rem;
    }

    .osb-step-header {
        padding: 1.5rem;
    }

    .osb-step-number {
        width: 40px;
        height: 40px;
        font-size: 1.2rem;
    }

    .osb-step-title {
        font-size: 1.4rem;
    }

    .osb-upload-zone {
        padding: 1.5rem;
    }

    .osb-school-info-mini {
        flex-direction: column;
        gap: 10px;
    }

    .osb-competition-grid {
        grid-template-columns: 1fr;
        padding: 20px;
    }

    .osb-form-grid {
        grid-template-columns: 1fr;
    }
}

/* Login/Authentication Styles */
.osb-login-message {
    text-align: center;
    padding: 60px 30px;
}

.osb-login-icon {
    font-size: 4rem;
    margin-bottom: 20px;
}

.osb-login-message h2 {
    margin: 0 0 15px 0;
    color: #333;
    font-size: 2rem;
}

.osb-login-message p {
    margin: 0 0 30px 0;
    color: #666;
    font-size: 1.1rem;
}

.osb-login-info {
    background: linear-gradient(135deg, #f8fcff 0%, #f0f8ff 100%);
    border: 2px solid #e3f2fd;
    border-radius: 12px;
    padding: 25px;
    margin: 30px 0;
    text-align: left;
    box-shadow: 0 4px 12px rgba(0,82,204,0.08);
}

.osb-login-info h3 {
    margin: 0 0 15px 0;
    color: #333;
}

.osb-login-info ul {
    margin: 0;
    padding-left: 20px;
}

.osb-login-info li {
    margin-bottom: 10px;
    color: #666;
}

.osb-login-actions {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
}

@media (max-width: 768px) {
    .osb-login-actions {
        flex-direction: column;
        align-items: center;
    }

    .osb-login-actions .osb-btn {
        width: 100%;
        max-width: 300px;
    }
}

/* Professional Step 3 Student Registration Styles */
.osb-step-content {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.osb-section-card {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 8px 32px rgba(0,0,0,0.08);
    border: 2px solid #f1f7ff;
    transition: all 0.3s ease;
}

.osb-section-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 40px rgba(0,82,204,0.12);
    border-color: #e3f2fd;
}

.osb-section-header {
    display: flex;
    align-items: center;
    gap: 1.5rem;
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 2px solid #f1f7ff;
}

.osb-section-icon .osb-icon-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0052cc 0%, #0066ff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: white;
    box-shadow: 0 4px 12px rgba(0,82,204,0.3);
}

.osb-section-content h3 {
    margin: 0 0 0.5rem 0;
    color: #1a365d;
    font-size: 1.5rem;
    font-weight: 600;
}

.osb-section-content p {
    margin: 0;
    color: #64748b;
    font-size: 1rem;
}

.osb-professional-form {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.osb-form-section {
    background: #f8fcff;
    border-radius: 12px;
    padding: 1.5rem;
    border: 1px solid #e3f2fd;
}

.osb-form-section-header {
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 1px solid #dbeafe;
}

.osb-form-section-header h4 {
    margin: 0 0 0.5rem 0;
    color: #1e40af;
    font-size: 1.2rem;
    font-weight: 600;
}

.osb-section-description {
    margin: 0;
    color: #64748b;
    font-size: 0.9rem;
}

.osb-form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.osb-form-group {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
}

.osb-form-group label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-weight: 600;
    color: #374151;
}

.osb-label-text {
    font-size: 0.95rem;
}

.osb-required {
    color: #dc2626;
    font-weight: bold;
}

.osb-form-input,
.osb-form-select {
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: white;
}

.osb-form-input:focus,
.osb-form-select:focus {
    outline: none;
    border-color: #0052cc;
    box-shadow: 0 0 0 3px rgba(0,82,204,0.1);
    transform: translateY(-1px);
}

.osb-form-input::placeholder {
    color: #9ca3af;
}

.osb-documents-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 1.5rem;
}

.osb-document-card {
    background: white;
    border: 2px solid #f1f5f9;
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
}

.osb-document-card:hover {
    border-color: #0052cc;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,82,204,0.15);
}

.osb-document-info {
    display: flex;
    gap: 1rem;
    margin-bottom: 1.5rem;
}

.osb-document-icon {
    font-size: 2rem;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 50px;
    height: 50px;
    background: linear-gradient(135deg, #f8fcff 0%, #e3f2fd 100%);
    border-radius: 12px;
    flex-shrink: 0;
}

.osb-document-details h5 {
    margin: 0 0 0.5rem 0;
    color: #1e293b;
    font-size: 1.1rem;
    font-weight: 600;
}

.osb-document-details p {
    margin: 0 0 0.5rem 0;
    color: #64748b;
    font-size: 0.9rem;
    line-height: 1.4;
}

.osb-download-link {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    color: #0052cc;
    text-decoration: none;
    font-size: 0.9rem;
    font-weight: 500;
    transition: all 0.3s ease;
}

.osb-download-link:hover {
    color: #003d99;
    transform: translateX(2px);
}

.osb-upload-zone-mini {
    border: 2px dashed #cbd5e1;
    border-radius: 8px;
    padding: 1.5rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    background: #f8fafc;
}

.osb-upload-zone-mini:hover {
    border-color: #0052cc;
    background: linear-gradient(135deg, #f8fcff 0%, #f0f8ff 100%);
    transform: translateY(-2px);
}

.osb-upload-content-mini {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
}

.osb-upload-icon-mini {
    font-size: 2rem;
    opacity: 0.7;
}

.osb-upload-text-mini {
    display: flex;
    flex-direction: column;
    gap: 0.25rem;
}

.osb-upload-text-mini strong {
    color: #374151;
    font-size: 1rem;
}

.osb-upload-text-mini small {
    color: #6b7280;
    font-size: 0.8rem;
}

.osb-file-preview {
    margin-top: 1rem;
    padding: 1rem;
    background: #f0f9ff;
    border: 1px solid #bae6fd;
    border-radius: 6px;
    font-size: 0.9rem;
    color: #0369a1;
}

.osb-form-actions-section {
    display: flex;
    justify-content: center;
    margin-top: 1rem;
}

/* Student Showcase Styles */
.osb-students-showcase {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 1.5rem;
}

.osb-student-showcase-card {
    background: white;
    border: 2px solid #f1f5f9;
    border-radius: 16px;
    padding: 1.5rem;
    display: flex;
    gap: 1rem;
    transition: all 0.3s ease;
}

.osb-student-showcase-card:hover {
    border-color: #0052cc;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,82,204,0.15);
}

.osb-student-avatar {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0052cc 0%, #0066ff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    box-shadow: 0 4px 12px rgba(0,82,204,0.3);
}

.osb-avatar-initials {
    color: white;
    font-weight: bold;
    font-size: 1.2rem;
}

.osb-student-showcase-info {
    flex: 1;
}

.osb-student-showcase-info h4 {
    margin: 0 0 0.5rem 0;
    color: #1e293b;
    font-size: 1.2rem;
    font-weight: 600;
}

.osb-student-meta {
    display: flex;
    gap: 1rem;
    margin-bottom: 0.5rem;
}

.osb-meta-item {
    display: flex;
    align-items: center;
    gap: 0.25rem;
    font-size: 0.9rem;
    color: #64748b;
}

.osb-meta-icon {
    font-size: 0.9rem;
}

.osb-parent-info {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    font-size: 0.9rem;
    color: #64748b;
    margin-bottom: 0.75rem;
}

.osb-document-status {
    margin-bottom: 0.5rem;
}

.osb-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 0.75rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 500;
}

.osb-status-complete {
    background: #dcfce7;
    color: #166534;
}

.osb-student-showcase-actions {
    display: flex;
    flex-direction: column;
    gap: 0.5rem;
    align-items: stretch;
}

.osb-btn-small {
    padding: 0.5rem 1rem;
    font-size: 0.85rem;
    min-width: 80px;
}

.osb-btn-outline {
    background: white;
    color: #0052cc;
    border: 2px solid #0052cc;
}

.osb-btn-outline:hover {
    background: #0052cc;
    color: white;
}

.osb-btn-danger-outline {
    background: white;
    color: #dc2626;
    border: 2px solid #dc2626;
}

.osb-btn-danger-outline:hover {
    background: #dc2626;
    color: white;
}

/* Navigation Section */
.osb-navigation-section {
    background: linear-gradient(135deg, #f8fcff 0%, #f0f8ff 100%);
    border: 2px solid #dbeafe;
}

.osb-navigation-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 2rem;
}

.osb-nav-info h4 {
    margin: 0 0 0.5rem 0;
    color: #1e40af;
    font-size: 1.3rem;
    font-weight: 600;
}

.osb-nav-info p {
    margin: 0;
    color: #64748b;
    font-size: 1rem;
}

.osb-navigation-actions {
    display: flex;
    gap: 1rem;
    flex-shrink: 0;
}

/* Responsive Design */
@media (max-width: 768px) {
    .osb-form-grid {
        grid-template-columns: 1fr;
    }

    .osb-documents-grid {
        grid-template-columns: 1fr;
    }

    .osb-students-showcase {
        grid-template-columns: 1fr;
    }

    .osb-navigation-content {
        flex-direction: column;
        text-align: center;
    }

    .osb-navigation-actions {
        flex-direction: column;
        width: 100%;
    }

    .osb-student-showcase-card {
        flex-direction: column;
        text-align: center;
    }

    .osb-student-showcase-actions {
        flex-direction: row;
        justify-content: center;
    }
}

/* Student Limit and Requirements Styling */
.osb-student-limit-info {
    margin-top: 1rem;
    display: flex;
    align-items: center;
    gap: 1rem;
    flex-wrap: wrap;
}

.osb-student-count {
    background: #f0f9ff;
    color: #0369a1;
    padding: 0.5rem 1rem;
    border-radius: 20px;
    font-weight: 600;
    font-size: 0.9rem;
    border: 2px solid #bae6fd;
}

.osb-count-full {
    background: #fef2f2;
    color: #dc2626;
    border-color: #fecaca;
}

.osb-limit-reached {
    background: #dc2626;
    color: white;
    padding: 0.5rem 1rem;
    border-radius: 15px;
    font-size: 0.8rem;
    font-weight: 600;
}

.osb-form-disabled-message {
    margin-top: 2rem;
}

.osb-limit-reached-card {
    background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);
    border: 2px solid #fecaca;
    border-radius: 16px;
    padding: 2rem;
    text-align: center;
}

.osb-limit-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
}

.osb-limit-reached-card h4 {
    color: #dc2626;
    margin: 0 0 1rem 0;
    font-size: 1.3rem;
    font-weight: 600;
}

.osb-limit-reached-card p {
    color: #7f1d1d;
    margin: 0.5rem 0;
    line-height: 1.5;
}

.osb-requirement-status {
    margin-top: 1rem;
    display: flex;
    justify-content: center;
    gap: 1rem;
}

.osb-requirement-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 0.5rem;
    background: white;
    padding: 1rem;
    border-radius: 12px;
    border: 2px solid #fbbf24;
    min-width: 120px;
}

.osb-requirement-number {
    font-size: 1.5rem;
    font-weight: bold;
    color: #d97706;
}

.osb-requirement-text {
    font-size: 0.9rem;
    color: #92400e;
    text-align: center;
}

/* Disabled button styling */
.osb-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none !important;
}

.osb-btn:disabled:hover {
    background: inherit;
    transform: none !important;
    box-shadow: inherit;
}

/* Step 4: Final Submission Styles */
.osb-review-summary {
    margin-bottom: 25px;
}

.osb-summary-section {
    margin-bottom: 30px;
    padding-bottom: 25px;
    border-bottom: 1px solid #eee;
}

.osb-summary-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
    padding-bottom: 0;
}

.osb-summary-title {
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 15px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.osb-summary-title .osb-icon {
    font-size: 20px;
}

.osb-summary-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.osb-summary-item {
    background: #f8f9fc;
    padding: 15px;
    border-radius: 8px;
    border: 1px solid #e8eaed;
}

.osb-summary-label {
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    color: #6c757d;
    letter-spacing: 0.5px;
    margin-bottom: 5px;
}

.osb-summary-value {
    font-size: 16px;
    font-weight: 500;
    color: #2c3e50;
}

/* Students Preview */
.osb-students-preview {
    display: grid;
    gap: 12px;
}

.osb-student-preview-card {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #f8f9fc;
    border: 1px solid #e8eaed;
    border-radius: 10px;
    padding: 15px;
    transition: all 0.2s ease;
}

.osb-student-preview-card:hover {
    border-color: #4285f4;
    box-shadow: 0 2px 8px rgba(66, 133, 244, 0.1);
}

.osb-student-preview-header {
    display: flex;
    align-items: center;
    gap: 12px;
    flex: 1;
}

.osb-student-number {
    background: #4285f4;
    color: white;
    width: 28px;
    height: 28px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
    flex-shrink: 0;
}

.osb-student-info {
    flex: 1;
}

.osb-student-name {
    font-size: 16px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 2px;
}

.osb-student-details {
    font-size: 13px;
    color: #6c757d;
}

.osb-student-status {
    flex-shrink: 0;
}

.osb-status-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    padding: 4px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.osb-status-ready {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

/* Requirements Checklist */
.osb-requirements-checklist {
    display: grid;
    gap: 10px;
}

.osb-requirement-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 12px 15px;
    background: #f8f9fc;
    border-radius: 8px;
    border: 1px solid #e8eaed;
}

.osb-requirement-completed {
    background: #d4edda;
    border-color: #c3e6cb;
}

.osb-requirement-icon {
    font-size: 16px;
    flex-shrink: 0;
}

.osb-requirement-text {
    font-size: 14px;
    font-weight: 500;
    color: #2c3e50;
}

/* Agreement Section */
.osb-submission-card {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: white;
    border: none;
}

.osb-submission-card .osb-card-header {
    border-bottom-color: rgba(255, 255, 255, 0.2);
}

.osb-submission-card .osb-card-title,
.osb-submission-card .osb-card-subtitle {
    color: white;
}

.osb-agreement-box {
    background: rgba(255, 255, 255, 0.95);
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 25px;
    color: #2c3e50;
}

.osb-agreement-content h4 {
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 15px;
}

.osb-agreement-text {
    font-size: 14px;
    line-height: 1.6;
    color: #495057;
}

.osb-agreement-text ul {
    margin: 10px 0;
    padding-left: 20px;
}

.osb-agreement-text li {
    margin-bottom: 8px;
}

.osb-agreement-checkbox {
    margin-top: 20px;
    padding-top: 20px;
    border-top: 1px solid #e8eaed;
}

.osb-checkbox-container {
    display: flex;
    align-items: flex-start;
    gap: 12px;
    cursor: pointer;
    user-select: none;
}

.osb-checkbox-container input[type="checkbox"] {
    display: none;
}

.osb-checkmark {
    width: 20px;
    height: 20px;
    border: 2px solid #ddd;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    transition: all 0.2s ease;
    background: white;
}

.osb-checkbox-container input[type="checkbox"]:checked + .osb-checkmark {
    background: #4285f4;
    border-color: #4285f4;
}

.osb-checkbox-container input[type="checkbox"]:checked + .osb-checkmark::after {
    content: "✓";
    color: white;
    font-size: 12px;
    font-weight: bold;
}

.osb-checkbox-text {
    font-size: 14px;
    line-height: 1.5;
    color: #2c3e50;
    font-weight: 500;
}

/* Submit Button Loading State */
.osb-btn-submit .osb-btn-loading {
    display: none;
}

.osb-btn-submit.loading .osb-btn-icon,
.osb-btn-submit.loading .osb-btn-text {
    display: none;
}

.osb-btn-submit.loading .osb-btn-loading {
    display: flex;
    align-items: center;
    gap: 8px;
}

.osb-spinner {
    width: 16px;
    height: 16px;
    border: 2px solid rgba(255, 255, 255, 0.3);
    border-top: 2px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

/* Responsive Design for Step 4 */
@media (max-width: 768px) {
    .osb-summary-grid {
        grid-template-columns: 1fr;
    }

    .osb-student-preview-card {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .osb-student-preview-header {
        width: 100%;
    }

    .osb-student-status {
        align-self: flex-end;
    }

    .osb-submission-actions {
        flex-direction: column;
        gap: 12px;
    }

    .osb-submission-actions .osb-btn {
        width: 100%;
        justify-content: center;
    }
}

/* Multi-Student Form Styles */
.osb-multi-student-container {
    display: flex;
    flex-direction: column;
    gap: 2rem;
}

.osb-requirements-info {
    background: linear-gradient(135deg, #f0f9ff 0%, #e0f2fe 100%);
    border: 2px solid #0ea5e9;
    border-radius: 16px;
    padding: 1.5rem;
    margin-bottom: 2rem;
}

.osb-requirements-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1rem;
}

.osb-requirements-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0052cc 0%, #0066ff 100%);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.3rem;
    color: white;
    box-shadow: 0 4px 12px rgba(0,82,204,0.3);
}

.osb-requirements-content h4 {
    margin: 0 0 0.5rem 0;
    color: #1e40af;
    font-size: 1.3rem;
    font-weight: 600;
}

.osb-requirements-stats {
    display: flex;
    gap: 2rem;
    align-items: center;
    flex-wrap: wrap;
}

.osb-stat-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 1rem;
    background: white;
    border-radius: 12px;
    border: 2px solid #bfdbfe;
    min-width: 120px;
}

.osb-stat-number {
    font-size: 1.8rem;
    font-weight: bold;
    color: #1e40af;
}

.osb-stat-label {
    font-size: 0.9rem;
    color: #64748b;
    text-align: center;
    margin-top: 0.25rem;
}

.osb-students-forms-container {
    background: white;
    border-radius: 16px;
    padding: 2rem;
    box-shadow: 0 8px 32px rgba(0,0,0,0.08);
    border: 2px solid #f1f7ff;
}

.osb-forms-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #f1f7ff;
}

.osb-forms-title {
    display: flex;
    align-items: center;
    gap: 1rem;
}

.osb-forms-title h4 {
    margin: 0;
    color: #1e40af;
    font-size: 1.3rem;
    font-weight: 600;
}

.osb-add-student-btn {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    color: white;
    border: 2px solid #10b981;
    padding: 0.75rem 1.5rem;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.osb-add-student-btn:hover:not(:disabled) {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(16,185,129,0.3);
}

.osb-add-student-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none !important;
}

.osb-student-form-card {
    background: #f8fcff;
    border: 2px solid #e3f2fd;
    border-radius: 16px;
    padding: 2rem;
    margin-bottom: 1.5rem;
    transition: all 0.3s ease;
    position: relative;
}

.osb-student-form-card:hover {
    border-color: #0052cc;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(0,82,204,0.15);
}

.osb-student-form-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 1.5rem;
    padding-bottom: 1rem;
    border-bottom: 2px solid #dbeafe;
}

.osb-student-form-header h4 {
    margin: 0;
    color: #1e40af;
    font-size: 1.2rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.osb-documents-section {
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 2px solid #dbeafe;
}

.osb-documents-section h5 {
    margin: 0 0 1rem 0;
    color: #1e40af;
    font-size: 1.1rem;
    font-weight: 600;
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.osb-documents-row {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1.5rem;
}

.osb-document-upload {
    background: white;
    border: 2px solid #f1f5f9;
    border-radius: 12px;
    padding: 1.5rem;
    transition: all 0.3s ease;
}

.osb-document-upload:hover {
    border-color: #0052cc;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,82,204,0.1);
}

.osb-document-upload label {
    display: block;
    margin-bottom: 1rem;
    font-weight: 600;
    color: #374151;
    font-size: 1rem;
}

.osb-file-input {
    width: 100%;
    padding: 12px 16px;
    border: 2px solid #e5e7eb;
    border-radius: 8px;
    font-size: 0.95rem;
    transition: all 0.3s ease;
    background: white;
    cursor: pointer;
}

.osb-file-input:focus {
    outline: none;
    border-color: #0052cc;
    box-shadow: 0 0 0 3px rgba(0,82,204,0.1);
}

.osb-file-input:invalid {
    border-color: #dc2626;
}

.osb-multi-student-actions {
    background: linear-gradient(135deg, #f1f7ff 0%, #e3f2fd 100%);
    border: 2px solid #dbeafe;
    border-radius: 16px;
    padding: 2rem;
    margin-top: 2rem;
    text-align: center;
}

.osb-multi-student-actions h4 {
    margin: 0 0 1rem 0;
    color: #1e40af;
    font-size: 1.3rem;
    font-weight: 600;
}

.osb-actions-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
}

.osb-submit-multi-btn {
    background: linear-gradient(135deg, #0052cc 0%, #0066ff 100%);
    color: white;
    border: 2px solid #0052cc;
    padding: 1rem 2rem;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 0.5rem;
    min-width: 200px;
    justify-content: center;
}

.osb-submit-multi-btn:hover:not(:disabled) {
    background: linear-gradient(135deg, #0066ff 0%, #0052cc 100%);
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,82,204,0.3);
}

.osb-submit-multi-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none !important;
}

/* Warning and Info States */
.osb-requirements-warning {
    background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
    border-color: #f59e0b;
}

.osb-requirements-warning .osb-requirements-icon {
    background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
}

.osb-requirements-warning h4 {
    color: #92400e;
}

.osb-requirements-success {
    background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%);
    border-color: #10b981;
}

.osb-requirements-success .osb-requirements-icon {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
}

.osb-requirements-success h4 {
    color: #065f46;
}

/* Mobile Responsive for Multi-Student Forms */
@media (max-width: 768px) {
    .osb-requirements-stats {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }

    .osb-stat-item {
        min-width: auto;
    }

    .osb-forms-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }

    .osb-documents-row {
        grid-template-columns: 1fr;
    }

    .osb-actions-buttons {
        flex-direction: column;
        align-items: stretch;
    }

    .osb-submit-multi-btn,
    .osb-add-student-btn {
        min-width: auto;
        width: 100%;
    }

    .osb-student-form-header {
        flex-direction: column;
        gap: 1rem;
        align-items: stretch;
    }
}

/* Modal Styles for Edit Student */
.osb-modal-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    padding: 1rem;
}

.osb-modal-content {
    background: white;
    border-radius: 16px;
    padding: 0;
    max-width: 600px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
}

.osb-modal-header {
    padding: 1.5rem 2rem;
    border-bottom: 2px solid #f1f7ff;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: linear-gradient(135deg, #0052cc 0%, #0066ff 100%);
    color: white;
    border-radius: 16px 16px 0 0;
}

.osb-modal-header h3 {
    margin: 0;
    color: white;
    font-size: 1.3rem;
    font-weight: 600;
}

.osb-modal-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    color: white;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    transition: all 0.3s ease;
}

.osb-modal-close:hover {
    background: rgba(255, 255, 255, 0.2);
}

.osb-modal-form {
    padding: 2rem;
}

.osb-modal-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-end;
    margin-top: 2rem;
    padding-top: 1rem;
    border-top: 2px solid #f1f7ff;
}

/* Mobile responsive for modal */
@media (max-width: 768px) {
    .osb-modal-overlay {
        padding: 0.5rem;
    }

    .osb-modal-content {
        max-height: 95vh;
    }

    .osb-modal-header {
        padding: 1rem 1.5rem;
    }

    .osb-modal-form {
        padding: 1.5rem;
    }

    .osb-modal-actions {
        flex-direction: column;
    }

    .osb-modal-actions .osb-btn {
        width: 100%;
    }
}
</style>

<script>
// Professional file input trigger function
function triggerFileInput(inputId) {
    const input = document.getElementById(inputId);
    if (input) {
        input.click();

        // Add file change handler if not already added
        if (!input.hasAttribute('data-handler-added')) {
            input.addEventListener('change', function(e) {
                const file = e.target.files[0];
                const previewDiv = document.getElementById(inputId + '_preview');

                if (file && previewDiv) {
                    previewDiv.style.display = 'block';
                    previewDiv.innerHTML = `
                        <strong>📄 ${file.name}</strong><br>
                        <small>Size: ${(file.size / 1024 / 1024).toFixed(2)} MB</small>
                    `;
                }
            });
            input.setAttribute('data-handler-added', 'true');
        }
    }
}

jQuery(document).ready(function($) {
    console.log('New Dashboard Loaded');

    // Initialize multi-student form if on Step 3
    if (window.location.search.includes('step=3') || $('#step-3').length > 0) {
        initializeMultiStudentForm();
    }

    // Competition selection
    $(document).on('click', '.osb-select-competition', function() {
        const eventId = $(this).data('event-id');

        // Submit competition selection
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'osb_select_competition',
                event_id: eventId,
                token: '<?php echo esc_js(sanitize_text_field($_GET['token'] ?? '')); ?>',
                nonce: '<?php echo wp_create_nonce('osb_dashboard_nonce'); ?>'
            },
            success: function(response) {
                console.log('Competition selection response:', response);
                if (response.success) {
                    // Always redirect to refresh with the updated token and step
                    if (response.data.redirect_url) {
                        console.log('Redirecting to:', response.data.redirect_url);
                        window.location.href = response.data.redirect_url;
                    } else {
                        console.log('Reloading page');
                        location.reload(); // Refresh to show next step
                    }
                } else {
                    console.error('Competition selection error:', response.data);
                    alert('Error: ' + response.data);
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', error, xhr.responseText);
                alert('Connection error. Please try again.');
            }
        });
    });

    // Download Template Handlers
    $(document).on('click', '.osb-download-eoi, .osb-download-template', function(e) {
        e.preventDefault();
        const template = $(this).data('template');

        if (!template) {
            alert('Template type not specified');
            return;
        }

        // Create download link
        const downloadUrl = '<?php echo admin_url('admin-ajax.php'); ?>?action=osb_download_template&template=' + encodeURIComponent(template) + '&nonce=<?php echo wp_create_nonce('osb_download_template'); ?>';

        // Create temporary link and trigger download
        const link = document.createElement('a');
        link.href = downloadUrl;
        link.download = template + '_form.pdf';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    });

    // Simple and Clean EOI Upload Handler
    function initializeEOIUpload() {
        console.log('Initializing EOI upload functionality...');

        // File upload elements
        const dropzone = document.getElementById('eoi-dropzone');
        const fileInput = document.getElementById('eoi-file-input');
        const fileInfo = document.getElementById('eoi-file-info');
        const submitBtn = document.getElementById('eoi-submit-btn');
        const removeBtn = document.getElementById('eoi-remove-file');
        const uploadForm = document.getElementById('osb-eoi-upload-form');

        // Check if all elements exist
        if (!dropzone || !fileInput || !fileInfo || !submitBtn) {
            console.log('EOI upload elements not found, skipping initialization');
            return;
        }

        console.log('All EOI elements found, setting up handlers...');

        // Click dropzone to open file browser
        dropzone.addEventListener('click', function(e) {
            e.preventDefault();
            console.log('Dropzone clicked - using fallback method that works');

            // Clear main file input first
            fileInput.value = '';

            // Use the fallback method that we know works
            createFallbackInput();
        });

        // Separate function for fallback input
        function createFallbackInput() {
            console.log('Creating fallback file input...');
            const tempInput = document.createElement('input');
            tempInput.type = 'file';
            tempInput.accept = '.pdf';
            tempInput.style.position = 'absolute';
            tempInput.style.left = '-9999px';
            tempInput.style.opacity = '0';

            tempInput.addEventListener('change', function(e) {
                console.log('Fallback file input changed');
                const file = e.target.files[0];
                if (file) {
                    console.log('File selected via fallback:', file.name);
                    // Copy to main file input
                    try {
                        const dt = new DataTransfer();
                        dt.items.add(file);
                        fileInput.files = dt.files;
                        handleFileSelect(file);
                    } catch (err) {
                        console.error('Error copying file:', err);
                        // Direct handle if copy fails
                        handleFileSelect(file);
                    }
                }
                // Remove temp input
                document.body.removeChild(tempInput);
            });

            document.body.appendChild(tempInput);
            tempInput.click();
        }

        // Handle file selection - multiple event listeners for better compatibility
        fileInput.addEventListener('change', function(e) {
            console.log('File input changed (change event)');
            console.log('Event target:', e.target);
            console.log('Files array:', e.target.files);
            console.log('Files length:', e.target.files.length);

            const file = e.target.files[0];
            if (file) {
                console.log('File selected:', file.name, file.type, file.size);
                handleFileSelect(file);
            } else {
                console.log('No file selected or file array empty');
            }
        });

        // Additional event listener for input event
        fileInput.addEventListener('input', function(e) {
            console.log('File input changed (input event)');
            const file = e.target.files[0];
            if (file) {
                console.log('File selected via input event:', file.name);
                handleFileSelect(file);
            }
        });

        // Debug: Monitor file input for any attribute changes
        console.log('File input initial state:');
        console.log('- ID:', fileInput.id);
        console.log('- Name:', fileInput.name);
        console.log('- Type:', fileInput.type);
        console.log('- Accept:', fileInput.accept);
        console.log('- Style position:', fileInput.style.position);

        // Add focus/blur detection to see if file browser opens
        fileInput.addEventListener('focus', function() {
            console.log('File input gained focus - file browser should open');
        });

        fileInput.addEventListener('blur', function() {
            console.log('File input lost focus - file browser closed or user clicked away');
        });

        // Add window focus detection
        let windowLostFocus = false;
        window.addEventListener('blur', function() {
            windowLostFocus = true;
            console.log('Window lost focus - file browser might have opened');
        });

        window.addEventListener('focus', function() {
            if (windowLostFocus) {
                windowLostFocus = false;
                console.log('Window regained focus - file browser closed');

                // Check if file was selected after window regains focus
                setTimeout(() => {
                    if (fileInput.files.length > 0) {
                        console.log('File found after window refocus:', fileInput.files[0].name);
                        const file = fileInput.files[0];
                        handleFileSelect(file);
                    }
                }, 100);
            }
        });

        // Basic drag and drop
        dropzone.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.style.backgroundColor = '#f0f8ff';
        });

        dropzone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.style.backgroundColor = '';
        });

        dropzone.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.style.backgroundColor = '';

            const files = e.dataTransfer.files;
            if (files.length > 0) {
                const file = files[0];
                console.log('File dropped:', file.name, file.type, file.size);

                // Clear main file input and directly handle the dropped file
                fileInput.value = '';

                // Update the main file input with the dropped file for form submission
                try {
                    const dt = new DataTransfer();
                    dt.items.add(file);
                    fileInput.files = dt.files;
                    console.log('Dropped file added to main input');
                } catch (err) {
                    console.log('Could not add to main input, will handle directly');
                }

                // Handle the file regardless
                handleFileSelect(file);
            }
        });

        // Handle file selection logic
        function handleFileSelect(file) {
            // Store the file for later submission
            currentSelectedFile = file;

            // Validate file type
            if (file.type !== 'application/pdf') {
                alert('Please select a PDF file only.');
                fileInput.value = '';
                currentSelectedFile = null;
                return;
            }

            // Validate file size (5MB max)
            if (file.size > 5 * 1024 * 1024) {
                alert('File size must be less than 5MB.');
                fileInput.value = '';
                currentSelectedFile = null;
                return;
            }

            // Show file info
            const fileName = fileInfo.querySelector('.osb-file-name');
            const fileSize = fileInfo.querySelector('.osb-file-size');

            if (fileName && fileSize) {
                fileName.textContent = file.name;
                fileSize.textContent = formatFileSize(file.size);
            }

            // Toggle visibility
            dropzone.style.display = 'none';
            fileInfo.style.display = 'block';
            submitBtn.disabled = false;

            console.log('File validated and UI updated. File stored for submission.');
        }

        // Remove file handler
        if (removeBtn) {
            removeBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log('Remove file clicked');

                fileInput.value = '';
                dropzone.style.display = 'block';
                fileInfo.style.display = 'none';
                submitBtn.disabled = true;
            });
        }

        // Store the current file for submission (since we use fallback inputs)
        let currentSelectedFile = null;

        // Form submission handler
        if (uploadForm) {
            uploadForm.addEventListener('submit', function(e) {
                e.preventDefault();
                console.log('Form submitted');

                // Try to get file from main input first, then fallback to stored file
                let file = fileInput.files[0] || currentSelectedFile;

                if (!file) {
                    alert('Please select a file to upload.');
                    return;
                }

                console.log('Uploading file:', file.name, file.size, file.type);

                // Prepare form data
                const formData = new FormData();
                formData.append('action', 'osb_upload_eoi_document');
                formData.append('eoi_document', file);
                formData.append('token', '<?php echo esc_js(sanitize_text_field($_GET['token'] ?? '')); ?>');
                formData.append('nonce', '<?php echo wp_create_nonce('osb_dashboard_nonce'); ?>');

                // Show loading state
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="osb-btn-icon">⏳</span><span class="osb-btn-text">Uploading...</span>';

                console.log('Sending AJAX request with file:', file.name);

                // Submit via AJAX
                fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => {
                    console.log('Response status:', response.status);
                    if (!response.ok) {
                        throw new Error('Network response was not ok');
                    }
                    return response.text(); // Get as text first to see raw response
                })
                .then(text => {
                    console.log('Raw response:', text);
                    try {
                        const data = JSON.parse(text);
                        console.log('Parsed response:', data);

                        if (data.success) {
                            alert('Expression of Interest uploaded successfully!');
                            window.location.reload();
                        } else {
                            alert('Upload failed: ' + (data.data || 'Unknown error'));
                            resetSubmitButton();
                        }
                    } catch (e) {
                        console.error('JSON parse error:', e);
                        console.error('Response was:', text);
                        alert('Upload failed: Invalid response from server');
                        resetSubmitButton();
                    }
                })
                .catch(error => {
                    console.error('Upload error:', error);
                    alert('Upload failed. Please try again.');
                    resetSubmitButton();
                });
            });
        }

        // Reset submit button
        function resetSubmitButton() {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<span class="osb-btn-icon">🚀</span><span class="osb-btn-text">Upload & Continue to Next Step</span>';
        }

        // Format file size helper
        function formatFileSize(bytes) {
            if (bytes === 0) return '0 Bytes';
            const k = 1024;
            const sizes = ['Bytes', 'KB', 'MB', 'GB'];
            const i = Math.floor(Math.log(bytes) / Math.log(k));
            return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
        }

        console.log('EOI upload handlers initialized successfully');
    }

    // Initialize upload functionality
    initializeEOIUpload();

    // Student Registration Form Handler
    $('#osb-add-student-form').on('submit', function(e) {
        e.preventDefault();
        console.log('Student form submitted');

        // Collect form data
        const formData = new FormData();
        formData.append('action', 'osb_add_student');
        formData.append('token', '<?php echo esc_js(sanitize_text_field($_GET['token'] ?? '')); ?>');
        formData.append('nonce', '<?php echo wp_create_nonce('osb_dashboard_nonce'); ?>');

        // Student information
        formData.append('first_name', $('#student_first_name').val());
        formData.append('last_name', $('#student_last_name').val());
        formData.append('grade', $('#student_grade').val());
        formData.append('date_of_birth', $('#student_dob').val());
        formData.append('parent_name', $('#parent_name').val());
        formData.append('parent_email', $('#parent_email').val());

        // Student documents
        const birthCertificate = document.getElementById('birth_certificate').files[0];
        const parentalConsent = document.getElementById('parental_consent').files[0];

        if (birthCertificate) {
            formData.append('birth_certificate', birthCertificate);
        }
        if (parentalConsent) {
            formData.append('parental_consent', parentalConsent);
        }

        // Validate required documents
        if (!birthCertificate) {
            alert('Please upload the student\'s birth certificate.');
            return;
        }
        if (!parentalConsent) {
            alert('Please upload the signed parental consent form.');
            return;
        }

        // Show loading state
        const submitBtn = $(this).find('button[type="submit"]');
        const originalText = submitBtn.html();
        submitBtn.prop('disabled', true).html('<span class="osb-btn-icon">⏳</span> Adding Student...');

        // Submit via AJAX
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                console.log('Student registration response:', response);

                if (response.success) {
                    alert('Student added successfully!');
                    // Reload page to show updated student list
                    window.location.reload();
                } else {
                    alert('Failed to add student: ' + (response.data || 'Unknown error'));
                    submitBtn.prop('disabled', false).html(originalText);
                }
            },
            error: function(xhr, status, error) {
                console.error('Student registration error:', error);
                alert('Network error occurred. Please try again.');
                submitBtn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Final agreement checkbox
    $('#final-agreement').on('change', function() {
        $('#submit-final-registration').prop('disabled', !this.checked);
    });

    // Submit final registration
    $('#submit-final-registration').on('click', function() {
        if (confirm('Are you sure you want to submit your registration? This action cannot be undone.')) {
            // Submit final registration
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'osb_submit_final_registration',
                    token: '<?php echo esc_js(sanitize_text_field($_GET['token'] ?? '')); ?>',
                    nonce: '<?php echo wp_create_nonce('osb_dashboard_nonce'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        alert('Success: ' + (response.data.message || 'Registration submitted successfully!'));
                        // Redirect to dashboard without step parameter to show completion page
                        const baseUrl = window.location.pathname;
                        const urlParams = new URLSearchParams(window.location.search);
                        urlParams.delete('step'); // Remove step parameter
                        const newUrl = baseUrl + (urlParams.toString() ? '?' + urlParams.toString() : '');
                        window.location.href = newUrl;
                    } else {
                        alert('Error: ' + (response.data || 'Unknown error occurred.'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Final submission error:', error);
                    alert('Network error occurred. Please try again.');
                }
            });
        }
    });
});

// Helper functions
function proceedToFinalStep() {
    // Get current URL parameters to preserve token
    const urlParams = new URLSearchParams(window.location.search);
    const token = urlParams.get('token');

    // Build new URL with step 4 and token
    let newUrl = window.location.pathname + '?step=4';
    if (token) {
        newUrl += '&token=' + encodeURIComponent(token);
    }

    // Navigate to step 4
    window.location.href = newUrl;
}

function editStudent(studentId) {
    console.log('Edit student:', studentId);

    // Create a modal or form to edit student details
    // For now, we'll use a simple prompt-based approach

    // First, get the current student data
    jQuery.ajax({
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        type: 'POST',
        data: {
            action: 'osb_get_student',
            student_id: studentId,
            token: '<?php echo esc_js(sanitize_text_field($_GET['token'] ?? '')); ?>',
            nonce: '<?php echo wp_create_nonce('osb_dashboard_nonce'); ?>'
        },
        success: function(response) {
            if (response.success && response.data) {
                const student = response.data;

                // Create a simple edit form in a modal-like div
                const editForm = `
                    <div class="osb-modal-overlay" id="edit-student-modal">
                        <div class="osb-modal-content">
                            <div class="osb-modal-header">
                                <h3>Edit Student: ${student.first_name} ${student.last_name}</h3>
                                <button type="button" class="osb-modal-close" onclick="closeEditModal()">&times;</button>
                            </div>
                            <form id="edit-student-form" class="osb-modal-form">
                                <div class="osb-form-grid">
                                    <div class="osb-form-group">
                                        <label>First Name <span class="osb-required">*</span></label>
                                        <input type="text" id="edit-first-name" value="${student.first_name}" required class="osb-form-input">
                                    </div>
                                    <div class="osb-form-group">
                                        <label>Last Name <span class="osb-required">*</span></label>
                                        <input type="text" id="edit-last-name" value="${student.last_name}" required class="osb-form-input">
                                    </div>
                                    <div class="osb-form-group">
                                        <label>Grade <span class="osb-required">*</span></label>
                                        <select id="edit-grade" required class="osb-form-select">
                                            <option value="Primary 1" ${student.grade_level === 'Primary 1' ? 'selected' : ''}>Primary 1</option>
                                            <option value="Primary 2" ${student.grade_level === 'Primary 2' ? 'selected' : ''}>Primary 2</option>
                                            <option value="Primary 3" ${student.grade_level === 'Primary 3' ? 'selected' : ''}>Primary 3</option>
                                            <option value="Primary 4" ${student.grade_level === 'Primary 4' ? 'selected' : ''}>Primary 4</option>
                                            <option value="Primary 5" ${student.grade_level === 'Primary 5' ? 'selected' : ''}>Primary 5</option>
                                            <option value="Primary 6" ${student.grade_level === 'Primary 6' ? 'selected' : ''}>Primary 6</option>
                                            <option value="JSS1" ${student.grade_level === 'JSS1' ? 'selected' : ''}>JSS1</option>
                                            <option value="JSS2" ${student.grade_level === 'JSS2' ? 'selected' : ''}>JSS2</option>
                                            <option value="JSS3" ${student.grade_level === 'JSS3' ? 'selected' : ''}>JSS3</option>
                                            <option value="SS1" ${student.grade_level === 'SS1' ? 'selected' : ''}>SS1</option>
                                            <option value="SS2" ${student.grade_level === 'SS2' ? 'selected' : ''}>SS2</option>
                                            <option value="SS3" ${student.grade_level === 'SS3' ? 'selected' : ''}>SS3</option>
                                        </select>
                                    </div>
                                    <div class="osb-form-group">
                                        <label>Date of Birth <span class="osb-required">*</span></label>
                                        <input type="date" id="edit-dob" value="${student.birth_date}" required class="osb-form-input">
                                    </div>
                                    <div class="osb-form-group">
                                        <label>Parent/Guardian Name <span class="osb-required">*</span></label>
                                        <input type="text" id="edit-parent-name" value="${student.parent_name}" required class="osb-form-input">
                                    </div>
                                    <div class="osb-form-group">
                                        <label>Parent/Guardian Email <span class="osb-required">*</span></label>
                                        <input type="email" id="edit-parent-email" value="${student.parent_email || ''}" required class="osb-form-input">
                                    </div>
                                </div>

                                <!-- Document Upload Section -->
                                <div class="osb-documents-section">
                                    <h5>📋 Update Documents (Optional)</h5>
                                    <p style="color: #64748b; font-size: 0.9rem; margin-bottom: 1rem;">
                                        Leave empty to keep existing documents, or upload new files to replace them.
                                    </p>
                                    <div class="osb-documents-row">
                                        <div class="osb-document-upload">
                                            <label>Birth Certificate</label>
                                            <input type="file" id="edit-birth-certificate"
                                                   accept=".pdf,.jpg,.jpeg,.png" class="osb-file-input">
                                            <small style="color: #64748b;">Current: ${student.birth_cert_status || 'Uploaded'}</small>
                                        </div>
                                        <div class="osb-document-upload">
                                            <label>Parental Consent</label>
                                            <input type="file" id="edit-parental-consent"
                                                   accept=".pdf,.jpg,.jpeg,.png" class="osb-file-input">
                                            <small style="color: #64748b;">Current: ${student.consent_status || 'Uploaded'}</small>
                                        </div>
                                    </div>
                                </div>

                                <div class="osb-modal-actions">
                                    <button type="button" class="osb-btn osb-btn-secondary" onclick="closeEditModal()">Cancel</button>
                                    <button type="submit" class="osb-btn osb-btn-primary">
                                        <span class="osb-btn-icon">💾</span>
                                        Update Student
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                `;

                // Add modal to page
                jQuery('body').append(editForm);

                // Handle form submission
                jQuery('#edit-student-form').on('submit', function(e) {
                    e.preventDefault();
                    updateStudent(studentId);
                });

            } else {
                alert('Failed to load student data: ' + (response.data || 'Unknown error'));
            }
        },
        error: function() {
            alert('Network error occurred while loading student data.');
        }
    });
}

function closeEditModal() {
    jQuery('#edit-student-modal').remove();
}

function updateStudent(studentId) {
    // Create FormData object to handle file uploads
    const formData = new FormData();
    formData.append('action', 'osb_update_student');
    formData.append('student_id', studentId);
    formData.append('first_name', jQuery('#edit-first-name').val());
    formData.append('last_name', jQuery('#edit-last-name').val());
    formData.append('grade_level', jQuery('#edit-grade').val());
    formData.append('birth_date', jQuery('#edit-dob').val());
    formData.append('parent_name', jQuery('#edit-parent-name').val());
    formData.append('parent_email', jQuery('#edit-parent-email').val());
    formData.append('token', '<?php echo esc_js(sanitize_text_field($_GET['token'] ?? '')); ?>');
    formData.append('nonce', '<?php echo wp_create_nonce('osb_dashboard_nonce'); ?>');

    // Handle file uploads if new files are selected
    const birthCertFile = document.getElementById('edit-birth-certificate').files[0];
    const parentalConsentFile = document.getElementById('edit-parental-consent').files[0];

    if (birthCertFile) {
        formData.append('birth_certificate', birthCertFile);
    }
    if (parentalConsentFile) {
        formData.append('parental_consent', parentalConsentFile);
    }

    // Show loading state
    const submitBtn = jQuery('#edit-student-form button[type="submit"]');
    const originalText = submitBtn.html();
    submitBtn.prop('disabled', true).html('<span class="osb-btn-icon">⏳</span> Updating...');

    jQuery.ajax({
        url: '<?php echo admin_url('admin-ajax.php'); ?>',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                alert('Student updated successfully!');
                closeEditModal();
                window.location.reload();
            } else {
                alert('Failed to update student: ' + (response.data || 'Unknown error'));
                submitBtn.prop('disabled', false).html(originalText);
            }
        },
        error: function() {
            alert('Network error occurred while updating student.');
            submitBtn.prop('disabled', false).html(originalText);
        }
    });
}

function removeStudent(studentId) {
    if (confirm('Are you sure you want to remove this student? This action cannot be undone.')) {
        console.log('Removing student:', studentId);

        jQuery.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'osb_delete_student',
                student_id: studentId,
                token: '<?php echo esc_js(sanitize_text_field($_GET['token'] ?? '')); ?>',
                nonce: '<?php echo wp_create_nonce('osb_dashboard_nonce'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert('Student removed successfully!');
                    window.location.reload();
                } else {
                    alert('Failed to remove student: ' + (response.data || 'Unknown error'));
                }
            },
            error: function() {
                alert('Network error occurred while removing student.');
            }
        });
    }
}

function goBackToStep(step) {
    if (confirm('Are you sure you want to go back? Any unsaved changes will be lost.')) {
        // Get current URL parameters to preserve token
        const urlParams = new URLSearchParams(window.location.search);
        const token = urlParams.get('token');

        // Build new URL with step and token
        let newUrl = window.location.pathname + '?step=' + step;
        if (token) {
            newUrl += '&token=' + encodeURIComponent(token);
        }

        window.location.href = newUrl;
    }
}

// Multi-Student Form Management
let studentFormCount = 0;
let currentStudents = <?php echo json_encode($students ?? []); ?>;
let currentEvent = <?php echo json_encode($current_event ?? null); ?>;

function initializeMultiStudentForm() {
    console.log('Initializing multi-student form...');

    // Add first student form automatically if no students exist
    if (currentStudents.length === 0) {
        addStudentForm();
    }

    updateSubmitButtonState();
}

function addStudentForm() {
    studentFormCount++;

    // Check maximum limit
    if (currentEvent && currentEvent.max_students_per_school &&
        (currentStudents.length + studentFormCount) > parseInt(currentEvent.max_students_per_school)) {
        alert(`You cannot add more than ${currentEvent.max_students_per_school} students for this event.`);
        return;
    }

    const container = document.getElementById('multi-student-forms');
    if (!container) return;

    const formHtml = `
        <div class="osb-student-form-card" id="student-form-${studentFormCount}" data-form-id="${studentFormCount}">
            <div class="osb-student-form-header">
                <h4>👤 Student ${currentStudents.length + studentFormCount}</h4>
                ${studentFormCount > 1 ? `<button type="button" class="osb-btn osb-btn-danger-outline osb-btn-small" onclick="removeStudentForm(${studentFormCount})">Remove</button>` : ''}
            </div>

            <div class="osb-form-grid">
                <div class="osb-form-group">
                    <label>
                        <span class="osb-label-text">First Name</span>
                        <span class="osb-required">*</span>
                    </label>
                    <input type="text" name="students[${studentFormCount}][first_name]" class="osb-form-input" required
                           placeholder="Enter student's first name">
                </div>

                <div class="osb-form-group">
                    <label>
                        <span class="osb-label-text">Last Name</span>
                        <span class="osb-required">*</span>
                    </label>
                    <input type="text" name="students[${studentFormCount}][last_name]" class="osb-form-input" required
                           placeholder="Enter student's last name">
                </div>

                <div class="osb-form-group">
                    <label>
                        <span class="osb-label-text">Grade/Class</span>
                        <span class="osb-required">*</span>
                    </label>
                    <select name="students[${studentFormCount}][grade]" class="osb-form-select" required>
                        <option value="">Select Grade</option>
                        <option value="Primary 1">Primary 1</option>
                        <option value="Primary 2">Primary 2</option>
                        <option value="Primary 3">Primary 3</option>
                        <option value="Primary 4">Primary 4</option>
                        <option value="Primary 5">Primary 5</option>
                        <option value="Primary 6">Primary 6</option>
                        <option value="JSS1">JSS1</option>
                        <option value="JSS2">JSS2</option>
                        <option value="JSS3">JSS3</option>
                        <option value="SS1">SS1</option>
                        <option value="SS2">SS2</option>
                        <option value="SS3">SS3</option>
                    </select>
                </div>

                <div class="osb-form-group">
                    <label>
                        <span class="osb-label-text">Date of Birth</span>
                        <span class="osb-required">*</span>
                    </label>
                    <input type="date" name="students[${studentFormCount}][date_of_birth]" class="osb-form-input" required>
                </div>

                <div class="osb-form-group">
                    <label>
                        <span class="osb-label-text">Parent/Guardian Name</span>
                        <span class="osb-required">*</span>
                    </label>
                    <input type="text" name="students[${studentFormCount}][parent_name]" class="osb-form-input" required
                           placeholder="Full name of parent or guardian">
                </div>

                <div class="osb-form-group">
                    <label>
                        <span class="osb-label-text">Parent/Guardian Email</span>
                        <span class="osb-required">*</span>
                    </label>
                    <input type="email" name="students[${studentFormCount}][parent_email]" class="osb-form-input" required
                           placeholder="parent@example.com">
                </div>
            </div>

            <div class="osb-documents-section">
                <h5>📋 Required Documents</h5>
                <div class="osb-documents-row">
                    <div class="osb-document-upload">
                        <label>Birth Certificate <span class="osb-required">*</span></label>
                        <input type="file" name="students[${studentFormCount}][birth_certificate]"
                               accept=".pdf,.jpg,.jpeg,.png" required class="osb-file-input">
                    </div>
                    <div class="osb-document-upload">
                        <label>Parental Consent <span class="osb-required">*</span></label>
                        <input type="file" name="students[${studentFormCount}][parental_consent]"
                               accept=".pdf,.jpg,.jpeg,.png" required class="osb-file-input">
                    </div>
                </div>
            </div>
        </div>
    `;

    container.insertAdjacentHTML('beforeend', formHtml);
    updateStudentCount();
    updateSubmitButtonState();

    // Scroll to new form
    const newForm = document.getElementById(`student-form-${studentFormCount}`);
    if (newForm) {
        newForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
    }
}

function removeStudentForm(formId) {
    const form = document.getElementById(`student-form-${formId}`);
    if (form) {
        form.remove();
        updateStudentCount();
        updateSubmitButtonState();

        // Renumber remaining forms
        const remainingForms = document.querySelectorAll('.osb-student-form-card');
        remainingForms.forEach((form, index) => {
            const header = form.querySelector('.osb-student-form-header h4');
            if (header) {
                header.textContent = `👤 Student ${currentStudents.length + index + 1}`;
            }
        });
    }
}

function updateStudentCount() {
    const countDisplay = document.getElementById('current-student-count');
    const formsContainer = document.getElementById('multi-student-forms');

    if (countDisplay && formsContainer) {
        const formCount = formsContainer.querySelectorAll('.osb-student-form-card').length;
        const totalCount = currentStudents.length + formCount;
        countDisplay.textContent = totalCount;
    }
}

function updateSubmitButtonState() {
    const submitBtn = document.getElementById('multi-student-submit');
    const addBtn = document.getElementById('add-student-form-btn');
    const formsContainer = document.getElementById('multi-student-forms');

    if (!submitBtn || !formsContainer || !currentEvent) return;

    const formCount = formsContainer.querySelectorAll('.osb-student-form-card').length;
    const totalCount = currentStudents.length + formCount;
    const minStudents = parseInt(currentEvent.min_students_per_school || 1);
    const maxStudents = parseInt(currentEvent.max_students_per_school || 999);

    // Enable/disable submit button based on minimum requirement
    submitBtn.disabled = totalCount < minStudents;

    // Enable/disable add button based on maximum limit
    if (addBtn) {
        addBtn.disabled = totalCount >= maxStudents;
    }

    // Update submit button text
    if (totalCount < minStudents) {
        submitBtn.innerHTML = `<span class="osb-btn-icon">⚠️</span> Need ${minStudents - totalCount} More Student${minStudents - totalCount > 1 ? 's' : ''}`;
    } else {
        submitBtn.innerHTML = `<span class="osb-btn-icon">🚀</span> Register ${formCount} Student${formCount > 1 ? 's' : ''}`;
    }
}

function submitMultiStudentForms() {
    const formsContainer = document.getElementById('multi-student-forms');
    const submitBtn = document.getElementById('multi-student-submit');

    if (!formsContainer || !submitBtn) return;

    const forms = formsContainer.querySelectorAll('.osb-student-form-card');
    if (forms.length === 0) {
        alert('Please add at least one student before submitting.');
        return;
    }

    // Validate all forms
    let allValid = true;
    const studentsData = [];

    forms.forEach((form, index) => {
        const formData = new FormData();
        const inputs = form.querySelectorAll('input, select');
        let formValid = true;
        const studentData = {};

        inputs.forEach(input => {
            if (input.type === 'file') {
                if (input.files.length === 0 && input.required) {
                    formValid = false;
                    allValid = false;
                    input.style.borderColor = '#dc3545';
                } else if (input.files.length > 0) {
                    studentData[input.name.split('[')[2].replace(']', '')] = input.files[0];
                }
            } else {
                if (!input.value.trim() && input.required) {
                    formValid = false;
                    allValid = false;
                    input.style.borderColor = '#dc3545';
                } else {
                    input.style.borderColor = '';
                    studentData[input.name.split('[')[2].replace(']', '')] = input.value.trim();
                }
            }
        });

        if (formValid) {
            studentsData.push(studentData);
        }
    });

    if (!allValid) {
        alert('Please fill in all required fields and upload all required documents.');
        return;
    }

    // Show loading state
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="osb-btn-icon">⏳</span> Registering Students...';

    // Submit each student individually
    let submitted = 0;
    let errors = [];

    studentsData.forEach((studentData, index) => {
        const formData = new FormData();
        formData.append('action', 'osb_add_student');
        formData.append('token', '<?php echo esc_js(sanitize_text_field($_GET['token'] ?? '')); ?>');
        formData.append('nonce', '<?php echo wp_create_nonce('osb_dashboard_nonce'); ?>');

        // Add student data
        Object.keys(studentData).forEach(key => {
            formData.append(key, studentData[key]);
        });

        // Submit via AJAX
        fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            submitted++;

            if (!data.success) {
                errors.push(`Student ${index + 1}: ${data.data || 'Unknown error'}`);
            }

            // Check if all submissions are complete
            if (submitted === studentsData.length) {
                if (errors.length === 0) {
                    alert('All students registered successfully!');
                    window.location.reload();
                } else {
                    alert('Some registrations failed:\n' + errors.join('\n'));
                    submitBtn.disabled = false;
                    updateSubmitButtonState();
                }
            }
        })
        .catch(error => {
            submitted++;
            errors.push(`Student ${index + 1}: Network error`);
            console.error('Student registration error:', error);

            if (submitted === studentsData.length) {
                alert('Some registrations failed:\n' + errors.join('\n'));
                submitBtn.disabled = false;
                updateSubmitButtonState();
            }
        });
    });
}
</script>