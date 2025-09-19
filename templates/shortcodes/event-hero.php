<?php
/**
 * Event Hero Shortcode Template
 *
 * @var object $event
 * @var string $video_url
 * @var array $atts
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>

<?php
// Get dynamic data
global $wpdb;
$db = OSB_Database::getInstance();
$donation_calc = OSB_Donation_Calculator::getInstance();

// Get event statistics
$school_count = $wpdb->get_var(
    $wpdb->prepare(
        "SELECT COUNT(*) FROM {$wpdb->prefix}" . OSB_TABLE_PREFIX . "registrations WHERE event_id = %d AND status = 'approved'",
        $event->id
    )
);

// Get total donations and calculate prize fund
$total_donations = $donation_calc->getTotalDonations($event->id);
$base_prize = 180000; // Base prize fund
$total_prize = $base_prize + $total_donations;

// Get ALL event videos from database for reel display
$table_prefix = defined('OSB_TABLE_PREFIX') ? OSB_TABLE_PREFIX : 'osb_';
$event_videos = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}{$table_prefix}event_videos WHERE event_id = %d AND is_active = 1 ORDER BY created_at DESC",
        $event->id
    )
);

// Get ALL events with their videos, ordered by status hierarchy (upcoming first, live, then completed)
$all_events = $wpdb->get_results(
    "SELECT e.*, v.id as video_id, v.title as video_title, v.youtube_url, v.flyer_image, v.video_type, v.description as video_description, v.is_active, v.created_at as video_created_at
     FROM {$wpdb->prefix}{$table_prefix}events e
     LEFT JOIN {$wpdb->prefix}{$table_prefix}event_videos v ON e.id = v.event_id AND v.is_active = 1
     ORDER BY
        CASE
            WHEN e.status = 'upcoming' THEN 1
            WHEN e.status = 'live' THEN 2
            WHEN e.status = 'completed' THEN 3
            ELSE 4
        END ASC,
        e.event_date ASC,
        v.created_at DESC"
);

// Get the main video for current event (first video or current event's video)
$main_video = !empty($event_videos) ? $event_videos[0] : null;

// If no videos for current event, get latest video from any event
if (!$main_video && !empty($all_events)) {
    $main_video = $all_events[0];
}


// Calculate days until event
$event_date = new DateTime($event->event_date);
$today = new DateTime();
$days_left = $today->diff($event_date)->days;
$is_upcoming = $event_date > $today;

// Determine event round/phase
$event_phase = 'Competition';
if (!empty($event->description) && stripos($event->description, 'final') !== false) {
    $event_phase = 'Final Round';
} elseif (!empty($event->description) && stripos($event->description, 'semi') !== false) {
    $event_phase = 'Semi-Final Round';
} elseif (!empty($event->description) && stripos($event->description, 'quarter') !== false) {
    $event_phase = 'Quarter-Final Round';
}
?>

<div class="osb-event-hero">
    <div class="osb-hero-container">

        <!-- Video Section -->
        <div class="osb-video-section">
            <!-- Main Video/Flyer Area -->
            <div class="osb-main-video-wrapper">
                <div class="osb-main-video">
            <?php if ($main_video && !empty($main_video->youtube_url)): ?>
                <!-- Show YouTube video if exists -->
                <?php
                preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $main_video->youtube_url, $matches);
                $youtube_id = $matches[1] ?? '';
                ?>
                <?php if ($youtube_id): ?>
                <div class="osb-video-container">
                    <div class="osb-youtube-player">
                        <iframe src="https://www.youtube.com/embed/<?php echo esc_attr($youtube_id); ?>?rel=0&showinfo=0"
                                frameborder="0" allowfullscreen></iframe>
                    </div>
                </div>
                <?php endif; ?>

            <?php elseif ($main_video && !empty($main_video->flyer_image)): ?>
                <!-- Show video flyer image -->
                <div class="osb-flyer-display">
                    <img src="<?php echo esc_url($main_video->flyer_image); ?>"
                         alt="<?php echo esc_attr($main_video->title); ?>"
                         class="osb-flyer-image">
                    <div class="osb-flyer-overlay">
                        <div class="osb-overlay-content">
                            <div class="osb-flyer-icon">🎥</div>
                            <h2 class="osb-flyer-status"><?php echo esc_html($main_video->title); ?></h2>
                            <h3 class="osb-flyer-round"><?php echo esc_html(ucfirst(str_replace('_', ' ', $main_video->video_type))); ?></h3>
                            <?php if (!empty($main_video->description)): ?>
                            <p class="osb-flyer-venue"><?php echo esc_html($main_video->description); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <?php elseif ($event->status === 'upcoming' && !empty($video_url) && preg_match('/\.(jpg|jpeg|png|gif)$/i', $video_url)): ?>
                <!-- Show uploaded event flyer for upcoming events -->
                <div class="osb-flyer-display">
                    <img src="<?php echo esc_url($video_url); ?>"
                         alt="<?php echo esc_attr($event->title); ?>"
                         class="osb-flyer-image">

                    <?php if ($is_upcoming): ?>
                    <div class="osb-flyer-overlay">
                        <div class="osb-overlay-content">
                            <div class="osb-flyer-icon">🏆</div>
                            <h2 class="osb-flyer-status"><?php echo $event->status === 'upcoming' ? 'COMING SOON' : strtoupper($event->status); ?></h2>
                            <h3 class="osb-flyer-round"><?php echo esc_html($event_phase); ?></h3>
                            <p class="osb-flyer-date">
                                <?php echo date('F j, Y', strtotime($event->event_date)); ?>
                                <?php if (!empty($event->event_time)): ?>
                                    | <?php echo date('g:i A', strtotime($event->event_time)); ?>
                                <?php endif; ?>
                            </p>
                            <?php if (!empty($event->venue_name)): ?>
                            <p class="osb-flyer-venue"><?php echo esc_html($event->venue_name); ?></p>
                            <?php endif; ?>

                            <button type="button" class="osb-btn osb-btn-event-details" id="osb-flyer-details-btn" data-event-id="<?php echo $event->id; ?>">
                                📋 <?php _e('View Event Details', 'spelling-bee-pro'); ?>
                            </button>
                        </div>

                        <?php if ($is_upcoming): ?>
                        <div class="osb-days-left">
                            <?php echo $days_left; ?> <?php echo $days_left == 1 ? 'Day' : 'Days'; ?> Left
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>

            <?php else: ?>
                <!-- Default coming soon flyer -->
                <div class="osb-upcoming-flyer">
                    <div class="osb-flyer-icon">🏆</div>
                    <h2 class="osb-flyer-status"><?php echo $event->status === 'upcoming' ? 'COMING SOON' : strtoupper($event->status); ?></h2>
                    <h3 class="osb-flyer-round"><?php echo esc_html($event_phase); ?></h3>
                    <p class="osb-flyer-date">
                        <?php echo date('F j, Y', strtotime($event->event_date)); ?>
                        <?php if (!empty($event->event_time)): ?>
                            | <?php echo date('g:i A', strtotime($event->event_time)); ?>
                        <?php endif; ?>
                    </p>
                    <?php if (!empty($event->venue_name)): ?>
                    <p class="osb-flyer-venue"><?php echo esc_html($event->venue_name); ?></p>
                    <?php endif; ?>

                    <button type="button" class="osb-btn osb-btn-event-details" id="osb-flyer-details-btn" data-event-id="<?php echo $event->id; ?>">
                        📋 <?php _e('View Event Details', 'spelling-bee-pro'); ?>
                    </button>

                    <?php if ($is_upcoming): ?>
                    <div class="osb-days-left">
                        <?php echo $days_left; ?> <?php echo $days_left == 1 ? 'Day' : 'Days'; ?> Left
                    </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <!-- Dynamic Ticker -->
            <div class="osb-video-ticker">
                <div class="osb-ticker-content">
                    <?php if ($event->status === 'upcoming'): ?>
                        🎉 <?php printf(__('Registration open - %d schools registered so far', 'spelling-bee-pro'), intval($school_count)); ?> •
                        💰 <?php printf(__('₦%s in prizes available', 'spelling-bee-pro'), number_format($total_prize)); ?> •
                        📍 <?php echo !empty($event->venue_name) ? esc_html($event->venue_name) : __('Venue TBA', 'spelling-bee-pro'); ?> •
                    <?php elseif ($event->status === 'live'): ?>
                        🔴 <?php _e('Competition is currently live', 'spelling-bee-pro'); ?> •
                        🏫 <?php printf(__('%d schools competing', 'spelling-bee-pro'), intval($school_count)); ?> •
                        🏆 <?php printf(__('₦%s prize fund', 'spelling-bee-pro'), number_format($total_prize)); ?> •
                    <?php else: ?>
                        ✅ <?php _e('Competition completed', 'spelling-bee-pro'); ?> •
                        🏫 <?php printf(__('%d schools participated', 'spelling-bee-pro'), intval($school_count)); ?> •
                        💰 <?php printf(__('₦%s total prizes awarded', 'spelling-bee-pro'), number_format($total_prize)); ?> •
                    <?php endif; ?>
                </div>
            </div>
            </div>

                <!-- Registration Button centered under main video only -->
                <?php if ($atts['show_registration'] === 'true' && $event->status === 'upcoming'): ?>
                <div class="osb-hero-cta">
                    <a href="#osb-registration-form" class="osb-btn osb-btn-primary osb-btn-large">
                        <?php _e('Register Your School', 'spelling-bee-pro'); ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- Video Reel -->
            <div class="osb-video-reel">
                <h4 class="osb-reel-title">📺 <?php _e('Bee Media', 'spelling-bee-pro'); ?></h4>

                <?php if (!empty($all_events)): ?>

                    <!-- Loop through all admin-created events -->
                    <?php
                    $displayed_events = array();
                    $reel_count = 0;

                    foreach ($all_events as $event_item):
                        // Skip if we already displayed this event
                        if (in_array($event_item->id, $displayed_events)) continue;
                        $displayed_events[] = $event_item->id;

                        $reel_count++;

                        // Use video ID if available, otherwise use event ID for events without videos
                        $item_id = $event_item->video_id ? $event_item->video_id : 'event_' . $event_item->id;
                    ?>
                        <div class="osb-reel-video" data-video-id="<?php echo $item_id; ?>" data-event-id="<?php echo $event_item->id; ?>" data-type="<?php echo $event_item->video_id ? 'video' : 'event'; ?>">

                            <?php if (!empty($event_item->youtube_url)): ?>
                                <!-- YouTube Video Thumbnail -->
                                <?php
                                preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $event_item->youtube_url, $matches);
                                $youtube_id = $matches[1] ?? '';
                                ?>
                                <?php if ($youtube_id): ?>
                                    <div class="osb-reel-thumbnail">
                                        <img src="https://img.youtube.com/vi/<?php echo esc_attr($youtube_id); ?>/maxresdefault.jpg"
                                             alt="<?php echo esc_attr($event_item->video_title ?: $event_item->title); ?>"
                                             class="osb-reel-thumb-img"
                                             onerror="this.src='https://img.youtube.com/vi/<?php echo esc_attr($youtube_id); ?>/hqdefault.jpg'">
                                        <div class="osb-reel-overlay">
                                            <div class="osb-reel-play-icon">▶</div>
                                        </div>
                                        <div class="osb-reel-info">
                                            <div class="osb-reel-title-text"><?php echo esc_html($event_item->video_title ?: $event_item->title); ?></div>
                                            <div class="osb-reel-subtitle"><?php echo esc_html(ucfirst(str_replace('_', ' ', $event_item->video_type ?: $event_item->status))); ?></div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                            <?php elseif (!empty($event_item->flyer_image)): ?>
                                <!-- Flyer Image Thumbnail -->
                                <div class="osb-reel-thumbnail">
                                    <img src="<?php echo esc_url($event_item->flyer_image); ?>"
                                         alt="<?php echo esc_attr($event_item->video_title ?: $event_item->title); ?>"
                                         class="osb-reel-thumb-img">
                                    <div class="osb-reel-overlay">
                                        <div class="osb-reel-play-icon">📸</div>
                                    </div>
                                    <div class="osb-reel-info">
                                        <div class="osb-reel-title-text"><?php echo esc_html($event_item->video_title ?: $event_item->title); ?></div>
                                        <div class="osb-reel-subtitle"><?php echo esc_html(ucfirst(str_replace('_', ' ', $event_item->video_type ?: $event_item->status))); ?></div>
                                    </div>
                                </div>

                            <?php elseif (!empty($event_item->flyer_url)): ?>
                                <!-- Event Flyer as Thumbnail -->
                                <div class="osb-reel-thumbnail">
                                    <img src="<?php echo esc_url($event_item->flyer_url); ?>"
                                         alt="<?php echo esc_attr($event_item->title); ?>"
                                         class="osb-reel-thumb-img">
                                    <div class="osb-reel-overlay">
                                        <div class="osb-reel-play-icon">🏆</div>
                                    </div>
                                    <div class="osb-reel-info">
                                        <div class="osb-reel-title-text"><?php echo esc_html($event_item->title); ?></div>
                                        <div class="osb-reel-subtitle"><?php echo esc_html(ucfirst($event_item->status)); ?></div>
                                    </div>
                                </div>

                            <?php else: ?>
                                <!-- Event without media - show event info -->
                                <div class="osb-reel-placeholder">
                                    <div class="osb-reel-content">
                                        <div class="osb-reel-icon">
                                            <?php
                                            switch($event_item->status) {
                                                case 'upcoming': echo '🏆'; break;
                                                case 'live': echo '🔴'; break;
                                                case 'completed': echo '✅'; break;
                                                default: echo '🎭'; break;
                                            }
                                            ?>
                                        </div>
                                        <div class="osb-reel-title-text"><?php echo esc_html($event_item->title); ?></div>
                                        <div class="osb-reel-subtitle"><?php echo esc_html(ucfirst($event_item->status)); ?></div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>

                <?php else: ?>
                    <!-- No events available -->
                    <div class="osb-no-events-message">
                        <div class="osb-reel-placeholder">
                            <div class="osb-reel-content">
                                <div class="osb-reel-icon">📋</div>
                                <div class="osb-reel-title-text"><?php _e('No Events Created', 'spelling-bee-pro'); ?></div>
                                <div class="osb-reel-subtitle"><?php _e('Events will appear here when admin creates them', 'spelling-bee-pro'); ?></div>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>


<style>
/* Hero Section - Professional Layout - FULL WIDTH BREAKTHROUGH */
.osb-event-hero {
    background: #000;
    color: white;
    padding: 1rem 0;
    margin: 0;
    position: relative;
    overflow: hidden;
    width: 100vw;
    max-width: none;
    margin-left: calc(-50vw + 50%);
    margin-right: calc(-50vw + 50%);
    box-sizing: border-box;
}

