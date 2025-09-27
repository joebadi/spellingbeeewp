<?php
/**
 * Events List Template
 *
 * @var array $events
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$admin_menu = OSB_Admin_Menu::getInstance();
?>

<div class="wrap">
    <h1 class="wp-heading-inline"><?php _e('Events', 'spelling-bee-pro'); ?></h1>
    <a href="<?php echo $admin_menu->getAdminUrl('events', array('action' => 'new')); ?>" class="page-title-action">
        <?php _e('Add New Event', 'spelling-bee-pro'); ?>
    </a>

    <?php if (isset($_GET['deleted'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Event deleted successfully.', 'spelling-bee-pro'); ?></p>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['saved'])): ?>
        <div class="notice notice-success is-dismissible">
            <p><?php _e('Event saved successfully.', 'spelling-bee-pro'); ?></p>
        </div>
    <?php endif; ?>

    <hr class="wp-header-end">

    <!-- Filters -->
    <div class="osb-filters">
        <div class="osb-filter-bar">
            <select id="osb-status-filter" class="osb-filter-select">
                <option value=""><?php _e('All Statuses', 'spelling-bee-pro'); ?></option>
                <option value="upcoming"><?php _e('Upcoming', 'spelling-bee-pro'); ?></option>
                <option value="live"><?php _e('Live', 'spelling-bee-pro'); ?></option>
                <option value="completed"><?php _e('Completed', 'spelling-bee-pro'); ?></option>
                <option value="cancelled"><?php _e('Cancelled', 'spelling-bee-pro'); ?></option>
            </select>

            <select id="osb-year-filter" class="osb-filter-select">
                <option value=""><?php _e('All Years', 'spelling-bee-pro'); ?></option>
                <?php
                $years = array_unique(array_map(function($event) {
                    return $event->year;
                }, $events));
                rsort($years);
                foreach ($years as $year):
                ?>
                    <option value="<?php echo $year; ?>"><?php echo $year; ?></option>
                <?php endforeach; ?>
            </select>

            <input type="search" id="osb-search-events" class="osb-search-input"
                   placeholder="<?php _e('Search events...', 'spelling-bee-pro'); ?>">

            <button type="button" id="osb-clear-filters" class="button">
                <?php _e('Clear Filters', 'spelling-bee-pro'); ?>
            </button>
        </div>
    </div>

    <?php if (!empty($events)): ?>
        <div class="osb-events-grid">
            <?php foreach ($events as $event): ?>
                <div class="osb-event-card" data-status="<?php echo esc_attr($event->status); ?>" data-year="<?php echo esc_attr($event->year); ?>">
                    <div class="osb-event-header">
                        <h3 class="osb-event-title">
                            <a href="<?php echo $admin_menu->getAdminUrl('events', array('action' => 'view', 'event_id' => $event->id)); ?>">
                                <?php echo esc_html($event->title); ?>
                            </a>
                        </h3>
                        <span class="osb-event-status osb-status-<?php echo esc_attr($event->status); ?>">
                            <?php echo esc_html(ucfirst($event->status)); ?>
                        </span>
                    </div>

                    <div class="osb-event-meta">
                        <div class="osb-event-date">
                            <strong><?php _e('Date:', 'spelling-bee-pro'); ?></strong>
                            <?php echo date('F j, Y', strtotime($event->event_date)); ?>
                            <?php if (!empty($event->event_time)): ?>
                                at <?php echo date('g:i A', strtotime($event->event_time)); ?>
                            <?php endif; ?>
                        </div>

                        <?php if (!empty($event->venue_name)): ?>
                        <div class="osb-event-venue">
                            <strong><?php _e('Venue:', 'spelling-bee-pro'); ?></strong>
                            <?php echo esc_html($event->venue_name); ?>
                        </div>
                        <?php endif; ?>

                        <div class="osb-event-year">
                            <strong><?php _e('Year:', 'spelling-bee-pro'); ?></strong>
                            <?php echo esc_html($event->year); ?>
                        </div>

                        <?php if (!empty($event->prize_fund_goal) && $event->prize_fund_goal > 0): ?>
                        <div class="osb-event-prize">
                            <strong><?php _e('Prize Fund Goal:', 'spelling-bee-pro'); ?></strong>
                            $<?php echo number_format($event->prize_fund_goal, 2); ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <?php if (!empty($event->description)): ?>
                    <div class="osb-event-description">
                        <?php echo wp_trim_words(esc_html($event->description), 20); ?>
                    </div>
                    <?php endif; ?>

                    <!-- Event Statistics -->
                    <div class="osb-event-stats">
                        <?php
                        global $wpdb;
                        $registrations_count = $wpdb->get_var(
                            $wpdb->prepare(
                                "SELECT COUNT(*) FROM {$wpdb->prefix}osb_registrations WHERE event_id = %d",
                                $event->id
                            )
                        );

                        $donations_total = $wpdb->get_var(
                            $wpdb->prepare(
                                "SELECT SUM(amount) FROM {$wpdb->prefix}osb_donations WHERE event_id = %d AND status = 'completed'",
                                $event->id
                            )
                        );
                        ?>
                        <div class="osb-stat-item">
                            <span class="osb-stat-label"><?php _e('Registrations:', 'spelling-bee-pro'); ?></span>
                            <span class="osb-stat-value"><?php echo intval($registrations_count); ?></span>
                        </div>

                        <div class="osb-stat-item">
                            <span class="osb-stat-label"><?php _e('Donations:', 'spelling-bee-pro'); ?></span>
                            <span class="osb-stat-value">$<?php echo number_format(floatval($donations_total), 2); ?></span>
                        </div>
                    </div>

                    <div class="osb-event-actions">
                        <a href="<?php echo $admin_menu->getAdminUrl('events', array('action' => 'view', 'event_id' => $event->id)); ?>"
                           class="button">
                            <?php _e('View', 'spelling-bee-pro'); ?>
                        </a>

                        <a href="<?php echo $admin_menu->getAdminUrl('events', array('action' => 'edit', 'event_id' => $event->id)); ?>"
                           class="button button-primary">
                            <?php _e('Edit', 'spelling-bee-pro'); ?>
                        </a>

                        <a href="<?php echo $admin_menu->getAdminUrl('registrations', array('event_id' => $event->id)); ?>"
                           class="button">
                            <?php _e('Registrations', 'spelling-bee-pro'); ?>
                        </a>

                        <div class="osb-event-actions-dropdown">
                            <button type="button" class="button osb-dropdown-toggle">
                                <?php _e('More', 'spelling-bee-pro'); ?> ▼
                            </button>
                            <div class="osb-dropdown-menu">
                                <a href="<?php echo $admin_menu->getAdminUrl('donations', array('event_id' => $event->id)); ?>">
                                    <?php _e('View Donations', 'spelling-bee-pro'); ?>
                                </a>
                                <a href="<?php echo $admin_menu->getAdminUrl('reports', array('event_id' => $event->id)); ?>">
                                    <?php _e('Generate Report', 'spelling-bee-pro'); ?>
                                </a>
                                <hr>
                                <a href="<?php echo $admin_menu->getActionUrl('delete_event', array('event_id' => $event->id)); ?>"
                                   class="osb-delete-link"
                                   onclick="return confirm('<?php _e('Are you sure you want to delete this event? This action cannot be undone.', 'spelling-bee-pro'); ?>')">
                                    <?php _e('Delete Event', 'spelling-bee-pro'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Pagination would go here if needed -->
        <div class="osb-pagination">
            <!-- Pagination links -->
        </div>

    <?php else: ?>
        <div class="osb-no-events">
            <div class="osb-empty-state">
                <div class="osb-empty-icon">📅</div>
                <h3><?php _e('No Events Found', 'spelling-bee-pro'); ?></h3>
                <p><?php _e('Create your first spelling bee event to get started.', 'spelling-bee-pro'); ?></p>
                <a href="<?php echo $admin_menu->getAdminUrl('events', array('action' => 'new')); ?>"
                   class="button button-primary button-large">
                    <?php _e('Create New Event', 'spelling-bee-pro'); ?>
                </a>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
.osb-filters {
    margin: 20px 0;
    padding: 15px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.osb-filter-bar {
    display: flex;
    gap: 10px;
    align-items: center;
    flex-wrap: wrap;
}

.osb-filter-select,
.osb-search-input {
    padding: 8px 12px;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 14px;
}

.osb-search-input {
    min-width: 200px;
    flex: 1;
}

.osb-events-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
    gap: 20px;
    margin: 20px 0;
}

.osb-event-card {
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
    padding: 20px;
    transition: all 0.2s ease;
    position: relative;
}

.osb-event-card:hover {
    border-color: #0073aa;
    box-shadow: 0 2px 8px rgba(0,115,170,0.1);
}

.osb-event-header {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 15px;
}

.osb-event-title {
    margin: 0;
    flex: 1;
}

.osb-event-title a {
    text-decoration: none;
    color: #0073aa;
}

.osb-event-title a:hover {
    color: #005a87;
}

.osb-event-status {
    margin-left: 10px;
    padding: 4px 8px;
    border-radius: 3px;
    font-size: 12px;
    font-weight: bold;
    text-transform: uppercase;
    white-space: nowrap;
}

.osb-event-meta {
    margin-bottom: 15px;
    font-size: 14px;
    line-height: 1.6;
}

.osb-event-meta > div {
    margin-bottom: 5px;
}

.osb-event-description {
    margin-bottom: 15px;
    color: #666;
    font-size: 14px;
    line-height: 1.5;
}

.osb-event-stats {
    display: flex;
    justify-content: space-between;
    margin-bottom: 15px;
    padding: 10px;
    background: #f9f9f9;
    border-radius: 4px;
}

.osb-stat-item {
    text-align: center;
}

.osb-stat-label {
    display: block;
    font-size: 12px;
    color: #666;
    margin-bottom: 2px;
}

.osb-stat-value {
    display: block;
    font-size: 16px;
    font-weight: bold;
    color: #0073aa;
}

.osb-event-actions {
    display: flex;
    gap: 8px;
    align-items: center;
    flex-wrap: wrap;
}

.osb-event-actions-dropdown {
    position: relative;
    margin-left: auto;
}

.osb-dropdown-menu {
    display: none;
    position: absolute;
    right: 0;
    top: 100%;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 4px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    z-index: 1000;
    min-width: 150px;
}

.osb-dropdown-menu.show {
    display: block;
}

.osb-dropdown-menu a {
    display: block;
    padding: 8px 12px;
    text-decoration: none;
    color: #333;
    font-size: 13px;
}

.osb-dropdown-menu a:hover {
    background: #f5f5f5;
}

.osb-dropdown-menu hr {
    margin: 5px 0;
    border: 0;
    border-top: 1px solid #eee;
}

.osb-delete-link {
    color: #dc3232 !important;
}

.osb-delete-link:hover {
    background: #dc3232 !important;
    color: white !important;
}

.osb-no-events {
    margin: 40px 0;
}

.osb-empty-state {
    text-align: center;
    padding: 60px 20px;
    background: #fff;
    border: 1px solid #ddd;
    border-radius: 8px;
}

.osb-empty-icon {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.3;
}

.osb-empty-state h3 {
    color: #666;
    margin-bottom: 10px;
}

.osb-empty-state p {
    color: #999;
    margin-bottom: 20px;
}

/* Status Colors */
.osb-status-upcoming { background: #0073aa; color: white; }
.osb-status-live { background: #00a32a; color: white; }
.osb-status-completed { background: #666; color: white; }
.osb-status-cancelled { background: #dc3232; color: white; }

/* Responsive Design */
@media (max-width: 768px) {
    .osb-events-grid {
        grid-template-columns: 1fr;
    }

    .osb-filter-bar {
        flex-direction: column;
        align-items: stretch;
    }

    .osb-filter-select,
    .osb-search-input {
        width: 100%;
    }

    .osb-event-header {
        flex-direction: column;
        gap: 10px;
    }

    .osb-event-actions {
        justify-content: space-between;
    }

    .osb-event-stats {
        flex-direction: column;
        gap: 10px;
    }
}

/* Hidden class for filtering */
.osb-event-card.hidden {
    display: none;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Dropdown toggle
    $('.osb-dropdown-toggle').on('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        $('.osb-dropdown-menu').removeClass('show');
        $(this).siblings('.osb-dropdown-menu').addClass('show');
    });

    // Close dropdown when clicking outside
    $(document).on('click', function() {
        $('.osb-dropdown-menu').removeClass('show');
    });

    // Prevent dropdown from closing when clicking inside
    $('.osb-dropdown-menu').on('click', function(e) {
        e.stopPropagation();
    });

    // Filtering functionality
    function filterEvents() {
        const statusFilter = $('#osb-status-filter').val();
        const yearFilter = $('#osb-year-filter').val();
        const searchTerm = $('#osb-search-events').val().toLowerCase();

        $('.osb-event-card').each(function() {
            const $card = $(this);
            const status = $card.data('status');
            const year = $card.data('year').toString();
            const title = $card.find('.osb-event-title a').text().toLowerCase();
            const description = $card.find('.osb-event-description').text().toLowerCase();

            let show = true;

            // Status filter
            if (statusFilter && status !== statusFilter) {
                show = false;
            }

            // Year filter
            if (yearFilter && year !== yearFilter) {
                show = false;
            }

            // Search filter
            if (searchTerm && !title.includes(searchTerm) && !description.includes(searchTerm)) {
                show = false;
            }

            $card.toggleClass('hidden', !show);
        });

        // Show/hide empty state
        const visibleCards = $('.osb-event-card:not(.hidden)').length;
        $('.osb-no-events').toggle(visibleCards === 0);
    }

    // Bind filter events
    $('#osb-status-filter, #osb-year-filter').on('change', filterEvents);
    $('#osb-search-events').on('input', filterEvents);

    // Clear filters
    $('#osb-clear-filters').on('click', function() {
        $('#osb-status-filter').val('');
        $('#osb-year-filter').val('');
        $('#osb-search-events').val('');
        filterEvents();
    });
});
</script>