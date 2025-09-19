<?php
/**
 * Settings Admin Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Get current settings
$organization_name = get_option('osb_organization_name', 'Omafuru Foundation');
$contact_email = get_option('osb_contact_email', 'info@omafarufoundation.org');
$registration_enabled = get_option('osb_registration_enabled', '1');
$max_students_per_school = get_option('osb_max_students_per_school', '5');
$min_students_per_school = get_option('osb_min_students_per_school', '3');
$require_parent_consent = get_option('osb_require_parent_consent', '1');
$auto_approve_schools = get_option('osb_auto_approve_schools', '0');
$email_notifications_enabled = get_option('osb_email_notifications_enabled', '1');
$donation_enabled = get_option('osb_donation_enabled', '1');
$prize_distribution = isset($prize_distribution) ? $prize_distribution : array(
    'first_place' => 40,
    'second_place' => 25,
    'third_place' => 15,
    'participation' => 15,
    'organization' => 5
);

// Display settings errors/notices
settings_errors('osb_settings');
?>

<div class="wrap osb-admin-page">
    <div class="osb-header">
        <h1 class="wp-heading-inline">
            <span class="dashicons dashicons-admin-settings"></span>
            Settings
        </h1>

        <div class="osb-header-actions">
            <button type="button" class="button" id="reset-settings">
                <span class="dashicons dashicons-backup"></span>
                Reset to Defaults
            </button>
            <button type="button" class="button" id="export-settings">
                <span class="dashicons dashicons-download"></span>
                Export Settings
            </button>
            <button type="button" class="button" id="import-settings">
                <span class="dashicons dashicons-upload"></span>
                Import Settings
            </button>
        </div>
    </div>

    <hr class="wp-header-end">

    <form method="post" class="osb-settings-form">
        <?php wp_nonce_field('osb_settings_nonce', 'settings_nonce'); ?>

        <div class="osb-settings-container">

            <!-- General Settings -->
            <div class="osb-settings-section">
                <h2 class="osb-section-title">
                    <span class="dashicons dashicons-admin-generic"></span>
                    General Settings
                </h2>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="osb_organization_name"><?php _e('Organization Name', 'spelling-bee-pro'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="osb_organization_name" name="osb_organization_name"
                                   value="<?php echo esc_attr($organization_name); ?>" class="regular-text" />
                            <p class="description"><?php _e('The name of your organization as it appears on certificates and emails.', 'spelling-bee-pro'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="osb_contact_email"><?php _e('Contact Email', 'spelling-bee-pro'); ?></label>
                        </th>
                        <td>
                            <input type="email" id="osb_contact_email" name="osb_contact_email"
                                   value="<?php echo esc_attr($contact_email); ?>" class="regular-text" />
                            <p class="description"><?php _e('Primary contact email for participant inquiries and notifications.', 'spelling-bee-pro'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Registration Status', 'spelling-bee-pro'); ?></th>
                        <td>
                            <fieldset>
                                <label>
                                    <input type="radio" name="osb_registration_enabled" value="1" <?php checked($registration_enabled, '1'); ?> />
                                    <?php _e('Open - Schools can register', 'spelling-bee-pro'); ?>
                                </label><br>
                                <label>
                                    <input type="radio" name="osb_registration_enabled" value="0" <?php checked($registration_enabled, '0'); ?> />
                                    <?php _e('Closed - Registration disabled', 'spelling-bee-pro'); ?>
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Competition Settings -->
            <div class="osb-settings-section">
                <h2 class="osb-section-title">
                    <span class="dashicons dashicons-awards"></span>
                    Competition Settings
                </h2>

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="osb_min_students_per_school"><?php _e('Minimum Students per School', 'spelling-bee-pro'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="osb_min_students_per_school" name="osb_min_students_per_school"
                                   value="<?php echo esc_attr($min_students_per_school); ?>" min="1" max="10" class="small-text" />
                            <p class="description"><?php _e('Minimum number of students a school must register.', 'spelling-bee-pro'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="osb_max_students_per_school"><?php _e('Maximum Students per School', 'spelling-bee-pro'); ?></label>
                        </th>
                        <td>
                            <input type="number" id="osb_max_students_per_school" name="osb_max_students_per_school"
                                   value="<?php echo esc_attr($max_students_per_school); ?>" min="1" max="20" class="small-text" />
                            <p class="description"><?php _e('Maximum number of students a school can register.', 'spelling-bee-pro'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Parent Consent', 'spelling-bee-pro'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="osb_require_parent_consent" value="1" <?php checked($require_parent_consent, '1'); ?> />
                                <?php _e('Require parent/guardian consent forms for all students', 'spelling-bee-pro'); ?>
                            </label>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Auto Approval', 'spelling-bee-pro'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="osb_auto_approve_schools" value="1" <?php checked($auto_approve_schools, '1'); ?> />
                                <?php _e('Automatically approve school registrations (not recommended)', 'spelling-bee-pro'); ?>
                            </label>
                            <p class="description"><?php _e('When enabled, schools will be automatically approved without manual review.', 'spelling-bee-pro'); ?></p>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Prize Distribution -->
            <div class="osb-settings-section">
                <h2 class="osb-section-title">
                    <span class="dashicons dashicons-money-alt"></span>
                    Prize Distribution
                </h2>

                <p class="osb-section-description">
                    <?php _e('Configure how prize money is distributed among winners and organization costs. Total must equal 100%.', 'spelling-bee-pro'); ?>
                </p>

                <div class="osb-prize-grid">
                    <div class="osb-prize-item">
                        <label for="first_place"><?php _e('1st Place Winner', 'spelling-bee-pro'); ?></label>
                        <div class="osb-input-group">
                            <input type="number" id="first_place" name="prize_distribution[first_place]"
                                   value="<?php echo esc_attr($prize_distribution['first_place']); ?>"
                                   min="0" max="100" class="osb-percentage-input" />
                            <span class="osb-input-suffix">%</span>
                        </div>
                    </div>

                    <div class="osb-prize-item">
                        <label for="second_place"><?php _e('2nd Place Winner', 'spelling-bee-pro'); ?></label>
                        <div class="osb-input-group">
                            <input type="number" id="second_place" name="prize_distribution[second_place]"
                                   value="<?php echo esc_attr($prize_distribution['second_place']); ?>"
                                   min="0" max="100" class="osb-percentage-input" />
                            <span class="osb-input-suffix">%</span>
                        </div>
                    </div>

                    <div class="osb-prize-item">
                        <label for="third_place"><?php _e('3rd Place Winner', 'spelling-bee-pro'); ?></label>
                        <div class="osb-input-group">
                            <input type="number" id="third_place" name="prize_distribution[third_place]"
                                   value="<?php echo esc_attr($prize_distribution['third_place']); ?>"
                                   min="0" max="100" class="osb-percentage-input" />
                            <span class="osb-input-suffix">%</span>
                        </div>
                    </div>

                    <div class="osb-prize-item">
                        <label for="participation"><?php _e('Participation Awards', 'spelling-bee-pro'); ?></label>
                        <div class="osb-input-group">
                            <input type="number" id="participation" name="prize_distribution[participation]"
                                   value="<?php echo esc_attr($prize_distribution['participation']); ?>"
                                   min="0" max="100" class="osb-percentage-input" />
                            <span class="osb-input-suffix">%</span>
                        </div>
                    </div>

                    <div class="osb-prize-item">
                        <label for="organization"><?php _e('Organization Costs', 'spelling-bee-pro'); ?></label>
                        <div class="osb-input-group">
                            <input type="number" id="organization" name="prize_distribution[organization]"
                                   value="<?php echo esc_attr($prize_distribution['organization']); ?>"
                                   min="0" max="100" class="osb-percentage-input" />
                            <span class="osb-input-suffix">%</span>
                        </div>
                    </div>

                    <div class="osb-prize-total">
                        <label><?php _e('Total:', 'spelling-bee-pro'); ?></label>
                        <span id="total-percentage" class="osb-total-display">100%</span>
                    </div>
                </div>

                <div id="percentage-warning" class="osb-warning" style="display: none;">
                    <span class="dashicons dashicons-warning"></span>
                    <?php _e('Warning: Total percentage must equal 100%', 'spelling-bee-pro'); ?>
                </div>
            </div>

            <!-- Email Settings -->
            <div class="osb-settings-section">
                <h2 class="osb-section-title">
                    <span class="dashicons dashicons-email"></span>
                    Email Settings
                </h2>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Email Notifications', 'spelling-bee-pro'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="osb_email_notifications_enabled" value="1" <?php checked($email_notifications_enabled, '1'); ?> />
                                <?php _e('Send automated email notifications to schools and participants', 'spelling-bee-pro'); ?>
                            </label>
                        </td>
                    </tr>
                </table>

                <div class="osb-email-templates">
                    <h3><?php _e('Email Templates', 'spelling-bee-pro'); ?></h3>
                    <div class="osb-template-grid">
                        <div class="osb-template-item">
                            <h4><?php _e('Registration Confirmation', 'spelling-bee-pro'); ?></h4>
                            <p><?php _e('Sent when a school submits their registration', 'spelling-bee-pro'); ?></p>
                            <button type="button" class="button" data-template="registration_confirmation">
                                <?php _e('Edit Template', 'spelling-bee-pro'); ?>
                            </button>
                        </div>

                        <div class="osb-template-item">
                            <h4><?php _e('Approval Notification', 'spelling-bee-pro'); ?></h4>
                            <p><?php _e('Sent when a school registration is approved', 'spelling-bee-pro'); ?></p>
                            <button type="button" class="button" data-template="approval_notification">
                                <?php _e('Edit Template', 'spelling-bee-pro'); ?>
                            </button>
                        </div>

                        <div class="osb-template-item">
                            <h4><?php _e('Event Reminder', 'spelling-bee-pro'); ?></h4>
                            <p><?php _e('Sent as a reminder before the competition', 'spelling-bee-pro'); ?></p>
                            <button type="button" class="button" data-template="event_reminder">
                                <?php _e('Edit Template', 'spelling-bee-pro'); ?>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Donation Settings -->
            <div class="osb-settings-section">
                <h2 class="osb-section-title">
                    <span class="dashicons dashicons-heart"></span>
                    Donation Settings
                </h2>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Enable Donations', 'spelling-bee-pro'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="osb_donation_enabled" value="1" <?php checked($donation_enabled, '1'); ?> />
                                <?php _e('Allow donations to support the competition', 'spelling-bee-pro'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- Advanced Settings -->
            <div class="osb-settings-section">
                <h2 class="osb-section-title">
                    <span class="dashicons dashicons-admin-tools"></span>
                    Advanced Settings
                </h2>

                <table class="form-table">
                    <tr>
                        <th scope="row"><?php _e('Debug Mode', 'spelling-bee-pro'); ?></th>
                        <td>
                            <label>
                                <input type="checkbox" name="osb_debug_enabled" value="1" <?php checked(get_option('osb_debug_enabled'), '1'); ?> />
                                <?php _e('Enable debug logging for troubleshooting', 'spelling-bee-pro'); ?>
                            </label>
                            <p class="description"><?php _e('Only enable when troubleshooting issues. May impact performance.', 'spelling-bee-pro'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row"><?php _e('Data Retention', 'spelling-bee-pro'); ?></th>
                        <td>
                            <select name="osb_data_retention_period">
                                <option value="1" <?php selected(get_option('osb_data_retention_period', '5'), '1'); ?>>1 year</option>
                                <option value="2" <?php selected(get_option('osb_data_retention_period', '5'), '2'); ?>>2 years</option>
                                <option value="5" <?php selected(get_option('osb_data_retention_period', '5'), '5'); ?>>5 years</option>
                                <option value="10" <?php selected(get_option('osb_data_retention_period', '5'), '10'); ?>>10 years</option>
                                <option value="0" <?php selected(get_option('osb_data_retention_period', '5'), '0'); ?>>Keep indefinitely</option>
                            </select>
                            <p class="description"><?php _e('How long to keep competition data and participant information.', 'spelling-bee-pro'); ?></p>
                        </td>
                    </tr>
                </table>

                <div class="osb-maintenance-actions">
                    <h3><?php _e('Maintenance Actions', 'spelling-bee-pro'); ?></h3>
                    <div class="osb-action-buttons">
                        <button type="button" class="button" id="clear-cache">
                            <span class="dashicons dashicons-update"></span>
                            <?php _e('Clear Cache', 'spelling-bee-pro'); ?>
                        </button>
                        <button type="button" class="button" id="cleanup-logs">
                            <span class="dashicons dashicons-trash"></span>
                            <?php _e('Clean Up Logs', 'spelling-bee-pro'); ?>
                        </button>
                        <button type="button" class="button" id="check-system">
                            <span class="dashicons dashicons-admin-tools"></span>
                            <?php _e('System Check', 'spelling-bee-pro'); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Save Button -->
        <div class="osb-save-section">
            <p class="submit">
                <input type="submit" name="submit" id="submit" class="button-primary button-large"
                       value="<?php _e('Save Settings', 'spelling-bee-pro'); ?>" />
                <span class="osb-save-status" style="display: none;">
                    <span class="dashicons dashicons-yes-alt"></span>
                    <?php _e('Settings saved successfully!', 'spelling-bee-pro'); ?>
                </span>
            </p>
        </div>
    </form>
</div>

<!-- Email Template Modal -->
<div id="email-template-modal" class="osb-modal" style="display: none;">
    <div class="osb-modal-content">
        <div class="osb-modal-header">
            <h3 id="template-title"><?php _e('Edit Email Template', 'spelling-bee-pro'); ?></h3>
            <button type="button" class="osb-modal-close">&times;</button>
        </div>
        <div class="osb-modal-body">
            <form id="template-form">
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="template-subject"><?php _e('Subject Line', 'spelling-bee-pro'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="template-subject" class="widefat" />
                        </td>
                    </tr>
                    <tr>
                        <th scope="row">
                            <label for="template-content"><?php _e('Email Content', 'spelling-bee-pro'); ?></label>
                        </th>
                        <td>
                            <textarea id="template-content" rows="10" class="widefat"></textarea>
                            <p class="description">
                                <?php _e('Available placeholders: {school_name}, {contact_name}, {event_title}, {event_date}', 'spelling-bee-pro'); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="osb-modal-footer">
            <button type="button" id="save-template" class="button button-primary">
                <?php _e('Save Template', 'spelling-bee-pro'); ?>
            </button>
            <button type="button" class="button osb-modal-close">
                <?php _e('Cancel', 'spelling-bee-pro'); ?>
            </button>
        </div>
    </div>
</div>

<style>
/* Settings Page Styles */
.osb-settings-container {
    display: flex;
    flex-direction: column;
    gap: 30px;
    max-width: 1000px;
}