.osb-hero-container {
    max-width: none;
    width: 100%;
    margin: 0;
    padding: 0;
}

.osb-hero-header {
    text-align: center;
    margin-bottom: 2rem;
}

.osb-hero-title {
    font-size: 2.5rem;
    font-weight: 700;
    line-height: 1.2;
    margin: 0 0 15px 0;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
}

.osb-hero-subtitle {
    font-size: 1.1rem;
    opacity: 0.9;
    margin: 0 0 30px 0;
    line-height: 1.6;
}

.osb-hero-cta {
    text-align: center;
}

/* Video Section Layout - FULL WIDTH */
.osb-video-section {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 2rem;
    margin-bottom: 2rem;
    align-items: start;
    width: 100%;
    max-width: none;
    padding: 0 20px;
    box-sizing: border-box;
}

/* Main Video Wrapper - FULL WIDTH */
.osb-main-video-wrapper {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
    width: 100%;
    max-width: none;
}

/* Main Video Area - FULL WIDTH */
.osb-main-video {
    background: rgba(0,0,0,0.2);
    border-radius: 15px;
    overflow: hidden;
    position: relative;
    height: 450px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.3);
    transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
    width: 100%;
    max-width: none;
}

/* Flyer Styles */
.osb-upcoming-flyer {
    width: 100%;
    height: 100%;
    background: linear-gradient(135deg, #ff6b6b, #ee5a24);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 2rem;
    color: white;
    position: relative;
}

.osb-flyer-display {
    width: 100%;
    height: 100%;
    position: relative;
    overflow: hidden;
    border-radius: 15px;
}

.osb-flyer-image {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
}

.osb-flyer-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0, 0, 0, 0.8);
    backdrop-filter: blur(5px);
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    text-align: center;
    padding: 2rem;
    color: white;
}

