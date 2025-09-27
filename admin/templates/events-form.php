<?php
/**
 * Events Form Template (New/Edit Event)
 *
 * @var object|null $event
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$is_edit = !empty($event);
$page_title = $is_edit ? __('Edit Event', 'spelling-bee-pro') : __('Add New Event', 'spelling-bee-pro');
?>

<div class="wrap">
    <h1><?php echo esc_html($page_title); ?></h1>

    <?php if (isset($_GET['saved'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Event saved successfully.', 'spelling-bee-pro'); ?></p>
        </div>
    <?php endif; ?>

    <form id="osb-event-form" method="post" action="<?php echo admin_url('admin-post.php'); ?>" enctype="multipart/form-data">
        <?php wp_nonce_field('osb_save_event', 'osb_event_nonce'); ?>

        <input type="hidden" name="action" value="osb_save_event">
        <input type="hidden" name="event_id" value="<?php echo $is_edit ? intval($event->id) : 0; ?>">

        <div class="osb-form-container">
            <div class="osb-form-main">
                <!-- Basic Information -->
                <div class="osb-form-section">
                    <h2><?php _e('Basic Information', 'spelling-bee-pro'); ?></h2>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="event-title"><?php _e('Event Title', 'spelling-bee-pro'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="text"
                                       id="event-title"
                                       name="title"
                                       value="<?php echo $is_edit ? esc_attr($event->title) : ''; ?>"
                                       class="regular-text"
                                       required>
                                <p class="description"><?php _e('Enter the name of your spelling bee event.', 'spelling-bee-pro'); ?></p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="event-description"><?php _e('Description', 'spelling-bee-pro'); ?></label>
                            </th>
                            <td>
                                <textarea id="event-description"
                                          name="description"
                                          rows="5"
                                          cols="50"
                                          class="large-text"><?php echo $is_edit ? esc_textarea($event->description) : ''; ?></textarea>
                                <p class="description"><?php _e('Provide a detailed description of the event.', 'spelling-bee-pro'); ?></p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="event-year"><?php _e('Event Year', 'spelling-bee-pro'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="number"
                                       id="event-year"
                                       name="year"
                                       value="<?php echo $is_edit ? intval($event->year) : date('Y'); ?>"
                                       min="<?php echo date('Y') - 5; ?>"
                                       max="<?php echo date('Y') + 10; ?>"
                                       class="small-text"
                                       required>
                                <p class="description"><?php _e('The year this competition will take place.', 'spelling-bee-pro'); ?></p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="event-status"><?php _e('Status', 'spelling-bee-pro'); ?></label>
                            </th>
                            <td>
                                <select id="event-status" name="status" class="regular-text">
                                    <option value="upcoming" <?php echo ($is_edit && $event->status === 'upcoming') ? 'selected' : ''; ?>>
                                        <?php _e('Upcoming', 'spelling-bee-pro'); ?>
                                    </option>
                                    <option value="live" <?php echo ($is_edit && $event->status === 'live') ? 'selected' : ''; ?>>
                                        <?php _e('Live', 'spelling-bee-pro'); ?>
                                    </option>
                                    <option value="completed" <?php echo ($is_edit && $event->status === 'completed') ? 'selected' : ''; ?>>
                                        <?php _e('Completed', 'spelling-bee-pro'); ?>
                                    </option>
                                    <option value="cancelled" <?php echo ($is_edit && $event->status === 'cancelled') ? 'selected' : ''; ?>>
                                        <?php _e('Cancelled', 'spelling-bee-pro'); ?>
                                    </option>
                                </select>
                                <p class="description"><?php _e('Current status of the event.', 'spelling-bee-pro'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Date and Time -->
                <div class="osb-form-section">
                    <h2><?php _e('Date & Time', 'spelling-bee-pro'); ?></h2>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="event-date"><?php _e('Event Date', 'spelling-bee-pro'); ?> <span class="required">*</span></label>
                            </th>
                            <td>
                                <input type="date"
                                       id="event-date"
                                       name="event_date"
                                       value="<?php echo $is_edit ? esc_attr($event->event_date) : ''; ?>"
                                       class="regular-text"
                                       required>
                                <p class="description"><?php _e('The date when the competition will be held.', 'spelling-bee-pro'); ?></p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="event-time"><?php _e('Event Time', 'spelling-bee-pro'); ?></label>
                            </th>
                            <td>
                                <input type="time"
                                       id="event-time"
                                       name="event_time"
                                       value="<?php echo $is_edit ? esc_attr($event->event_time) : ''; ?>"
                                       class="regular-text">
                                <p class="description"><?php _e('The time when the competition will start.', 'spelling-bee-pro'); ?></p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="registration-deadline"><?php _e('Registration Deadline', 'spelling-bee-pro'); ?></label>
                            </th>
                            <td>
                                <input type="date"
                                       id="registration-deadline"
                                       name="registration_deadline"
                                       value="<?php echo $is_edit ? esc_attr($event->registration_deadline) : ''; ?>"
                                       class="regular-text">
                                <p class="description"><?php _e('Last date for school registrations.', 'spelling-bee-pro'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Venue Information -->
                <div class="osb-form-section">
                    <h2><?php _e('Venue Information', 'spelling-bee-pro'); ?></h2>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="venue-name"><?php _e('Venue Name', 'spelling-bee-pro'); ?></label>
                            </th>
                            <td>
                                <input type="text"
                                       id="venue-name"
                                       name="venue_name"
                                       value="<?php echo $is_edit ? esc_attr($event->venue_name) : ''; ?>"
                                       class="regular-text">
                                <p class="description"><?php _e('Name of the venue where the competition will be held.', 'spelling-bee-pro'); ?></p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="venue-address"><?php _e('Venue Address', 'spelling-bee-pro'); ?></label>
                            </th>
                            <td>
                                <textarea id="venue-address"
                                          name="venue_address"
                                          rows="4"
                                          cols="50"
                                          class="large-text"><?php echo $is_edit ? esc_textarea($event->venue_address) : ''; ?></textarea>
                                <p class="description"><?php _e('Full address of the venue.', 'spelling-bee-pro'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>

                <!-- Competition Settings -->
                <div class="osb-form-section">
                    <h2><?php _e('Competition Settings', 'spelling-bee-pro'); ?></h2>

                    <table class="form-table">
                        <tr>
                            <th scope="row">
                                <label for="min-students"><?php _e('Min Students per School', 'spelling-bee-pro'); ?></label>
                            </th>
                            <td>
                                <input type="number"
                                       id="min-students"
                                       name="min_students_per_school"
                                       value="<?php echo $is_edit ? intval($event->min_students_per_school ?? 3) : 3; ?>"
                                       min="1"
                                       max="10"
                                       class="small-text">
                                <p class="description"><?php _e('Minimum number of students each school must register to participate.', 'spelling-bee-pro'); ?></p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="max-students"><?php _e('Max Students per School', 'spelling-bee-pro'); ?></label>
                            </th>
                            <td>
                                <input type="number"
                                       id="max-students"
                                       name="max_students_per_school"
                                       value="<?php echo $is_edit ? intval($event->max_students_per_school) : 5; ?>"
                                       min="1"
                                       max="20"
                                       class="small-text">
                                <p class="description"><?php _e('Maximum number of students each school can register.', 'spelling-bee-pro'); ?></p>
                            </td>
                        </tr>

                        <tr>
                            <th scope="row">
                                <label for="prize-fund-goal"><?php _e('Prize Fund Goal', 'spelling-bee-pro'); ?></label>
                            </th>
                            <td>
                                <input type="number"
                                       id="prize-fund-goal"
                                       name="prize_fund_goal"
                                       value="<?php echo $is_edit ? floatval($event->prize_fund_goal) : ''; ?>"
                                       step="0.01"
                                       min="0"
                                       class="regular-text">
                                <p class="description"><?php _e('Target amount for the prize fund (optional).', 'spelling-bee-pro'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <div class="osb-form-sidebar">
                <!-- Event Flyer -->
                <div class="osb-form-section">
                    <h3><?php _e('Event Flyer', 'spelling-bee-pro'); ?></h3>

                    <div id="osb-flyer-preview">
                        <?php if ($is_edit && !empty($event->flyer_url)): ?>
                            <div class="osb-current-flyer">
                                <img src="<?php echo esc_url($event->flyer_url); ?>" alt="Current Flyer" style="max-width: 100%; height: auto;">
                                <p><strong><?php _e('Current Flyer', 'spelling-bee-pro'); ?></strong></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <p>
                        <input type="url"
                               id="flyer-url"
                               name="flyer_url"
                               value="<?php echo $is_edit ? esc_url($event->flyer_url) : ''; ?>"
                               class="widefat"
                               placeholder="<?php _e('Enter flyer image URL or upload below', 'spelling-bee-pro'); ?>">
                    </p>

                    <p>
                        <button type="button" id="upload-flyer-btn" class="button button-secondary">
                            <?php _e('Upload Flyer', 'spelling-bee-pro'); ?>
                        </button>
                    </p>

                    <p class="description">
                        <?php _e('Upload or provide URL for the event flyer image. Recommended size: 800x600 pixels.', 'spelling-bee-pro'); ?>
                    </p>
                </div>

                <!-- Video Management -->
                <div class="osb-form-section">
                    <h3><?php _e('Video Management', 'spelling-bee-pro'); ?></h3>

                    <?php if ($is_edit): ?>
                        <div id="osb-video-list">
                            <?php
                            global $wpdb;
                            $videos = $wpdb->get_results(
                                $wpdb->prepare(
                                    "SELECT * FROM {$wpdb->prefix}" . OSB_TABLE_PREFIX . "event_videos WHERE event_id = %d ORDER BY created_at DESC",
                                    $event->id
                                )
                            );
                            ?>

                            <?php if (!empty($videos)): ?>
                                <?php foreach ($videos as $video): ?>
                                    <div class="osb-video-item" data-video-id="<?php echo $video->id; ?>">
                                        <!-- Video Thumbnail -->
                                        <?php if (!empty($video->youtube_url)): ?>
                                            <?php
                                            // Extract YouTube video ID for thumbnail
                                            preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $video->youtube_url, $matches);
                                            $youtube_id = $matches[1] ?? '';
                                            ?>
                                            <?php if ($youtube_id): ?>
                                                <div class="osb-video-thumbnail">
                                                    <img src="https://img.youtube.com/vi/<?php echo esc_attr($youtube_id); ?>/maxresdefault.jpg"
                                                         alt="<?php echo esc_attr($video->title); ?>"
                                                         onerror="this.src='https://img.youtube.com/vi/<?php echo esc_attr($youtube_id); ?>/hqdefault.jpg'">
                                                    <div class="osb-video-overlay">
                                                        <span class="dashicons dashicons-controls-play"></span>
                                                    </div>
                                                </div>
                                            <?php endif; ?>
                                        <?php elseif (!empty($video->flyer_image)): ?>
                                            <div class="osb-video-thumbnail">
                                                <img src="<?php echo esc_url($video->flyer_image); ?>" alt="<?php echo esc_attr($video->title); ?>">
                                                <div class="osb-video-overlay">
                                                    <span class="dashicons dashicons-format-image"></span>
                                                </div>
                                            </div>
                                        <?php else: ?>
                                            <div class="osb-video-placeholder">
                                                <span class="dashicons dashicons-video-alt3"></span>
                                            </div>
                                        <?php endif; ?>

                                        <div class="osb-video-info">
                                            <strong><?php echo esc_html($video->title); ?></strong><br>
                                            <small><?php echo esc_html(ucfirst(str_replace('_', ' ', $video->video_type))); ?> -
                                                <?php echo $video->is_active ? __('Active', 'spelling-bee-pro') : __('Inactive', 'spelling-bee-pro'); ?>
                                            </small>
                                            <?php if (!empty($video->youtube_url)): ?>
                                                <br><small><a href="<?php echo esc_url($video->youtube_url); ?>" target="_blank">View on YouTube</a></small>
                                            <?php endif; ?>
                                        </div>
                                        <div class="osb-video-actions">
                                            <button type="button" class="button button-small osb-edit-video" data-video-id="<?php echo $video->id; ?>">
                                                <?php _e('Edit', 'spelling-bee-pro'); ?>
                                            </button>
                                            <button type="button" class="button button-small osb-delete-video" data-video-id="<?php echo $video->id; ?>">
                                                <?php _e('Delete', 'spelling-bee-pro'); ?>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p><?php _e('No video added yet.', 'spelling-bee-pro'); ?></p>
                            <?php endif; ?>
                        </div>

                        <?php if (empty($videos)): ?>
                            <button type="button" id="add-video-btn" class="button button-secondary">
                                <?php _e('Add Video', 'spelling-bee-pro'); ?>
                            </button>
                        <?php else: ?>
                            <p class="description">
                                <?php _e('Only one video per event is allowed. Delete the current video to add a new one.', 'spelling-bee-pro'); ?>
                            </p>
                        <?php endif; ?>
                    <?php else: ?>
                        <p class="description">
                            <?php _e('Video management will be available after saving the event.', 'spelling-bee-pro'); ?>
                        </p>
                    <?php endif; ?>
                </div>

                <!-- Save Actions -->
                <div class="osb-form-section">
                    <h3><?php _e('Actions', 'spelling-bee-pro'); ?></h3>

                    <div class="osb-save-actions">
                        <p>
                            <input type="submit" name="save" id="save-event" class="button button-primary button-large"
                                   value="<?php echo $is_edit ? __('Update Event', 'spelling-bee-pro') : __('Create Event', 'spelling-bee-pro'); ?>">
                        </p>

                        <p>
                            <input type="submit" name="save_and_continue" id="save-and-continue" class="button button-secondary"
                                   value="<?php _e('Save & Continue Editing', 'spelling-bee-pro'); ?>">
                        </p>

                        <p>
                            <a href="<?php echo admin_url('admin.php?page=spelling-bee-events'); ?>" class="button">
                                <?php _e('Cancel', 'spelling-bee-pro'); ?>
                            </a>
                        </p>

                        <?php if ($is_edit): ?>
                        <hr>
                        <p>
                            <a href="<?php echo admin_url('admin.php?page=spelling-bee-registrations&event_id=' . $event->id); ?>"
                               class="button">
                                <?php _e('View Registrations', 'spelling-bee-pro'); ?>
                            </a>
                        </p>

                        <p>
                            <a href="<?php echo admin_url('admin.php?page=spelling-bee-donations&event_id=' . $event->id); ?>"
                               class="button">
                                <?php _e('View Donations', 'spelling-bee-pro'); ?>
                            </a>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<!-- Video Modal -->
<div id="osb-video-modal" class="osb-modal" style="display: none;">
    <div class="osb-modal-content">
        <div class="osb-modal-header">
            <h3 id="osb-video-modal-title"><?php _e('Add Video', 'spelling-bee-pro'); ?></h3>
            <button type="button" class="osb-modal-close">&times;</button>
        </div>
        <div class="osb-modal-body">
            <form id="osb-video-form">
                <input type="hidden" id="video-id" name="video_id" value="">
                <input type="hidden" id="video-event-id" name="event_id" value="<?php echo $is_edit ? $event->id : 0; ?>">

                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label for="video-title"><?php _e('Video Title', 'spelling-bee-pro'); ?></label>
                        </th>
                        <td>
                            <input type="text" id="video-title" name="video_title" class="widefat" required>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="video-type"><?php _e('Video Type', 'spelling-bee-pro'); ?></label>
                        </th>
                        <td>
                            <select id="video-type" name="video_type" class="widefat">
                                <option value="flyer"><?php _e('Flyer/Promotional', 'spelling-bee-pro'); ?></option>
                                <option value="live"><?php _e('Live Stream', 'spelling-bee-pro'); ?></option>
                                <option value="highlight"><?php _e('Highlights', 'spelling-bee-pro'); ?></option>
                                <option value="recap"><?php _e('Event Recap', 'spelling-bee-pro'); ?></option>
                            </select>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="video-url"><?php _e('Video URL', 'spelling-bee-pro'); ?></label>
                        </th>
                        <td>
                            <input type="url" id="video-url" name="video_url" class="widefat" required>
                            <p class="description"><?php _e('YouTube, Vimeo, or direct video URL.', 'spelling-bee-pro'); ?></p>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="video-description"><?php _e('Description', 'spelling-bee-pro'); ?></label>
                        </th>
                        <td>
                            <textarea id="video-description" name="video_description" rows="3" class="widefat"></textarea>
                        </td>
                    </tr>

                    <tr>
                        <th scope="row">
                            <label for="video-active"><?php _e('Status', 'spelling-bee-pro'); ?></label>
                        </th>
                        <td>
                            <label>
                                <input type="checkbox" id="video-active" name="is_active" value="1" checked>
                                <?php _e('Active (visible to public)', 'spelling-bee-pro'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
            </form>
        </div>
        <div class="osb-modal-footer">
            <button type="button" id="save-video-btn" class="button button-primary">
                <?php _e('Save Video', 'spelling-bee-pro'); ?>
            </button>
            <button type="button" class="button osb-modal-close">
                <?php _e('Cancel', 'spelling-bee-pro'); ?>
            </button>
        </div>
    </div>
</div>

<style>
.osb-form-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-top: 20px;
}

.osb-form-main,
.osb-form-sidebar {
    background: #fff;
    padding: 0;
}

.osb-form-section {
    background: #fff;
    border: 1px solid #ddd;
    margin-bottom: 20px;
    border-radius: 4px;
}

.osb-form-section h2,
.osb-form-section h3 {
    margin: 0;
    padding: 15px 20px;
    background: #f9f9f9;
    border-bottom: 1px solid #ddd;
    font-size: 16px;
}

.osb-form-section .form-table {
    margin: 0;
    padding: 20px;
}

.osb-form-section p {
    margin: 15px 20px;
}

.required {
    color: #dc3232;
}

.osb-current-flyer img {
    border: 1px solid #ddd;
    border-radius: 4px;
}

/* Video Management Styles */
.osb-video-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px;
    border: 1px solid #ddd;
    border-radius: 4px;
    margin-bottom: 10px;
    background: #f9f9f9;
}