.osb-settings-section {
    background: #fff;
    border-radius: 12px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.osb-section-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 1.5rem;
    color: #333;
    margin: 0 0 20px 0;
    padding-bottom: 15px;
    border-bottom: 2px solid #e9ecef;
}

.osb-section-description {
    color: #666;
    font-size: 1rem;
    margin-bottom: 20px;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
    border-left: 4px solid #0073aa;
}

.osb-prize-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin-bottom: 20px;
}

.osb-prize-item {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.osb-prize-item label {
    font-weight: 600;
    color: #333;
}

.osb-input-group {
    display: flex;
    align-items: center;
    position: relative;
}

.osb-percentage-input {
    width: 80px;
    padding-right: 30px;
}

.osb-input-suffix {
    position: absolute;
    right: 10px;
    color: #666;
    font-weight: bold;
}

.osb-prize-total {
    display: flex;
    flex-direction: column;
    gap: 8px;
    background: #f8f9fa;
    padding: 15px;
    border-radius: 8px;
    text-align: center;
}

.osb-prize-total label {
    font-weight: 600;
    color: #333;
}

.osb-total-display {
    font-size: 1.5rem;
    font-weight: bold;
    color: #28a745;
}

.osb-total-display.error {
    color: #dc3545;
}

.osb-warning {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 12px;
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 4px;
    color: #856404;
    margin-top: 15px;
}

.osb-email-templates {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
}

.osb-template-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.osb-template-item {
    background: #f8f9fa;
    border-radius: 8px;
    padding: 20px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.osb-template-item:hover {
    border-color: #0073aa;
    background: #f0f8ff;
}

.osb-template-item h4 {
    margin: 0 0 8px 0;
    color: #333;
}

.osb-template-item p {
    margin: 0 0 15px 0;
    color: #666;
    font-size: 0.9rem;
}

.osb-maintenance-actions {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #e9ecef;
}

.osb-action-buttons {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
    margin-top: 15px;
}

.osb-action-buttons .button {
    display: flex;
    align-items: center;
    gap: 8px;
}

.osb-save-section {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 30px;
    text-align: center;
    margin-top: 40px;
}

.osb-save-status {
    color: #28a745;
    font-weight: 600;
    margin-left: 15px;
}

.osb-modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 100000;
    display: flex;
    align-items: center;
    justify-content: center;
}

.osb-modal-content {
    background: #fff;
    border-radius: 8px;
    width: 90%;
    max-width: 600px;
    max-height: 90vh;
    overflow-y: auto;
}

.osb-modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 20px;
    border-bottom: 1px solid #ddd;
}

