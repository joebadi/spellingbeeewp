<?php
/**
 * Event View Template
 *
 * @var object $event
 * @var array $registrations
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

if (!$event) {
    echo '<div class="wrap"><h1>Event Not Found</h1><p>The requested event could not be found.</p></div>';
    return;
}

// Get event statistics
global $wpdb;
$stats = $wpdb->get_row($wpdb->prepare("
    SELECT
        COUNT(r.id) as total_registrations,
        SUM(CASE WHEN r.status = 'pending' THEN 1 ELSE 0 END) as pending_registrations,
        SUM(CASE WHEN r.status = 'approved' THEN 1 ELSE 0 END) as approved_registrations,
        SUM(CASE WHEN r.status = 'rejected' THEN 1 ELSE 0 END) as rejected_registrations,
        COUNT(DISTINCT r.school_id) as total_schools,
        COALESCE(SUM(d.amount), 0) as total_donations
    FROM {$wpdb->prefix}osb_registrations r
    LEFT JOIN {$wpdb->prefix}osb_donations d ON r.event_id = d.event_id
    WHERE r.event_id = %d
", $event->id), ARRAY_A);

// Get recent registrations
$recent_registrations = $wpdb->get_results($wpdb->prepare("
    SELECT r.*, s.name as school_name, u.display_name
    FROM {$wpdb->prefix}osb_registrations r
    LEFT JOIN {$wpdb->prefix}osb_schools s ON r.school_id = s.id
    LEFT JOIN {$wpdb->prefix}users u ON r.user_id = u.ID
    WHERE r.event_id = %d
    ORDER BY r.created_at DESC
    LIMIT 5
", $event->id));

// Get event videos
$table_prefix = defined('OSB_TABLE_PREFIX') ? OSB_TABLE_PREFIX : 'osb_';
$videos = $wpdb->get_results($wpdb->prepare("
    SELECT * FROM {$wpdb->prefix}{$table_prefix}event_videos
    WHERE event_id = %d AND is_active = 1
    ORDER BY created_at DESC
", $event->id));

// Get sponsors
$sponsors = $wpdb->get_results($wpdb->prepare("
    SELECT * FROM {$wpdb->prefix}osb_sponsors
    WHERE event_id = %d AND status = 'active'
    ORDER BY sponsorship_level DESC, amount DESC
", $event->id));
?>

<div class="wrap osb-admin-page">
    <div class="osb-header">
        <h1 class="wp-heading-inline">
            <span class="dashicons dashicons-visibility"></span>
            Event Details: <?php echo esc_html($event->name ?: $event->title); ?>
        </h1>

        <div class="osb-header-actions">
            <a href="<?php echo admin_url('admin.php?page=spelling-bee-events&action=edit&event_id=' . $event->id); ?>"
               class="button button-primary">
                <span class="dashicons dashicons-edit"></span>
                Edit Event
            </a>
            <a href="<?php echo admin_url('admin.php?page=spelling-bee-registrations&event_id=' . $event->id); ?>"
               class="button">
                <span class="dashicons dashicons-groups"></span>
                View Registrations
            </a>
            <a href="<?php echo admin_url('admin.php?page=spelling-bee-donations&event_id=' . $event->id); ?>"
               class="button">
                <span class="dashicons dashicons-heart"></span>
                View Donations
            </a>
            <a href="<?php echo admin_url('admin.php?page=spelling-bee-events'); ?>"
               class="button">
                <span class="dashicons dashicons-arrow-left-alt"></span>
                Back to Events
            </a>
        </div>
    </div>

    <!-- Event Status Banner -->
    <div class="osb-status-banner status-<?php echo esc_attr($event->status); ?>">
        <div class="status-info">
            <strong>Status:</strong> <?php echo esc_html(ucfirst($event->status)); ?>
            <?php if ($event->status === 'upcoming'): ?>
                <span class="status-details">Event is scheduled for <?php echo date('F j, Y', strtotime($event->start_date ?: $event->event_date)); ?></span>
            <?php elseif ($event->status === 'live'): ?>
                <span class="status-details">Event is currently in progress</span>
            <?php elseif ($event->status === 'completed'): ?>
                <span class="status-details">Event was completed on <?php echo date('F j, Y', strtotime($event->start_date ?: $event->event_date)); ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="osb-view-container">
        <!-- Main Event Information -->
        <div class="osb-view-main">
            <!-- Event Overview -->
            <div class="osb-view-section">
                <h2>Event Overview</h2>
                <div class="event-overview-grid">
                    <div class="overview-item">
                        <label>Event Name:</label>
                        <value><?php echo esc_html($event->name ?: $event->title); ?></value>
                    </div>
                    <div class="overview-item">
                        <label>Year:</label>
                        <value><?php echo esc_html($event->year); ?></value>
                    </div>
                    <div class="overview-item">
                        <label>Date:</label>
                        <value><?php echo $event->start_date ? date('F j, Y', strtotime($event->start_date)) : (date('F j, Y', strtotime($event->event_date))); ?></value>
                    </div>
                    <div class="overview-item">
                        <label>Time:</label>
                        <value><?php echo $event->start_time ? date('g:i A', strtotime($event->start_time)) : ($event->event_time ? date('g:i A', strtotime($event->event_time)) : 'Not specified'); ?></value>
                    </div>
                    <div class="overview-item">
                        <label>Venue:</label>
                        <value><?php echo esc_html($event->venue_name ?: $event->venue ?: 'Not specified'); ?></value>
                    </div>
                    <div class="overview-item">
                        <label>Max Students per School:</label>
                        <value><?php echo intval($event->max_students_per_school); ?></value>
                    </div>
                    <div class="overview-item">
                        <label>Registration Deadline:</label>
                        <value><?php echo $event->registration_deadline ? date('F j, Y', strtotime($event->registration_deadline)) : 'Not set'; ?></value>
                    </div>
                    <div class="overview-item">
                        <label>Prize Fund Goal:</label>
                        <value>$<?php echo number_format($event->prize_fund_goal ?: 0, 2); ?></value>
                    </div>
                </div>

                <?php if ($event->description): ?>
                    <div class="event-description">
                        <h3>Description</h3>
                        <p><?php echo nl2br(esc_html($event->description)); ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($event->venue_address): ?>
                    <div class="venue-address">
                        <h3>Venue Address</h3>
                        <p><?php echo nl2br(esc_html($event->venue_address)); ?></p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Statistics -->
            <div class="osb-view-section">
                <h2>Event Statistics</h2>
                <div class="osb-stats-grid">
                    <div class="osb-stat-card">
                        <div class="osb-stat-icon">
                            <span class="dashicons dashicons-groups"></span>
                        </div>
                        <div class="osb-stat-content">
                            <div class="osb-stat-number"><?php echo number_format($stats['total_registrations'] ?: 0); ?></div>
                            <div class="osb-stat-label">Total Registrations</div>
                        </div>
                    </div>

                    <div class="osb-stat-card pending">
                        <div class="osb-stat-icon">
                            <span class="dashicons dashicons-clock"></span>
                        </div>
                        <div class="osb-stat-content">
                            <div class="osb-stat-number"><?php echo number_format($stats['pending_registrations'] ?: 0); ?></div>
                            <div class="osb-stat-label">Pending</div>
                        </div>
                    </div>

                    <div class="osb-stat-card approved">
                        <div class="osb-stat-icon">
                            <span class="dashicons dashicons-yes-alt"></span>
                        </div>
                        <div class="osb-stat-content">
                            <div class="osb-stat-number"><?php echo number_format($stats['approved_registrations'] ?: 0); ?></div>
                            <div class="osb-stat-label">Approved</div>
                        </div>
                    </div>

                    <div class="osb-stat-card">
                        <div class="osb-stat-icon">
                            <span class="dashicons dashicons-building"></span>
                        </div>
                        <div class="osb-stat-content">
                            <div class="osb-stat-number"><?php echo number_format($stats['total_schools'] ?: 0); ?></div>
                            <div class="osb-stat-label">Participating Schools</div>
                        </div>
                    </div>

                    <div class="osb-stat-card donations">
                        <div class="osb-stat-icon">
                            <span class="dashicons dashicons-heart"></span>
                        </div>
                        <div class="osb-stat-content">
                            <div class="osb-stat-number">$<?php echo number_format($stats['total_donations'] ?: 0, 2); ?></div>
                            <div class="osb-stat-label">Total Donations</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Registrations -->
            <?php if (!empty($recent_registrations)): ?>
                <div class="osb-view-section">
                    <h2>Recent Registrations</h2>
                    <div class="osb-table-container">
                        <table class="wp-list-table widefat fixed striped">
                            <thead>
                                <tr>
                                    <th>School</th>
                                    <th>Contact Person</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_registrations as $registration): ?>
                                    <tr>
                                        <td><?php echo esc_html($registration->school_name); ?></td>
                                        <td><?php echo esc_html($registration->display_name); ?></td>
                                        <td>
                                            <span class="status-badge status-<?php echo esc_attr($registration->status); ?>">
                                                <?php echo esc_html(ucfirst($registration->status)); ?>
                                            </span>
                                        </td>
                                        <td><?php echo date('M j, Y', strtotime($registration->created_at)); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <p>
                        <a href="<?php echo admin_url('admin.php?page=spelling-bee-registrations&event_id=' . $event->id); ?>"
                           class="button">View All Registrations</a>
                    </p>
                </div>
            <?php endif; ?>

            <!-- Event Videos -->
            <?php if (!empty($videos)): ?>
                <div class="osb-view-section">
                    <h2>Event Videos</h2>
                    <div class="videos-grid">
                        <?php foreach ($videos as $video): ?>
                            <div class="video-item">
                                <!-- Video Thumbnail -->
                                <?php if (!empty($video->flyer_image)): ?>
                                    <div class="video-thumbnail">
                                        <img src="<?php echo esc_url($video->flyer_image); ?>" alt="<?php echo esc_attr($video->title); ?>" />
                                        <div class="video-overlay">
                                            <span class="dashicons dashicons-controls-play"></span>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <div class="video-placeholder">
                                        <span class="dashicons dashicons-video-alt3"></span>
                                        <span class="video-type-badge"><?php echo esc_html(ucfirst(str_replace('_', ' ', $video->video_type))); ?></span>
                                    </div>
                                <?php endif; ?>

                                <div class="video-info">
                                    <h4><?php echo esc_html($video->title); ?></h4>
                                    <p class="video-type"><?php echo esc_html(ucfirst(str_replace('_', ' ', $video->video_type))); ?></p>
                                    <?php if ($video->description): ?>
                                        <p class="video-description"><?php echo esc_html($video->description); ?></p>
                                    <?php endif; ?>
                                    <div class="video-status">
                                        <span class="status-badge status-<?php echo $video->is_active ? 'active' : 'inactive'; ?>">
                                            <?php echo $video->is_active ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </div>
                                    <p>
                                        <?php if (!empty($video->youtube_url)): ?>
                                            <a href="<?php echo esc_url($video->youtube_url); ?>" target="_blank" class="button button-small">
                                                <span class="dashicons dashicons-video-alt3"></span>
                                                Watch Video
                                            </a>
                                        <?php else: ?>
                                            <span class="button button-small button-disabled">
                                                <span class="dashicons dashicons-video-alt3"></span>
                                                No Video URL
                                            </span>
                                        <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Sidebar -->
        <div class="osb-view-sidebar">
            <!-- Event Flyer -->
            <?php if ($event->flyer_url): ?>
                <div class="osb-view-section">
                    <h3>Event Flyer</h3>
                    <div class="event-flyer">
                        <img src="<?php echo esc_url($event->flyer_url); ?>" alt="Event Flyer" style="max-width: 100%; height: auto;">
                    </div>
                </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="osb-view-section">
                <h3>Quick Actions</h3>
                <div class="quick-actions">
                    <a href="<?php echo admin_url('admin.php?page=spelling-bee-events&action=edit&event_id=' . $event->id); ?>"
                       class="button button-primary button-large">
                        <span class="dashicons dashicons-edit"></span>
                        Edit Event
                    </a>

                    <a href="<?php echo admin_url('admin.php?page=spelling-bee-registrations&event_id=' . $event->id); ?>"
                       class="button button-large">
                        <span class="dashicons dashicons-groups"></span>
                        Manage Registrations
                    </a>

                    <a href="<?php echo admin_url('admin.php?page=spelling-bee-students&event_id=' . $event->id); ?>"
                       class="button button-large">
                        <span class="dashicons dashicons-welcome-learn-more"></span>
                        View Students
                    </a>

                    <a href="<?php echo admin_url('admin.php?page=spelling-bee-donations&event_id=' . $event->id); ?>"
                       class="button button-large">
                        <span class="dashicons dashicons-heart"></span>
                        Manage Donations
                    </a>

                    <a href="<?php echo admin_url('admin.php?page=spelling-bee-documents&event_id=' . $event->id); ?>"
                       class="button button-large">
                        <span class="dashicons dashicons-media-document"></span>
                        Review Documents
                    </a>

                    <a href="<?php echo admin_url('admin.php?page=spelling-bee-sponsors&event_id=' . $event->id); ?>"
                       class="button button-large">
                        <span class="dashicons dashicons-star-filled"></span>
                        Manage Sponsors
                    </a>
                </div>
            </div>

            <!-- Event Sponsors -->
            <?php if (!empty($sponsors)): ?>
                <div class="osb-view-section">
                    <h3>Event Sponsors</h3>
                    <div class="sponsors-list">
                        <?php foreach ($sponsors as $sponsor): ?>
                            <div class="sponsor-item">
                                <?php if ($sponsor->logo_url): ?>
                                    <img src="<?php echo esc_url($sponsor->logo_url); ?>" alt="<?php echo esc_attr($sponsor->company_name); ?>" class="sponsor-logo">
                                <?php endif; ?>
                                <div class="sponsor-info">
                                    <strong><?php echo esc_html($sponsor->company_name); ?></strong>
                                    <span class="sponsorship-level level-<?php echo esc_attr($sponsor->sponsorship_level); ?>">
                                        <?php echo esc_html(ucfirst($sponsor->sponsorship_level)); ?>
                                    </span>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Event Details -->
            <div class="osb-view-section">
                <h3>Event Details</h3>
                <table class="event-details-table">
                    <tr>
                        <td><strong>Created:</strong></td>
                        <td><?php echo date('F j, Y g:i A', strtotime($event->created_at)); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Last Updated:</strong></td>
                        <td><?php echo date('F j, Y g:i A', strtotime($event->updated_at)); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Event ID:</strong></td>
                        <td><?php echo intval($event->id); ?></td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<style>
.osb-view-container {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin-top: 20px;
}

.osb-view-main,
.osb-view-sidebar {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.osb-view-section {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 20px;
}

.osb-view-section h2,
.osb-view-section h3 {
    margin-top: 0;
    margin-bottom: 15px;
    border-bottom: 1px solid #eee;
    padding-bottom: 10px;
}

.osb-status-banner {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 15px;
    margin-bottom: 20px;
}

.osb-status-banner.status-upcoming {
    border-left: 4px solid #0073aa;
}

.osb-status-banner.status-live {
    border-left: 4px solid #00a32a;
}

.osb-status-banner.status-completed {
    border-left: 4px solid #6c757d;
}

.osb-status-banner.status-cancelled {
    border-left: 4px solid #d63638;
}

.status-details {
    color: #666;
    font-weight: normal;
    margin-left: 10px;
}

.event-overview-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
    margin-bottom: 20px;
}

.overview-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.overview-item label {
    font-weight: 600;
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
}

.overview-item value {
    font-size: 14px;
    color: #333;
}

.event-description,
.venue-address {
    margin-top: 20px;
}

.osb-stats-grid.donations {
    border-left: 4px solid #e91e63;
}

.osb-stats-grid.approved {
    border-left: 4px solid #00a32a;
}

.videos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 15px;
}

.video-item {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 0;
    overflow: hidden;
    background: #fff;
}

.video-thumbnail {
    position: relative;
    width: 100%;
    height: 150px;
    overflow: hidden;
}

.video-thumbnail img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.video-overlay {
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

.video-item:hover .video-overlay {
    opacity: 1;
}

.video-overlay .dashicons {
    font-size: 40px;
    color: white;
}

.video-placeholder {
    width: 100%;
    height: 150px;
    background: linear-gradient(45deg, #f0f0f0, #e0e0e0);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    position: relative;
}

.video-placeholder .dashicons {
    font-size: 40px;
    color: #999;
    margin-bottom: 10px;
}

.video-type-badge {
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 11px;
    text-transform: uppercase;
    font-weight: 600;
}

.video-info {
    padding: 15px;
}

.video-info h4 {
    margin: 0 0 10px 0;
    font-size: 14px;
    line-height: 1.3;
}

.video-type {
    font-size: 12px;
    color: #666;
    text-transform: uppercase;
    margin: 5px 0;
    font-weight: 600;
}

.video-description {
    font-size: 13px;
    color: #666;
    line-height: 1.4;
    margin: 10px 0;
}

.video-status {
    margin: 10px 0;
}

.status-badge {
    font-size: 11px;
    padding: 3px 8px;
    border-radius: 3px;
    text-transform: uppercase;
    font-weight: 600;
}

.status-badge.status-active {
    background: #00a32a;
    color: white;
}

.status-badge.status-inactive {
    background: #dba617;
    color: white;
}

.button-disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

.quick-actions {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.quick-actions .button {
    justify-content: flex-start;
    text-align: left;
}

.sponsors-list {
    display: flex;
    flex-direction: column;
    gap: 10px;
}

.sponsor-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px;
    border: 1px solid #eee;
    border-radius: 4px;
}

.sponsor-logo {
    width: 40px;
    height: 40px;
    object-fit: contain;
}

.sponsor-info {
    display: flex;
    flex-direction: column;
    gap: 3px;
}

.sponsorship-level {
    font-size: 11px;
    padding: 2px 6px;
    border-radius: 3px;
    text-transform: uppercase;
    font-weight: 600;
}

.level-platinum {
    background: #e5e4e2;
    color: #333;
}

.level-gold {
    background: #ffd700;
    color: #333;
}

.level-silver {
    background: #c0c0c0;
    color: #333;
}

.level-bronze {
    background: #cd7f32;
    color: #fff;
}

.event-details-table {
    width: 100%;
    border-collapse: collapse;
}

.event-details-table td {
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.event-details-table td:first-child {
    width: 40%;
}

@media (max-width: 768px) {
    .osb-view-container {
        grid-template-columns: 1fr;
    }

    .osb-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 10px;
    }

    .event-overview-grid {
        grid-template-columns: 1fr;
    }
}
</style>