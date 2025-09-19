<?php
/**
 * Event Info Shortcode Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Variables passed from shortcode
$event = isset($event) ? $event : null;
$atts = isset($atts) ? $atts : array();
?>

<div class="osb-event-info">
    <?php if ($event): ?>
        <div class="osb-event-header">
            <h2 class="osb-event-title"><?php echo esc_html($event->title); ?></h2>
            <div class="osb-event-status">
                <span class="osb-status osb-status-<?php echo esc_attr($event->status); ?>">
                    <?php echo esc_html(ucfirst($event->status)); ?>
                </span>
            </div>
        </div>

        <div class="osb-event-details">
            <?php if ($atts['show_date'] === 'true'): ?>
                <div class="osb-detail-item osb-date">
                    <div class="osb-detail-icon">📅</div>
                    <div class="osb-detail-content">
                        <h3>Event Date</h3>
                        <p><?php echo date('F j, Y', strtotime($event->event_date)); ?></p>
                        <?php if (!empty($event->event_time)): ?>
                            <span class="osb-time">
                                <?php echo date('g:i A', strtotime($event->event_time)); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($atts['show_venue'] === 'true' && !empty($event->venue_name)): ?>
                <div class="osb-detail-item osb-venue">
                    <div class="osb-detail-icon">📍</div>
                    <div class="osb-detail-content">
                        <h3>Venue</h3>
                        <p><?php echo esc_html($event->venue_name); ?></p>
                        <?php if (!empty($event->venue_address)): ?>
                            <span class="osb-address">
                                <?php echo esc_html($event->venue_address); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($event->registration_deadline)): ?>
                <div class="osb-detail-item osb-deadline">
                    <div class="osb-detail-icon">⏰</div>
                    <div class="osb-detail-content">
                        <h3>Registration Deadline</h3>
                        <p><?php echo date('F j, Y', strtotime($event->registration_deadline)); ?></p>
                        <?php
                        $days_left = ceil((strtotime($event->registration_deadline) - time()) / (60 * 60 * 24));
                        if ($days_left > 0 && $event->status === 'upcoming'):
                        ?>
                            <span class="osb-countdown">
                                <?php printf(_n('%d day left', '%d days left', $days_left, 'spelling-bee-pro'), $days_left); ?>
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($event->age_range_min) || !empty($event->age_range_max)): ?>
                <div class="osb-detail-item osb-age-range">
                    <div class="osb-detail-icon">👥</div>
                    <div class="osb-detail-content">
                        <h3>Age Range</h3>
                        <p>
                            <?php
                            if (!empty($event->age_range_min) && !empty($event->age_range_max)) {
                                printf('%d - %d years old', $event->age_range_min, $event->age_range_max);
                            } elseif (!empty($event->age_range_min)) {
                                printf('%d+ years old', $event->age_range_min);
                            } else {
                                printf('Up to %d years old', $event->age_range_max);
                            }
                            ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($event->max_participants)): ?>
                <div class="osb-detail-item osb-capacity">
                    <div class="osb-detail-icon">🎯</div>
                    <div class="osb-detail-content">
                        <h3>Capacity</h3>
                        <p><?php echo number_format($event->max_participants); ?> participants</p>
                        <?php
                        // Get current registration count
                        global $wpdb;
                        $registered = $wpdb->get_var($wpdb->prepare(
                            "SELECT COUNT(*) FROM {$wpdb->prefix}osb_registrations WHERE event_id = %d AND status = 'approved'",
                            $event->id
                        ));
                        if ($registered > 0):
                        ?>
                            <span class="osb-progress-text">
                                <?php echo number_format($registered); ?> registered
                            </span>
                            <div class="osb-capacity-bar">
                                <div class="osb-capacity-fill"
                                     style="width: <?php echo min(100, ($registered / $event->max_participants) * 100); ?>%"></div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (!empty($event->entry_fee)): ?>
                <div class="osb-detail-item osb-fee">
                    <div class="osb-detail-icon">💰</div>
                    <div class="osb-detail-content">
                        <h3>Entry Fee</h3>
                        <p>$<?php echo number_format($event->entry_fee, 2); ?></p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($atts['show_description'] === 'true' && !empty($event->description)): ?>
            <div class="osb-event-description">
                <h3>About This Event</h3>
                <div class="osb-description-content">
                    <?php echo wp_kses_post(wpautop($event->description)); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (!empty($event->rules_url) || !empty($event->study_materials_url)): ?>
            <div class="osb-event-resources">
                <h3>Resources</h3>
                <div class="osb-resources-grid">
                    <?php if (!empty($event->rules_url)): ?>
                        <a href="<?php echo esc_url($event->rules_url); ?>" target="_blank" class="osb-resource-link">
                            <div class="osb-resource-icon">📋</div>
                            <div class="osb-resource-info">
                                <span class="osb-resource-title">Competition Rules</span>
                                <span class="osb-resource-desc">Download official rules and guidelines</span>
                            </div>
                        </a>
                    <?php endif; ?>

                    <?php if (!empty($event->study_materials_url)): ?>
                        <a href="<?php echo esc_url($event->study_materials_url); ?>" target="_blank" class="osb-resource-link">
                            <div class="osb-resource-icon">📚</div>
                            <div class="osb-resource-info">
                                <span class="osb-resource-title">Study Materials</span>
                                <span class="osb-resource-desc">Access practice words and study guides</span>
                            </div>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($event->status === 'upcoming'): ?>
            <div class="osb-event-actions">
                <a href="#registration-form" class="osb-register-btn">
                    Register Now
                </a>
                <a href="#donation-form" class="osb-support-btn">
                    Support This Event
                </a>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="osb-no-event">
            <div class="osb-empty-state">
                <div class="osb-empty-icon">📅</div>
                <h3>No Event Information</h3>
                <p>Event information is not available at this time.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
/* Event Info Styles */
.osb-event-info {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

.osb-event-header {
    text-align: center;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e9ecef;
}

.osb-event-title {
    font-size: 2.5rem;
    font-weight: bold;
    color: #0073aa;
    margin: 0 0 15px 0;
}

.osb-event-status {
    margin-top: 10px;
}

.osb-status {
    display: inline-block;
    padding: 8px 20px;
    border-radius: 20px;
    font-size: 0.9rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.osb-status-upcoming { background: #fff3cd; color: #856404; border: 2px solid #ffeaa7; }
.osb-status-live { background: #f8d7da; color: #721c24; border: 2px solid #f5c6cb; }
.osb-status-completed { background: #d4edda; color: #155724; border: 2px solid #c3e6cb; }
.osb-status-cancelled { background: #f8d7da; color: #721c24; border: 2px solid #f5c6cb; }

.osb-event-details {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 25px;
    margin-bottom: 40px;
}

.osb-detail-item {
    background: white;
    border-radius: 15px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    display: flex;
    align-items: flex-start;
    gap: 20px;
    transition: all 0.3s ease;
    border-left: 4px solid #0073aa;
}

.osb-detail-item:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.12);
}

.osb-detail-icon {
    font-size: 2rem;
    flex-shrink: 0;
    opacity: 0.8;
}

.osb-detail-content {
    flex: 1;
}

.osb-detail-content h3 {
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
    margin: 0 0 8px 0;
}

.osb-detail-content p {
    font-size: 1rem;
    color: #666;
    margin: 0 0 5px 0;
    font-weight: 500;
}

.osb-time,
.osb-address,
.osb-countdown,
.osb-progress-text {
    font-size: 0.9rem;
    color: #999;
    display: block;
    margin-top: 5px;
}

.osb-countdown {
    color: #dc3545;
    font-weight: 600;
}

.osb-capacity-bar {
    width: 100%;
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    overflow: hidden;
    margin-top: 8px;
}

.osb-capacity-fill {
    height: 100%;
    background: linear-gradient(90deg, #28a745, #20c997);
    border-radius: 4px;
    transition: width 0.3s ease;
}

.osb-event-description {
    background: #f8f9fa;
    border-radius: 15px;
    padding: 30px;
    margin-bottom: 30px;
}

.osb-event-description h3 {
    font-size: 1.5rem;
    color: #333;
    margin: 0 0 20px 0;
    font-weight: 600;
}

.osb-description-content {
    color: #666;
    line-height: 1.7;
    font-size: 1rem;
}

.osb-description-content p {
    margin-bottom: 15px;
}

.osb-event-resources {
    margin-bottom: 40px;
}

.osb-event-resources h3 {
    font-size: 1.5rem;
    color: #333;
    margin: 0 0 20px 0;
    font-weight: 600;
    text-align: center;
}

.osb-resources-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.osb-resource-link {
    display: flex;
    align-items: center;
    gap: 15px;
    background: white;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    padding: 20px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.osb-resource-link:hover {
    border-color: #0073aa;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,115,170,0.2);
    text-decoration: none;
}

.osb-resource-icon {
    font-size: 2rem;
    opacity: 0.8;
}

.osb-resource-info {
    flex: 1;
}

.osb-resource-title {
    display: block;
    font-weight: 600;
    color: #333;
    margin-bottom: 3px;
}

.osb-resource-desc {
    font-size: 0.9rem;
    color: #666;
}

.osb-event-actions {
    display: flex;
    justify-content: center;
    gap: 20px;
    margin-top: 40px;
    flex-wrap: wrap;
}

.osb-register-btn,
.osb-support-btn {
    padding: 15px 40px;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 25px;
    text-decoration: none;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    display: inline-block;
}

.osb-register-btn {
    background: linear-gradient(135deg, #0073aa, #005177);
    color: white;
}

.osb-register-btn:hover {
    background: linear-gradient(135deg, #005177, #003d5c);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,115,170,0.4);
    text-decoration: none;
    color: white;
}

.osb-support-btn {
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
}

.osb-support-btn:hover {
    background: linear-gradient(135deg, #218838, #1ea187);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(40,167,69,0.4);
    text-decoration: none;
    color: white;
}

.osb-no-event {
    margin: 40px 0;
}

.osb-empty-state {
    text-align: center;
    padding: 60px 20px;
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
    margin-bottom: 10px;
    font-size: 1.5rem;
}

.osb-empty-state p {
    color: #666;
    font-size: 1.1rem;
    line-height: 1.6;
}

/* Responsive Design */
@media (max-width: 768px) {
    .osb-event-info {
        padding: 15px;
    }

    .osb-event-title {
        font-size: 2rem;
    }

    .osb-event-details {
        grid-template-columns: 1fr;
        gap: 20px;
    }

    .osb-detail-item {
        padding: 20px;
    }

    .osb-event-description {
        padding: 25px;
    }

    .osb-resources-grid {
        grid-template-columns: 1fr;
    }

    .osb-event-actions {
        flex-direction: column;
        align-items: stretch;
    }

    .osb-register-btn,
    .osb-support-btn {
        text-align: center;
    }
}

@media (max-width: 600px) {
    .osb-event-title {
        font-size: 1.8rem;
    }

    .osb-detail-item {
        flex-direction: column;
        text-align: center;
        gap: 15px;
        padding: 18px;
    }

    .osb-detail-icon {
        font-size: 2.5rem;
    }

    .osb-resource-link {
        flex-direction: column;
        text-align: center;
        gap: 10px;
        padding: 18px;
    }

    .osb-resource-icon {
        font-size: 2.5rem;
    }
}
</style>