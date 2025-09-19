<?php
/**
 * Registration Form Shortcode Template
 *
 * @var object $event
 * @var int $step
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$steps = array(
    1 => __('School Information', 'omafuru-spelling-bee'),
    2 => __('Student Registration', 'omafuru-spelling-bee'),
    3 => __('Document Upload', 'omafuru-spelling-bee'),
    4 => __('Review & Confirm', 'omafuru-spelling-bee'),
    5 => __('Complete Registration', 'omafuru-spelling-bee')
);
?>

<div id="osb-registration-form" class="osb-registration-container">
    <div class="osb-registration-header">
        <h2><?php printf(__('Register for %s', 'omafuru-spelling-bee'), esc_html($event->title)); ?></h2>
        <div class="osb-registration-info">
            <p><?php _e('Complete the registration process in 5 simple steps. All information is secure and confidential.', 'omafuru-spelling-bee'); ?></p>
        </div>
    </div>

    <!-- Progress Indicator -->
    <div class="osb-progress-container">
        <div class="osb-progress-bar">
            <?php foreach ($steps as $step_num => $step_name): ?>
                <div class="osb-progress-step <?php echo $step >= $step_num ? 'osb-step-completed' : ''; ?> <?php echo $step == $step_num ? 'osb-step-active' : ''; ?>">
                    <div class="osb-step-number"><?php echo $step_num; ?></div>
                    <div class="osb-step-label"><?php echo esc_html($step_name); ?></div>
                </div>
                <?php if ($step_num < count($steps)): ?>
                    <div class="osb-progress-line <?php echo $step > $step_num ? 'osb-line-completed' : ''; ?>"></div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Form Container -->
    <div class="osb-form-wrapper">
        <form id="osb-multi-step-form" class="osb-registration-form">
            <?php wp_nonce_field('osb_registration', 'osb_registration_nonce'); ?>
            <input type="hidden" name="event_id" value="<?php echo esc_attr($event->id); ?>">
            <input type="hidden" name="current_step" id="osb-current-step" value="<?php echo intval($step); ?>">

            <!-- Step 1: School Information -->
            <div id="osb-step-1" class="osb-form-step <?php echo $step == 1 ? 'osb-step-active' : 'osb-step-hidden'; ?>">
                <div class="osb-step-content">
                    <h3><?php _e('Step 1: School Information', 'omafuru-spelling-bee'); ?></h3>
                    <p class="osb-step-description">
                        <?php _e('Please provide your school details and contact information.', 'omafuru-spelling-bee'); ?>
                    </p>

                    <div class="osb-form-grid">
                        <div class="osb-form-group osb-full-width">
                            <label for="school_name"><?php _e('School Name', 'omafuru-spelling-bee'); ?> <span class="osb-required">*</span></label>
                            <input type="text" id="school_name" name="school_name" class="osb-form-control" required>
                        </div>

                        <div class="osb-form-group">
                            <label for="school_type"><?php _e('School Type', 'omafuru-spelling-bee'); ?></label>
                            <select id="school_type" name="school_type" class="osb-form-control">
                                <option value=""><?php _e('Select School Type', 'omafuru-spelling-bee'); ?></option>
                                <option value="public"><?php _e('Public School', 'omafuru-spelling-bee'); ?></option>
                                <option value="private"><?php _e('Private School', 'omafuru-spelling-bee'); ?></option>
                                <option value="federal"><?php _e('Federal School', 'omafuru-spelling-bee'); ?></option>
                                <option value="state"><?php _e('State School', 'omafuru-spelling-bee'); ?></option>
                            </select>
                        </div>

                        <div class="osb-form-group">
                            <label for="contact_person"><?php _e('Contact Person', 'omafuru-spelling-bee'); ?> <span class="osb-required">*</span></label>
                            <input type="text" id="contact_person" name="contact_person" class="osb-form-control" required>
                        </div>

                        <div class="osb-form-group osb-full-width">
                            <label for="address"><?php _e('School Address', 'omafuru-spelling-bee'); ?></label>
                            <textarea id="address" name="address" class="osb-form-control" rows="3"></textarea>
                        </div>

                        <div class="osb-form-group">
                            <label for="city"><?php _e('City', 'omafuru-spelling-bee'); ?></label>
                            <input type="text" id="city" name="city" class="osb-form-control">
                        </div>

                        <div class="osb-form-group">
                            <label for="state"><?php _e('State/Province', 'omafuru-spelling-bee'); ?></label>
                            <input type="text" id="state" name="state" class="osb-form-control">
                        </div>

                        <div class="osb-form-group">
                            <label for="postal_code"><?php _e('Postal Code', 'omafuru-spelling-bee'); ?></label>
                            <input type="text" id="postal_code" name="postal_code" class="osb-form-control">
                        </div>

                        <div class="osb-form-group">
                            <label for="country"><?php _e('Country', 'omafuru-spelling-bee'); ?></label>
                            <select id="country" name="country" class="osb-form-control">
                                <option value=""><?php _e('Select Country', 'omafuru-spelling-bee'); ?></option>
                                <option value="NG" selected><?php _e('Nigeria', 'omafuru-spelling-bee'); ?></option>
                                <option value="US"><?php _e('United States', 'omafuru-spelling-bee'); ?></option>
                                <option value="GB"><?php _e('United Kingdom', 'omafuru-spelling-bee'); ?></option>
                                <option value="CA"><?php _e('Canada', 'omafuru-spelling-bee'); ?></option>
                            </select>
                        </div>

                        <div class="osb-form-group">
                            <label for="contact_email"><?php _e('Contact Email', 'omafuru-spelling-bee'); ?> <span class="osb-required">*</span></label>
                            <input type="email" id="contact_email" name="contact_email" class="osb-form-control" required>
                        </div>

                        <div class="osb-form-group">
                            <label for="contact_phone"><?php _e('Contact Phone', 'omafuru-spelling-bee'); ?></label>
                            <input type="tel" id="contact_phone" name="contact_phone" class="osb-form-control">
                        </div>
                    </div>
                </div>

                <div class="osb-step-actions">
                    <button type="button" class="osb-btn osb-btn-primary osb-next-step" data-next="2">
                        <?php _e('Continue to Student Registration', 'omafuru-spelling-bee'); ?>
                    </button>
                </div>
            </div>

            <!-- Step 2: Student Registration -->
            <div id="osb-step-2" class="osb-form-step osb-step-hidden">
                <div class="osb-step-content">
                    <h3><?php _e('Step 2: Student Registration', 'omafuru-spelling-bee'); ?></h3>
                    <p class="osb-step-description">
                        <?php printf(__('Register up to %d students for your school. You can add more students by clicking "Add Student" button.', 'omafuru-spelling-bee'), intval($event->max_students_per_school)); ?>
                    </p>

                    <div id="osb-students-container">
                        <!-- Student forms will be dynamically added here -->
                    </div>

                    <div class="osb-add-student-section">
                        <button type="button" id="osb-add-student-btn" class="osb-btn osb-btn-secondary">
                            <?php _e('Add Student', 'omafuru-spelling-bee'); ?>
                        </button>
                        <span class="osb-student-count">
                            <?php printf(__('Students added: <span id="student-count">0</span> / %d', 'omafuru-spelling-bee'), intval($event->max_students_per_school)); ?>
                        </span>
                    </div>
                </div>

                <div class="osb-step-actions">
                    <button type="button" class="osb-btn osb-btn-secondary osb-prev-step" data-prev="1">
                        <?php _e('Previous', 'omafuru-spelling-bee'); ?>
                    </button>
                    <button type="button" class="osb-btn osb-btn-primary osb-next-step" data-next="3" disabled>
                        <?php _e('Continue to Documents', 'omafuru-spelling-bee'); ?>
                    </button>
                </div>
            </div>

            <!-- Step 3: Document Upload -->
            <div id="osb-step-3" class="osb-form-step osb-step-hidden">
                <div class="osb-step-content">
                    <h3><?php _e('Step 3: Document Upload', 'omafuru-spelling-bee'); ?></h3>
                    <p class="osb-step-description">
                        <?php _e('Please upload required documents. All documents should be in PDF, DOC, or image format.', 'omafuru-spelling-bee'); ?>
                    </p>

                    <div class="osb-upload-section">
                        <div class="osb-upload-area" id="osb-upload-dropzone">
                            <div class="osb-upload-icon">📎</div>
                            <div class="osb-upload-text">
                                <p><?php _e('Drag and drop files here or', 'omafuru-spelling-bee'); ?></p>
                                <button type="button" class="osb-btn osb-btn-outline" id="osb-browse-files">
                                    <?php _e('Browse Files', 'omafuru-spelling-bee'); ?>
                                </button>
                            </div>
                            <input type="file" id="osb-file-input" name="documents[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" capture="environment" style="display: none;">
                        </div>

                        <div class="osb-file-requirements">
                            <h4><?php _e('Document Requirements:', 'omafuru-spelling-bee'); ?></h4>
                            <ul>
                                <li><?php _e('School registration certificate or proof of accreditation', 'omafuru-spelling-bee'); ?></li>
                                <li><?php _e('Student identification documents (birth certificates, school IDs)', 'omafuru-spelling-bee'); ?></li>
                                <li><?php _e('Parental consent forms (if applicable)', 'omafuru-spelling-bee'); ?></li>
                                <li><?php _e('Maximum file size: 5MB per file', 'omafuru-spelling-bee'); ?></li>
                                <li><?php _e('Supported formats: PDF, DOC, DOCX, JPG, PNG', 'omafuru-spelling-bee'); ?></li>
                            </ul>
                        </div>

                        <div id="osb-uploaded-files" class="osb-uploaded-files">
                            <!-- Uploaded files will be shown here -->
                        </div>
                    </div>
                </div>

                <div class="osb-step-actions">
                    <button type="button" class="osb-btn osb-btn-secondary osb-prev-step" data-prev="2">
                        <?php _e('Previous', 'omafuru-spelling-bee'); ?>
                    </button>
                    <button type="button" class="osb-btn osb-btn-primary osb-next-step" data-next="4">
                        <?php _e('Continue to Review', 'omafuru-spelling-bee'); ?>
                    </button>
                </div>
            </div>

            <!-- Step 4: Review & Confirm -->
            <div id="osb-step-4" class="osb-form-step osb-step-hidden">
                <div class="osb-step-content">
                    <h3><?php _e('Step 4: Review & Confirm', 'omafuru-spelling-bee'); ?></h3>
                    <p class="osb-step-description">
                        <?php _e('Please review all information before submitting your registration.', 'omafuru-spelling-bee'); ?>
                    </p>

                    <div id="osb-review-content">
                        <!-- Review content will be populated dynamically -->
                    </div>
                </div>

                <div class="osb-step-actions">
                    <button type="button" class="osb-btn osb-btn-secondary osb-prev-step" data-prev="3">
                        <?php _e('Previous', 'omafuru-spelling-bee'); ?>
                    </button>
                    <button type="button" class="osb-btn osb-btn-primary osb-next-step" data-next="5">
                        <?php _e('Submit Registration', 'omafuru-spelling-bee'); ?>
                    </button>
                </div>
            </div>

            <!-- Step 5: Complete -->
            <div id="osb-step-5" class="osb-form-step osb-step-hidden">
                <div class="osb-step-content">
                    <div class="osb-success-message">
                        <div class="osb-success-icon">✅</div>
                        <h3><?php _e('Registration Submitted Successfully!', 'omafuru-spelling-bee'); ?></h3>
                        <p><?php _e('Thank you for registering. Your registration is now under review.', 'omafuru-spelling-bee'); ?></p>

                        <div class="osb-registration-details" id="osb-final-details">
                            <!-- Registration details will be shown here -->
                        </div>

                        <div class="osb-next-steps">
                            <h4><?php _e('What happens next?', 'omafuru-spelling-bee'); ?></h4>
                            <ul>
                                <li><?php _e('You will receive a confirmation email shortly', 'omafuru-spelling-bee'); ?></li>
                                <li><?php _e('Our team will review your registration within 2-3 business days', 'omafuru-spelling-bee'); ?></li>
                                <li><?php _e('You will be notified via email about the approval status', 'omafuru-spelling-bee'); ?></li>
                                <li><?php _e('Keep your registration token for future reference', 'omafuru-spelling-bee'); ?></li>
                            </ul>
                        </div>

                        <div class="osb-final-actions">
                            <button type="button" class="osb-btn osb-btn-primary" id="osb-download-receipt">
                                <?php _e('Download Receipt', 'omafuru-spelling-bee'); ?>
                            </button>
                            <a href="<?php echo home_url(); ?>" class="osb-btn osb-btn-secondary">
                                <?php _e('Return to Home', 'omafuru-spelling-bee'); ?>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Loading overlay -->
            <div id="osb-loading-overlay" class="osb-loading-overlay" style="display: none;">
                <div class="osb-spinner"></div>
                <p><?php _e('Processing...', 'omafuru-spelling-bee'); ?></p>
            </div>
        </form>
    </div>
</div>

<!-- Student Form Template -->
<template id="osb-student-template">
    <div class="osb-student-form">
        <div class="osb-student-header">
            <h4><?php _e('Student', 'omafuru-spelling-bee'); ?> <span class="student-number"></span></h4>
            <button type="button" class="osb-remove-student-btn" title="<?php _e('Remove Student', 'omafuru-spelling-bee'); ?>">×</button>
        </div>

        <div class="osb-form-grid">
            <div class="osb-form-group">
                <label><?php _e('First Name', 'omafuru-spelling-bee'); ?> <span class="osb-required">*</span></label>
                <input type="text" name="students[][first_name]" class="osb-form-control" required>
            </div>

            <div class="osb-form-group">
                <label><?php _e('Last Name', 'omafuru-spelling-bee'); ?> <span class="osb-required">*</span></label>
                <input type="text" name="students[][last_name]" class="osb-form-control" required>
            </div>

            <div class="osb-form-group">
                <label><?php _e('Date of Birth', 'omafuru-spelling-bee'); ?> <span class="osb-required">*</span></label>
                <input type="date" name="students[][date_of_birth]" class="osb-form-control" required>
            </div>

            <div class="osb-form-group">
                <label><?php _e('Grade Level', 'omafuru-spelling-bee'); ?> <span class="osb-required">*</span></label>
                <select name="students[][grade_level]" class="osb-form-control" required>
                    <option value=""><?php _e('Select Grade', 'omafuru-spelling-bee'); ?></option>
                    <option value="1"><?php _e('Grade 1', 'omafuru-spelling-bee'); ?></option>
                    <option value="2"><?php _e('Grade 2', 'omafuru-spelling-bee'); ?></option>
                    <option value="3"><?php _e('Grade 3', 'omafuru-spelling-bee'); ?></option>
                    <option value="4"><?php _e('Grade 4', 'omafuru-spelling-bee'); ?></option>
                    <option value="5"><?php _e('Grade 5', 'omafuru-spelling-bee'); ?></option>
                    <option value="6"><?php _e('Grade 6', 'omafuru-spelling-bee'); ?></option>
                    <option value="7"><?php _e('Grade 7', 'omafuru-spelling-bee'); ?></option>
                    <option value="8"><?php _e('Grade 8', 'omafuru-spelling-bee'); ?></option>
                    <option value="9"><?php _e('Grade 9', 'omafuru-spelling-bee'); ?></option>
                    <option value="10"><?php _e('Grade 10', 'omafuru-spelling-bee'); ?></option>
                    <option value="11"><?php _e('Grade 11', 'omafuru-spelling-bee'); ?></option>
                    <option value="12"><?php _e('Grade 12', 'omafuru-spelling-bee'); ?></option>
                </select>
            </div>

            <div class="osb-form-group">
                <label><?php _e('Gender', 'omafuru-spelling-bee'); ?></label>
                <select name="students[][gender]" class="osb-form-control">
                    <option value=""><?php _e('Select Gender', 'omafuru-spelling-bee'); ?></option>
                    <option value="male"><?php _e('Male', 'omafuru-spelling-bee'); ?></option>
                    <option value="female"><?php _e('Female', 'omafuru-spelling-bee'); ?></option>
                    <option value="other"><?php _e('Other', 'omafuru-spelling-bee'); ?></option>
                </select>
            </div>

            <div class="osb-form-group">
                <label><?php _e('Student Email', 'omafuru-spelling-bee'); ?></label>
                <input type="email" name="students[][email]" class="osb-form-control">
            </div>

            <div class="osb-form-group osb-full-width">
                <h5><?php _e('Parent/Guardian Information', 'omafuru-spelling-bee'); ?></h5>
            </div>

            <div class="osb-form-group">
                <label><?php _e('Parent/Guardian Name', 'omafuru-spelling-bee'); ?> <span class="osb-required">*</span></label>
                <input type="text" name="students[][parent_name]" class="osb-form-control" required>
            </div>

            <div class="osb-form-group">
                <label><?php _e('Parent/Guardian Email', 'omafuru-spelling-bee'); ?> <span class="osb-required">*</span></label>
                <input type="email" name="students[][parent_email]" class="osb-form-control" required>
            </div>

            <div class="osb-form-group">
                <label><?php _e('Parent/Guardian Phone', 'omafuru-spelling-bee'); ?></label>
                <input type="tel" name="students[][parent_phone]" class="osb-form-control">
            </div>

            <div class="osb-form-group">
                <label><?php _e('Emergency Contact Name', 'omafuru-spelling-bee'); ?></label>
                <input type="text" name="students[][emergency_contact_name]" class="osb-form-control">
            </div>

            <div class="osb-form-group">
                <label><?php _e('Emergency Contact Phone', 'omafuru-spelling-bee'); ?></label>
                <input type="tel" name="students[][emergency_contact_phone]" class="osb-form-control">
            </div>

            <div class="osb-form-group osb-full-width">
                <label><?php _e('Medical Conditions/Allergies', 'omafuru-spelling-bee'); ?></label>
                <textarea name="students[][medical_conditions]" class="osb-form-control" rows="2" placeholder="<?php _e('Please list any medical conditions, allergies, or special needs...', 'omafuru-spelling-bee'); ?>"></textarea>
            </div>
        </div>
    </div>
</template>

<style>
.osb-registration-container {
    max-width: 1000px;
    margin: 0 auto;
    padding: 20px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.osb-registration-header {
    text-align: center;
    margin-bottom: 40px;
}

.osb-registration-header h2 {
    font-size: 2.5rem;
    color: #0073aa;
    margin-bottom: 10px;
}

.osb-registration-info p {
    color: #666;
    font-size: 1.1rem;
    max-width: 600px;
    margin: 0 auto;
}

.osb-progress-container {
    margin-bottom: 40px;
}

.osb-progress-bar {
    display: flex;
    align-items: center;
    justify-content: center;
    flex-wrap: wrap;
    gap: 10px;
}

.osb-progress-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    min-width: 120px;
}

.osb-step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #e0e0e0;
    color: #999;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    margin-bottom: 8px;
    transition: all 0.3s ease;
}

.osb-step-active .osb-step-number {
    background: #0073aa;
    color: white;
}

.osb-step-completed .osb-step-number {
    background: #46b450;
    color: white;
}

.osb-step-label {
    font-size: 0.9rem;
    color: #666;
    font-weight: 500;
}

.osb-step-active .osb-step-label {
    color: #0073aa;
    font-weight: 600;
}

.osb-progress-line {
    width: 50px;
    height: 2px;
    background: #e0e0e0;
    margin: 0 10px;
    margin-top: -25px;
}

.osb-line-completed {
    background: #46b450;
}

.osb-form-wrapper {
    position: relative;
}

.osb-form-step {
    min-height: 400px;
}

.osb-step-hidden {
    display: none;
}

.osb-step-content {
    margin-bottom: 40px;
}

.osb-step-content h3 {
    color: #0073aa;
    margin-bottom: 10px;
    font-size: 1.8rem;
}

.osb-step-description {
    color: #666;
    margin-bottom: 30px;
    font-size: 1.1rem;
    line-height: 1.6;
}

.osb-form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 30px;
}

.osb-form-group {
    display: flex;
    flex-direction: column;
}

.osb-full-width {
    grid-column: 1 / -1;
}

.osb-form-group label {
    font-weight: 600;
    margin-bottom: 8px;
    color: #333;
}

.osb-required {
    color: #dc3232;
}

.osb-form-control {
    padding: 12px 16px;
    border: 2px solid #e0e0e0;
    border-radius: 6px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
}

.osb-form-control:focus {
    border-color: #0073aa;
    outline: none;
    box-shadow: 0 0 0 3px rgba(0, 115, 170, 0.1);
}

.osb-student-form {
    background: #f9f9f9;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 25px;
    margin-bottom: 20px;
    position: relative;
}

.osb-student-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    padding-bottom: 15px;
    border-bottom: 1px solid #ddd;
}

.osb-student-header h4 {
    margin: 0;
    color: #0073aa;
}

.osb-remove-student-btn {
    background: #dc3232;
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    cursor: pointer;
    font-size: 18px;
    line-height: 1;
}

.osb-add-student-section {
    text-align: center;
    margin: 30px 0;
    padding: 30px;
    border: 2px dashed #ddd;
    border-radius: 8px;
}

.osb-student-count {
    display: block;
    margin-top: 10px;
    color: #666;
}

.osb-upload-area {
    border: 2px dashed #0073aa;
    border-radius: 8px;
    padding: 40px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 30px;
}

.osb-upload-area:hover {
    border-color: #005a87;
    background: rgba(0, 115, 170, 0.05);
}

.osb-upload-icon {
    font-size: 48px;
    margin-bottom: 20px;
    opacity: 0.5;
}

.osb-file-requirements {
    background: #f0f8ff;
    padding: 20px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.osb-file-requirements h4 {
    color: #0073aa;
    margin-bottom: 15px;
}

.osb-file-requirements ul {
    margin: 0;
    padding-left: 20px;
    color: #666;
}

.osb-uploaded-files {
    margin-top: 20px;
}

.osb-step-actions {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px 0;
    border-top: 1px solid #e0e0e0;
}

.osb-btn {
    padding: 12px 30px;
    border: none;
    border-radius: 6px;
    font-size: 1rem;
    font-weight: 600;
    cursor: pointer;
    text-decoration: none;
    display: inline-block;
    transition: all 0.3s ease;
    text-align: center;
}

.osb-btn-primary {
    background: #0073aa;
    color: white;
}

.osb-btn-primary:hover:not(:disabled) {
    background: #005a87;
    transform: translateY(-2px);
}

.osb-btn-secondary {
    background: #666;
    color: white;
}

.osb-btn-secondary:hover {
    background: #555;
}

.osb-btn-outline {
    background: transparent;
    color: #0073aa;
    border: 2px solid #0073aa;
}

.osb-btn-outline:hover {
    background: #0073aa;
    color: white;
}

.osb-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.osb-success-message {
    text-align: center;
    padding: 40px;
}

.osb-success-icon {
    font-size: 64px;
    margin-bottom: 20px;
}

.osb-success-message h3 {
    color: #46b450;
    font-size: 2rem;
    margin-bottom: 20px;
}

.osb-registration-details,
.osb-next-steps {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    margin: 20px 0;
    text-align: left;
}

.osb-final-actions {
    margin-top: 30px;
    display: flex;
    gap: 15px;
    justify-content: center;
}

.osb-loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(255, 255, 255, 0.9);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 100;
}

.osb-spinner {
    border: 4px solid #f3f3f3;
    border-top: 4px solid #0073aa;
    border-radius: 50%;
    width: 50px;
    height: 50px;
    animation: spin 1s linear infinite;
    margin-bottom: 20px;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

@media (max-width: 768px) {
    .osb-form-grid {
        grid-template-columns: 1fr;
    }

    .osb-progress-bar {
        flex-direction: column;
        gap: 20px;
    }

    .osb-progress-line {
        width: 2px;
        height: 30px;
        margin: 0;
    }

    .osb-step-actions {
        flex-direction: column;
        gap: 15px;
    }

    .osb-btn {
        width: 100%;
    }

    .osb-final-actions {
        flex-direction: column;
    }

    .osb-registration-container {
        margin: 0 10px;
        padding: 15px;
    }

    .osb-registration-header h2 {
        font-size: 2rem;
    }

    .osb-registration-info p {
        font-size: 1rem;
    }

    .osb-step-content h3 {
        font-size: 1.5rem;
    }

    .osb-step-description {
        font-size: 1rem;
    }

    .osb-progress-step {
        min-width: 100px;
    }

    .osb-step-label {
        font-size: 0.8rem;
    }

    .osb-student-form {
        padding: 20px;
    }

    .osb-upload-area {
        padding: 30px 20px;
    }

    .osb-upload-icon {
        font-size: 36px;
    }
}

@media (max-width: 480px) {
    .osb-registration-container {
        margin: 0 5px;
        padding: 10px;
    }

    .osb-registration-header h2 {
        font-size: 1.8rem;
    }

    .osb-registration-info p {
        font-size: 0.95rem;
    }

    .osb-step-content h3 {
        font-size: 1.3rem;
    }

    .osb-step-description {
        font-size: 0.9rem;
    }

    .osb-progress-step {
        min-width: 80px;
    }

    .osb-step-number {
        width: 35px;
        height: 35px;
        font-size: 0.9rem;
    }

    .osb-step-label {
        font-size: 0.75rem;
    }

    .osb-form-control {
        padding: 10px 12px;
        font-size: 0.95rem;
    }

    .osb-student-form {
        padding: 15px;
    }

    .osb-student-header h4 {
        font-size: 1.1rem;
    }

    .osb-upload-area {
        padding: 25px 15px;
    }

    .osb-upload-icon {
        font-size: 32px;
    }

    .osb-btn {
        padding: 10px 20px;
        font-size: 0.9rem;
    }
}

/* Auto-save and progress tracking styles */
.osb-autosave-indicator {
    position: fixed;
    top: 20px;
    right: 20px;
    z-index: 1000;
    background: white;
    border-radius: 20px;
    padding: 8px 16px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    font-size: 0.9rem;
}

