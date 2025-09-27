<?php
/**
 * SpellingBee Dashboard Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Variables passed from shortcode
$registration = isset($registration) ? $registration : null;
$students = isset($students) ? $students : array();
?>

<div class="osb-spellingbee-dashboard">
    <?php if ($registration): ?>
        <div class="osb-status-header">
            <h2 class="osb-status-title">SpellingBee Dashboard</h2>
            <div class="osb-status-badge">
                <span class="osb-status osb-status-<?php echo esc_attr($registration->status); ?>">
                    <?php echo esc_html(ucfirst($registration->status)); ?>
                </span>
            </div>
        </div>

        <div class="osb-status-content">
            <!-- School Information -->
            <div class="osb-section osb-school-info">
                <h3>School Information</h3>
                <div class="osb-info-grid">
                    <div class="osb-info-item">
                        <label>School Name</label>
                        <span><?php echo esc_html($registration->school_name); ?></span>
                    </div>
                    <div class="osb-info-item">
                        <label>Registration Date</label>
                        <span><?php echo date('F j, Y', strtotime($registration->created_at)); ?></span>
                    </div>
                    <div class="osb-info-item">
                        <label>Contact Person</label>
                        <span><?php echo esc_html($registration->contact_name); ?></span>
                    </div>
                    <div class="osb-info-item">
                        <label>Email</label>
                        <span><?php echo esc_html($registration->contact_email); ?></span>
                    </div>
                </div>
            </div>

            <!-- Registration Details -->
            <?php if ($registration->status === 'approved'): ?>
                <div class="osb-section osb-approval-info">
                    <div class="osb-success-message">
                        <div class="osb-success-icon">✅</div>
                        <div class="osb-success-content">
                            <h3>Registration Approved!</h3>
                            <p>Congratulations! Your school has been approved to participate in the competition.</p>
                            <?php if (!empty($registration->approved_at)): ?>
                                <small>Approved on <?php echo date('F j, Y', strtotime($registration->approved_at)); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <?php elseif ($registration->status === 'pending'): ?>
                <div class="osb-section osb-pending-info">
                    <div class="osb-pending-message">
                        <div class="osb-pending-icon">⏳</div>
                        <div class="osb-pending-content">
                            <h3>Registration Under Review</h3>
                            <p>Your registration is currently being reviewed by our team. You will receive an email notification once a decision has been made.</p>
                            <small>Submitted on <?php echo date('F j, Y', strtotime($registration->created_at)); ?></small>
                        </div>
                    </div>
                </div>

            <?php elseif ($registration->status === 'rejected'): ?>
                <div class="osb-section osb-rejection-info">
                    <div class="osb-error-message">
                        <div class="osb-error-icon">❌</div>
                        <div class="osb-error-content">
                            <h3>Registration Not Approved</h3>
                            <p>Unfortunately, your registration was not approved for this competition.</p>
                            <?php if (!empty($registration->rejection_reason)): ?>
                                <div class="osb-rejection-reason">
                                    <strong>Reason:</strong> <?php echo esc_html($registration->rejection_reason); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($registration->rejected_at)): ?>
                                <small>Decision made on <?php echo date('F j, Y', strtotime($registration->rejected_at)); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Students List -->
            <?php if (!empty($students)): ?>
                <div class="osb-section osb-students-section">
                    <h3>Registered Students (<?php echo count($students); ?>)</h3>
                    <div class="osb-students-grid">
                        <?php foreach ($students as $student): ?>
                            <div class="osb-student-card">
                                <div class="osb-student-header">
                                    <div class="osb-student-avatar">
                                        <?php echo strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)); ?>
                                    </div>
                                    <div class="osb-student-info">
                                        <h4><?php echo esc_html($student->first_name . ' ' . $student->last_name); ?></h4>
                                        <?php if (!empty($student->student_id)): ?>
                                            <span class="osb-student-id">ID: <?php echo esc_html($student->student_id); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="osb-student-details">
                                    <?php if (!empty($student->age)): ?>
                                        <div class="osb-detail">
                                            <label>Age:</label>
                                            <span><?php echo intval($student->age); ?> years</span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($student->grade)): ?>
                                        <div class="osb-detail">
                                            <label>Grade:</label>
                                            <span><?php echo esc_html($student->grade); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($student->previous_participation)): ?>
                                        <div class="osb-detail">
                                            <label>Previous Participation:</label>
                                            <span class="osb-yes">Yes</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Document Upload Section -->
            <div class="osb-section osb-documents-section">
                <h3>📋 Required Documents</h3>
                <p class="osb-section-description">Upload the required documents to complete your registration process.</p>

                <div class="osb-documents-grid">
                    <!-- School Registration Certificate -->
                    <div class="osb-document-card">
                        <div class="osb-document-header">
                            <div class="osb-document-icon">🏫</div>
                            <div class="osb-document-info">
                                <h4>School Registration Certificate</h4>
                                <span class="osb-document-status osb-status-pending">Pending</span>
                            </div>
                        </div>
                        <div class="osb-document-upload">
                            <input type="file" id="school-cert" class="osb-file-input" accept=".pdf,.jpg,.jpeg,.png" data-doc-type="school_certificate">
                            <label for="school-cert" class="osb-upload-btn">
                                <span class="osb-upload-icon">📤</span>
                                Choose File
                            </label>
                            <div class="osb-upload-info">
                                <small>PDF, JPG, PNG (Max 5MB)</small>
                            </div>
                        </div>
                    </div>

                    <!-- Student Birth Certificates -->
                    <div class="osb-document-card">
                        <div class="osb-document-header">
                            <div class="osb-document-icon">🆔</div>
                            <div class="osb-document-info">
                                <h4>Student Birth Certificates</h4>
                                <span class="osb-document-status osb-status-pending">Pending</span>
                            </div>
                        </div>
                        <div class="osb-document-upload">
                            <input type="file" id="birth-certs" class="osb-file-input" accept=".pdf,.jpg,.jpeg,.png" multiple data-doc-type="birth_certificates">
                            <label for="birth-certs" class="osb-upload-btn">
                                <span class="osb-upload-icon">📤</span>
                                Choose Files
                            </label>
                            <div class="osb-upload-info">
                                <small>Multiple files allowed (Max 5MB each)</small>
                            </div>
                        </div>
                    </div>

                    <!-- Parental Consent Forms -->
                    <div class="osb-document-card">
                        <div class="osb-document-header">
                            <div class="osb-document-icon">✍️</div>
                            <div class="osb-document-info">
                                <h4>Parental Consent Forms</h4>
                                <span class="osb-document-status osb-status-pending">Pending</span>
                            </div>
                        </div>
                        <div class="osb-document-upload">
                            <input type="file" id="consent-forms" class="osb-file-input" accept=".pdf,.jpg,.jpeg,.png" multiple data-doc-type="consent_forms">
                            <label for="consent-forms" class="osb-upload-btn">
                                <span class="osb-upload-icon">📤</span>
                                Choose Files
                            </label>
                            <div class="osb-upload-info">
                                <small>Multiple files allowed (Max 5MB each)</small>
                            </div>
                        </div>
                    </div>

                    <!-- School Endorsement Letter -->
                    <div class="osb-document-card">
                        <div class="osb-document-header">
                            <div class="osb-document-icon">👨‍💼</div>
                            <div class="osb-document-info">
                                <h4>School Endorsement Letter</h4>
                                <span class="osb-document-status osb-status-pending">Pending</span>
                            </div>
                        </div>
                        <div class="osb-document-upload">
                            <input type="file" id="endorsement-letter" class="osb-file-input" accept=".pdf,.jpg,.jpeg,.png" data-doc-type="endorsement_letter">
                            <label for="endorsement-letter" class="osb-upload-btn">
                                <span class="osb-upload-icon">📤</span>
                                Choose File
                            </label>
                            <div class="osb-upload-info">
                                <small>PDF, JPG, PNG (Max 5MB)</small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="osb-documents-progress">
                    <div class="osb-progress-header">
                        <h4>📊 Upload Progress</h4>
                        <span class="osb-progress-text">0 of 4 documents uploaded</span>
                    </div>
                    <div class="osb-progress-bar">
                        <div class="osb-progress-fill" style="width: 0%"></div>
                    </div>
                </div>

                <div class="osb-documents-help">
                    <h4>💡 Upload Tips</h4>
                    <ul>
                        <li>Ensure documents are clear and readable</li>
                        <li>Use high-quality scans or photos</li>
                        <li>File names should include student names where applicable</li>
                        <li>All documents must be submitted before the deadline</li>
                    </ul>
                </div>
            </div>

            <!-- Student Registration Section -->
            <div class="osb-section osb-student-registration">
                <div class="osb-section-header">
                    <h3>🎓 Student Registration</h3>
                    <button class="osb-add-student-btn" id="osb-add-student-btn">
                        <span class="osb-btn-icon">+</span>
                        Add Student
                    </button>
                </div>
                <p class="osb-section-description">Register your students for the competition. You can add between 3-5 students per school.</p>

                <div class="osb-students-container" id="osb-students-container">
                    <!-- Existing students will be loaded here -->
                    <?php if (!empty($students)): ?>
                        <?php foreach ($students as $index => $student): ?>
                            <div class="osb-student-form" data-student-index="<?php echo $index; ?>">
                                <div class="osb-student-form-header">
                                    <h4>Student <?php echo ($index + 1); ?></h4>
                                    <button type="button" class="osb-remove-student-btn" data-student-index="<?php echo $index; ?>">×</button>
                                </div>
                                <div class="osb-student-form-grid">
                                    <div class="osb-form-group">
                                        <label>First Name *</label>
                                        <input type="text" name="students[<?php echo $index; ?>][first_name]" value="<?php echo esc_attr($student->first_name); ?>" required>
                                    </div>
                                    <div class="osb-form-group">
                                        <label>Last Name *</label>
                                        <input type="text" name="students[<?php echo $index; ?>][last_name]" value="<?php echo esc_attr($student->last_name); ?>" required>
                                    </div>
                                    <div class="osb-form-group">
                                        <label>Age *</label>
                                        <input type="number" name="students[<?php echo $index; ?>][age]" value="<?php echo esc_attr($student->age); ?>" min="13" max="18" required>
                                    </div>
                                    <div class="osb-form-group">
                                        <label>Grade *</label>
                                        <select name="students[<?php echo $index; ?>][grade]" required>
                                            <option value="">Select Grade</option>
                                            <option value="JSS1" <?php selected($student->grade, 'JSS1'); ?>>JSS 1</option>
                                            <option value="JSS2" <?php selected($student->grade, 'JSS2'); ?>>JSS 2</option>
                                            <option value="JSS3" <?php selected($student->grade, 'JSS3'); ?>>JSS 3</option>
                                            <option value="SS1" <?php selected($student->grade, 'SS1'); ?>>SS 1</option>
                                            <option value="SS2" <?php selected($student->grade, 'SS2'); ?>>SS 2</option>
                                            <option value="SS3" <?php selected($student->grade, 'SS3'); ?>>SS 3</option>
                                        </select>
                                    </div>
                                    <div class="osb-form-group">
                                        <label>Date of Birth *</label>
                                        <input type="date" name="students[<?php echo $index; ?>][date_of_birth]" value="<?php echo esc_attr($student->date_of_birth); ?>" required>
                                    </div>
                                    <div class="osb-form-group">
                                        <label>Student ID (Optional)</label>
                                        <input type="text" name="students[<?php echo $index; ?>][student_id]" value="<?php echo esc_attr($student->student_id); ?>" placeholder="School student ID">
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="osb-student-actions">
                    <button type="button" class="osb-save-students-btn" id="osb-save-students-btn">
                        <span class="osb-btn-icon">💾</span>
                        Save Students
                    </button>
                    <div class="osb-student-count">
                        <span id="osb-student-count"><?php echo count($students); ?></span> of 5 students registered
                    </div>
                </div>

                <div class="osb-student-help">
                    <h4>💡 Student Registration Tips</h4>
                    <ul>
                        <li>Each school must register between 3-5 students</li>
                        <li>Students must be between 13-18 years old</li>
                        <li>Ensure all information is accurate as it will be used for certificates</li>
                        <li>Student IDs are optional but recommended for internal tracking</li>
                    </ul>
                </div>
            </div>

            <!-- Next Steps -->
            <?php if ($registration->status === 'approved'): ?>
                <div class="osb-section osb-next-steps">
                    <h3>Next Steps</h3>
                    <div class="osb-steps-list">
                        <div class="osb-step">
                            <div class="osb-step-number">1</div>
                            <div class="osb-step-content">
                                <h4>Prepare Your Students</h4>
                                <p>Download study materials and practice words from the resources section.</p>
                            </div>
                        </div>
                        <div class="osb-step">
                            <div class="osb-step-number">2</div>
                            <div class="osb-step-content">
                                <h4>Event Details</h4>
                                <p>You will receive detailed event information and logistics via email closer to the competition date.</p>
                            </div>
                        </div>
                        <div class="osb-step">
                            <div class="osb-step-number">3</div>
                            <div class="osb-step-content">
                                <h4>Competition Day</h4>
                                <p>Bring all students and required documentation on the day of the competition.</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Contact Information -->
            <div class="osb-section osb-contact-info">
                <h3>Need Help?</h3>
                <div class="osb-contact-grid">
                    <div class="osb-contact-item">
                        <div class="osb-contact-icon">📧</div>
                        <div class="osb-contact-content">
                            <label>Email Support</label>
                            <a href="mailto:support@omafarufoundation.org">support@omafarufoundation.org</a>
                        </div>
                    </div>
                    <div class="osb-contact-item">
                        <div class="osb-contact-icon">📞</div>
                        <div class="osb-contact-content">
                            <label>Phone Support</label>
                            <a href="tel:+1234567890">+1 (234) 567-8900</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <div class="osb-dashboard-login">
            <div class="osb-login-header">
                <h2>Access Your SpellingBee Dashboard</h2>
                <p>Enter your registration token or email address to access your school's dashboard.</p>
            </div>

            <div class="osb-login-form">
                <form id="osb-dashboard-login-form">
                    <div class="osb-form-group">
                        <label for="login-token">Registration Token</label>
                        <input type="text" id="login-token" name="token" placeholder="Enter your registration token" class="osb-form-input">
                        <small class="osb-help-text">You received this in your confirmation email</small>
                    </div>

                    <div class="osb-form-divider">
                        <span>OR</span>
                    </div>

                    <div class="osb-form-group">
                        <label for="login-email">School Email Address</label>
                        <input type="email" id="login-email" name="email" placeholder="Enter your school email" class="osb-form-input">
                        <small class="osb-help-text">We'll send you a dashboard link</small>
                    </div>

                    <button type="submit" class="osb-login-btn">Access Dashboard</button>
                </form>
            </div>

            <div class="osb-login-help">
                <div class="osb-help-section">
                    <h3>Need Help?</h3>
                    <ul>
                        <li><strong>Lost your token?</strong> Use your email address above</li>
                        <li><strong>Changed email?</strong> Contact us at <a href="mailto:support@omafarufoundation.org">support@omafarufoundation.org</a></li>
                        <li><strong>Haven't registered yet?</strong> <a href="/spelling-bee/">Register your school here</a></li>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
/* Registration Status Styles */
.osb-spellingbee-dashboard {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

.osb-status-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e9ecef;
    flex-wrap: wrap;
    gap: 20px;
}

