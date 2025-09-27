<?php
/**
 * Enhanced Expression of Interest Form Template
 * Fully digital online form with mobile signature capture
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Variables passed from shortcode
$registration = isset($registration) ? $registration : null;
$event = isset($event) ? $event : null;
$school = isset($school) ? $school : null;

// Get existing EOI data if available
$eoi_data = array();
if ($registration && !empty($registration->eoi_form_data)) {
    $eoi_data = json_decode($registration->eoi_form_data, true) ?: array();
}

// Check if form is already submitted
$is_submitted = $registration && !empty($registration->digital_signature);
?>

<div class="osb-enhanced-eoi-form">
    <!-- Form Header -->
    <div class="osb-eoi-header">
        <div class="osb-header-icon">
            <span class="osb-icon">📋</span>
        </div>
        <div class="osb-header-content">
            <h2 class="osb-form-title">Expression of Interest</h2>
            <p class="osb-form-subtitle">
                Complete this digital form to express your school's interest in participating in the competition.
                <strong>No document uploads required!</strong>
            </p>
        </div>
    </div>

    <?php if ($is_submitted): ?>
        <!-- Submitted State -->
        <div class="osb-eoi-submitted">
            <div class="osb-success-icon">✅</div>
            <h3>Expression of Interest Submitted Successfully!</h3>
            <p>Your digital form was submitted on <?php echo date('F j, Y \a\t g:i A', strtotime($registration->signature_timestamp)); ?></p>
            <div class="osb-submission-details">
                <div class="osb-detail-item">
                    <span class="osb-detail-label">Submission ID:</span>
                    <span class="osb-detail-value"><?php echo esc_html($registration->token); ?></span>
                </div>
                <div class="osb-detail-item">
                    <span class="osb-detail-label">Status:</span>
                    <span class="osb-detail-value osb-status-badge osb-status-<?php echo esc_attr($registration->status); ?>">
                        <?php echo esc_html(ucfirst($registration->status)); ?>
                    </span>
                </div>
            </div>
            <div class="osb-next-steps">
                <p><strong>Next Steps:</strong></p>
                <ul>
                    <li>Your submission is now pending admin approval</li>
                    <li>You will receive an email notification when approved</li>
                    <li>Draw dates and group assignments will be communicated via email/SMS</li>
                </ul>
            </div>
        </div>
    <?php else: ?>
        <!-- Active Form -->
        <form id="osb-enhanced-eoi-form" class="osb-digital-form" method="post" novalidate>
            <?php wp_nonce_field('osb_submit_eoi', 'osb_eoi_nonce'); ?>
            <input type="hidden" name="action" value="osb_submit_enhanced_eoi">
            <input type="hidden" name="registration_id" value="<?php echo $registration ? $registration->id : ''; ?>">
            <input type="hidden" name="school_id" value="<?php echo $school ? $school->id : ''; ?>">
            <input type="hidden" name="form_start_time" value="<?php echo time(); ?>">

            <!-- Form Progress Indicator -->
            <div class="osb-form-progress">
                <div class="osb-progress-bar">
                    <div class="osb-progress-fill" id="osb-progress-fill"></div>
                </div>
                <span class="osb-progress-text" id="osb-progress-text">0% Complete</span>
            </div>

            <!-- Section 1: School Information -->
            <div class="osb-form-section" data-section="school-info">
                <div class="osb-section-header">
                    <h3 class="osb-section-title">
                        <span class="osb-section-icon">🏫</span>
                        School Information
                    </h3>
                    <p class="osb-section-description">Confirm and update your school details</p>
                </div>

                <div class="osb-form-grid">
                    <div class="osb-form-group osb-full-width">
                        <label for="school_name" class="osb-label required">School Name</label>
                        <input type="text"
                               id="school_name"
                               name="school_name"
                               class="osb-input"
                               value="<?php echo esc_attr($eoi_data['school_name'] ?? ($school->school_name ?? '')); ?>"
                               required
                               data-validation="required|min:3">
                        <div class="osb-validation-message"></div>
                    </div>

                    <div class="osb-form-group">
                        <label for="school_address" class="osb-label required">School Address</label>
                        <textarea id="school_address"
                                  name="school_address"
                                  class="osb-textarea"
                                  rows="3"
                                  required
                                  data-validation="required|min:10"><?php echo esc_textarea($eoi_data['school_address'] ?? ($school->address ?? '')); ?></textarea>
                        <div class="osb-validation-message"></div>
                    </div>

                    <div class="osb-form-group">
                        <label for="school_phone" class="osb-label required">School Phone</label>
                        <input type="tel"
                               id="school_phone"
                               name="school_phone"
                               class="osb-input"
                               value="<?php echo esc_attr($eoi_data['school_phone'] ?? ($school->phone ?? '')); ?>"
                               required
                               data-validation="required|phone">
                        <div class="osb-validation-message"></div>
                    </div>

                    <div class="osb-form-group">
                        <label for="school_email" class="osb-label required">School Email</label>
                        <input type="email"
                               id="school_email"
                               name="school_email"
                               class="osb-input"
                               value="<?php echo esc_attr($eoi_data['school_email'] ?? ($school->contact_email ?? '')); ?>"
                               required
                               data-validation="required|email">
                        <div class="osb-validation-message"></div>
                    </div>

                    <div class="osb-form-group">
                        <label for="school_type" class="osb-label required">School Type</label>
                        <select id="school_type" name="school_type" class="osb-select" required data-validation="required">
                            <option value="">Select School Type</option>
                            <option value="public" <?php selected($eoi_data['school_type'] ?? '', 'public'); ?>>Public School</option>
                            <option value="private" <?php selected($eoi_data['school_type'] ?? '', 'private'); ?>>Private School</option>
                            <option value="international" <?php selected($eoi_data['school_type'] ?? '', 'international'); ?>>International School</option>
                            <option value="religious" <?php selected($eoi_data['school_type'] ?? '', 'religious'); ?>>Religious School</option>
                        </select>
                        <div class="osb-validation-message"></div>
                    </div>

                    <div class="osb-form-group">
                        <label for="establishment_year" class="osb-label">Year Established</label>
                        <input type="number"
                               id="establishment_year"
                               name="establishment_year"
                               class="osb-input"
                               min="1800"
                               max="<?php echo date('Y'); ?>"
                               value="<?php echo esc_attr($eoi_data['establishment_year'] ?? ''); ?>"
                               data-validation="year">
                        <div class="osb-validation-message"></div>
                    </div>
                </div>
            </div>

            <!-- Section 2: Administration Contact -->
            <div class="osb-form-section" data-section="admin-contact">
                <div class="osb-section-header">
                    <h3 class="osb-section-title">
                        <span class="osb-section-icon">👨‍💼</span>
                        School Administration Contact
                    </h3>
                    <p class="osb-section-description">Primary contact person for competition matters</p>
                </div>

                <div class="osb-form-grid">
                    <div class="osb-form-group">
                        <label for="admin_title" class="osb-label required">Title/Position</label>
                        <select id="admin_title" name="admin_title" class="osb-select" required data-validation="required">
                            <option value="">Select Title</option>
                            <option value="principal" <?php selected($eoi_data['admin_title'] ?? '', 'principal'); ?>>Principal</option>
                            <option value="vice_principal" <?php selected($eoi_data['admin_title'] ?? '', 'vice_principal'); ?>>Vice Principal</option>
                            <option value="head_teacher" <?php selected($eoi_data['admin_title'] ?? '', 'head_teacher'); ?>>Head Teacher</option>
                            <option value="academic_director" <?php selected($eoi_data['admin_title'] ?? '', 'academic_director'); ?>>Academic Director</option>
                            <option value="coordinator" <?php selected($eoi_data['admin_title'] ?? '', 'coordinator'); ?>>Program Coordinator</option>
                            <option value="other" <?php selected($eoi_data['admin_title'] ?? '', 'other'); ?>>Other</option>
                        </select>
                        <div class="osb-validation-message"></div>
                    </div>

                    <div class="osb-form-group">
                        <label for="admin_name" class="osb-label required">Full Name</label>
                        <input type="text"
                               id="admin_name"
                               name="admin_name"
                               class="osb-input"
                               value="<?php echo esc_attr($eoi_data['admin_name'] ?? ''); ?>"
                               required
                               data-validation="required|min:3">
                        <div class="osb-validation-message"></div>
                    </div>

                    <div class="osb-form-group">
                        <label for="admin_phone" class="osb-label required">Phone Number</label>
                        <input type="tel"
                               id="admin_phone"
                               name="admin_phone"
                               class="osb-input"
                               value="<?php echo esc_attr($eoi_data['admin_phone'] ?? ''); ?>"
                               required
                               data-validation="required|phone">
                        <div class="osb-validation-message"></div>
                    </div>

                    <div class="osb-form-group">
                        <label for="admin_email" class="osb-label required">Email Address</label>
                        <input type="email"
                               id="admin_email"
                               name="admin_email"
                               class="osb-input"
                               value="<?php echo esc_attr($eoi_data['admin_email'] ?? ''); ?>"
                               required
                               data-validation="required|email">
                        <div class="osb-validation-message"></div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Competition Details -->
            <div class="osb-form-section" data-section="competition-details">
                <div class="osb-section-header">
                    <h3 class="osb-section-title">
                        <span class="osb-section-icon">🏆</span>
                        Competition Participation Details
                    </h3>
                    <p class="osb-section-description">Information about your school's participation</p>
                </div>

                <div class="osb-form-grid">
                    <div class="osb-form-group">
                        <label for="expected_participants" class="osb-label required">Expected Number of Participants</label>
                        <select id="expected_participants" name="expected_participants" class="osb-select" required data-validation="required">
                            <option value="">Select Range</option>
                            <option value="1-3" <?php selected($eoi_data['expected_participants'] ?? '', '1-3'); ?>>1-3 students</option>
                            <option value="4-6" <?php selected($eoi_data['expected_participants'] ?? '', '4-6'); ?>>4-6 students</option>
                            <option value="7-10" <?php selected($eoi_data['expected_participants'] ?? '', '7-10'); ?>>7-10 students</option>
                            <option value="11-15" <?php selected($eoi_data['expected_participants'] ?? '', '11-15'); ?>>11-15 students</option>
                            <option value="16+" <?php selected($eoi_data['expected_participants'] ?? '', '16+'); ?>>16+ students</option>
                        </select>
                        <div class="osb-validation-message"></div>
                    </div>

                    <div class="osb-form-group">
                        <label for="previous_participation" class="osb-label required">Previous Participation</label>
                        <select id="previous_participation" name="previous_participation" class="osb-select" required data-validation="required">
                            <option value="">Select Option</option>
                            <option value="first_time" <?php selected($eoi_data['previous_participation'] ?? '', 'first_time'); ?>>First time participating</option>
                            <option value="2021" <?php selected($eoi_data['previous_participation'] ?? '', '2021'); ?>>Participated in 2021</option>
                            <option value="2022" <?php selected($eoi_data['previous_participation'] ?? '', '2022'); ?>>Participated in 2022</option>
                            <option value="2023" <?php selected($eoi_data['previous_participation'] ?? '', '2023'); ?>>Participated in 2023</option>
                            <option value="multiple_years" <?php selected($eoi_data['previous_participation'] ?? '', 'multiple_years'); ?>>Multiple years</option>
                        </select>
                        <div class="osb-validation-message"></div>
                    </div>

                    <div class="osb-form-group osb-full-width">
                        <label for="motivation" class="osb-label required">Why does your school want to participate?</label>
                        <textarea id="motivation"
                                  name="motivation"
                                  class="osb-textarea"
                                  rows="4"
                                  required
                                  data-validation="required|min:20"
                                  placeholder="Tell us about your school's motivation for participating in the spelling bee competition..."><?php echo esc_textarea($eoi_data['motivation'] ?? ''); ?></textarea>
                        <div class="osb-validation-message"></div>
                    </div>

                    <div class="osb-form-group osb-full-width">
                        <label for="special_requirements" class="osb-label">Special Requirements or Accommodations</label>
                        <textarea id="special_requirements"
                                  name="special_requirements"
                                  class="osb-textarea"
                                  rows="3"
                                  placeholder="Any special requirements for accessibility, dietary restrictions, or other accommodations (optional)..."><?php echo esc_textarea($eoi_data['special_requirements'] ?? ''); ?></textarea>
                        <div class="osb-validation-message"></div>
                    </div>
                </div>
            </div>

            <!-- Section 4: Emergency Contact -->
            <div class="osb-form-section" data-section="emergency-contact">
                <div class="osb-section-header">
                    <h3 class="osb-section-title">
                        <span class="osb-section-icon">🚨</span>
                        Emergency Contact Information
                    </h3>
                    <p class="osb-section-description">Contact person in case of emergencies during the event</p>
                </div>

                <div class="osb-form-grid">
                    <div class="osb-form-group">
                        <label for="emergency_name" class="osb-label required">Contact Name</label>
                        <input type="text"
                               id="emergency_name"
                               name="emergency_name"
                               class="osb-input"
                               value="<?php echo esc_attr($eoi_data['emergency_name'] ?? ''); ?>"
                               required
                               data-validation="required|min:3">
                        <div class="osb-validation-message"></div>
                    </div>

                    <div class="osb-form-group">
                        <label for="emergency_relationship" class="osb-label required">Relationship to School</label>
                        <input type="text"
                               id="emergency_relationship"
                               name="emergency_relationship"
                               class="osb-input"
                               value="<?php echo esc_attr($eoi_data['emergency_relationship'] ?? ''); ?>"
                               required
                               data-validation="required"
                               placeholder="e.g., Assistant Principal, Teacher, Administrator">
                        <div class="osb-validation-message"></div>
                    </div>

                    <div class="osb-form-group">
                        <label for="emergency_phone" class="osb-label required">Phone Number</label>
                        <input type="tel"
                               id="emergency_phone"
                               name="emergency_phone"
                               class="osb-input"
                               value="<?php echo esc_attr($eoi_data['emergency_phone'] ?? ''); ?>"
                               required
                               data-validation="required|phone">
                        <div class="osb-validation-message"></div>
                    </div>

                    <div class="osb-form-group">
                        <label for="emergency_email" class="osb-label">Email Address</label>
                        <input type="email"
                               id="emergency_email"
                               name="emergency_email"
                               class="osb-input"
                               value="<?php echo esc_attr($eoi_data['emergency_email'] ?? ''); ?>"
                               data-validation="email">
                        <div class="osb-validation-message"></div>
                    </div>
                </div>
            </div>

            <!-- Section 5: Terms and Agreements -->
            <div class="osb-form-section" data-section="terms-agreements">
                <div class="osb-section-header">
                    <h3 class="osb-section-title">
                        <span class="osb-section-icon">📋</span>
                        Terms and Agreements
                    </h3>
                    <p class="osb-section-description">Please review and accept the terms below</p>
                </div>

                <div class="osb-terms-section">
                    <div class="osb-checkbox-group">
                        <label class="osb-checkbox-label">
                            <input type="checkbox"
                                   name="terms_competition_rules"
                                   value="1"
                                   required
                                   data-validation="required"
                                   <?php checked(!empty($eoi_data['terms_competition_rules'])); ?>>
                            <span class="osb-checkbox-custom"></span>
                            <span class="osb-checkbox-text">
                                I acknowledge that our school will abide by all competition rules and regulations as outlined by the Omafuru Foundation.
                            </span>
                        </label>
                    </div>

                    <div class="osb-checkbox-group">
                        <label class="osb-checkbox-label">
                            <input type="checkbox"
                                   name="terms_student_eligibility"
                                   value="1"
                                   required
                                   data-validation="required"
                                   <?php checked(!empty($eoi_data['terms_student_eligibility'])); ?>>
                            <span class="osb-checkbox-custom"></span>
                            <span class="osb-checkbox-text">
                                I confirm that all participating students will meet the age and grade eligibility requirements for the competition.
                            </span>
                        </label>
                    </div>

                    <div class="osb-checkbox-group">
                        <label class="osb-checkbox-label">
                            <input type="checkbox"
                                   name="terms_media_consent"
                                   value="1"
                                   required
                                   data-validation="required"
                                   <?php checked(!empty($eoi_data['terms_media_consent'])); ?>>
                            <span class="osb-checkbox-custom"></span>
                            <span class="osb-checkbox-text">
                                I consent to photography, videography, and media coverage of our school's participation for promotional purposes.
                            </span>
                        </label>
                    </div>

                    <div class="osb-checkbox-group">
                        <label class="osb-checkbox-label">
                            <input type="checkbox"
                                   name="terms_data_processing"
                                   value="1"
                                   required
                                   data-validation="required"
                                   <?php checked(!empty($eoi_data['terms_data_processing'])); ?>>
                            <span class="osb-checkbox-custom"></span>
                            <span class="osb-checkbox-text">
                                I consent to the processing of personal data submitted in this form for competition administration purposes.
                            </span>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Section 6: Digital Signature -->
            <div class="osb-form-section" data-section="digital-signature">
                <div class="osb-section-header">
                    <h3 class="osb-section-title">
                        <span class="osb-section-icon">✍️</span>
                        Digital Signature
                    </h3>
                    <p class="osb-section-description">
                        Please provide your digital signature to confirm the information above and complete your Expression of Interest.
                    </p>
                </div>

                <div class="osb-signature-section">
                    <div class="osb-signature-container">
                        <canvas id="osb-signature-pad" class="osb-signature-canvas"></canvas>
                        <div class="osb-signature-overlay" id="osb-signature-overlay">
                            <div class="osb-signature-prompt">
                                <span class="osb-signature-icon">✍️</span>
                                <p>Click or touch to sign</p>
                                <small>Use your mouse, finger, or stylus to sign above</small>
                            </div>
                        </div>
                    </div>

                    <div class="osb-signature-controls">
                        <button type="button" id="osb-clear-signature" class="osb-btn osb-btn-secondary">
                            <span class="osb-btn-icon">🗑️</span>
                            Clear Signature
                        </button>
                        <div class="osb-signature-status" id="osb-signature-status">
                            <span class="osb-status-text">Signature required</span>
                        </div>
                    </div>

                    <input type="hidden" name="digital_signature" id="osb-signature-data">
                    <div class="osb-validation-message" id="osb-signature-validation"></div>
                </div>

                <div class="osb-signature-info">
                    <p><strong>By signing above, I confirm that:</strong></p>
                    <ul>
                        <li>All information provided in this form is accurate and complete</li>
                        <li>I am authorized to represent the school in this capacity</li>
                        <li>I agree to all terms and conditions stated above</li>
                        <li>I understand that this signature has the same legal effect as a handwritten signature</li>
                    </ul>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="osb-form-actions">
                <div class="osb-action-buttons">
                    <button type="button" class="osb-btn osb-btn-secondary osb-btn-large" onclick="goBackToStep(1)">
                        <span class="osb-btn-icon">⬅️</span>
                        <span class="osb-btn-text">Back to Competition Selection</span>
                    </button>

                    <button type="submit" id="osb-submit-eoi" class="osb-btn osb-btn-primary osb-btn-large" disabled>
                        <span class="osb-btn-icon">📤</span>
                        <span class="osb-btn-text">Submit Expression of Interest</span>
                        <span class="osb-btn-loading" style="display: none;">
                            <span class="osb-spinner"></span>
                            Submitting...
                        </span>
                    </button>
                </div>

                <div class="osb-form-footer">
                    <p class="osb-footer-note">
                        <span class="osb-icon">🔒</span>
                        Your information is securely encrypted and protected.
                        We will only use this data for competition administration purposes.
                    </p>
                </div>
            </div>

            <!-- Google reCAPTCHA -->
            <div class="g-recaptcha" data-sitekey="YOUR_RECAPTCHA_SITE_KEY" data-size="invisible" data-callback="onRecaptchaVerified"></div>
        </form>
    <?php endif; ?>
</div>

<!-- Auto-save notification -->
<div id="osb-autosave-notification" class="osb-autosave-notification" style="display: none;">
    <span class="osb-autosave-icon">💾</span>
    <span class="osb-autosave-text">Form automatically saved</span>
</div>