.osb-overlay-content {
    background: transparent;
    padding: 0;
    margin: 0;
    z-index: 2;
}

.osb-overlay-content * {
    color: white !important;
}

.osb-flyer-icon {
    font-size: 3rem;
    margin-bottom: 1rem;
    color: white !important;
}

.osb-flyer-status {
    font-size: 2.2rem;
    font-weight: 700;
    margin: 0 0 0.5rem 0;
    text-shadow: 2px 2px 4px rgba(0,0,0,0.3);
    color: white !important;
}

.osb-flyer-round {
    font-size: 1.4rem;
    font-weight: 600;
    margin: 0 0 1rem 0;
    opacity: 0.95;
    color: white !important;
}

.osb-flyer-date {
    font-size: 1.1rem;
    margin: 0 0 0.3rem 0;
    font-weight: 500;
    color: white !important;
}

.osb-flyer-venue {
    font-size: 1rem;
    margin: 0 0 1rem 0;
    opacity: 0.9;
    color: white !important;
}

.osb-days-left {
    position: absolute;
    top: 0.5rem;
    right: 0.5rem;
    background: rgba(0,0,0,0.8);
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 0.7rem;
    font-weight: 600;
    border: 1px solid rgba(255,255,255,0.3);
    line-height: 1.2;
    z-index: 10;
}

/* Video Container */
.osb-video-container {
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.8);
    border-radius: 15px;
    overflow: hidden;
    position: relative;
}

.osb-youtube-player {
    width: 100%;
    height: 100%;
}

.osb-youtube-player iframe {
    width: 100%;
    height: 100%;
    border: none;
}