.osb-status-title {
    font-size: 2.2rem;
    font-weight: bold;
    color: #0073aa;
    margin: 0;
}

.osb-status-badge {
    flex-shrink: 0;
}

.osb-status {
    display: inline-block;
    padding: 12px 24px;
    border-radius: 25px;
    font-size: 1rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.osb-status-approved { background: #d4edda; color: #155724; border: 2px solid #c3e6cb; }
.osb-status-pending { background: #fff3cd; color: #856404; border: 2px solid #ffeaa7; }
.osb-status-rejected { background: #f8d7da; color: #721c24; border: 2px solid #f5c6cb; }

.osb-status-content {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.osb-section {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.osb-section h3 {
    font-size: 1.5rem;
    color: #333;
    margin: 0 0 20px 0;
    font-weight: 600;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 10px;
}

.osb-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.osb-info-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.osb-info-item label {
    font-weight: 600;
    color: #666;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.osb-info-item span {
    font-size: 1rem;
    color: #333;
    font-weight: 500;
}

.osb-success-message,
.osb-pending-message,
.osb-error-message {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 25px;
    border-radius: 12px;
    margin: 0;
}

.osb-success-message {
    background: #d4edda;
    border-left: 5px solid #28a745;
}

.osb-pending-message {
    background: #fff3cd;
    border-left: 5px solid #ffc107;
}

.osb-error-message {
    background: #f8d7da;
    border-left: 5px solid #dc3545;
}

.osb-success-icon,
.osb-pending-icon,
.osb-error-icon {
    font-size: 2.5rem;
    flex-shrink: 0;
}

.osb-success-content h3,
.osb-pending-content h3,
.osb-error-content h3 {
    margin: 0 0 10px 0;
    font-size: 1.3rem;
    border: none;
    padding: 0;
}

.osb-success-content p,
.osb-pending-content p,
.osb-error-content p {
    margin: 0 0 10px 0;
    font-size: 1rem;
    line-height: 1.6;
}

.osb-rejection-reason {
    background: rgba(255,255,255,0.5);
    padding: 15px;
    border-radius: 8px;
    margin: 15px 0;
    font-size: 0.95rem;
}

.osb-students-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.osb-student-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.osb-student-card:hover {
    border-color: #0073aa;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,115,170,0.15);
}

.osb-student-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.osb-student-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #0073aa;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.1rem;
}

.osb-student-info h4 {
    margin: 0 0 5px 0;
    font-size: 1.1rem;
    color: #333;
}

.osb-student-id {
    font-size: 0.85rem;
    color: #666;
    font-family: monospace;
}

.osb-student-details {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.osb-detail {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.9rem;
}

.osb-detail label {
    color: #666;
    font-weight: 500;
}

.osb-detail span {
    color: #333;
    font-weight: 600;
}

.osb-yes {
    color: #28a745 !important;
}

.osb-steps-list {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.osb-step {
    display: flex;
    align-items: flex-start;
    gap: 20px;
}

.osb-step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #0073aa;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.osb-step-content h4 {
    margin: 0 0 8px 0;
    font-size: 1.1rem;
    color: #333;
}

.osb-step-content p {
    margin: 0;
    color: #666;
    line-height: 1.6;
}

.osb-contact-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.osb-contact-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.osb-contact-item:hover {
    border-color: #0073aa;
    background: #e3f2fd;
}

.osb-contact-icon {
    font-size: 1.8rem;
    opacity: 0.8;
}

.osb-contact-content label {
    display: block;
    font-weight: 600;
    color: #333;
    margin-bottom: 3px;
    font-size: 0.9rem;
}

.osb-contact-content a {
    color: #0073aa;
    text-decoration: none;
    font-weight: 500;
}

.osb-contact-content a:hover {
    text-decoration: underline;
}

.osb-no-registration {
    margin: 40px 0;
}

.osb-empty-state {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 15px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.osb-empty-icon {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.7;
}

.osb-empty-state h3 {
    color: #333;
    margin-bottom: 15px;
    font-size: 1.8rem;
    font-weight: 600;
}

.osb-empty-state p {
    color: #666;
    font-size: 1.1rem;
    line-height: 1.6;
    margin-bottom: 25px;
}

.osb-contact-btn {
    background: #0073aa;
    color: white;
    padding: 12px 30px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-block;
}

.osb-contact-btn:hover {
    background: #005177;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,115,170,0.4);
    text-decoration: none;
    color: white;
}

/* Responsive Design */
@media (max-width: 768px) {
    .osb-spellingbee-dashboard {
        padding: 15px;
    }

    .osb-status-header {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }

    .osb-status-title {
        font-size: 1.8rem;
    }

    .osb-section {
        padding: 25px;
    }

    .osb-info-grid {
        grid-template-columns: 1fr;
    }

    .osb-students-grid {
        grid-template-columns: 1fr;
    }

    .osb-success-message,
    .osb-pending-message,
    .osb-error-message {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }

    .osb-contact-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 600px) {
    .osb-status-title {
        font-size: 1.6rem;
    }

    .osb-section {
        padding: 20px;
    }

    .osb-student-header {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }

    .osb-step {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }

    .osb-contact-item {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }
}

/* Dashboard Login Styles */
.osb-dashboard-login {
    max-width: 500px;
    margin: 0 auto;
    padding: 40px 20px;
}

.osb-login-header {
    text-align: center;
    margin-bottom: 40px;
}

.osb-login-header h2 {
    color: #2c3e50;
    font-size: 2.2rem;
    margin-bottom: 10px;
    font-weight: 600;
}

.osb-login-header p {
    color: #666;
    font-size: 1.1rem;
    line-height: 1.6;
}

.osb-login-form {
    background: white;
    padding: 40px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.osb-form-group {
    margin-bottom: 25px;
}

.osb-form-group label {
    display: block;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 8px;
    font-size: 1rem;
}

.osb-form-input {
    width: 100%;
    padding: 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.osb-form-input:focus {
    outline: none;
    border-color: #0073aa;
    box-shadow: 0 0 0 3px rgba(0,115,170,0.1);
}

.osb-help-text {
    display: block;
    color: #666;
    font-size: 0.9rem;
    margin-top: 5px;
    font-style: italic;
}

.osb-form-divider {
    text-align: center;
    margin: 30px 0;
    position: relative;
}

.osb-form-divider::before {
    content: '';
    position: absolute;
    top: 50%;
    left: 0;
    right: 0;
    height: 1px;
    background: #e9ecef;
    z-index: 1;
}

.osb-form-divider span {
    background: white;
    padding: 0 20px;
    color: #666;
    font-weight: 600;
    position: relative;
    z-index: 2;
}

.osb-login-btn {
    width: 100%;
    background: linear-gradient(135deg, #0073aa, #005177);
    color: white;
    padding: 15px;
    border: none;
    border-radius: 8px;
    font-size: 1.1rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.osb-login-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,115,170,0.4);
}

.osb-login-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.osb-login-help {
    background: #f8f9fa;
    padding: 30px;
    border-radius: 15px;
    border: 1px solid #e9ecef;
}

.osb-help-section h3 {
    color: #2c3e50;
    margin-bottom: 15px;
    font-size: 1.3rem;
}

.osb-help-section ul {
    list-style: none;
    padding: 0;
    margin: 0;
}

.osb-help-section li {
    margin-bottom: 12px;
    padding-left: 20px;
    position: relative;
}

.osb-help-section li::before {
    content: '💡';
    position: absolute;
    left: 0;
    top: 0;
}

.osb-help-section a {
    color: #0073aa;
    text-decoration: none;
    font-weight: 600;
}

.osb-help-section a:hover {
    text-decoration: underline;
}

@media (max-width: 768px) {
    .osb-dashboard-login {
        padding: 20px 15px;
    }

    .osb-login-form {
        padding: 25px;
    }

    .osb-login-header h2 {
        font-size: 1.8rem;
    }
}

/* Document Upload Styles */
.osb-documents-section {
    border: 2px solid #e9ecef;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
}

.osb-section-description {
    color: #666;
    margin-bottom: 25px;
    font-size: 1.1rem;
    line-height: 1.6;
}

.osb-documents-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 20px;
    margin-bottom: 30px;
}

.osb-document-card {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    transition: all 0.3s ease;
    position: relative;
}

.osb-document-card:hover {
    border-color: #0073aa;
    box-shadow: 0 5px 15px rgba(0,115,170,0.1);
    transform: translateY(-2px);
}

.osb-document-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.osb-document-icon {
    font-size: 2rem;
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
    border-radius: 10px;
    border: 2px solid #e9ecef;
}

.osb-document-info h4 {
    margin: 0 0 5px 0;
    color: #2c3e50;
    font-size: 1.1rem;
    font-weight: 600;
}

.osb-document-status {
    font-size: 0.85rem;
    padding: 4px 8px;
    border-radius: 12px;
    font-weight: 600;
    text-transform: uppercase;
}

.osb-status-pending {
    background: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

.osb-status-uploaded {
    background: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.osb-status-approved {
    background: #cce7ff;
    color: #0056b3;
    border: 1px solid #99d3ff;
}

.osb-document-upload {
    text-align: center;
}

.osb-file-input {
    display: none;
}

.osb-upload-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: linear-gradient(135deg, #0073aa, #005177);
    color: white;
    padding: 12px 20px;
    border-radius: 8px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    border: none;
    font-size: 1rem;
}

.osb-upload-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,115,170,0.4);
    color: white;
    text-decoration: none;
}

.osb-upload-icon {
    font-size: 1.1rem;
}

.osb-upload-info {
    margin-top: 8px;
}

.osb-upload-info small {
    color: #666;
    font-style: italic;
}

.osb-documents-progress {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    margin-bottom: 25px;
}

.osb-progress-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.osb-progress-header h4 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.2rem;
}

.osb-progress-text {
    color: #666;
    font-weight: 600;
}

.osb-progress-bar {
    width: 100%;
    height: 12px;
    background: #e9ecef;
    border-radius: 6px;
    overflow: hidden;
}

.osb-progress-fill {
    height: 100%;
    background: linear-gradient(135deg, #28a745, #20c997);
    transition: width 0.5s ease;
    border-radius: 6px;
}

.osb-documents-help {
    background: #e3f2fd;
    border: 2px solid #90caf9;
    border-radius: 12px;
    padding: 20px;
}

.osb-documents-help h4 {
    margin-top: 0;
    color: #1565c0;
    font-size: 1.1rem;
}

.osb-documents-help ul {
    margin: 15px 0 0 0;
    padding-left: 20px;
}

.osb-documents-help li {
    margin-bottom: 8px;
    color: #1565c0;
    line-height: 1.5;
}

@media (max-width: 768px) {
    .osb-documents-grid {
        grid-template-columns: 1fr;
    }

    .osb-progress-header {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }

    .osb-documents-section {
        padding: 20px;
    }
}

/* Student Registration Styles */
.osb-student-registration {
    border: 2px solid #e9ecef;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
    background: linear-gradient(135deg, #e3f2fd 0%, #bbdefb 100%);
}

.osb-section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
}

.osb-section-header h3 {
    margin: 0;
    color: #1565c0;
    font-size: 1.5rem;
}

.osb-add-student-btn {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    border: none;
    padding: 12px 20px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
}

.osb-add-student-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(40,167,69,0.4);
}

.osb-add-student-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.osb-btn-icon {
    font-size: 1.1rem;
    font-weight: bold;
}

.osb-students-container {
    margin-bottom: 25px;
}

.osb-student-form {
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 25px;
    margin-bottom: 20px;
    transition: all 0.3s ease;
}

.osb-student-form:hover {
    border-color: #1565c0;
    box-shadow: 0 5px 15px rgba(21,101,192,0.1);
}

.osb-student-form-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 2px solid #f8f9fa;
}

.osb-student-form-header h4 {
    margin: 0;
    color: #2c3e50;
    font-size: 1.2rem;
}

.osb-remove-student-btn {
    background: #dc3545;
    color: white;
    border: none;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    font-size: 1.2rem;
    font-weight: bold;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.osb-remove-student-btn:hover {
    background: #c82333;
    transform: scale(1.1);
}

.osb-student-form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.osb-student-form .osb-form-group {
    margin-bottom: 0;
}

.osb-student-form .osb-form-group label {
    display: block;
    font-weight: 600;
    color: #2c3e50;
    margin-bottom: 5px;
    font-size: 0.9rem;
}

.osb-student-form input,
.osb-student-form select {
    width: 100%;
    padding: 10px;
    border: 2px solid #e9ecef;
    border-radius: 6px;
    font-size: 1rem;
    transition: all 0.3s ease;
    box-sizing: border-box;
}

.osb-student-form input:focus,
.osb-student-form select:focus {
    outline: none;
    border-color: #1565c0;
    box-shadow: 0 0 0 3px rgba(21,101,192,0.1);
}

.osb-student-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 25px;
    padding: 20px;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 12px;
}

.osb-save-students-btn {
    background: linear-gradient(135deg, #0073aa, #005177);
    color: white;
    border: none;
    padding: 15px 25px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.3s ease;
    font-size: 1rem;
}

.osb-save-students-btn:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,115,170,0.4);
}

.osb-save-students-btn:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
    box-shadow: none;
}

.osb-student-count {
    font-weight: 600;
    color: #1565c0;
    font-size: 1rem;
}

.osb-student-help {
    background: #f3e5f5;
    border: 2px solid #ce93d8;
    border-radius: 12px;
    padding: 20px;
}

.osb-student-help h4 {
    margin-top: 0;
    color: #7b1fa2;
    font-size: 1.1rem;
}

.osb-student-help ul {
    margin: 15px 0 0 0;
    padding-left: 20px;
}

.osb-student-help li {
    margin-bottom: 8px;
    color: #7b1fa2;
    line-height: 1.5;
}

@media (max-width: 768px) {
    .osb-section-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }

    .osb-student-form-grid {
        grid-template-columns: 1fr;
    }

    .osb-student-actions {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }

    .osb-student-registration {
        padding: 20px;
    }
}

</style>

<script>
jQuery(document).ready(function($) {
    $('#osb-dashboard-login-form').on('submit', function(e) {
        e.preventDefault();

        const token = $('#login-token').val().trim();
        const email = $('#login-email').val().trim();
        const submitBtn = $('.osb-login-btn');

        // Validation
        if (!token && !email) {
            alert('Please enter either your registration token or email address.');
            return;
        }

        // Show loading state
        const originalText = submitBtn.text();
        submitBtn.text('Accessing Dashboard...').prop('disabled', true);

        if (token) {
            // Redirect with token
            window.location.href = '/spellingbee-dashboard/?token=' + encodeURIComponent(token);
        } else if (email) {
            // Send dashboard link via email
            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'osb_send_dashboard_link',
                    email: email,
                    nonce: '<?php echo wp_create_nonce('osb_dashboard_login'); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        alert('Dashboard link sent to your email! Please check your inbox.');
                        $('#login-email').val('');
                    } else {
                        alert('Error: ' + response.data);
                    }
                    submitBtn.text(originalText).prop('disabled', false);
                },
                error: function() {
                    alert('Network error. Please try again.');
                    submitBtn.text(originalText).prop('disabled', false);
                }
            });
        }
    });

    // Clear other field when typing in one
    $('#login-token').on('input', function() {
        if ($(this).val().trim()) {
            $('#login-email').val('');
        }
    });

    $('#login-email').on('input', function() {
        if ($(this).val().trim()) {
            $('#login-token').val('');
        }
    });

    // Document upload functionality
    $('.osb-file-input').on('change', function() {
        const fileInput = this;
        const docType = $(this).data('doc-type');
        const files = fileInput.files;

        if (files.length === 0) return;

        // Validate file size (5MB max)
        for (let i = 0; i < files.length; i++) {
            if (files[i].size > 5 * 1024 * 1024) {
                alert('File "' + files[i].name + '" is too large. Maximum size is 5MB.');
                fileInput.value = '';
                return;
            }
        }

        // Update UI to show upload in progress
        const documentCard = $(this).closest('.osb-document-card');
        const statusSpan = documentCard.find('.osb-document-status');
        const uploadBtn = documentCard.find('.osb-upload-btn');

        statusSpan.removeClass('osb-status-pending').addClass('osb-status-uploading');
        statusSpan.text('Uploading...');
        uploadBtn.text('Uploading...');

        // Prepare form data
        const formData = new FormData();
        for (let i = 0; i < files.length; i++) {
            formData.append('documents[]', files[i]);
        }
        formData.append('action', 'osb_upload_documents');
        formData.append('doc_type', docType);
        formData.append('token', '<?php echo esc_js($_GET['token'] ?? ''); ?>');
        formData.append('nonce', '<?php echo wp_create_nonce('osb_document_upload'); ?>');

        // Upload files
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                if (response.success) {
                    statusSpan.removeClass('osb-status-uploading').addClass('osb-status-uploaded');
                    statusSpan.text('Uploaded');
                    uploadBtn.html('<span class="osb-upload-icon">✅</span> Uploaded');

                    // Update progress
                    updateUploadProgress();

                    alert('Documents uploaded successfully!');
                } else {
                    statusSpan.removeClass('osb-status-uploading').addClass('osb-status-pending');
                    statusSpan.text('Pending');
                    uploadBtn.html('<span class="osb-upload-icon">📤</span> Choose File' + (files.length > 1 ? 's' : ''));

                    alert('Upload failed: ' + response.data);
                }
                fileInput.value = '';
            },
            error: function() {
                statusSpan.removeClass('osb-status-uploading').addClass('osb-status-pending');
                statusSpan.text('Pending');
                uploadBtn.html('<span class="osb-upload-icon">📤</span> Choose File' + (files.length > 1 ? 's' : ''));

                alert('Network error. Please try again.');
                fileInput.value = '';
            }
        });
    });

    // Function to update upload progress
    function updateUploadProgress() {
        const totalDocs = $('.osb-document-card').length;
        const uploadedDocs = $('.osb-status-uploaded').length;
        const progressPercent = (uploadedDocs / totalDocs) * 100;

        $('.osb-progress-fill').css('width', progressPercent + '%');
        $('.osb-progress-text').text(uploadedDocs + ' of ' + totalDocs + ' documents uploaded');
    }

    // Initialize progress on page load
    updateUploadProgress();

    // Student registration functionality
    let studentIndex = $('.osb-student-form').length;
    const maxStudents = 5;
    const minStudents = 3;

    // Add student button
    $('#osb-add-student-btn').on('click', function() {
        if (studentIndex >= maxStudents) {
            alert('Maximum of ' + maxStudents + ' students allowed per school.');
            return;
        }

        addStudentForm();
    });

    // Remove student button (delegated event)
    $(document).on('click', '.osb-remove-student-btn', function() {
        const currentStudentCount = $('.osb-student-form').length;

        if (currentStudentCount <= minStudents) {
            alert('Minimum of ' + minStudents + ' students required per school.');
            return;
        }

        $(this).closest('.osb-student-form').remove();
        updateStudentCount();
        reindexStudentForms();
    });

    // Save students button
    $('#osb-save-students-btn').on('click', function() {
        const studentForms = $('.osb-student-form');
        const students = [];
        let isValid = true;

        // Validate and collect student data
        studentForms.each(function(index) {
            const form = $(this);
            const student = {
                first_name: form.find('input[name*="[first_name]"]').val().trim(),
                last_name: form.find('input[name*="[last_name]"]').val().trim(),
                age: form.find('input[name*="[age]"]').val(),
                grade: form.find('select[name*="[grade]"]').val(),
                date_of_birth: form.find('input[name*="[date_of_birth]"]').val(),
                student_id: form.find('input[name*="[student_id]"]').val().trim()
            };

            // Validation
            if (!student.first_name || !student.last_name || !student.age || !student.grade || !student.date_of_birth) {
                alert('Please fill in all required fields for Student ' + (index + 1));
                isValid = false;
                return false;
            }

            if (student.age < 13 || student.age > 18) {
                alert('Student ' + (index + 1) + ' age must be between 13 and 18 years.');
                isValid = false;
                return false;
            }

            students.push(student);
        });

        if (!isValid) return;

        if (students.length < minStudents) {
            alert('Please register at least ' + minStudents + ' students.');
            return;
        }

        // Show loading
        const saveBtn = $('#osb-save-students-btn');
        const originalText = saveBtn.html();
        saveBtn.html('<span class="osb-btn-icon">⏳</span> Saving...').prop('disabled', true);

        // Save students
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'osb_save_students',
                students: students,
                token: '<?php echo esc_js($_GET['token'] ?? ''); ?>',
                nonce: '<?php echo wp_create_nonce('osb_save_students'); ?>'
            },
            success: function(response) {
                if (response.success) {
                    alert('Students saved successfully!');
                } else {
                    alert('Error saving students: ' + response.data);
                }
                saveBtn.html(originalText).prop('disabled', false);
            },
            error: function() {
                alert('Network error. Please try again.');
                saveBtn.html(originalText).prop('disabled', false);
            }
        });
    });

    // Function to add a new student form
    function addStudentForm() {
        const newIndex = studentIndex;
        const studentHtml = `
            <div class="osb-student-form" data-student-index="${newIndex}">
                <div class="osb-student-form-header">
                    <h4>Student ${newIndex + 1}</h4>
                    <button type="button" class="osb-remove-student-btn" data-student-index="${newIndex}">×</button>
                </div>
                <div class="osb-student-form-grid">
                    <div class="osb-form-group">
                        <label>First Name *</label>
                        <input type="text" name="students[${newIndex}][first_name]" required>
                    </div>
                    <div class="osb-form-group">
                        <label>Last Name *</label>
                        <input type="text" name="students[${newIndex}][last_name]" required>
                    </div>
                    <div class="osb-form-group">
                        <label>Age *</label>
                        <input type="number" name="students[${newIndex}][age]" min="13" max="18" required>
                    </div>
                    <div class="osb-form-group">
                        <label>Grade *</label>
                        <select name="students[${newIndex}][grade]" required>
                            <option value="">Select Grade</option>
                            <option value="JSS1">JSS 1</option>
                            <option value="JSS2">JSS 2</option>
                            <option value="JSS3">JSS 3</option>
                            <option value="SS1">SS 1</option>
                            <option value="SS2">SS 2</option>
                            <option value="SS3">SS 3</option>
                        </select>
                    </div>
                    <div class="osb-form-group">
                        <label>Date of Birth *</label>
                        <input type="date" name="students[${newIndex}][date_of_birth]" required>
                    </div>
                    <div class="osb-form-group">
                        <label>Student ID (Optional)</label>
                        <input type="text" name="students[${newIndex}][student_id]" placeholder="School student ID">
                    </div>
                </div>
            </div>
        `;

        $('#osb-students-container').append(studentHtml);
        studentIndex++;
        updateStudentCount();

        // Disable add button if max reached
        if (studentIndex >= maxStudents) {
            $('#osb-add-student-btn').prop('disabled', true).html('<span class="osb-btn-icon">✓</span> Maximum Reached');
        }
    }

    // Function to update student count display
    function updateStudentCount() {
        const currentCount = $('.osb-student-form').length;
        $('#osb-student-count').text(currentCount);

        // Update add button state
        const addBtn = $('#osb-add-student-btn');
        if (currentCount >= maxStudents) {
            addBtn.prop('disabled', true).html('<span class="osb-btn-icon">✓</span> Maximum Reached');
        } else {
            addBtn.prop('disabled', false).html('<span class="osb-btn-icon">+</span> Add Student');
        }
    }

    // Function to reindex student forms after removal
    function reindexStudentForms() {
        $('.osb-student-form').each(function(index) {
            const form = $(this);
            form.attr('data-student-index', index);
            form.find('h4').text('Student ' + (index + 1));
            form.find('.osb-remove-student-btn').attr('data-student-index', index);

            // Update input names
            form.find('input, select').each(function() {
                const name = $(this).attr('name');
                if (name) {
                    const newName = name.replace(/\[\d+\]/, '[' + index + ']');
                    $(this).attr('name', newName);
                }
            });
        });

        studentIndex = $('.osb-student-form').length;
    }

    // Initialize student count on page load
    updateStudentCount();
});
</script>