.osb-modal-header h3 {
    margin: 0;
}

.osb-modal-close {
    background: none;
    border: none;
    font-size: 24px;
    cursor: pointer;
    padding: 0;
    width: 30px;
    height: 30px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.osb-modal-body {
    padding: 20px;
}

.osb-modal-footer {
    padding: 20px;
    border-top: 1px solid #ddd;
    text-align: right;
}

.osb-modal-footer .button {
    margin-left: 10px;
}

/* Responsive */
@media (max-width: 768px) {
    .osb-header {
        flex-direction: column;
        align-items: stretch;
    }

    .osb-prize-grid {
        grid-template-columns: 1fr;
    }

    .osb-template-grid {
        grid-template-columns: 1fr;
    }

    .osb-action-buttons {
        flex-direction: column;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Calculate percentage total
    function updateTotal() {
        let total = 0;
        $('.osb-percentage-input').each(function() {
            total += parseInt($(this).val()) || 0;
        });

        const $totalDisplay = $('#total-percentage');
        $totalDisplay.text(total + '%');

        if (total === 100) {
            $totalDisplay.removeClass('error');
            $('#percentage-warning').hide();
        } else {
            $totalDisplay.addClass('error');
            $('#percentage-warning').show();
        }
    }

    // Update total on input change
    $('.osb-percentage-input').on('input', updateTotal);

    // Email template editing
    $('[data-template]').on('click', function() {
        const templateType = $(this).data('template');
        $('#template-title').text('Edit ' + $(this).siblings('h4').text());
        $('#email-template-modal').show();

        // Load template data via AJAX
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'osb_admin_action',
                sub_action: 'get_email_template',
                template_type: templateType,
                nonce: osb_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#template-subject').val(response.data.subject);
                    $('#template-content').val(response.data.content);
                }
            }
        });
    });

    // Save email template
    $('#save-template').on('click', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).text('<?php _e('Saving...', 'spelling-bee-pro'); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'osb_admin_action',
                sub_action: 'save_email_template',
                template_type: 'registration_confirmation', // Get from modal context
                subject: $('#template-subject').val(),
                content: $('#template-content').val(),
                nonce: osb_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#email-template-modal').hide();
                    alert('<?php _e('Template saved successfully!', 'spelling-bee-pro'); ?>');
                } else {
                    alert('<?php _e('Error:', 'spelling-bee-pro'); ?> ' + response.data);
                }
                $btn.prop('disabled', false).text('<?php _e('Save Template', 'spelling-bee-pro'); ?>');
            },
            error: function() {
                alert('<?php _e('Network error. Please try again.', 'spelling-bee-pro'); ?>');
                $btn.prop('disabled', false).text('<?php _e('Save Template', 'spelling-bee-pro'); ?>');
            }
        });
    });

    // Close modal
    $('.osb-modal-close').on('click', function() {
        $('.osb-modal').hide();
    });

    // Maintenance actions
    $('#clear-cache').on('click', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).text('<?php _e('Clearing...', 'spelling-bee-pro'); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'osb_admin_action',
                sub_action: 'clear_cache',
                nonce: osb_ajax.nonce
            },
            success: function(response) {
                alert(response.success ? '<?php _e('Cache cleared successfully!', 'spelling-bee-pro'); ?>' : response.data);
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-update"></span> <?php _e('Clear Cache', 'spelling-bee-pro'); ?>');
            }
        });
    });

    $('#cleanup-logs').on('click', function() {
        if (!confirm('<?php _e('Are you sure you want to delete old log files?', 'spelling-bee-pro'); ?>')) {
            return;
        }

        const $btn = $(this);
        $btn.prop('disabled', true).text('<?php _e('Cleaning...', 'spelling-bee-pro'); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'osb_admin_action',
                sub_action: 'cleanup_logs',
                nonce: osb_ajax.nonce
            },
            success: function(response) {
                alert(response.success ? '<?php _e('Logs cleaned successfully!', 'spelling-bee-pro'); ?>' : response.data);
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-trash"></span> <?php _e('Clean Up Logs', 'spelling-bee-pro'); ?>');
            }
        });
    });

    $('#check-system').on('click', function() {
        const $btn = $(this);
        $btn.prop('disabled', true).text('<?php _e('Checking...', 'spelling-bee-pro'); ?>');

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'osb_admin_action',
                sub_action: 'system_check',
                nonce: osb_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php _e('System Check Results:', 'spelling-bee-pro'); ?>\n\n' + response.data.report);
                } else {
                    alert('<?php _e('Error:', 'spelling-bee-pro'); ?> ' + response.data);
                }
                $btn.prop('disabled', false).html('<span class="dashicons dashicons-admin-tools"></span> <?php _e('System Check', 'spelling-bee-pro'); ?>');
            }
        });
    });

    // Reset settings
    $('#reset-settings').on('click', function() {
        if (!confirm('<?php _e('Are you sure you want to reset all settings to defaults? This cannot be undone.', 'spelling-bee-pro'); ?>')) {
            return;
        }

        $.ajax({
            url: ajaxurl,
            type: 'POST',
            data: {
                action: 'osb_admin_action',
                sub_action: 'reset_settings',
                nonce: osb_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('<?php _e('Settings reset successfully!', 'spelling-bee-pro'); ?>');
                    location.reload();
                } else {
                    alert('<?php _e('Error:', 'spelling-bee-pro'); ?> ' + response.data);
                }
            }
        });
    });

    // Initialize total calculation
    updateTotal();
});
</script>