/* Ticker */
.osb-video-ticker {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(90deg, rgba(0,0,0,0.95), rgba(0,0,0,0.8), rgba(0,0,0,0.95));
    color: white;
    padding: 12px 0;
    overflow: hidden;
    white-space: nowrap;
    backdrop-filter: blur(5px);
}

.osb-ticker-content {
    display: inline-block;
    animation: ticker-scroll 30s linear infinite;
    font-size: 0.9rem;
    font-weight: 500;
    padding-left: 100%;
}

@keyframes ticker-scroll {
    0% { transform: translateX(0); }
    100% { transform: translateX(-100%); }
}

/* Video Reel */
.osb-video-reel {
    background: rgba(255,255,255,0.1);
    border-radius: 15px;
    padding: 1rem;
    height: 550px;
    width: 300px;
    min-width: 300px;
    max-width: 300px;
    overflow-y: auto;
    backdrop-filter: blur(10px);
    box-shadow: 0 8px 30px rgba(0,0,0,0.2);
    border: 1px solid rgba(255,255,255,0.15);
}

.osb-reel-title {
    color: white;
    margin: 0 0 1rem 0;
    text-align: center;
    font-size: 0.9rem;
    font-weight: 600;
    text-shadow: 1px 1px 2px rgba(0,0,0,0.3);
}

.osb-video-reel::-webkit-scrollbar {
    width: 8px;
}

.osb-video-reel::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.1);
    border-radius: 10px;
}

.osb-video-reel::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.3);
    border-radius: 10px;
}

.osb-video-reel::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.5);
}

/* Reel Video Items */
.osb-reel-video {
    background: #000;
    border-radius: 10px;
    margin-bottom: 1rem;
    overflow: hidden;
    cursor: pointer;
    transition: all 0.3s ease;
    aspect-ratio: 16/9;
    position: relative;
}

.osb-reel-video:hover {
    transform: scale(1.02);
}

.osb-reel-video:last-child {
    margin-bottom: 0;
}

