<?php
/**
 * Schools Form Template (New/Edit School)
 *
 * @var object|null $school
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$is_edit = !empty($school);
$page_title = $is_edit ? __('Edit School', 'spelling-bee-pro') : __('Add New School', 'spelling-bee-pro');
$admin_menu = OSB_Admin_Menu::getInstance();
?>

<div class="wrap">
    <h1><?php echo esc_html($page_title); ?></h1>

    <?php if (isset($_GET['saved'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('School saved successfully.', 'spelling-bee-pro'); ?></p>
        </div>
    <?php endif; ?>

    <form id="osb-school-form" method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <?php wp_nonce_field('osb_save_school', 'osb_school_nonce'); ?>

        <input type="hidden" name="action" value="osb_save_school">
        <input type="hidden" name="school_id" value="<?php echo $is_edit ? intval($school->id) : 0; ?>">

        <div class="osb-form-container">
            <div class="osb-form-main">
                <!-- Basic Information -->
                <div class="osb-form-section">
                    <h2><?php _e('Basic Information', 'spelling-bee-pro'); ?></h2>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="school-name"><?php _e('School Name', 'spelling-bee-pro'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="text"
                                       id="school-name"
                                       name="school_name"
                                       value="<?php echo $is_edit ? esc_attr($school->school_name) : ''; ?>"
                                       class="regular-text"
                                       required>
                                <p class="description"><?php _e('Enter the full name of the school.', 'spelling-bee-pro'); ?></p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="school-type"><?php _e('School Type', 'spelling-bee-pro'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <select id="school-type" name="school_type" required>
                                    <option value=""><?php _e('Select Type', 'spelling-bee-pro'); ?></option>
                                    <option value="public" <?php echo ($is_edit && $school->school_type === 'public') ? 'selected' : ''; ?>><?php _e('Public', 'spelling-bee-pro'); ?></option>
                                    <option value="private" <?php echo ($is_edit && $school->school_type === 'private') ? 'selected' : ''; ?>><?php _e('Private', 'spelling-bee-pro'); ?></option>
                                    <option value="federal" <?php echo ($is_edit && $school->school_type === 'federal') ? 'selected' : ''; ?>><?php _e('Federal', 'spelling-bee-pro'); ?></option>
                                    <option value="state" <?php echo ($is_edit && $school->school_type === 'state') ? 'selected' : ''; ?>><?php _e('State', 'spelling-bee-pro'); ?></option>
                                </select>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="school-state"><?php _e('State', 'spelling-bee-pro'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="text"
                                       id="school-state"
                                       name="state"
                                       value="<?php echo $is_edit ? esc_attr($school->state) : ''; ?>"
                                       class="regular-text"
                                       required>
                                <p class="description"><?php _e('State where the school is located.', 'spelling-bee-pro'); ?></p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="school-address"><?php _e('Address', 'spelling-bee-pro'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <textarea id="school-address"
                                          name="address"
                                          class="large-text"
                                          rows="3"
                                          required><?php echo $is_edit ? esc_textarea($school->address) : ''; ?></textarea>
                                <p class="description"><?php _e('Full address of the school.', 'spelling-bee-pro'); ?></p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="school-status"><?php _e('Status', 'spelling-bee-pro'); ?></label>
                            </th>
                            <td>
                                <select id="school-status" name="status">
                                    <option value="pending" <?php echo ($is_edit && $school->status === 'pending') ? 'selected' : ''; ?>><?php _e('Pending', 'spelling-bee-pro'); ?></option>
                                    <option value="documents_submitted" <?php echo ($is_edit && $school->status === 'documents_submitted') ? 'selected' : ''; ?>><?php _e('Documents Submitted', 'spelling-bee-pro'); ?></option>
                                    <option value="under_review" <?php echo ($is_edit && $school->status === 'under_review') ? 'selected' : ''; ?>><?php _e('Under Review', 'spelling-bee-pro'); ?></option>
                                    <option value="approved" <?php echo ($is_edit && $school->status === 'approved') ? 'selected' : ''; ?>><?php _e('Approved', 'spelling-bee-pro'); ?></option>
                                    <option value="rejected" <?php echo ($is_edit && $school->status === 'rejected') ? 'selected' : ''; ?>><?php _e('Rejected', 'spelling-bee-pro'); ?></option>
                                    <option value="confirmed" <?php echo ($is_edit && $school->status === 'confirmed') ? 'selected' : ''; ?>><?php _e('Confirmed', 'spelling-bee-pro'); ?></option>
                                </select>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Contact Information -->
                <div class="osb-form-section">
                    <h2><?php _e('Contact Information', 'spelling-bee-pro'); ?></h2>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="contact-person"><?php _e('Contact Person', 'spelling-bee-pro'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="text"
                                       id="contact-person"
                                       name="contact_person"
                                       value="<?php echo $is_edit ? esc_attr($school->contact_person) : ''; ?>"
                                       class="regular-text"
                                       required>
                                <p class="description"><?php _e('Name of the school representative.', 'spelling-bee-pro'); ?></p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="contact-email"><?php _e('Contact Email', 'spelling-bee-pro'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="email"
                                       id="contact-email"
                                       name="contact_email"
                                       value="<?php echo $is_edit ? esc_attr($school->contact_email) : ''; ?>"
                                       class="regular-text"
                                       required>
                                <p class="description"><?php _e('Primary email address for school communications.', 'spelling-bee-pro'); ?></p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="contact-phone"><?php _e('Contact Phone', 'spelling-bee-pro'); ?></label>
                            </th>
                            <td>
                                <input type="tel"
                                       id="contact-phone"
                                       name="phone"
                                       value="<?php echo $is_edit ? esc_attr($school->phone) : ''; ?>"
                                       class="regular-text">
                                <p class="description"><?php _e('Phone number for the school representative.', 'spelling-bee-pro'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- WordPress User Association -->
                <?php if ($is_edit && $school->wp_user_id): ?>
                <div class="osb-form-section">
                    <h2><?php _e('WordPress User', 'spelling-bee-pro'); ?></h2>

                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php _e('Associated User', 'spelling-bee-pro'); ?></th>
                            <td>
                                <?php
                                $user = get_user_by('ID', $school->wp_user_id);
                                if ($user): ?>
                                    <p>
                                        <strong><?php echo esc_html($user->display_name); ?></strong>
                                        (<?php echo esc_html($user->user_email); ?>)
                                        <br>
                                        <a href="<?php echo get_edit_user_link($school->wp_user_id); ?>" target="_blank">
                                            <?php _e('Edit User', 'spelling-bee-pro'); ?>
                                        </a>
                                    </p>
                                <?php else: ?>
                                    <p class="osb-error"><?php _e('Associated user not found.', 'spelling-bee-pro'); ?></p>
                                <?php endif; ?>
                            </td>
                        </tr>
                    </table>
                </div>
                <?php endif; ?>

                <!-- Submit Buttons -->
                <div class="osb-form-actions">
                    <?php submit_button($is_edit ? __('Update School', 'spelling-bee-pro') : __('Create School', 'spelling-bee-pro'), 'primary', 'submit'); ?>
                    <a href="<?php echo $admin_menu->getAdminUrl('schools'); ?>" class="button">
                        <?php _e('Cancel', 'spelling-bee-pro'); ?>
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
.osb-form-container {
    max-width: 800px;
}

.osb-form-section {
    background: #fff;
    border: 1px solid #ccd0d4;
    box-shadow: 0 1px 1px rgba(0,0,0,.04);
    margin-bottom: 20px;
}

.osb-form-section h2 {
    background: #f7f7f7;
    border-bottom: 1px solid #ccd0d4;
    margin: 0;
    padding: 15px 20px;
    font-size: 16px;
}

.osb-form-section .form-table {
    margin: 0;
    padding: 20px;
}

.required {
    color: #d63638;
}

.osb-form-actions {
    margin: 20px 0;
}

.osb-error {
    color: #d63638;
}
</style>