.osb-saving {
    color: #ff9800;
}

.osb-saved {
    color: #4caf50;
}

.osb-unsaved {
    color: #f44336;
}

.osb-resume-notification {
    background: linear-gradient(135deg, #4caf50, #45a049);
    color: white;
    border-radius: 8px;
    margin-bottom: 20px;
    animation: slideInDown 0.3s ease;
}

.osb-notification-content {
    display: flex;
    align-items: center;
    padding: 15px 20px;
}

.osb-notification-icon {
    font-size: 1.5rem;
    margin-right: 10px;
}

.osb-notification-text {
    flex: 1;
    font-weight: 500;
}

.osb-notification-close {
    background: none;
    border: none;
    color: white;
    font-size: 1.2rem;
    cursor: pointer;
    padding: 0;
    margin-left: 10px;
    opacity: 0.8;
}

.osb-notification-close:hover {
    opacity: 1;
}

.osb-step-valid .osb-step-number {
    background: #4caf50 !important;
    color: white !important;
}

.osb-step-invalid .osb-step-number {
    background: #f44336 !important;
    color: white !important;
}

.osb-step-valid .osb-step-number::after {
    content: "✓";
    position: absolute;
    top: -5px;
    right: -5px;
    background: #4caf50;
    color: white;
    border-radius: 50%;
    width: 16px;
    height: 16px;
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid white;
}

.osb-step-invalid .osb-step-number::after {
    content: "!";
    position: absolute;
    top: -5px;
    right: -5px;
    background: #f44336;
    color: white;
    border-radius: 50%;
    width: 16px;
    height: 16px;
    font-size: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    border: 2px solid white;
}

.osb-step-number {
    position: relative;
}

@keyframes slideInDown {
    0% {
        transform: translateY(-20px);
        opacity: 0;
    }
    100% {
        transform: translateY(0);
        opacity: 1;
    }
}

/* Mobile file upload styles */
.osb-uploaded-file {
    display: flex;
    align-items: center;
    background: white;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 15px;
    margin-bottom: 10px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.osb-file-icon {
    font-size: 2rem;
    margin-right: 15px;
    flex-shrink: 0;
}

.osb-file-info {
    flex: 1;
    min-width: 0;
}

.osb-file-name {
    display: block;
    font-weight: 600;
    color: #333;
    margin-bottom: 5px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
}

.osb-file-size-info {
    display: flex;
    align-items: center;
    flex-wrap: wrap;
    gap: 8px;
    font-size: 0.9rem;
    color: #666;
}

.osb-compressed-badge {
    background: #4caf50;
    color: white;
    padding: 2px 6px;
    border-radius: 10px;
    font-size: 0.8rem;
}

.osb-savings {
    color: #4caf50;
    font-weight: 600;
}

.osb-upload-complete {
    color: #4caf50;
    font-weight: 600;
    font-size: 0.9rem;
}

.osb-upload-error {
    color: #f44336;
    font-weight: 600;
    font-size: 0.9rem;
}

.osb-remove-file {
    background: #f44336;
    color: white;
    border: none;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    font-size: 16px;
    cursor: pointer;
    margin-left: 10px;
    flex-shrink: 0;
}

.osb-remove-file:hover {
    background: #d32f2f;
}

.osb-upload-progress {
    margin-top: 8px;
    width: 100%;
}

.osb-progress-bar-container {
    position: relative;
    background: #e0e0e0;
    border-radius: 10px;
    height: 20px;
    overflow: hidden;
}

.osb-progress-bar {
    background: linear-gradient(90deg, #4caf50, #45a049);
    height: 100%;
    transition: width 0.3s ease;
    border-radius: 10px;
}

.osb-progress-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 0.8rem;
    font-weight: 600;
    color: white;
    text-shadow: 0 1px 2px rgba(0,0,0,0.3);
}

/* Enhanced mobile file picker */
.osb-upload-area {
    border: 2px dashed #0073aa;
    border-radius: 12px;
    padding: 30px 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-bottom: 20px;
    background: linear-gradient(135deg, rgba(0, 115, 170, 0.05), rgba(0, 115, 170, 0.1));
}

.osb-upload-area:hover,
.osb-upload-area.drag-over {
    border-color: #005a87;
    background: rgba(0, 115, 170, 0.15);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0, 115, 170, 0.2);
}

.osb-upload-icon {
    font-size: 3rem;
    margin-bottom: 15px;
    opacity: 0.7;
}

.osb-upload-text p {
    margin: 10px 0;
    font-size: 1.1rem;
    color: #0073aa;
}

/* Mobile optimizations */
@media (max-width: 768px) {
    .osb-autosave-indicator {
        position: static;
        display: inline-block;
        margin: 10px 0;
    }

    .osb-upload-area {
        padding: 25px 15px;
        margin-bottom: 15px;
    }

    .osb-upload-icon {
        font-size: 2.5rem;
    }

    .osb-upload-text p {
        font-size: 1rem;
    }

    .osb-uploaded-file {
        padding: 12px;
        flex-direction: column;
        align-items: stretch;
    }

    .osb-file-icon {
        align-self: center;
        margin: 0 0 10px 0;
    }

    .osb-file-info {
        text-align: center;
        margin-bottom: 10px;
    }

    .osb-file-name {
        white-space: normal;
        text-align: center;
    }

    .osb-file-size-info {
        justify-content: center;
    }

    .osb-remove-file {
        align-self: center;
        margin: 0;
    }

    .osb-btn-outline {
        padding: 12px 24px;
        font-size: 1rem;
        border-radius: 25px;
    }
}

@media (max-width: 480px) {
    .osb-upload-area {
        padding: 20px 10px;
    }

    .osb-upload-icon {
        font-size: 2rem;
    }

    .osb-upload-text p {
        font-size: 0.9rem;
    }

    .osb-btn-outline {
        padding: 10px 20px;
        font-size: 0.9rem;
    }

    .osb-uploaded-file {
        padding: 10px;
    }

    .osb-file-icon {
        font-size: 1.5rem;
        margin-bottom: 8px;
    }

    .osb-file-name {
        font-size: 0.9rem;
    }

    .osb-file-size-info {
        font-size: 0.8rem;
    }
}

/* School Recognition System Styles */
.osb-school-recognition-banner {
    margin: 20px 0;
    padding: 20px;
    border-radius: 12px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    border-left: 5px solid;
}

.osb-banner-new {
    background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
    border-left-color: #2196f3;
}

.osb-banner-returning {
    background: linear-gradient(135deg, #e8f5e8, #f0f8ff);
    border-left-color: #4caf50;
}

.osb-banner-verified {
    background: linear-gradient(135deg, #fff3e0, #e8f5e8);
    border-left-color: #ff9800;
}

.osb-banner-premium {
    background: linear-gradient(135deg, #fce4ec, #f3e5f5);
    border-left-color: #9c27b0;
}

.osb-recognition-content {
    display: flex;
    align-items: flex-start;
    gap: 20px;
}

.osb-recognition-icon {
    font-size: 3rem;
    flex-shrink: 0;
}

.osb-recognition-info {
    flex: 1;
}

.osb-recognition-info h3 {
    margin: 0 0 10px 0;
    color: #333;
    font-size: 1.5rem;
}

.osb-recognition-info p {
    margin: 5px 0;
    color: #666;
    font-size: 0.9rem;
}

.osb-recognition-benefits {
    margin-top: 15px;
    padding: 15px;
    background: rgba(255,255,255,0.7);
    border-radius: 8px;
}

.osb-recognition-benefits strong {
    color: #333;
}

.osb-recognition-benefits ul {
    margin: 10px 0 0 0;
    padding-left: 20px;
}

.osb-recognition-benefits li {
    margin: 5px 0;
    color: #555;
    font-size: 0.9rem;
}

/* Pre-filled field indicators */
.osb-pre-filled {
    background-color: #f0f8ff !important;
    border-color: #4caf50 !important;
}

.osb-pre-filled-indicator {
    color: #4caf50;
    font-size: 0.8rem;
    margin-left: 8px;
    font-weight: 600;
}

/* Processing indicators */
.osb-processing-indicator {
    display: inline-block;
    padding: 8px 16px;
    border-radius: 20px;
    margin: 10px 0;
    font-size: 0.9rem;
    font-weight: 600;
}

.osb-processing-indicator.osb-premium {
    background: linear-gradient(135deg, #9c27b0, #7b1fa2);
    color: white;
}

.osb-processing-indicator.osb-verified {
    background: linear-gradient(135deg, #ff9800, #f57c00);
    color: white;
}

.osb-processing-indicator.osb-returning {
    background: linear-gradient(135deg, #4caf50, #388e3c);
    color: white;
}

/* Validation and support hints */
.osb-validation-hint,
.osb-support-hint {
    padding: 15px;
    border-radius: 8px;
    margin: 15px 0;
    border-left: 4px solid;
}

.osb-validation-hint.osb-premium {
    background: #fce4ec;
    border-left-color: #9c27b0;
    color: #7b1fa2;
}

.osb-support-hint.osb-verified {
    background: #fff3e0;
    border-left-color: #ff9800;
    color: #f57c00;
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
    max-width: 300px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}

/* History button */
.osb-history-btn {
    margin: 10px 0;
    font-size: 0.9rem;
}

/* Modal styles */
.osb-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    display: none;
    z-index: 2000;
    align-items: center;
    justify-content: center;
}

.osb-modal-content {
    background: white;
    border-radius: 12px;
    max-width: 600px;
    width: 90%;
    max-height: 80%;
    overflow-y: auto;
    box-shadow: 0 8px 24px rgba(0,0,0,0.3);
}

.osb-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
}

.osb-modal-header h3 {
    margin: 0;
    color: #333;
}

.osb-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    color: #666;
}

.osb-modal-close:hover {
    color: #f44336;
}

.osb-modal-body {
    padding: 20px;
}

/* Mobile optimizations for recognition system */
@media (max-width: 768px) {
    .osb-recognition-content {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }

    .osb-recognition-icon {
        font-size: 2.5rem;
        align-self: center;
    }

    .osb-recognition-info h3 {
        font-size: 1.3rem;
    }

    .osb-processing-indicator {
        display: block;
        text-align: center;
        margin: 15px 0;
    }

    .osb-support-hint.osb-verified {
        position: static;
        max-width: none;
        margin: 20px 0;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    let currentStep = 1;
    let studentCount = 0;
    const maxStudents = <?php echo intval($event->max_students_per_school); ?>;
    let uploadedFiles = [];
    let schoolData = {};
    let studentData = [];

    // Auto-save functionality
    let autoSaveInterval;
    let hasUnsavedChanges = false;
    let registrationId = null;

    // Initialize form
    initializeForm();

    function initializeForm() {
        // Check for existing registration/resume data
        checkForResumeData();

        // Add first student automatically
        addStudent();

        // Bind events
        bindEvents();

        // Start auto-save
        startAutoSave();

        // Visual progress indicators
        updateVisualProgress();
    }

    function handleReturningSchool(response) {
        // Show recognition banner
        showSchoolRecognitionBanner(response.recognition_summary, response.classification);

        // Pre-fill form data
        if (response.pre_filled_data) {
            populateSchoolForm(response.pre_filled_data);
        }

        // Apply classification benefits
        applyClassificationBenefits(response.classification, response.recognition_summary);

        // Store school data for next steps
        window.returningSchoolData = {
            school_id: response.school_id,
            classification: response.classification,
            recognition_summary: response.recognition_summary
        };
    }

    function showSchoolRecognitionBanner(summary, classification) {
        const bannerConfig = getClassificationBannerConfig(classification);

        const banner = $('<div class="osb-school-recognition-banner">');
        banner.html(`
            <div class="osb-recognition-content">
                <div class="osb-recognition-icon">${bannerConfig.icon}</div>
                <div class="osb-recognition-info">
                    <h3>${summary.classification_label}</h3>
                    <p>Total Participations: ${summary.total_participations} | Reputation Score: ${summary.reputation_score}/100</p>
                    <div class="osb-recognition-benefits">
                        <strong>Your benefits:</strong>
                        <ul>
                            ${summary.key_benefits.map(benefit => `<li>${benefit}</li>`).join('')}
                        </ul>
                    </div>
                </div>
            </div>
        `);

        banner.addClass(bannerConfig.class);
        $('.osb-registration-header').after(banner);

        // Animate banner appearance
        banner.hide().slideDown(500);
    }

    function getClassificationBannerConfig(classification) {
        const configs = {
            'new': {
                icon: '🆕',
                class: 'osb-banner-new'
            },
            'returning': {
                icon: '🔄',
                class: 'osb-banner-returning'
            },
            'verified': {
                icon: '✅',
                class: 'osb-banner-verified'
            },
            'premium': {
                icon: '⭐',
                class: 'osb-banner-premium'
            }
        };

        return configs[classification] || configs['new'];
    }

    function populateSchoolForm(data) {
        Object.keys(data).forEach(key => {
            const field = $(`[name="${key}"]`);
            if (field.length && data[key]) {
                field.val(data[key]);

                // Add visual indicator for pre-filled fields
                field.addClass('osb-pre-filled');
                field.after('<span class="osb-pre-filled-indicator">✓ Pre-filled</span>');
            }
        });

        // Trigger validation update
        updateStepValidation();
    }

    function applyClassificationBenefits(classification, summary) {
        // Apply benefits based on classification
        switch (classification) {
            case 'premium':
                enablePremiumFeatures();
                break;
            case 'verified':
                enableVerifiedFeatures();
                break;
            case 'returning':
                enableReturningFeatures();
                break;
        }
    }

    function enablePremiumFeatures() {
        // Express processing indicator
        addProcessingIndicator('premium', '⚡ Express Processing Enabled');

        // Auto-validation hint
        addValidationHint('premium', 'Your documents will be pre-validated based on your excellent history.');
    }

    function enableVerifiedFeatures() {
        // Priority processing
        addProcessingIndicator('verified', '🚀 Priority Processing');

        // Dedicated support
        addSupportHint('verified', 'Dedicated support available at premium@spelling-bee.com');
    }

    function enableReturningFeatures() {
        // Streamlined process
        addProcessingIndicator('returning', '📋 Streamlined Process');

        // History access
        addHistoryAccess();
    }

    function addProcessingIndicator(type, message) {
        const indicator = $(`<div class="osb-processing-indicator osb-${type}">
            <span class="osb-indicator-text">${message}</span>
        </div>`);

        $('.osb-registration-header').append(indicator);
    }

    function addValidationHint(type, message) {
        const hint = $(`<div class="osb-validation-hint osb-${type}">
            <strong>💡 Tip:</strong> ${message}
        </div>`);

        $('#osb-step-3 .osb-step-content').prepend(hint);
    }

    function addSupportHint(type, message) {
        const hint = $(`<div class="osb-support-hint osb-${type}">
            <strong>🎧 Support:</strong> ${message}
        </div>`);

        $('.osb-registration-container').append(hint);
    }

    function addHistoryAccess() {
        const historyButton = $(`<button type="button" class="osb-btn osb-btn-outline osb-history-btn">
            📊 View Registration History
        </button>`);

        historyButton.on('click', showRegistrationHistory);
        $('.osb-registration-header').append(historyButton);
    }

    function showRegistrationHistory() {
        if (!window.returningSchoolData) return;

        // This would typically load via AJAX
        const modal = $(`<div class="osb-modal osb-history-modal">
            <div class="osb-modal-content">
                <div class="osb-modal-header">
                    <h3>📊 Registration History</h3>
                    <button class="osb-modal-close">×</button>
                </div>
                <div class="osb-modal-body">
                    <p>Loading registration history...</p>
                </div>
            </div>
        </div>`);

        $('body').append(modal);
        modal.fadeIn();

        // Close modal functionality
        modal.find('.osb-modal-close').on('click', function() {
            modal.fadeOut(function() {
                modal.remove();
            });
        });
    }

    function checkForResumeData() {
        // Check if there's an existing registration in progress
        const urlParams = new URLSearchParams(window.location.search);
        const resumeToken = urlParams.get('resume');

        if (resumeToken) {
            loadResumeData(resumeToken);
        }
    }

    function loadResumeData(token) {
        $.ajax({
            url: osb_public_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'osb_load_resume_data',
                token: token,
                nonce: $('[name="osb_registration_nonce"]').val()
            },
            success: function(response) {
                if (response.success && response.data) {
                    populateFormFromCache(response.data);
                    showResumeNotification(response.data.last_active_step);
                }
            }
        });
    }

    function populateFormFromCache(data) {
        if (!data.form_data_cache) return;

        const cachedData = JSON.parse(data.form_data_cache);
        registrationId = data.id;

        // Populate school data
        if (cachedData.school) {
            Object.keys(cachedData.school).forEach(key => {
                $('[name="' + key + '"]').val(cachedData.school[key]);
            });
        }

        // Populate student data
        if (cachedData.students && cachedData.students.length > 0) {
            // Clear existing students first
            $('#osb-students-container').empty();
            studentCount = 0;

            // Add students from cache
            cachedData.students.forEach((student, index) => {
                addStudent();
                const studentForm = $('.osb-student-form').eq(index);
                Object.keys(student).forEach(key => {
                    studentForm.find('[name*="[' + key + ']"]').val(student[key]);
                });
            });
        }

        // Go to last active step
        if (data.last_active_step && data.last_active_step > 1) {
            goToStep(data.last_active_step);
        }

        updateVisualProgress();
    }

    function showResumeNotification(lastStep) {
        const notification = $('<div class="osb-resume-notification">')
            .html('<div class="osb-notification-content">' +
                  '<span class="osb-notification-icon">💾</span>' +
                  '<span class="osb-notification-text"><?php _e("Registration resumed from Step", "omafuru-spelling-bee"); ?> ' + lastStep + '</span>' +
                  '<button type="button" class="osb-notification-close">×</button>' +
                  '</div>');

        $('.osb-registration-container').prepend(notification);

        notification.find('.osb-notification-close').on('click', function() {
            notification.fadeOut();
        });

        setTimeout(() => {
            notification.fadeOut();
        }, 5000);
    }

    function startAutoSave() {
        autoSaveInterval = setInterval(() => {
            if (hasUnsavedChanges && currentStep > 0 && currentStep < 5) {
                autoSaveFormData();
            }
        }, 30000); // Auto-save every 30 seconds

        // Track form changes
        $(document).on('input change', '.osb-form-control', function() {
            hasUnsavedChanges = true;
            updateAutoSaveIndicator('unsaved');
        });
    }

    function autoSaveFormData() {
        const formData = collectFormData();

        $.ajax({
            url: osb_public_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'osb_auto_save_registration',
                registration_id: registrationId,
                event_id: $('[name="event_id"]').val(),
                current_step: currentStep,
                form_data: JSON.stringify(formData),
                nonce: $('[name="osb_registration_nonce"]').val()
            },
            success: function(response) {
                if (response.success) {
                    if (!registrationId) {
                        registrationId = response.data.registration_id;
                    }
                    hasUnsavedChanges = false;
                    updateAutoSaveIndicator('saved');
                }
            }
        });
    }

    function collectFormData() {
        const formData = {
            school: {},
            students: [],
            files: []
        };

        // Collect school data
        ['school_name', 'school_type', 'contact_person', 'contact_email', 'contact_phone',
         'address', 'city', 'state', 'postal_code', 'country'].forEach(field => {
            formData.school[field] = $('[name="' + field + '"]').val();
        });

        // Collect student data
        $('.osb-student-form').each(function() {
            const student = {};
            $(this).find('input, select, textarea').each(function() {
                const name = $(this).attr('name');
                if (name && name.includes('[')) {
                    const fieldName = name.substring(name.lastIndexOf('[') + 1, name.lastIndexOf(']'));
                    student[fieldName] = $(this).val();
                }
            });
            formData.students.push(student);
        });

        // Collect file data for auto-save
        uploadedFiles.forEach(file => {
            formData.files.push({
                name: file.name,
                size: file.size,
                type: file.type,
                uploaded: file.uploaded || false,
                serverPath: file.serverPath || null,
                compressed: file.compressed || false,
                originalSize: file.originalSize || file.size
            });
        });

        return formData;
    }

    function updateAutoSaveIndicator(status) {
        let indicator = $('.osb-autosave-indicator');

        if (indicator.length === 0) {
            indicator = $('<div class="osb-autosave-indicator">').appendTo('.osb-registration-header');
        }

        switch (status) {
            case 'saving':
                indicator.html('<span class="osb-saving">💾 <?php _e("Saving...", "omafuru-spelling-bee"); ?></span>');
                break;
            case 'saved':
                indicator.html('<span class="osb-saved">✅ <?php _e("Saved", "omafuru-spelling-bee"); ?></span>');
                setTimeout(() => indicator.fadeOut(), 2000);
                break;
            case 'unsaved':
                indicator.html('<span class="osb-unsaved">⚠️ <?php _e("Unsaved changes", "omafuru-spelling-bee"); ?></span>').show();
                break;
        }
    }

    function updateVisualProgress() {
        // Update step completion status based on validation
        $('.osb-progress-step').each(function(index) {
            const stepNum = index + 1;
            const $step = $(this);

            if (stepNum < currentStep) {
                $step.addClass('osb-step-completed');
            } else if (stepNum === currentStep) {
                $step.addClass('osb-step-active').removeClass('osb-step-completed');
            } else {
                $step.removeClass('osb-step-active osb-step-completed');
            }
        });

        // Update progress lines
        $('.osb-progress-line').each(function(index) {
            if (index < currentStep - 1) {
                $(this).addClass('osb-line-completed');
            } else {
                $(this).removeClass('osb-line-completed');
            }
        });

        // Update step validation indicators
        updateStepValidationIndicators();
    }

    function updateStepValidationIndicators() {
        // Add validation indicators to each step
        $('.osb-progress-step').each(function(index) {
            const stepNum = index + 1;
            const $step = $(this);

            if (stepNum < currentStep) {
                // Previous steps should show completion
                const isValid = validateStepData(stepNum);
                if (isValid) {
                    $step.addClass('osb-step-valid').removeClass('osb-step-invalid');
                } else {
                    $step.addClass('osb-step-invalid').removeClass('osb-step-valid');
                }
            }
        });
    }

    function validateStepData(stepNumber) {
        switch (stepNumber) {
            case 1:
                return $('[name="school_name"]').val().trim() &&
                       $('[name="contact_person"]').val().trim() &&
                       $('[name="contact_email"]').val().trim();
            case 2:
                return studentCount > 0 && $('.osb-student-form [required]').toArray().every(el => $(el).val().trim());
            case 3:
                return true; // Documents are optional
            default:
                return false;
        }
    }

    function bindEvents() {
        // Step navigation
        $('.osb-next-step').on('click', function() {
            const nextStep = $(this).data('next');

            // Handle school validation and recognition for step 1
            if (currentStep === 1) {
                handleSchoolValidation(nextStep);
            } else if (validateCurrentStep()) {
                goToStep(nextStep);
            }
        });

        function handleSchoolValidation(nextStep) {
            if (!validateCurrentStep()) {
                return;
            }

            // Show loading
            $('.osb-next-step[data-next="2"]').prop('disabled', true).text('<?php _e("Checking school...", "omafuru-spelling-bee"); ?>');

            // Collect school data
            const schoolData = {
                action: 'osb_submit_registration',
                step: 1,
                nonce: $('[name="osb_registration_nonce"]').val(),
                event_id: $('[name="event_id"]').val(),
                school_name: $('[name="school_name"]').val(),
                school_type: $('[name="school_type"]').val(),
                contact_person: $('[name="contact_person"]').val(),
                contact_email: $('[name="contact_email"]').val(),
                contact_phone: $('[name="contact_phone"]').val(),
                address: $('[name="address"]').val(),
                city: $('[name="city"]').val(),
                state: $('[name="state"]').val(),
                postal_code: $('[name="postal_code"]').val(),
                country: $('[name="country"]').val()
            };

            $.ajax({
                url: osb_public_ajax.ajax_url,
                type: 'POST',
                data: schoolData,
                success: function(response) {
                    $('.osb-next-step[data-next="2"]').prop('disabled', false).text('<?php _e("Continue to Student Registration", "omafuru-spelling-bee"); ?>');

                    if (response.success) {
                        if (response.data.returning_school) {
                            handleReturningSchool(response.data);
                        }
                        goToStep(nextStep);
                    } else {
                        alert('<?php _e("Registration failed:", "omafuru-spelling-bee"); ?> ' + response.data);
                    }
                },
                error: function() {
                    $('.osb-next-step[data-next="2"]').prop('disabled', false).text('<?php _e("Continue to Student Registration", "omafuru-spelling-bee"); ?>');
                    alert('<?php _e("Registration failed. Please try again.", "omafuru-spelling-bee"); ?>');
                }
            });
        }

        $('.osb-prev-step').on('click', function() {
            const prevStep = $(this).data('prev');
            goToStep(prevStep);
        });

        // Student management
        $('#osb-add-student-btn').on('click', addStudent);
        $(document).on('click', '.osb-remove-student-btn', removeStudent);

        // File upload - Enhanced mobile support
        $('#osb-browse-files').on('click', function() {
            $('#osb-file-input').click();
        });

        // Mobile-specific file input handling
        $('#osb-file-input').on('change', handleFileUpload);

        // Enhanced mobile file picker
        $('#osb-upload-dropzone').on('click', function(e) {
            if (e.target === this || $(e.target).hasClass('osb-upload-icon') || $(e.target).hasClass('osb-upload-text')) {
                $('#osb-file-input').click();
            }
        });

        // Improved mobile drag/drop with visual feedback
        let dragCounter = 0;

        $('#osb-upload-dropzone').on('dragenter', function(e) {
            e.preventDefault();
            dragCounter++;
            $(this).addClass('drag-over');
        });

        $('#osb-upload-dropzone').on('dragleave', function(e) {
            e.preventDefault();
            dragCounter--;
            if (dragCounter === 0) {
                $(this).removeClass('drag-over');
            }
        });

        // Complete drag and drop handling
        $('#osb-upload-dropzone').on('dragover', function(e) {
            e.preventDefault();
            // Visual feedback already handled in dragenter
        });

        $('#osb-upload-dropzone').on('drop', function(e) {
            e.preventDefault();
            dragCounter = 0;
            $(this).removeClass('drag-over');

            const files = e.originalEvent.dataTransfer.files;
            handleFiles(files);
        });

        // Form validation
        $(document).on('input change', '.osb-form-control', updateStepValidation);
    }

    function addStudent() {
        if (studentCount >= maxStudents) {
            alert('<?php printf(__("Maximum %d students allowed per school.", "omafuru-spelling-bee"), intval($event->max_students_per_school)); ?>');
            return;
        }

        studentCount++;
        const template = $('#osb-student-template').html();
        const studentForm = $(template);

        studentForm.find('.student-number').text(studentCount);
        studentForm.find('input, select, textarea').each(function() {
            const name = $(this).attr('name');
            if (name) {
                $(this).attr('name', name.replace('[]', '[' + (studentCount - 1) + ']'));
            }
        });

        $('#osb-students-container').append(studentForm);
        $('#student-count').text(studentCount);

        updateAddStudentButton();
        updateStepValidation();
    }

    function removeStudent() {
        if (studentCount <= 1) {
            alert('<?php _e("At least one student is required.", "omafuru-spelling-bee"); ?>');
            return;
        }

        $(this).closest('.osb-student-form').remove();
        studentCount--;

        // Renumber students
        $('.osb-student-form').each(function(index) {
            $(this).find('.student-number').text(index + 1);
            $(this).find('input, select, textarea').each(function() {
                const name = $(this).attr('name');
                if (name && name.includes('[')) {
                    const baseName = name.substring(0, name.indexOf('['));
                    const fieldName = name.substring(name.indexOf(']') + 1);
                    $(this).attr('name', baseName + '[' + index + ']' + fieldName);
                }
            });
        });

        $('#student-count').text(studentCount);
        updateAddStudentButton();
        updateStepValidation();
    }

    function updateAddStudentButton() {
        const addBtn = $('#osb-add-student-btn');
        if (studentCount >= maxStudents) {
            addBtn.prop('disabled', true).text('<?php _e("Maximum students reached", "omafuru-spelling-bee"); ?>');
        } else {
            addBtn.prop('disabled', false).text('<?php _e("Add Student", "omafuru-spelling-bee"); ?>');
        }
    }

    function handleFileUpload(e) {
        const files = e.target.files;
        handleFiles(files);
    }

    function handleFiles(files) {
        for (let file of files) {
            if (validateFile(file)) {
                // Check if it's an image that needs compression
                if (file.type.startsWith('image/') && file.size > 1024 * 1024) { // 1MB threshold
                    compressImage(file).then(compressedFile => {
                        uploadedFiles.push(compressedFile);
                        displayUploadedFile(compressedFile);
                        updateAutoSaveIndicator('unsaved');
                    }).catch(error => {
                        console.error('Compression failed:', error);
                        // Fallback to original file
                        uploadedFiles.push(file);
                        displayUploadedFile(file);
                        updateAutoSaveIndicator('unsaved');
                        // Start progressive upload
                        if (registrationId) {
                            uploadFileProgressively(compressedFile);
                        }
                    });
                } else {
                    uploadedFiles.push(file);
                    displayUploadedFile(file);
                    updateAutoSaveIndicator('unsaved');
                    // Start progressive upload
                    if (registrationId) {
                        uploadFileProgressively(file);
                    }
                }
            }
        }
    }

    function uploadFileProgressively(file) {
        const chunkSize = 1024 * 1024; // 1MB chunks
        const totalChunks = Math.ceil(file.size / chunkSize);
        let currentChunk = 0;

        const fileId = 'file_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
        file.uploadId = fileId;

        // Find the file display element
        const fileElements = $('.osb-uploaded-file');
        let fileElement = null;
        fileElements.each(function() {
            if ($(this).find('.osb-file-name').text() === file.name) {
                fileElement = $(this);
                return false;
            }
        });

        if (!fileElement) return;

        // Add progress bar
        const progressContainer = $('<div class="osb-upload-progress">');
        const progressBar = $('<div class="osb-progress-bar-container">' +
                            '<div class="osb-progress-bar" style="width: 0%"></div>' +
                            '<span class="osb-progress-text">0%</span>' +
                            '</div>');
        progressContainer.append(progressBar);
        fileElement.append(progressContainer);

        function uploadChunk() {
            const start = currentChunk * chunkSize;
            const end = Math.min(start + chunkSize, file.size);
            const chunk = file.slice(start, end);

            const formData = new FormData();
            formData.append('action', 'osb_upload_file_chunk');
            formData.append('file_chunk', chunk);
            formData.append('file_id', fileId);
            formData.append('file_name', file.name);
            formData.append('chunk_number', currentChunk);
            formData.append('total_chunks', totalChunks);
            formData.append('registration_id', registrationId);
            formData.append('nonce', $('[name="osb_registration_nonce"]').val());

            $.ajax({
                url: osb_public_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        currentChunk++;
                        const progress = Math.round((currentChunk / totalChunks) * 100);

                        // Update progress bar
                        fileElement.find('.osb-progress-bar').css('width', progress + '%');
                        fileElement.find('.osb-progress-text').text(progress + '%');

                        if (currentChunk < totalChunks) {
                            // Upload next chunk
                            uploadChunk();
                        } else {
                            // Upload complete
                            setTimeout(() => {
                                progressContainer.fadeOut(300, function() {
                                    $(this).remove();
                                });
                                fileElement.find('.osb-file-info').append(
                                    '<span class="osb-upload-complete">✅ <?php _e("Uploaded", "omafuru-spelling-bee"); ?></span>'
                                );
                            }, 500);

                            // Store server file info
                            if (response.data && response.data.file_path) {
                                file.serverPath = response.data.file_path;
                                file.uploaded = true;
                            }
                        }
                    } else {
                        // Upload failed
                        progressContainer.html('<span class="osb-upload-error">❌ <?php _e("Upload failed", "omafuru-spelling-bee"); ?></span>');
                        console.error('Chunk upload failed:', response.data);
                    }
                },
                error: function() {
                    progressContainer.html('<span class="osb-upload-error">❌ <?php _e("Connection error", "omafuru-spelling-bee"); ?></span>');
                }
            });
        }

        // Start upload
        uploadChunk();
    }

    function compressImage(file, maxWidth = 1920, maxHeight = 1080, quality = 0.8) {
        return new Promise((resolve, reject) => {
            const canvas = document.createElement('canvas');
            const ctx = canvas.getContext('2d');
            const img = new Image();

            img.onload = function() {
                // Calculate new dimensions
                let { width, height } = calculateOptimalDimensions(
                    img.width, img.height, maxWidth, maxHeight
                );

                canvas.width = width;
                canvas.height = height;

                // Draw and compress
                ctx.drawImage(img, 0, 0, width, height);

                canvas.toBlob(function(blob) {
                    if (blob) {
                        // Create new file with compressed data
                        const compressedFile = new File([blob], file.name, {
                            type: file.type,
                            lastModified: Date.now()
                        });

                        // Add compression info
                        compressedFile.originalSize = file.size;
                        compressedFile.compressed = true;

                        resolve(compressedFile);
                    } else {
                        reject(new Error('Compression failed'));
                    }
                }, file.type, quality);
            };

            img.onerror = function() {
                reject(new Error('Failed to load image'));
            };

            img.src = URL.createObjectURL(file);
        });
    }

    function calculateOptimalDimensions(origWidth, origHeight, maxWidth, maxHeight) {
        let width = origWidth;
        let height = origHeight;

        // Calculate scaling factor
        const widthRatio = maxWidth / width;
        const heightRatio = maxHeight / height;
        const ratio = Math.min(widthRatio, heightRatio);

        // Only scale down, never up
        if (ratio < 1) {
            width = Math.round(width * ratio);
            height = Math.round(height * ratio);
        }

        return { width, height };
    }

    function validateFile(file) {
        const allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'image/jpeg', 'image/png', 'image/jpg'];
        const maxSize = 5 * 1024 * 1024; // 5MB

        if (!allowedTypes.includes(file.type)) {
            alert('<?php _e("Invalid file type. Please upload PDF, DOC, DOCX, JPG, or PNG files only.", "omafuru-spelling-bee"); ?>');
            return false;
        }

        if (file.size > maxSize) {
            alert('<?php _e("File size too large. Maximum 5MB per file.", "omafuru-spelling-bee"); ?>');
            return false;
        }

        return true;
    }

    function displayUploadedFile(file) {
        const fileItem = $('<div class="osb-uploaded-file">');

        // File info section
        const fileInfo = $('<div class="osb-file-info">');
        fileInfo.append($('<span class="osb-file-name">').text(file.name));

        // File size with compression info
        const sizeInfo = $('<div class="osb-file-size-info">');
        sizeInfo.append($('<span class="osb-file-size">').text(formatFileSize(file.size)));

        if (file.compressed && file.originalSize) {
            const savings = ((file.originalSize - file.size) / file.originalSize * 100).toFixed(1);
            sizeInfo.append($('<span class="osb-compression-info">').html(
                ' <span class="osb-compressed-badge">📱 Compressed</span> ' +
                '<span class="osb-savings">-' + savings + '%</span>'
            ));
        }

        fileInfo.append(sizeInfo);

        // File type icon
        const fileIcon = $('<div class="osb-file-icon">');
        const extension = file.name.split('.').pop().toLowerCase();
        let icon = '📄'; // default
        if (['jpg', 'jpeg', 'png', 'gif'].includes(extension)) icon = '🖼️';
        else if (['pdf'].includes(extension)) icon = '📄';
        else if (['doc', 'docx'].includes(extension)) icon = '📝';
        fileIcon.text(icon);

        // Remove button
        const removeBtn = $('<button type="button" class="osb-remove-file" title="Remove file">×</button>');

        // Build file item
        fileItem.append(fileIcon);
        fileItem.append(fileInfo);
        fileItem.append(removeBtn);

        $('#osb-uploaded-files').append(fileItem);

        // Show upload animation
        fileItem.hide().fadeIn(300);

        removeBtn.on('click', function() {
            const index = uploadedFiles.indexOf(file);
            if (index > -1) {
                uploadedFiles.splice(index, 1);
            }
            fileItem.fadeOut(300, function() {
                $(this).remove();
                updateAutoSaveIndicator('unsaved');
            });
        });
    }

    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function goToStep(stepNumber) {
        // Hide current step
        $('.osb-form-step').addClass('osb-step-hidden');
        $('.osb-progress-step').removeClass('osb-step-active');

        // Show new step
        $('#osb-step-' + stepNumber).removeClass('osb-step-hidden');
        $('.osb-progress-step').eq(stepNumber - 1).addClass('osb-step-active');

        // Update progress
        $('.osb-progress-step').each(function(index) {
            if (index < stepNumber - 1) {
                $(this).addClass('osb-step-completed');
            } else if (index === stepNumber - 1) {
                $(this).addClass('osb-step-active');
            }
        });

        $('.osb-progress-line').each(function(index) {
            if (index < stepNumber - 1) {
                $(this).addClass('osb-line-completed');
            }
        });

        currentStep = stepNumber;
        $('#osb-current-step').val(currentStep);

        // Update visual progress
        updateVisualProgress();

        // Auto-save step progress
        if (registrationId) {
            updateStepProgress(stepNumber);
        }

        // Scroll to top
        $('html, body').animate({
            scrollTop: $('#osb-registration-form').offset().top - 50
        }, 500);

        // Special handling for step 4 (review)
        if (stepNumber === 4) {
            populateReviewStep();
        }

        // Special handling for step 5 (submit)
        if (stepNumber === 5) {
            submitRegistration();
        }
    }

    function updateStepProgress(stepNumber) {
        $.ajax({
            url: osb_public_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'osb_update_step_progress',
                registration_id: registrationId,
                step: stepNumber,
                nonce: $('[name="osb_registration_nonce"]').val()
            }
        });
    }
    }

    function validateCurrentStep() {
        let isValid = true;

        switch (currentStep) {
            case 1:
                // Validate school information
                const requiredFields = ['school_name', 'contact_person', 'contact_email'];
                requiredFields.forEach(function(field) {
                    const $field = $('[name="' + field + '"]');
                    if (!$field.val().trim()) {
                        $field.addClass('error');
                        isValid = false;
                    } else {
                        $field.removeClass('error');
                    }
                });
                break;

            case 2:
                // Validate students
                if (studentCount === 0) {
                    alert('<?php _e("At least one student is required.", "omafuru-spelling-bee"); ?>');
                    isValid = false;
                } else {
                    $('.osb-student-form').each(function() {
                        const requiredFields = $(this).find('[required]');
                        requiredFields.each(function() {
                            if (!$(this).val().trim()) {
                                $(this).addClass('error');
                                isValid = false;
                            } else {
                                $(this).removeClass('error');
                            }
                        });
                    });
                }
                break;

            case 3:
                // Document upload is optional
                break;

            case 4:
                // Review step, no validation needed
                break;
        }

        if (!isValid) {
            alert('<?php _e("Please fill in all required fields.", "omafuru-spelling-bee"); ?>');
        }

        return isValid;
    }

    function updateStepValidation() {
        // Enable/disable next button based on current step validation
        let canProceed = true;

        switch (currentStep) {
            case 1:
                const requiredFields = ['school_name', 'contact_person', 'contact_email'];
                requiredFields.forEach(function(field) {
                    if (!$('[name="' + field + '"]').val().trim()) {
                        canProceed = false;
                    }
                });
                break;

            case 2:
                if (studentCount === 0) {
                    canProceed = false;
                } else {
                    $('.osb-student-form [required]').each(function() {
                        if (!$(this).val().trim()) {
                            canProceed = false;
                        }
                    });
                }
                break;
        }

        $('#osb-step-' + currentStep + ' .osb-next-step').prop('disabled', !canProceed);
    }

    function populateReviewStep() {
        // Collect all form data
        schoolData = {
            school_name: $('[name="school_name"]').val(),
            school_type: $('[name="school_type"]').val(),
            contact_person: $('[name="contact_person"]').val(),
            contact_email: $('[name="contact_email"]').val(),
            contact_phone: $('[name="contact_phone"]').val(),
            address: $('[name="address"]').val(),
            city: $('[name="city"]').val(),
            state: $('[name="state"]').val(),
            postal_code: $('[name="postal_code"]').val(),
            country: $('[name="country"]').val()
        };

        studentData = [];
        $('.osb-student-form').each(function() {
            const student = {};
            $(this).find('input, select, textarea').each(function() {
                const name = $(this).attr('name');
                if (name) {
                    const fieldName = name.substring(name.lastIndexOf('[') + 1, name.lastIndexOf(']'));
                    student[fieldName] = $(this).val();
                }
            });
            studentData.push(student);
        });

        // Generate review HTML
        let reviewHtml = '<div class="osb-review-sections">';

        // School information
        reviewHtml += '<div class="osb-review-section">';
        reviewHtml += '<h4><?php _e("School Information", "omafuru-spelling-bee"); ?></h4>';
        reviewHtml += '<div class="osb-review-grid">';
        reviewHtml += '<div class="osb-review-item"><strong><?php _e("School Name:", "omafuru-spelling-bee"); ?></strong> ' + schoolData.school_name + '</div>';
        reviewHtml += '<div class="osb-review-item"><strong><?php _e("Contact Person:", "omafuru-spelling-bee"); ?></strong> ' + schoolData.contact_person + '</div>';
        reviewHtml += '<div class="osb-review-item"><strong><?php _e("Email:", "omafuru-spelling-bee"); ?></strong> ' + schoolData.contact_email + '</div>';
        if (schoolData.contact_phone) {
            reviewHtml += '<div class="osb-review-item"><strong><?php _e("Phone:", "omafuru-spelling-bee"); ?></strong> ' + schoolData.contact_phone + '</div>';
        }
        reviewHtml += '</div>';
        reviewHtml += '</div>';

        // Students
        reviewHtml += '<div class="osb-review-section">';
        reviewHtml += '<h4><?php _e("Registered Students", "omafuru-spelling-bee"); ?> (' + studentData.length + ')</h4>';
        studentData.forEach(function(student, index) {
            reviewHtml += '<div class="osb-student-review">';
            reviewHtml += '<h5><?php _e("Student", "omafuru-spelling-bee"); ?> ' + (index + 1) + ': ' + student.first_name + ' ' + student.last_name + '</h5>';
            reviewHtml += '<div class="osb-review-grid">';
            reviewHtml += '<div class="osb-review-item"><strong><?php _e("Grade:", "omafuru-spelling-bee"); ?></strong> ' + student.grade_level + '</div>';
            reviewHtml += '<div class="osb-review-item"><strong><?php _e("Date of Birth:", "omafuru-spelling-bee"); ?></strong> ' + student.date_of_birth + '</div>';
            reviewHtml += '<div class="osb-review-item"><strong><?php _e("Parent/Guardian:", "omafuru-spelling-bee"); ?></strong> ' + student.parent_name + '</div>';
            reviewHtml += '<div class="osb-review-item"><strong><?php _e("Parent Email:", "omafuru-spelling-bee"); ?></strong> ' + student.parent_email + '</div>';
            reviewHtml += '</div>';
            reviewHtml += '</div>';
        });
        reviewHtml += '</div>';

        // Documents
        if (uploadedFiles.length > 0) {
            reviewHtml += '<div class="osb-review-section">';
            reviewHtml += '<h4><?php _e("Uploaded Documents", "omafuru-spelling-bee"); ?> (' + uploadedFiles.length + ')</h4>';
            reviewHtml += '<ul>';
            uploadedFiles.forEach(function(file) {
                reviewHtml += '<li>' + file.name + ' (' + formatFileSize(file.size) + ')</li>';
            });
            reviewHtml += '</ul>';
            reviewHtml += '</div>';
        }

        // Agreement
        reviewHtml += '<div class="osb-review-section">';
        reviewHtml += '<div class="osb-agreement">';
        reviewHtml += '<label class="osb-checkbox-label">';
        reviewHtml += '<input type="checkbox" id="agreement-accepted" name="agreement_accepted" required> ';
        reviewHtml += '<?php _e("I agree to the terms and conditions and confirm that all information provided is accurate.", "omafuru-spelling-bee"); ?>';
        reviewHtml += '</label>';
        reviewHtml += '</div>';
        reviewHtml += '</div>';

        reviewHtml += '</div>';

        $('#osb-review-content').html(reviewHtml);

        // Bind agreement checkbox
        $('#agreement-accepted').on('change', function() {
            $('#osb-step-4 .osb-next-step').prop('disabled', !$(this).is(':checked'));
        }).trigger('change');
    }

    function submitRegistration() {
        // Show loading
        $('#osb-loading-overlay').show();

        // Collect final data
        const formData = new FormData();
        formData.append('action', 'osb_submit_registration');
        formData.append('nonce', $('[name="osb_registration_nonce"]').val());
        formData.append('event_id', $('[name="event_id"]').val());
        formData.append('step', '5');

        // School data
        Object.keys(schoolData).forEach(key => {
            formData.append('school[' + key + ']', schoolData[key] || '');
        });

        // Student data
        studentData.forEach((student, index) => {
            Object.keys(student).forEach(key => {
                formData.append('students[' + index + '][' + key + ']', student[key] || '');
            });
        });

        // Files
        uploadedFiles.forEach(file => {
            formData.append('documents[]', file);
        });

        formData.append('agreement_accepted', $('#agreement-accepted').is(':checked') ? '1' : '0');

        $.ajax({
            url: osb_public_ajax.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                $('#osb-loading-overlay').hide();

                if (response.success) {
                    // Show success details
                    let detailsHtml = '<div class="osb-registration-token">';
                    detailsHtml += '<strong><?php _e("Registration Token:", "omafuru-spelling-bee"); ?></strong> ';
                    detailsHtml += '<code>' + response.data.registration_token + '</code>';
                    detailsHtml += '</div>';

                    if (response.data.status_url) {
                        detailsHtml += '<div class="osb-status-url">';
                        detailsHtml += '<strong><?php _e("Check Status:", "omafuru-spelling-bee"); ?></strong> ';
                        detailsHtml += '<a href="' + response.data.status_url + '" target="_blank">' + response.data.status_url + '</a>';
                        detailsHtml += '</div>';
                    }

                    $('#osb-final-details').html(detailsHtml);

                    // Enable download receipt button
                    $('#osb-download-receipt').on('click', function() {
                        downloadReceipt(response.data);
                    });

                } else {
                    alert('<?php _e("Registration failed:", "omafuru-spelling-bee"); ?> ' + response.data);
                    goToStep(4); // Go back to review
                }
            },
            error: function() {
                $('#osb-loading-overlay').hide();
                alert('<?php _e("Registration failed. Please try again.", "omafuru-spelling-bee"); ?>');
                goToStep(4); // Go back to review
            }
        });
    }

    function downloadReceipt(data) {
        // Generate a simple receipt
        const receiptContent = generateReceiptHTML(data);
        const blob = new Blob([receiptContent], { type: 'text/html' });
        const url = URL.createObjectURL(blob);

        const a = document.createElement('a');
        a.href = url;
        a.download = 'registration-receipt-' + data.registration_token + '.html';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    function generateReceiptHTML(data) {
        let html = '<!DOCTYPE html><html><head><title>Registration Receipt</title>';
        html += '<style>body { font-family: Arial, sans-serif; margin: 40px; } .header { text-align: center; margin-bottom: 30px; } .token { background: #f0f0f0; padding: 10px; border-radius: 5px; } </style>';
        html += '</head><body>';
        html += '<div class="header"><h1><?php echo esc_js($event->title); ?></h1><h2><?php _e("Registration Receipt", "omafuru-spelling-bee"); ?></h2></div>';
        html += '<p><strong><?php _e("Registration Token:", "omafuru-spelling-bee"); ?></strong> <span class="token">' + data.registration_token + '</span></p>';
        html += '<p><strong><?php _e("School:", "omafuru-spelling-bee"); ?></strong> ' + schoolData.school_name + '</p>';
        html += '<p><strong><?php _e("Contact:", "omafuru-spelling-bee"); ?></strong> ' + schoolData.contact_person + ' (' + schoolData.contact_email + ')</p>';
        html += '<p><strong><?php _e("Students Registered:", "omafuru-spelling-bee"); ?></strong> ' + studentData.length + '</p>';
        html += '<p><strong><?php _e("Registration Date:", "omafuru-spelling-bee"); ?></strong> ' + new Date().toLocaleDateString() + '</p>';
        html += '<p><?php _e("Keep this receipt for your records. You will receive email confirmation shortly.", "omafuru-spelling-bee"); ?></p>';
        html += '</body></html>';
        return html;
    }

    // Add error styles
    $('<style>')
        .prop('type', 'text/css')
        .html('.osb-form-control.error { border-color: #dc3232; box-shadow: 0 0 0 3px rgba(220, 50, 50, 0.1); }')
        .appendTo('head');
});
</script>