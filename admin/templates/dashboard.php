<?php
/**
 * Admin Dashboard Template
 *
 * @var array $stats
 * @var object $current_event
 * @var array $recent_registrations
 * @var array $pending_conflicts
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap">
    <h1><?php _e('Spelling Bee Dashboard', 'spelling-bee-pro'); ?></h1>

    <?php settings_errors('osb_dashboard'); ?>

    <!-- Current Event Section -->
    <div class="osb-dashboard-header">
        <?php if ($current_event): ?>
            <div class="osb-current-event">
                <h2><?php echo esc_html($current_event->title); ?></h2>
                <p><strong><?php _e('Date:', 'spelling-bee-pro'); ?></strong> <?php echo date('F j, Y', strtotime($current_event->event_date)); ?></p>
                <p><strong><?php _e('Status:', 'spelling-bee-pro'); ?></strong> <span class="osb-status osb-status-<?php echo esc_attr($current_event->status); ?>"><?php echo esc_html(ucfirst($current_event->status)); ?></span></p>
                <p><strong><?php _e('Venue:', 'spelling-bee-pro'); ?></strong> <?php echo esc_html($current_event->venue_name); ?></p>
            </div>
        <?php else: ?>
            <div class="osb-no-event">
                <h2><?php _e('No Active Event', 'spelling-bee-pro'); ?></h2>
                <p><?php _e('Create a new event to get started.', 'spelling-bee-pro'); ?></p>
                <a href="<?php echo admin_url('admin.php?page=spelling-bee-events&action=new'); ?>" class="button button-primary">
                    <?php _e('Create New Event', 'spelling-bee-pro'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Statistics Cards -->
    <div class="osb-stats-grid">
        <div class="osb-stat-card">
            <div class="osb-stat-number"><?php echo intval($stats['total_events']); ?></div>
            <div class="osb-stat-label"><?php _e('Total Events', 'spelling-bee-pro'); ?></div>
            <div class="osb-stat-icon">📅</div>
        </div>

        <div class="osb-stat-card">
            <div class="osb-stat-number"><?php echo intval($stats['total_schools']); ?></div>
            <div class="osb-stat-label"><?php _e('Registered Schools', 'spelling-bee-pro'); ?></div>
            <div class="osb-stat-icon">🏫</div>
        </div>

        <div class="osb-stat-card">
            <div class="osb-stat-number"><?php echo intval($stats['total_students']); ?></div>
            <div class="osb-stat-label"><?php _e('Students', 'spelling-bee-pro'); ?></div>
            <div class="osb-stat-icon">👨‍🎓</div>
        </div>

        <div class="osb-stat-card">
            <div class="osb-stat-number">$<?php echo number_format(floatval($stats['total_donations']), 2); ?></div>
            <div class="osb-stat-label"><?php _e('Total Donations', 'spelling-bee-pro'); ?></div>
            <div class="osb-stat-icon">💰</div>
        </div>
    </div>

    <!-- Registration Status Breakdown -->
    <?php if (!empty($stats['registrations'])): ?>
    <div class="osb-dashboard-section">
        <h3><?php _e('Registration Status', 'spelling-bee-pro'); ?></h3>
        <div class="osb-registration-stats">
            <?php foreach ($stats['registrations'] as $status => $data): ?>
                <div class="osb-reg-stat">
                    <span class="osb-reg-status osb-status-<?php echo esc_attr($status); ?>">
                        <?php echo esc_html(ucfirst($status)); ?>
                    </span>
                    <span class="osb-reg-count"><?php echo intval($data->count); ?></span>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>

    <div class="osb-dashboard-content">
        <div class="osb-dashboard-left">
            <!-- Recent Registrations -->
            <div class="osb-dashboard-section">
                <h3><?php _e('Recent Registrations', 'spelling-bee-pro'); ?></h3>
                <?php if (!empty($recent_registrations)): ?>
                    <div class="osb-recent-list">
                        <?php foreach ($recent_registrations as $registration): ?>
                            <div class="osb-recent-item">
                                <div class="osb-recent-school">
                                    <strong><?php echo esc_html($registration->school_name); ?></strong>
                                </div>
                                <div class="osb-recent-contact">
                                    <?php echo esc_html($registration->contact_person); ?><br>
                                    <small><?php echo esc_html($registration->contact_email); ?></small>
                                </div>
                                <div class="osb-recent-status">
                                    <span class="osb-status osb-status-<?php echo esc_attr($registration->status); ?>">
                                        <?php echo esc_html(ucfirst($registration->status)); ?>
                                    </span>
                                </div>
                                <div class="osb-recent-date">
                                    <small><?php echo date('M j, Y', strtotime($registration->created_at)); ?></small>
                                </div>
                                <div class="osb-recent-actions">
                                    <a href="<?php echo admin_url('admin.php?page=spelling-bee-registrations&action=view&registration_id=' . $registration->id); ?>"
                                       class="button button-small">
                                        <?php _e('View', 'spelling-bee-pro'); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="osb-section-footer">
                        <a href="<?php echo admin_url('admin.php?page=spelling-bee-registrations'); ?>" class="button">
                            <?php _e('View All Registrations', 'spelling-bee-pro'); ?>
                        </a>
                    </div>
                <?php else: ?>
                    <p><?php _e('No registrations yet.', 'spelling-bee-pro'); ?></p>
                <?php endif; ?>
            </div>
        </div>

        <div class="osb-dashboard-right">
            <!-- Pending User Conflicts -->
            <?php if (!empty($pending_conflicts)): ?>
            <div class="osb-dashboard-section osb-conflicts-section">
                <h3><?php _e('Pending User Conflicts', 'spelling-bee-pro'); ?>
                    <span class="osb-conflict-count"><?php echo count($pending_conflicts); ?></span>
                </h3>
                <div class="osb-conflict-list">
                    <?php foreach (array_slice($pending_conflicts, 0, 5) as $conflict): ?>
                        <div class="osb-conflict-item">
                            <div class="osb-conflict-type">
                                <strong><?php echo esc_html(ucfirst(str_replace('_', ' ', $conflict->conflict_type))); ?></strong>
                            </div>
                            <div class="osb-conflict-details">
                                <div class="osb-conflict-existing">
                                    <?php echo esc_html($conflict->existing_user_name); ?><br>
                                    <small><?php echo esc_html($conflict->existing_user_email); ?></small>
                                </div>
                                <div class="osb-conflict-confidence">
                                    <?php _e('Confidence:', 'spelling-bee-pro'); ?>
                                    <span class="osb-confidence-score"><?php echo intval($conflict->confidence_score); ?>%</span>
                                </div>
                            </div>
                            <div class="osb-conflict-actions">
                                <a href="<?php echo admin_url('admin.php?page=spelling-bee-conflicts'); ?>"
                                   class="button button-small button-primary">
                                    <?php _e('Resolve', 'spelling-bee-pro'); ?>
                                </a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="osb-section-footer">
                    <a href="<?php echo admin_url('admin.php?page=spelling-bee-conflicts'); ?>" class="button">
                        <?php _e('View All Conflicts', 'spelling-bee-pro'); ?>
                    </a>
                </div>
            </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div class="osb-dashboard-section">
                <h3><?php _e('Quick Actions', 'spelling-bee-pro'); ?></h3>
                <div class="osb-quick-actions">
                    <a href="<?php echo admin_url('admin.php?page=spelling-bee-events&action=new'); ?>"
                       class="osb-quick-action">
                        <span class="osb-action-icon">📅</span>
                        <span class="osb-action-text"><?php _e('Create Event', 'spelling-bee-pro'); ?></span>
                    </a>

                    <a href="<?php echo admin_url('admin.php?page=spelling-bee-schools&action=new'); ?>"
                       class="osb-quick-action">
                        <span class="osb-action-icon">🏫</span>
                        <span class="osb-action-text"><?php _e('Add School', 'spelling-bee-pro'); ?></span>
                    </a>

                    <a href="<?php echo admin_url('admin.php?page=spelling-bee-communications'); ?>"
                       class="osb-quick-action">
                        <span class="osb-action-icon">📧</span>
                        <span class="osb-action-text"><?php _e('Send Email', 'spelling-bee-pro'); ?></span>
                    </a>

                    <a href="<?php echo admin_url('admin.php?page=spelling-bee-reports'); ?>"
                       class="osb-quick-action">
                        <span class="osb-action-icon">📊</span>
                        <span class="osb-action-text"><?php _e('Generate Report', 'spelling-bee-pro'); ?></span>
                    </a>
                </div>
            </div>

            <!-- System Status -->
            <div class="osb-dashboard-section">
                <h3><?php _e('System Status', 'spelling-bee-pro'); ?></h3>
                <div class="osb-system-status">
                    <div class="osb-status-item">
                        <span class="osb-status-label"><?php _e('Plugin Version:', 'spelling-bee-pro'); ?></span>
                        <span class="osb-status-value"><?php echo OSB_PLUGIN_VERSION; ?></span>
                    </div>

                    <div class="osb-status-item">
                        <span class="osb-status-label"><?php _e('Database Status:', 'spelling-bee-pro'); ?></span>
                        <span class="osb-status-value osb-status-good"><?php _e('Connected', 'spelling-bee-pro'); ?></span>
                    </div>

                    <div class="osb-status-item">
                        <span class="osb-status-label"><?php _e('Email System:', 'spelling-bee-pro'); ?></span>
                        <span class="osb-status-value osb-status-good"><?php _e('Active', 'spelling-bee-pro'); ?></span>
                    </div>

                    <?php if (!empty($pending_conflicts)): ?>
                    <div class="osb-status-item osb-status-warning">
                        <span class="osb-status-label"><?php _e('User Conflicts:', 'spelling-bee-pro'); ?></span>
                        <span class="osb-status-value"><?php echo count($pending_conflicts); ?> <?php _e('pending', 'spelling-bee-pro'); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.osb-dashboard-header {
    background: #fff;
    padding: 20px;
    margin: 20px 0;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.osb-current-event h2 {
    margin-top: 0;
    color: #0073aa;
}

.osb-no-event {
    text-align: center;
    padding: 40px 20px;
}

.osb-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.osb-stat-card {
    background: #fff;
    padding: 20px;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-align: center;
    position: relative;
}

.osb-stat-number {
    font-size: 2.5em;
    font-weight: bold;
    color: #0073aa;
    line-height: 1;
}

.osb-stat-label {
    font-size: 14px;
    color: #666;
    margin-top: 8px;
}

.osb-stat-icon {
    position: absolute;
    top: 15px;
    right: 15px;
    font-size: 24px;
    opacity: 0.3;
}

.osb-dashboard-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
    margin: 20px 0;
}

.osb-dashboard-section {
    background: #fff;
    padding: 20px;
    margin: 0 0 20px 0;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.osb-dashboard-section h3 {
    margin-top: 0;
    padding-bottom: 10px;
    border-bottom: 1px solid #eee;
}

.osb-registration-stats {
    display: flex;
    gap: 20px;
    flex-wrap: wrap;
}

.osb-reg-stat {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 80px;
}

.osb-reg-status {
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    text-transform: uppercase;
    font-weight: bold;
}

.osb-reg-count {
    font-size: 24px;
    font-weight: bold;
    margin-top: 8px;
}

.osb-recent-list {
    max-height: 400px;
    overflow-y: auto;
}

.osb-recent-item {
    display: grid;
    grid-template-columns: 2fr 2fr 1fr 1fr 1fr;
    gap: 10px;
    padding: 15px 0;
    border-bottom: 1px solid #eee;
    align-items: center;
}

.osb-recent-item:last-child {
    border-bottom: none;
}

.osb-conflict-list {
    max-height: 300px;
    overflow-y: auto;
}

.osb-conflict-item {
    padding: 15px 0;
    border-bottom: 1px solid #eee;
}

.osb-conflict-item:last-child {
    border-bottom: none;
}

.osb-conflict-count {
    background: #dc3232;
    color: white;
    border-radius: 50%;
    padding: 2px 6px;
    font-size: 12px;
    margin-left: 8px;
}

.osb-confidence-score {
    font-weight: bold;
    color: #0073aa;
}

.osb-quick-actions {
    display: grid;
    gap: 10px;
}

.osb-quick-action {
    display: flex;
    align-items: center;
    padding: 12px;
    background: #f9f9f9;
    border: 1px solid #ddd;
    border-radius: 4px;
    text-decoration: none;
    color: #333;
    transition: all 0.2s;
}

.osb-quick-action:hover {
    background: #0073aa;
    color: white;
    text-decoration: none;
}

.osb-action-icon {
    margin-right: 10px;
    font-size: 18px;
}

.osb-system-status {
    font-size: 14px;
}

.osb-status-item {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.osb-status-item:last-child {
    border-bottom: none;
}

.osb-status-good {
    color: #46b450;
    font-weight: bold;
}

.osb-status-warning {
    color: #ffb900;
}

.osb-section-footer {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #eee;
}

/* Status Classes */
.osb-status {
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    text-transform: uppercase;
    font-weight: bold;
}

.osb-status-pending { background: #ffb900; color: white; }
.osb-status-approved { background: #46b450; color: white; }
.osb-status-rejected { background: #dc3232; color: white; }
.osb-status-upcoming { background: #0073aa; color: white; }
.osb-status-live { background: #00a32a; color: white; }
.osb-status-completed { background: #666; color: white; }
.osb-status-active { background: #46b450; color: white; }

@media (max-width: 768px) {
    .osb-dashboard-content {
        grid-template-columns: 1fr;
    }

    .osb-stats-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .osb-recent-item {
        grid-template-columns: 1fr;
        gap: 5px;
    }
}
</style>