.osb-reel-current .osb-reel-placeholder {
    background: linear-gradient(135deg, #ff6b6b, #ee5a24);
}

.osb-reel-placeholder {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(45deg, #333, #555);
    color: white;
    text-align: center;
    padding: 1rem;
    font-size: 0.9rem;
}

/* Reel Thumbnail Styles */
.osb-reel-thumbnail {
    width: 100%;
    height: 100%;
    position: relative;
    overflow: hidden;
    border-radius: 10px;
}

.osb-reel-thumb-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    display: block;
    transition: transform 0.3s ease;
}

.osb-reel-video:hover .osb-reel-thumb-img {
    transform: scale(1.05);
}

.osb-reel-overlay {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: rgba(0,0,0,0.4);
    display: flex;
    align-items: center;
    justify-content: center;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.osb-reel-video:hover .osb-reel-overlay {
    opacity: 1;
}

.osb-reel-play-icon {
    color: white;
    font-size: 1.5rem;
    text-shadow: 0 2px 4px rgba(0,0,0,0.5);
}

.osb-reel-content {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 4px;
    width: 100%;
    text-align: center;
}

.osb-reel-icon {
    font-size: 1.5rem;
    margin-bottom: 0.3rem;
}

.osb-reel-title-text {
    font-weight: bold;
    font-size: 0.8rem;
    line-height: 1.2;
}

.osb-reel-subtitle {
    font-size: 0.7rem;
    opacity: 0.8;
    text-transform: capitalize;
}

/* Enhanced Reel Video Styles */
.osb-reel-info {
    position: absolute;
    bottom: 0;
    left: 0;
    right: 0;
    background: linear-gradient(transparent, rgba(0,0,0,0.8));
    color: white;
    padding: 8px;
    transform: translateY(100%);
    transition: transform 0.3s ease;
}

.osb-reel-video:hover .osb-reel-info {
    transform: translateY(0);
}

.osb-reel-info .osb-reel-title-text {
    font-size: 0.75rem;
    font-weight: bold;
    margin-bottom: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.osb-reel-info .osb-reel-subtitle {
    font-size: 0.65rem;
    opacity: 0.8;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

/* Active/Current Video Styling */
.osb-reel-current .osb-reel-placeholder {
    background: linear-gradient(135deg, #ff6b6b, #ee5a24) !important;
    border: 2px solid #ff8c42;
}

.osb-reel-video.osb-active .osb-reel-thumbnail {
    border: 2px solid #ff6b6b;
    box-shadow: 0 0 15px rgba(255,107,107,0.5);
}

.osb-reel-video.osb-active .osb-reel-placeholder {
    border: 2px solid #ff6b6b;
    background: linear-gradient(135deg, #ff6b6b, #ee5a24);
}

/* Improved Hover States */
.osb-reel-video:hover {
    transform: scale(1.05);
    z-index: 2;
}

.osb-reel-video:hover .osb-reel-overlay {
    opacity: 1;
}

.osb-reel-video:hover .osb-reel-thumb-img {
    transform: scale(1.1);
}

/* Loading Animation for Video Switching */
.osb-main-video.osb-switching {
    opacity: 0.7;
    transform: scale(0.98);
    transition: all 0.3s ease;
}

.osb-main-video.osb-switching::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 40px;
    height: 40px;
    margin: -20px 0 0 -20px;
    border: 3px solid rgba(255,255,255,0.3);
    border-top: 3px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    z-index: 10;
}

/* Button Styles */
.osb-btn {
    display: inline-block;
    padding: 14px 32px;
    border: none;
    border-radius: 50px;
    font-size: 1.1rem;
    font-weight: 600;
    text-decoration: none;
    text-align: center;
    cursor: pointer;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
    text-transform: none;
    letter-spacing: 0.5px;
    position: relative;
    overflow: hidden;
}

.osb-btn-primary {
    background: linear-gradient(135deg, #ff4500 0%, #ff6b00 25%, #ff8c00 50%, #ff4500 75%, #e6390a 100%) !important;
    color: white !important;
    border: 2px solid rgba(255, 255, 255, 0.2) !important;
    font-weight: 700 !important;
    text-transform: uppercase !important;
    letter-spacing: 1px !important;
    box-shadow:
        0 8px 25px rgba(255, 69, 0, 0.6),
        0 4px 15px rgba(255, 107, 0, 0.5),
        inset 0 2px 0 rgba(255, 255, 255, 0.2),
        inset 0 -2px 0 rgba(0, 0, 0, 0.2) !important;
    backdrop-filter: blur(10px) !important;
    position: relative;
    overflow: hidden;
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1) !important;
}





.osb-btn-primary::before {
    content: '';
    position: absolute;
    top: 0;
    left: -100%;
    width: 100%;
    height: 100%;
    background: linear-gradient(
        90deg,
        transparent,
        rgba(255, 255, 255, 0.4),
        rgba(255, 255, 255, 0.6),
        rgba(255, 255, 255, 0.4),
        transparent
    );
    transition: left 0.8s ease-out;
    transform: skewX(-20deg);
}

.osb-btn-primary::after {
    content: '';
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    bottom: 0;
    background: linear-gradient(135deg, rgba(255, 255, 255, 0.2) 0%, transparent 50%, rgba(255, 255, 255, 0.1) 100%);
    opacity: 0;
    transition: opacity 0.3s ease;
    border-radius: inherit;
}

.osb-btn-primary:hover {
    background: linear-gradient(135deg, #e6390a 0%, #cc2900 25%, #ff4500 50%, #e6390a 75%, #cc2900 100%) !important;
    transform: translateY(-3px) scale(1.05) !important;
    box-shadow:
        0 15px 35px rgba(255, 69, 0, 0.7),
        0 8px 20px rgba(230, 57, 10, 0.6),
        inset 0 2px 0 rgba(255, 255, 255, 0.3),
        inset 0 -2px 0 rgba(0, 0, 0, 0.3) !important;
    border-color: rgba(255, 255, 255, 0.4) !important;
}

.osb-btn-primary:hover::before {
    left: 100%;
}

.osb-btn-primary:hover::after {
    opacity: 1;
}

.osb-btn-primary:active {
    transform: translateY(-1px) scale(1.02);
    box-shadow:
        0 8px 20px rgba(255, 107, 107, 0.4),
        0 4px 12px rgba(238, 90, 36, 0.3),
        inset 0 2px 0 rgba(255, 255, 255, 0.2),
        inset 0 -2px 0 rgba(0, 0, 0, 0.3);
    transition: all 0.15s ease;
}

.osb-btn-large {
    padding: 15px 41px;
    font-size: 1.1rem;
    font-weight: 700;
}

.osb-btn-event-details {
    background: rgba(255,255,255,0.2);
    border: 2px solid rgba(255,255,255,0.6);
    color: white;
    padding: 8px 16px;
    border-radius: 20px;
    margin-top: -0.5rem;
    font-size: 0.9rem;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    backdrop-filter: blur(5px);
    display: inline-block;
    text-decoration: none;
}

.osb-btn-event-details:hover {
    background: rgba(255,255,255,0.3);
    border-color: white;
    transform: translateY(-1px);
}

/* Registration Section Tabs Styling */
.osb-info-reg-container {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 3rem;
    align-items: start;
    margin: 2rem 0;
}

.osb-info-tabs-section {
    background: #f8f9fa;
    border-radius: 10px;
    overflow: hidden;
}

.osb-tab-buttons {
    display: flex;
    background: #e9ecef;
    border-radius: 10px 10px 0 0;
}

.osb-tab-button {
    flex: 1;
    padding: 1rem;
    background: transparent;
    border: none;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s;
    border-bottom: 3px solid transparent;
    color: #333;
    font-size: 0.9rem;
}

.osb-tab-button.active {
    background: white;
    color: #0052cc;
    border-bottom-color: #0052cc;
}

.osb-tab-button:hover {
    background: rgba(255,255,255,0.5);
}

.osb-tab-content {
    padding: 2rem;
    background: white;
    min-height: 400px;
    display: none;
}

.osb-tab-content.active {
    display: block;
}

.osb-tab-content h3 {
    color: #0052cc;
    margin-bottom: 1rem;
    font-size: 1.5rem;
}

.osb-tab-content ul {
    margin: 1rem 0;
    padding-left: 1.5rem;
}

.osb-tab-content li {
    margin-bottom: 0.5rem;
    line-height: 1.6;
}

.osb-registration-form {
    background: linear-gradient(135deg, #0052cc 0%, #003d99 50%, #004080 100%);
    color: white;
    padding: 2rem;
    border-radius: 10px;
}

.osb-registration-form h3 {
    color: white !important;
    margin-bottom: 1.5rem !important;
}

/* Responsive Design */
@media (max-width: 768px) {
    .osb-info-reg-container {
        grid-template-columns: 1fr;
        gap: 2rem;
    }

    .osb-tab-buttons {
        flex-direction: column;
    }

    .osb-tab-button {
        border-bottom: 1px solid #dee2e6;
        border-radius: 0;
    }

    .osb-tab-button.active {
        border-bottom-color: #0052cc;
    }
}

@media (max-width: 768px) {
    .osb-hero-container {
        padding: 0;
        max-width: none;
        width: 100%;
        margin: 0;
    }

    .osb-video-section {
        display: flex;
        flex-direction: column;
        gap: 1rem;
        margin-bottom: 1rem;
        width: 100%;
        max-width: none;
        padding: 0 5px;
        box-sizing: border-box;
    }

    .osb-main-video-wrapper {
        width: 100%;
        max-width: none;
        padding: 0;
        margin: 0;
    }

    .osb-main-video {
        width: 100%;
        max-width: none;
        height: 280px;
        margin: 0;
    }

    .osb-hero-cta {
        padding: 0 10px;
        margin: 1rem 0;
    }

    .osb-video-reel {
        width: 100%;
        height: 200px;
        max-width: 100%;
        min-width: 100%;
        margin: 0;
        padding: 2rem 10px 1rem 10px;
        overflow-x: auto;
        overflow-y: hidden;
        display: flex;
        flex-direction: row;
        gap: 1rem;
        align-items: flex-start;
        position: relative;
        background: rgba(255,255,255,0.1);
        border-radius: 0;
    }

    .osb-reel-title {
        position: absolute;
        top: 0.5rem;
        left: 15px;
        z-index: 2;
        width: auto;
        margin: 0;
        color: white;
        font-size: 0.9rem;
    }

    .osb-reel-video {
        min-width: 160px;
        width: 160px;
        height: 120px;
        margin-bottom: 0;
        flex-shrink: 0;
    }

    .osb-video-reel::-webkit-scrollbar {
        height: 6px;
        width: auto;
    }

    .osb-video-reel::-webkit-scrollbar-track {
        background: rgba(255,255,255,0.1);
        border-radius: 3px;
    }

    .osb-video-reel::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,0.3);
        border-radius: 3px;
    }

    /* Smooth scrolling */
    .osb-video-reel {
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
    }
}

    .osb-upcoming-flyer {
        padding: 1.5rem;
    }

    .osb-flyer-status {
        font-size: 1.3rem;
    }

    .osb-flyer-round {
        display: none !important; /* HIDE COMPETITION TEXT ON MOBILE */
    }

    .osb-flyer-date {
        font-size: 0.9rem;
    }

    .osb-flyer-venue {
        font-size: 0.85rem;
    }

    .osb-flyer-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
    }

    .osb-flyer-overlay {
        padding: 1rem;
    }

    .osb-btn {
        padding: 12px 28px;
        font-size: 1rem;
    }

    .osb-btn-large {
        padding: 14px 32px;
        font-size: 1.1rem;
    }
}

@media (max-width: 480px) {
    .osb-hero-container {
        padding: 0;
        width: 100%;
        max-width: none;
        margin: 0;
    }

    .osb-video-section {
        width: 100%;
        max-width: none;
        padding: 0 2px;
        margin: 0;
    }

    .osb-main-video-wrapper {
        padding: 0;
        width: 100%;
        max-width: none;
        margin: 0;
    }

    .osb-main-video {
        height: 220px;
        width: 100%;
        max-width: none;
        margin: 0;
    }

    .osb-hero-cta {
        padding: 0 5px;
        margin: 1rem 0;
    }

    .osb-btn {
        width: 100%;
        max-width: 300px;
        margin: 0 auto;
        display: block;
    }

    .osb-btn-large {
        padding: 14px 30px;
        font-size: 1.1rem;
    }

    .osb-video-reel {
        height: 180px;
        padding: 2rem 5px 1rem 5px;
        border-radius: 0;
    }

    .osb-reel-video {
        min-width: 140px;
        width: 140px;
        height: 100px;
    }

    .osb-reel-title {
        font-size: 0.8rem;
    }

    /* MOBILE FLYER TEXT FIXES - ENSURE BUTTON VISIBILITY */
    .osb-flyer-status {
        font-size: 1.1rem !important;
        margin: 0 0 0.3rem 0 !important;
    }

    .osb-flyer-round {
        display: none !important; /* HIDE COMPETITION TEXT ON MOBILE */
    }

    .osb-flyer-date {
        font-size: 0.8rem !important;
        margin: 0 0 0.3rem 0 !important;
    }

    .osb-flyer-venue {
        font-size: 0.75rem !important;
        margin: 0 0 0.5rem 0 !important;
    }

    .osb-flyer-icon {
        font-size: 1.5rem !important;
        margin-bottom: 0.3rem !important;
    }

    .osb-flyer-overlay {
        padding: 0.5rem !important;
        justify-content: center !important;
    }

    .osb-overlay-content {
        max-height: 180px !important;
        overflow: hidden !important;
        display: flex !important;
        flex-direction: column !important;
        justify-content: center !important;
        align-items: center !important;
    }

    .osb-upcoming-flyer {
        padding: 0.5rem !important;
    }

    .osb-btn-event-details {
        padding: 8px 16px !important;
        font-size: 0.85rem !important;
        margin-top: 0rem !important;
    }

    .osb-days-left {
        top: 0.3rem !important;
        right: 0.3rem !important;
        padding: 2px 6px !important;
        font-size: 0.6rem !important;
        border-radius: 8px !important;
    }
}

/* EXTRA SMALL MOBILE - CRITICAL BUTTON VISIBILITY & FULL WIDTH */
@media (max-width: 320px) {
    .osb-hero-container {
        padding: 0 !important;
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
    }

    .osb-video-section {
        width: 100% !important;
        max-width: none !important;
        padding: 0 1px !important;
        margin: 0 !important;
    }

    .osb-main-video-wrapper {
        padding: 0 !important;
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
    }

    .osb-main-video {
        height: 200px !important;
        width: 100% !important;
        max-width: none !important;
        margin: 0 !important;
    }

    .osb-flyer-status {
        font-size: 1rem !important;
        margin: 0 0 0.2rem 0 !important;
    }

    .osb-flyer-round {
        display: none !important; /* HIDE COMPETITION TEXT ON MOBILE */
    }

    .osb-flyer-date {
        font-size: 0.7rem !important;
        margin: 0 0 0.2rem 0 !important;
    }

    .osb-flyer-venue {
        font-size: 0.7rem !important;
        margin: 0 0 0.4rem 0 !important;
    }

    .osb-flyer-icon {
        font-size: 1.2rem !important;
        margin-bottom: 0.2rem !important;
    }

    .osb-flyer-overlay {
        padding: 0.3rem !important;
    }

    .osb-overlay-content {
        max-height: 160px !important;
    }

    .osb-btn-event-details {
        padding: 6px 12px !important;
        font-size: 0.8rem !important;
        margin-top: 0rem !important;
    }

    .osb-days-left {
        top: 0.2rem !important;
        right: 0.2rem !important;
        padding: 1px 4px !important;
        font-size: 0.55rem !important;
        border-radius: 6px !important;
    }
}

/* Additional Responsive Adjustments for Reel */
@media (max-width: 768px) {
    .osb-reel-title-text {
        font-size: 0.8rem;
    }

    .osb-reel-subtitle {
        font-size: 0.7rem;
    }
}

/* Event Details Popup Styles */
.osb-popup-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100vw;
    height: 100vh;
    background: rgba(0, 0, 0, 0.8);
    z-index: 10000;
    display: none;
    backdrop-filter: blur(5px);
}

.osb-popup-overlay.show {
    display: flex;
    justify-content: center;
    align-items: center;
}

.osb-popup-content {
    position: relative;
    max-width: 80vw;
    max-height: 80vh;
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
    margin: auto;
    transform: scale(0.9);
    transition: transform 0.3s ease;
    display: flex;
    justify-content: center;
    align-items: center;
}

.osb-popup-content.loaded {
    transform: scale(1);
}

.osb-popup-image {
    max-width: 100%;
    max-height: 80vh;
    width: auto;
    height: auto;
    display: block;
    object-fit: contain;
    border-radius: 15px;
}

.osb-popup-close {
    position: absolute;
    top: 15px;
    right: 15px;
    background: rgba(0, 0, 0, 0.7);
    color: white;
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.2rem;
    z-index: 10001;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.osb-popup-close:hover {
    background: rgba(0, 0, 0, 0.9);
    transform: scale(1.1);
}


@media (max-width: 768px) {
    .osb-popup-content {
        max-width: 95vw;
        max-height: 85vh;
    }

    .osb-popup-image {
        max-height: 85vh;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Smooth scroll to registration form
    $('a[href="#osb-registration-form"]').on('click', function(e) {
        e.preventDefault();
        const target = $('#osb-registration-form, .osb-registration-form').first();
        if (target.length) {
            $('html, body').animate({
                scrollTop: target.offset().top - 100
            }, 800);
        }
    });

    // Tab functionality for registration section
    $(document).on('click', '.osb-tab-button', function() {
        const targetTab = $(this).data('tab');

        // Remove active class from all buttons and content
        $('.osb-tab-button').removeClass('active');
        $('.osb-tab-content').removeClass('active');

        // Add active class to clicked button and corresponding content
        $(this).addClass('active');
        $('#' + targetTab).addClass('active');
    });

    // Event details popup functionality
    $(document).on('click', '.osb-btn-event-details', function(e) {
        e.preventDefault();
        const eventId = $(this).data('event-id');

        // Get event data
        $.ajax({
            url: '<?php echo admin_url('admin-ajax.php'); ?>',
            type: 'POST',
            data: {
                action: 'osb_admin_action',
                sub_action: 'get_event',
                event_id: eventId,
                nonce: '<?php echo wp_create_nonce('osb_admin_nonce'); ?>'
            },
            success: function(response) {
                if (response.success && response.data) {
                    const event = response.data;
                    const flyerUrl = event.flyer_url || '<?php echo OSB_PLUGIN_URL; ?>assets/images/default-flyer.jpg';

                    // Create popup content - ONLY flyer image
                    const popupHtml = `
                        <div class="osb-popup-overlay" id="osb-event-popup">
                            <div class="osb-popup-content">
                                <button class="osb-popup-close" id="osb-popup-close">×</button>
                                <img src="${flyerUrl}" alt="${event.title}" class="osb-popup-image">
                            </div>
                        </div>
                    `;

                    // Add popup to body
                    $('body').append(popupHtml);

                    // Show popup with proper centering
                    const $popup = $('#osb-event-popup');
                    $popup.addClass('show').fadeIn(300);

                    // Add loaded class for animation after a short delay
                    setTimeout(() => {
                        $popup.find('.osb-popup-content').addClass('loaded');
                    }, 50);

                    // Prevent body scroll
                    $('body').css('overflow', 'hidden');
                } else {
                    alert('<?php _e('Unable to load event details.', 'spelling-bee-pro'); ?>');
                }
            },
            error: function() {
                alert('<?php _e('Error loading event details.', 'spelling-bee-pro'); ?>');
            }
        });
    });

    // Close popup functionality
    $(document).on('click', '.osb-popup-close, .osb-popup-overlay', function(e) {
        if (e.target === this) {
            const $popup = $('#osb-event-popup');
            $popup.find('.osb-popup-content').removeClass('loaded');
            setTimeout(() => {
                $popup.removeClass('show').fadeOut(300, function() {
                    $(this).remove();
                    $('body').css('overflow', '');
                });
            }, 100);
        }
    });

    // Close popup with ESC key
    $(document).on('keydown', function(e) {
        if (e.keyCode === 27 && $('#osb-event-popup').length) {
            const $popup = $('#osb-event-popup');
            $popup.find('.osb-popup-content').removeClass('loaded');
            setTimeout(() => {
                $popup.removeClass('show').fadeOut(300, function() {
                    $(this).remove();
                    $('body').css('overflow', '');
                });
            }, 100);
        }
    });

    // Enhanced Video reel interactions - Media player behavior
    $('.osb-reel-video').on('click', function() {
        const $this = $(this);
        const mainVideo = $('.osb-main-video');
        const videoId = $this.data('video-id');
        const eventId = $this.data('event-id');
        const videoType = $this.data('type');
        const videoTitle = $this.find('.osb-reel-title-text').text();

        // Don't proceed if clicking on empty placeholder or no-events message
        if ($this.hasClass('osb-reel-placeholder-item') || $this.closest('.osb-no-events-message').length) {
            return false;
        }


        // Remove active state from all reel videos and add to clicked one
        $('.osb-reel-video').removeClass('osb-active');
        $this.addClass('osb-active');

        // Add switching animation to main video
        mainVideo.addClass('osb-switching');

        // Load video or event dynamically
        if (videoId && videoId !== '') {
            // Determine if it's a video or event
            const isEvent = videoType === 'event' || videoId.toString().startsWith('event_');
            const requestId = isEvent ? eventId : videoId;
            const requestAction = isEvent ? 'get_event' : 'get_video';

            $.ajax({
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                type: 'POST',
                data: {
                    action: 'osb_admin_action',
                    sub_action: requestAction,
                    [isEvent ? 'event_id' : 'video_id']: requestId,
                    nonce: '<?php echo wp_create_nonce('osb_admin_nonce'); ?>'
                },
                beforeSend: function() {
                    console.log('AJAX Request Details:', {
                        action: 'osb_admin_action',
                        sub_action: requestAction,
                        requestId: requestId,
                        isEvent: isEvent,
                        videoType: videoType,
                        videoId: videoId,
                        eventId: eventId
                    });
                },
                success: function(response) {
                    mainVideo.removeClass('osb-switching');

                    if (response.success && response.data) {
                        const data = response.data;
                        let videoContent = '';

                        // Handle YouTube videos (from video data or event data)
                        if (data.youtube_url) {
                            const youtubeRegex = /(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/;
                            const match = data.youtube_url.match(youtubeRegex);
                            const youtubeId = match ? match[1] : '';

                            if (youtubeId) {
                                videoContent = `
                                    <div class="osb-video-container">
                                        <div class="osb-youtube-player">
                                            <iframe src="https://www.youtube.com/embed/${youtubeId}?rel=0&showinfo=0&autoplay=1"
                                                    frameborder="0" allowfullscreen></iframe>
                                        </div>
                                    </div>
                                `;
                            }
                        }
                        // Handle flyer images (from video or event)
                        else if (data.flyer_image || data.flyer_url) {
                            const imageUrl = data.flyer_image || data.flyer_url;
                            videoContent = `
                                <div class="osb-flyer-display">
                                    <img src="${imageUrl}" alt="${data.title}" class="osb-flyer-image">
                                    <div class="osb-flyer-overlay">
                                        <div class="osb-overlay-content">
                                            <div class="osb-flyer-icon">${isEvent ? '🏆' : '📸'}</div>
                                            <h2 class="osb-flyer-status">${data.title}</h2>
                                            <h3 class="osb-flyer-round">${isEvent ? data.status.toUpperCase() : (data.video_type || 'event').replace('_', ' ').toUpperCase()}</h3>
                                            ${data.description ? `<p class="osb-flyer-venue">${data.description}</p>` : ''}
                                        </div>
                                    </div>
                                </div>
                            `;
                        }
                        // Handle events without media
                        else if (isEvent) {
                            videoContent = `
                                <div class="osb-upcoming-flyer">
                                    <div class="osb-flyer-icon">🏆</div>
                                    <h2 class="osb-flyer-status">${data.status === 'upcoming' ? 'COMING SOON' : data.status.toUpperCase()}</h2>
                                    <h3 class="osb-flyer-round">${data.title}</h3>
                                    <p class="osb-flyer-date">
                                        ${new Date(data.event_date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })}
                                        ${data.event_time ? ' | ' + data.event_time : ''}
                                    </p>
                                    ${data.venue_name ? `<p class="osb-flyer-venue">${data.venue_name}</p>` : ''}
                                </div>
                            `;
                        }

                        // Add ticker with event/video info
                        if (videoContent) {
                            const contentType = isEvent ? 'Event' : 'Video';
                            videoContent += `
                                <div class="osb-video-ticker">
                                    <div class="osb-ticker-content">
                                        📹 Now viewing: ${data.title} • ${contentType} • ${data.description || (isEvent ? data.status : 'Event media')} • Click on reel items to browse •
                                    </div>
                                </div>
                            `;

                            mainVideo.html(videoContent);
                        } else {
                            // Fallback content
                            osb_showVideoPlaceholder(mainVideo, data.title || videoTitle, isEvent ? 'Event has no media' : 'Video not available');
                        }
                    } else {
                        // Show error placeholder
                        osb_showVideoPlaceholder(mainVideo, videoTitle, isEvent ? 'Failed to load event' : 'Failed to load video');
                    }
                },
                error: function(xhr, status, error) {
                    mainVideo.removeClass('osb-switching');
                    console.error('AJAX error details:', {
                        status: status,
                        error: error,
                        responseText: xhr.responseText,
                        statusCode: xhr.status
                    });
                    osb_showVideoPlaceholder(mainVideo, videoTitle, 'Error loading ' + (isEvent ? 'event' : 'video'));
                }
            });
        } else {
            // No video ID - show placeholder
            mainVideo.removeClass('osb-switching');
            osb_showVideoPlaceholder(mainVideo, videoTitle, 'Video coming soon');
        }
    });

    // Helper function to show video placeholder
    function osb_showVideoPlaceholder(container, title, subtitle) {
        const placeholderContent = `
            <div class="osb-video-placeholder" style="background: linear-gradient(45deg, rgba(0,0,0,0.8), rgba(0,0,0,0.6)); display: flex; align-items: center; justify-content: center; text-align: center; color: white; height: 100%; position: relative;">
                <div>
                    <div style="font-size: 3rem; margin-bottom: 1rem;">🎥</div>
                    <div style="font-size: 1.2rem; margin-bottom: 0.5rem;">${title}</div>
                    <div style="font-size: 0.9rem; opacity: 0.8;">${subtitle}</div>
                </div>
                <div class="osb-video-ticker">
                    <div class="osb-ticker-content">
                        📹 ${title} • ${subtitle} • Click on reel items to browse events •
                    </div>
                </div>
            </div>
        `;
        container.html(placeholderContent);
    }

    // Pause ticker on hover
    $(document).on('mouseenter', '.osb-video-ticker', function() {
        $(this).find('.osb-ticker-content').css('animation-play-state', 'paused');
    }).on('mouseleave', '.osb-video-ticker', function() {
        $(this).find('.osb-ticker-content').css('animation-play-state', 'running');
    });

    // Add professional loading states
    $(document).on('ajaxStart', function() {
        $('.osb-main-video').addClass('osb-loading');
    }).on('ajaxStop', function() {
        $('.osb-main-video').removeClass('osb-loading');
    });
});
</script>

<!-- Add loading styles -->
<style>
.osb-main-video.osb-loading::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    width: 40px;
    height: 40px;
    margin: -20px 0 0 -20px;
    border: 3px solid rgba(255,255,255,0.3);
    border-top: 3px solid white;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    z-index: 10;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.osb-reel-video.osb-active {
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(255,107,107,0.4);
    border-color: #ff6b6b;
}

.osb-reel-video.osb-active {
    transform: scale(1.02);
    box-shadow: 0 4px 15px rgba(255,107,107,0.4);
    border: 2px solid #ff6b6b;
}
</style>