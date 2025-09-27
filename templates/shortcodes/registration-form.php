<?php
/**
 * Simplified Registration Form Template
 * Single-step registration with immediate dashboard redirect
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div id="osb-registration-form" class="osb-registration-container">
    <div class="osb-registration-header">
        <h2><?php printf(__('Register for %s', 'spelling-bee-pro'), esc_html($event->title)); ?></h2>
        <div class="osb-registration-info">
            <p><?php _e('Complete your school registration below. You will receive immediate access to your dashboard upon successful registration.', 'spelling-bee-pro'); ?></p>
        </div>
    </div>

    <!-- Form Container -->
    <div class="osb-form-wrapper">
        <form id="osb-simple-registration-form" class="osb-registration-form">
            <?php wp_nonce_field('osb_registration', 'osb_registration_nonce'); ?>
            <input type="hidden" name="event_id" value="<?php echo esc_attr($event->id); ?>">

            <!-- School Information Section -->
            <div class="osb-form-section">
                <h3><?php _e('School Information', 'spelling-bee-pro'); ?></h3>
                <div class="osb-form-grid">
                    <div class="osb-form-group osb-full-width">
                        <label for="school_name"><?php _e('School Name', 'spelling-bee-pro'); ?> <span class="osb-required">*</span></label>
                        <input type="text" id="school_name" name="school_name" class="osb-form-control" required>
                    </div>

                    <div class="osb-form-group">
                        <label for="school_type"><?php _e('School Type', 'spelling-bee-pro'); ?> <span class="osb-required">*</span></label>
                        <select id="school_type" name="school_type" class="osb-form-control" required>
                            <option value=""><?php _e('Select School Type', 'spelling-bee-pro'); ?></option>
                            <option value="public"><?php _e('Public School', 'spelling-bee-pro'); ?></option>
                            <option value="private"><?php _e('Private School', 'spelling-bee-pro'); ?></option>
                            <option value="federal"><?php _e('Federal School', 'spelling-bee-pro'); ?></option>
                            <option value="state"><?php _e('State School', 'spelling-bee-pro'); ?></option>
                        </select>
                    </div>

                    <div class="osb-form-group">
                        <label for="contact_person"><?php _e('Contact Person', 'spelling-bee-pro'); ?> <span class="osb-required">*</span></label>
                        <input type="text" id="contact_person" name="contact_person" class="osb-form-control" required>
                    </div>

                    <div class="osb-form-group osb-full-width">
                        <label for="address"><?php _e('School Address', 'spelling-bee-pro'); ?> <span class="osb-required">*</span></label>
                        <textarea id="address" name="address" class="osb-form-control" rows="3" required></textarea>
                    </div>

                    <div class="osb-form-group">
                        <label for="city"><?php _e('City', 'spelling-bee-pro'); ?> <span class="osb-required">*</span></label>
                        <input type="text" id="city" name="city" class="osb-form-control" required>
                    </div>

                    <div class="osb-form-group">
                        <label for="state"><?php _e('State/Province', 'spelling-bee-pro'); ?> <span class="osb-required">*</span></label>
                        <input type="text" id="state" name="state" class="osb-form-control" required>
                    </div>

                    <div class="osb-form-group">
                        <label for="postal_code"><?php _e('Postal Code', 'spelling-bee-pro'); ?></label>
                        <input type="text" id="postal_code" name="postal_code" class="osb-form-control">
                    </div>

                    <div class="osb-form-group">
                        <label for="country"><?php _e('Country', 'spelling-bee-pro'); ?></label>
                        <select id="country" name="country" class="osb-form-control">
                            <option value="NG" selected><?php _e('Nigeria', 'spelling-bee-pro'); ?></option>
                            <option value="US"><?php _e('United States', 'spelling-bee-pro'); ?></option>
                            <option value="GB"><?php _e('United Kingdom', 'spelling-bee-pro'); ?></option>
                            <option value="CA"><?php _e('Canada', 'spelling-bee-pro'); ?></option>
                        </select>
                    </div>

                    <div class="osb-form-group">
                        <label for="contact_email"><?php _e('Contact Email', 'spelling-bee-pro'); ?> <span class="osb-required">*</span></label>
                        <input type="email" id="contact_email" name="contact_email" class="osb-form-control" required>
                    </div>

                    <div class="osb-form-group">
                        <label for="contact_phone"><?php _e('Contact Phone', 'spelling-bee-pro'); ?></label>
                        <input type="tel" id="contact_phone" name="contact_phone" class="osb-form-control">
                    </div>
                </div>
            </div>

            <!-- Agreement Section -->
            <div class="osb-form-section">
                <div class="osb-agreement">
                    <label class="osb-checkbox-label">
                        <input type="checkbox" id="agreement_accepted" name="agreement_accepted" required>
                        <?php _e('I agree to the terms and conditions and confirm that all information provided is accurate.', 'spelling-bee-pro'); ?> <span class="osb-required">*</span>
                    </label>
                </div>
            </div>

            <!-- Submit Section -->
            <div class="osb-form-actions">
                <button type="submit" id="osb-submit-btn" class="osb-btn osb-btn-primary osb-btn-large" disabled>
                    <?php _e('Complete Registration & Access Dashboard', 'spelling-bee-pro'); ?>
                </button>
            </div>

            <!-- Loading overlay -->
            <div id="osb-loading-overlay" class="osb-loading-overlay" style="display: none;">
                <div class="osb-spinner"></div>
                <p><?php _e('Processing your registration...', 'spelling-bee-pro'); ?></p>
            </div>
        </form>
    </div>

    <!-- Response container for messages -->
    <div id="osb-response-container" style="display: none;">
        <!-- Messages will be displayed here -->
    </div>
</div>

<style>
.osb-registration-container {
    max-width: 800px;
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

.osb-form-section {
    background: #f9f9f9;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    padding: 25px;
    margin-bottom: 25px;
}

.osb-form-section h3 {
    color: #0073aa;
    margin-bottom: 20px;
    font-size: 1.8rem;
    border-bottom: 2px solid #0073aa;
    padding-bottom: 10px;
}

.osb-form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
    margin-bottom: 20px;
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

.osb-form-control:invalid {
    border-color: #dc3232;
}

.osb-agreement {
    text-align: center;
    padding: 20px;
}

.osb-checkbox-label {
    display: flex;
    align-items: flex-start;
    gap: 10px;
    font-size: 1rem;
    line-height: 1.5;
    cursor: pointer;
}

.osb-checkbox-label input[type="checkbox"] {
    margin: 0;
    flex-shrink: 0;
    width: 18px;
    height: 18px;
    margin-top: 2px;
}

.osb-form-actions {
    text-align: center;
    padding: 30px 0;
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

.osb-btn-large {
    padding: 16px 40px;
    font-size: 1.2rem;
}

.osb-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
    transform: none;
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
    border-radius: 12px;
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

/* Response styles */
.osb-redirect-message {
    text-align: center;
    padding: 40px;
    background: #f9f9f9;
    border-radius: 8px;
    border-left: 4px solid #46b450;
    margin-top: 20px;
}

