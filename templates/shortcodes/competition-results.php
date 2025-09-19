<?php
/**
 * Competition Results Shortcode Template - Enhanced Design
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Get selected event
$selected_event = isset($selected_event) ? $selected_event : null;
$events = isset($events) ? $events : array();
$results = isset($results) ? $results : array();

// Data is already prepared in the shortcodes class with all necessary fields
?>

<div class="osb-competition-results">
    <!-- Header Section with Enhanced Controls -->
    <div class="osb-results-header">
        <div class="osb-header-content">
            <h2 class="osb-results-title">🏆 Competition Results</h2>
            <p class="osb-results-subtitle">Celebrating academic excellence and spelling mastery</p>
        </div>

        <?php if ($atts['show_filter'] === 'true' && !empty($events)): ?>
            <div class="osb-controls-section">
                <div class="osb-filters-panel">
                    <div class="osb-filter-group">
                        <label for="event-filter">📅 Select Event:</label>
                        <form method="get" class="osb-filter-form">
                            <select id="event-filter" name="event_year" onchange="this.form.submit()">
                                <option value="">Select Event</option>
                                <?php foreach ($events as $event): ?>
                                    <option value="<?php echo esc_attr($event->year); ?>"
                                            <?php selected($_GET['event_year'] ?? '', $event->year); ?>>
                                        <?php echo esc_html($event->title . ' (' . $event->year . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                    </div>

                    <div class="osb-action-buttons">
                        <button class="osb-btn osb-btn-export" onclick="exportResults()">📊 Export</button>
                        <button class="osb-btn osb-btn-print" onclick="window.print()">🖨️ Print</button>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($selected_event): ?>
        <!-- Event Information Card -->
        <div class="osb-event-card">
            <div class="osb-event-header">
                <div class="osb-event-info">
                    <h3 class="osb-event-title"><?php echo esc_html($selected_event->title); ?></h3>
                    <div class="osb-event-meta">
                        <span class="osb-meta-item">
                            <i class="osb-icon">📅</i>
                            <?php echo date('F j, Y', strtotime($selected_event->event_date)); ?>
                        </span>
                        <?php if (!empty($selected_event->venue_name)): ?>
                            <span class="osb-meta-item">
                                <i class="osb-icon">📍</i>
                                <?php echo esc_html($selected_event->venue_name); ?>
                            </span>
                        <?php endif; ?>
                        <span class="osb-meta-item">
                            <span class="osb-status osb-status-<?php echo esc_attr($selected_event->status); ?>">
                                <?php echo esc_html(ucfirst($selected_event->status)); ?>
                            </span>
                        </span>
                    </div>
                </div>

                <?php if (!empty($results)): ?>
                    <div class="osb-competition-stats">
                        <div class="osb-stat-item">
                            <span class="osb-stat-number"><?php echo count($results); ?></span>
                            <span class="osb-stat-label">Participants</span>
                        </div>
                        <div class="osb-stat-item">
                            <span class="osb-stat-number"><?php echo count(array_unique(array_column($results, 'school_name'))); ?></span>
                            <span class="osb-stat-label">Schools</span>
                        </div>
                        <div class="osb-stat-item">
                            <span class="osb-stat-number"><?php echo !empty($results) ? round(array_sum(array_column($results, 'accuracy')) / count($results), 1) : 0; ?>%</span>
                            <span class="osb-stat-label">Avg Score</span>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($results)): ?>
            <!-- Results Table Section -->
            <div class="osb-detailed-results">
                <div class="osb-results-header-controls">
                    <h3 class="osb-section-title">📊 Competition Results</h3>
                </div>

                <!-- Table View Only -->
                <div class="osb-results-table-view active" id="table-view">
                    <div class="osb-table-container">
                        <table class="osb-results-table">
                            <thead>
                                <tr>
                                    <th class="osb-sortable" data-sort="position">
                                        <span>Rank</span>
                                        <i class="osb-sort-icon">↕️</i>
                                    </th>
                                    <th class="osb-sortable" data-sort="student">
                                        <span>Student</span>
                                        <i class="osb-sort-icon">↕️</i>
                                    </th>
                                    <th class="osb-sortable" data-sort="school">
                                        <span>School</span>
                                        <i class="osb-sort-icon">↕️</i>
                                    </th>
                                    <th class="osb-sortable" data-sort="accuracy">
                                        <span>Accuracy</span>
                                        <i class="osb-sort-icon">↕️</i>
                                    </th>
                                    <th class="osb-sortable" data-sort="time">
                                        <span>Time</span>
                                        <i class="osb-sort-icon">↕️</i>
                                    </th>
                                    <th>Performance</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($results as $index => $result): ?>
                                    <tr class="osb-table-row osb-rank-<?php echo $result->final_position; ?>" data-position="<?php echo $result->final_position; ?>">
                                        <td class="osb-position-cell">
                                            <div class="osb-position-badge">
                                                <?php if ($result->final_position <= 3): ?>
                                                    <span class="osb-medal-small">
                                                        <?php if ($result->final_position == 1): ?>🥇
                                                        <?php elseif ($result->final_position == 2): ?>🥈
                                                        <?php else: ?>🥉<?php endif; ?>
                                                    </span>
                                                <?php endif; ?>
                                                <span class="osb-position-num"><?php echo $result->final_position; ?></span>
                                            </div>
                                        </td>
                                        <td class="osb-student-cell">
                                            <div class="osb-student-info">
                                                <span class="osb-name"><?php echo esc_html($result->first_name . ' ' . $result->last_name); ?></span>
                                                <?php if ($result->accuracy >= 90): ?>
                                                    <span class="osb-mini-badge">⭐</span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="osb-school-cell">
                                            <span class="osb-school"><?php echo esc_html($result->school_name); ?></span>
                                        </td>
                                        <td class="osb-accuracy-cell">
                                            <div class="osb-accuracy-bar">
                                                <div class="osb-bar-fill" style="width: <?php echo $result->accuracy; ?>%;"></div>
                                                <span class="osb-accuracy-text"><?php echo $result->accuracy; ?>%</span>
                                            </div>
                                        </td>
                                        <td class="osb-time-cell">
                                            <span class="osb-time"><?php echo gmdate('i:s', $result->total_time); ?></span>
                                        </td>
                                        <td class="osb-performance-cell">
                                            <div class="osb-performance-indicators">
                                                <span class="osb-indicator">
                                                    📝 <?php echo $result->correct_answers; ?>/<?php echo $result->total_rounds; ?>
                                                </span>
                                                <span class="osb-indicator">
                                                    🔥 <?php echo $result->streak; ?>
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- No Results State -->
            <div class="osb-no-results">
                <div class="osb-empty-state">
                    <?php if ($selected_event->status === 'completed'): ?>
                        <div class="osb-empty-icon">⏳</div>
                        <h3>Results Being Processed</h3>
                        <p>Competition results are currently being compiled and verified. Please check back soon!</p>
                        <button class="osb-btn osb-btn-refresh" onclick="location.reload()">🔄 Refresh Page</button>
                    <?php else: ?>
                        <div class="osb-empty-icon">🏁</div>
                        <h3>Competition In Progress</h3>
                        <p>This exciting competition is still ongoing. Results will be available once all rounds are completed.</p>
                        <div class="osb-progress-indicator">
                            <div class="osb-progress-dots">
                                <span class="osb-dot active"></span>
                                <span class="osb-dot active"></span>
                                <span class="osb-dot"></span>
                                <span class="osb-dot"></span>
                            </div>
                            <p class="osb-progress-text">Round 2 of 4 in progress...</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <!-- No Event Selected State -->
        <div class="osb-no-event-selected">
            <div class="osb-empty-state">
                <div class="osb-empty-icon">🎯</div>
                <h3>Select a Competition</h3>
                <p>Choose a competition from the dropdown above to view detailed results and performance analytics.</p>
                <?php if (!empty($events)): ?>
                    <div class="osb-quick-select">
                        <h4>Recent Competitions:</h4>
                        <div class="osb-event-quick-links">
                            <?php foreach (array_slice($events, 0, 3) as $event): ?>
                                <button class="osb-quick-link" onclick="selectEvent('<?php echo esc_js($event->year); ?>')">
                                    <?php echo esc_html($event->title); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<!-- Include enhanced styles and JavaScript -->
<?php include 'competition-results-styles.php'; ?>
<?php include 'competition-results-scripts.php'; ?>