.osb-video-thumbnail {
    position: relative;
    width: 120px;
    height: 68px;
    overflow: hidden;
    border-radius: 4px;
    background: #000;
    flex-shrink: 0;
}

.osb-video-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.osb-video-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.osb-video-item:hover .osb-video-overlay {
    opacity: 1;
}

.osb-video-overlay .dashicons {
    color: white;
    font-size: 24px;
}

.osb-video-placeholder {
    width: 120px;
    height: 68px;
    background: linear-gradient(45deg, #f0f0f0, #e0e0e0);
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.osb-video-placeholder .dashicons {
    color: #999;
    font-size: 24px;
}

.osb-video-info {
    flex: 1;
}

.osb-video-info strong {
    font-size: 14px;
    display: block;
    margin-bottom: 5px;
}

.osb-video-info small {
    color: #666;
    font-size: 12px;
}

.osb-video-actions {
    display: flex;
    gap: 5px;
    flex-shrink: 0;
}

.osb-save-actions {
    padding: 20px;
}

.osb-save-actions p {
    margin: 10px 0;
}

/* Modal Styles */
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

@media (max-width: 768px) {
    .osb-form-container {
        grid-template-columns: 1fr;
    }

    .osb-modal-content {
        width: 95%;
        margin: 20px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Media uploader for flyer
    let flyerFrame;

    $('#upload-flyer-btn').on('click', function(e) {
        e.preventDefault();

        if (flyerFrame) {
            flyerFrame.open();
            return;
        }

        flyerFrame = wp.media({
            title: '<?php _e('Select Event Flyer', 'spelling-bee-pro'); ?>',
            button: {
                text: '<?php _e('Use this image', 'spelling-bee-pro'); ?>'
            },
            multiple: false,
            library: {
                type: 'image'
            }
        });

        flyerFrame.on('select', function() {
            const attachment = flyerFrame.state().get('selection').first().toJSON();
            $('#flyer-url').val(attachment.url);

            $('#osb-flyer-preview').html(
                '<div class="osb-current-flyer">' +
                '<img src="' + attachment.url + '" alt="Selected Flyer" style="max-width: 100%; height: auto;">' +
                '<p><strong><?php _e('Selected Flyer', 'spelling-bee-pro'); ?></strong></p>' +
                '</div>'
            );
        });

        flyerFrame.open();
    });

    // Video management
    $('#add-video-btn').on('click', function() {
        $('#osb-video-modal-title').text('<?php _e('Add Video', 'spelling-bee-pro'); ?>');
        $('#osb-video-form')[0].reset();
        $('#video-id').val('');
        $('#osb-video-modal').show();
    });

    $('.osb-edit-video').on('click', function() {
        const videoId = $(this).data('video-id');

        // Load video data via AJAX
        $.ajax({
            url: osb_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'osb_admin_action',
                sub_action: 'get_video',
                video_id: videoId,
                nonce: osb_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    const video = response.data;

                    $('#osb-video-modal-title').text('<?php _e('Edit Video', 'spelling-bee-pro'); ?>');
                    $('#video-id').val(video.id);
                    $('#video-title').val(video.video_title);
                    $('#video-type').val(video.video_type);
                    $('#video-url').val(video.video_url);
                    $('#video-description').val(video.video_description);
                    $('#video-active').prop('checked', video.is_active == 1);

                    $('#osb-video-modal').show();
                } else {
                    alert('Error loading video data: ' + (response.data.message || 'Unknown error'));
                }
            },
            error: function() {
                alert('Error loading video data. Please try again.');
            }
        });
    });

    $('.osb-delete-video').on('click', function() {
        if (!confirm('<?php _e('Are you sure you want to delete this video?', 'spelling-bee-pro'); ?>')) {
            return;
        }

        const videoId = $(this).data('video-id');

        $.ajax({
            url: osb_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'osb_admin_action',
                sub_action: 'delete_video',
                video_id: videoId,
                nonce: osb_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert('Video deleted successfully!');
                    location.reload();
                } else {
                    alert('Error: ' + (response.data.message || 'Failed to delete video'));
                }
            },
            error: function() {
                alert('Error deleting video. Please try again.');
            }
        });
    });

    // Modal close
    $('.osb-modal-close').on('click', function() {
        $('#osb-video-modal').hide();
    });

    // Save video
    $('#save-video-btn').on('click', function() {
        // Validate form
        const form = $('#osb-video-form')[0];
        if (!form.checkValidity()) {
            alert('Please fill in all required fields.');
            return;
        }

        // Save video via AJAX
        const formData = new FormData(form);
        formData.append('action', 'osb_admin_action');
        formData.append('sub_action', 'save_video');
        formData.append('nonce', osb_admin.nonce);

        $.ajax({
            url: osb_admin.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            beforeSend: function() {
                $('#save-video-btn').prop('disabled', true).text('Saving...');
            },
            success: function(response) {
                $('#save-video-btn').prop('disabled', false).text('Save Video');

                if (response.success) {
                    alert('Video saved successfully!');
                    $('#osb-video-modal').hide();
                    // Refresh video list
                    location.reload();
                } else {
                    alert('Error: ' + (response.data.message || response.data || 'Unknown error'));
                }
            },
            error: function() {
                $('#save-video-btn').prop('disabled', false).text('Save Video');
                alert('Error: Failed to save video. Please try again.');
            }
        });
    });

    // Form validation
    $('#osb-event-form').on('submit', function(e) {
        let isValid = true;

        // Check required fields
        const requiredFields = ['title', 'year', 'event_date'];
        requiredFields.forEach(function(field) {
            const $field = $('[name="' + field + '"]');
            if (!$field.val().trim()) {
                $field.css('border-color', '#dc3232');
                isValid = false;
            } else {
                $field.css('border-color', '');
            }
        });

        if (!isValid) {
            alert('<?php _e('Please fill in all required fields.', 'spelling-bee-pro'); ?>');
            e.preventDefault();
        }
    });
});
</script>