.osb-redirect-message .osb-success-icon {
    font-size: 64px;
    margin-bottom: 20px;
}

.osb-redirect-message h3 {
    color: #46b450;
    margin-bottom: 15px;
    font-size: 24px;
}

.osb-redirect-message p {
    margin-bottom: 10px;
    font-size: 16px;
}

.osb-dashboard-link {
    display: inline-block;
    padding: 12px 24px;
    background: #0073aa;
    color: white !important;
    text-decoration: none;
    border-radius: 4px;
    font-weight: bold;
    transition: background 0.3s;
}

.osb-dashboard-link:hover {
    background: #005a87;
    text-decoration: none;
}

/* Existing user message styles */
.osb-existing-user-message {
    text-align: center;
    padding: 40px;
    background: #fff3cd;
    border-radius: 8px;
    border-left: 4px solid #ffc107;
    margin-top: 20px;
}

.osb-existing-user-message .osb-info-icon {
    font-size: 64px;
    margin-bottom: 20px;
}

.osb-existing-user-message h3 {
    color: #856404;
    margin-bottom: 15px;
    font-size: 24px;
}

.osb-existing-user-message p {
    margin-bottom: 15px;
    font-size: 16px;
    color: #856404;
}

/* Mobile responsiveness */
@media (max-width: 768px) {
    .osb-form-grid {
        grid-template-columns: 1fr;
    }

    .osb-registration-container {
        margin: 0 10px;
        padding: 15px;
    }

    .osb-registration-header h2 {
        font-size: 2rem;
    }

    .osb-form-section {
        padding: 20px;
    }

    .osb-form-section h3 {
        font-size: 1.5rem;
    }

    .osb-checkbox-label {
        text-align: left;
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

    .osb-form-section {
        padding: 15px;
    }

    .osb-form-section h3 {
        font-size: 1.3rem;
    }

    .osb-btn-large {
        padding: 14px 30px;
        font-size: 1.1rem;
    }
}

.osb-form-wrapper {
    position: relative;
}
</style>

<script>
jQuery(document).ready(function($) {
    console.log('>>> SIMPLIFIED REGISTRATION FORM LOADED <<<');

    // Form validation
    function validateForm() {
        let isValid = true;
        const requiredFields = ['school_name', 'school_type', 'contact_person', 'address', 'city', 'state', 'contact_email'];

        requiredFields.forEach(function(field) {
            const $field = $('[name="' + field + '"]');
            if (!$field.val().trim()) {
                isValid = false;
            }
        });

        const agreementChecked = $('#agreement_accepted').is(':checked');
        if (!agreementChecked) {
            isValid = false;
        }

        return isValid;
    }

    // Update submit button state
    function updateSubmitButton() {
        const isValid = validateForm();
        $('#osb-submit-btn').prop('disabled', !isValid);
    }

    // Bind form validation
    $(document).on('input change', '.osb-form-control, #agreement_accepted', updateSubmitButton);

    // Form submission
    $('#osb-simple-registration-form').on('submit', function(e) {
        e.preventDefault();

        console.log('>>> FORM SUBMITTED - SIMPLIFIED WORKFLOW <<<');

        if (!validateForm()) {
            alert('<?php _e("Please fill in all required fields and accept the agreement.", "spelling-bee-pro"); ?>');
            return;
        }

        // Show loading
        $('#osb-loading-overlay').show();

        // Collect form data
        const formData = {
            action: 'osb_submit_registration',
            osb_registration_nonce: $('[name="osb_registration_nonce"]').val(),
            event_id: $('[name="event_id"]').val(),
            school_name: $('[name="school_name"]').val(),
            school_type: $('[name="school_type"]').val(),
            contact_person: $('[name="contact_person"]').val(),
            address: $('[name="address"]').val(),
            city: $('[name="city"]').val(),
            state: $('[name="state"]').val(),
            postal_code: $('[name="postal_code"]').val(),
            country: $('[name="country"]').val(),
            contact_email: $('[name="contact_email"]').val(),
            contact_phone: $('[name="contact_phone"]').val(),
            agreement_accepted: $('#agreement_accepted').is(':checked') ? '1' : '0'
        };

        console.log('Submitting form data:', formData);

        // Submit form
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: formData,
            success: function(response) {
                $('#osb-loading-overlay').hide();

                console.log('=== REGISTRATION RESPONSE ===');
                console.log('Full response:', response);

                if (response.success) {
                    console.log('Registration successful');

                    // Check if we should redirect to dashboard
                    if (response.data.redirect && response.data.dashboard_url) {
                        console.log('Redirecting to dashboard:', response.data.dashboard_url);

                        // Show success message and redirect
                        let redirectHtml = '<div class="osb-redirect-message">';
                        redirectHtml += '<div class="osb-success-icon">🎉</div>';
                        redirectHtml += '<h3><?php _e("Registration Successful!", "spelling-bee-pro"); ?></h3>';
                        redirectHtml += '<p><?php _e("Redirecting to your dashboard in 3 seconds...", "spelling-bee-pro"); ?></p>';
                        redirectHtml += '<p><a href="' + response.data.dashboard_url + '" class="osb-dashboard-link"><?php _e("Click here if not redirected automatically", "spelling-bee-pro"); ?></a></p>';
                        redirectHtml += '</div>';

                        $('#osb-response-container').html(redirectHtml).show();
                        $('#osb-simple-registration-form').hide();

                        // Redirect after 3 seconds
                        setTimeout(function() {
                            window.location.href = response.data.dashboard_url;
                        }, 3000);

                    } else {
                        // Show success message without redirect
                        let successHtml = '<div class="osb-redirect-message">';
                        successHtml += '<div class="osb-success-icon">✅</div>';
                        successHtml += '<h3><?php _e("Registration Successful!", "spelling-bee-pro"); ?></h3>';
                        successHtml += '<p>' + response.data.message + '</p>';
                        if (response.data.dashboard_url) {
                            successHtml += '<p><a href="' + response.data.dashboard_url + '" class="osb-dashboard-link"><?php _e("Access Your Dashboard", "spelling-bee-pro"); ?></a></p>';
                        }
                        successHtml += '</div>';

                        $('#osb-response-container').html(successHtml).show();
                        $('#osb-simple-registration-form').hide();
                    }

                } else {
                    console.log('Registration failed:', response.data);

                    // Check if this is an existing user error
                    if (response.data && response.data.type === 'existing_user') {
                        // Show existing user message with dashboard link
                        let existingUserHtml = '<div class="osb-existing-user-message">';
                        existingUserHtml += '<div class="osb-info-icon">ℹ️</div>';
                        existingUserHtml += '<h3>' + response.data.message + '</h3>';
                        existingUserHtml += '<p>' + response.data.action_message + '</p>';
                        existingUserHtml += '<p><a href="' + response.data.dashboard_url + '" class="osb-dashboard-link"><?php _e("Access Your Dashboard", "spelling-bee-pro"); ?></a></p>';
                        existingUserHtml += '</div>';

                        $('#osb-response-container').html(existingUserHtml).show();
                        $('#osb-simple-registration-form').hide();
                    } else {
                        // Regular error
                        alert('<?php _e("Registration failed:", "spelling-bee-pro"); ?> ' + response.data);
                    }
                }
            },
            error: function(xhr, status, error) {
                $('#osb-loading-overlay').hide();
                console.log('AJAX Error:', status, error);
                alert('<?php _e("Registration failed. Please try again.", "spelling-bee-pro"); ?>');
            }
        });
    });

    // Initial validation
    updateSubmitButton();
});
</script>