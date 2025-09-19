<?php
/**
 * Participating Schools Shortcode Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

global $wpdb;

// Get participating schools for the current event
$table_prefix = defined('OSB_TABLE_PREFIX') ? OSB_TABLE_PREFIX : 'osb_';
$approved_schools = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT r.*, s.school_name, s.logo_url, s.contact_email, s.address
         FROM {$wpdb->prefix}{$table_prefix}registrations r
         JOIN {$wpdb->prefix}{$table_prefix}schools s ON r.school_id = s.id
         WHERE r.event_id = %d AND r.status = 'approved'
         ORDER BY s.school_name ASC",
        $event->id
    )
);
?>

<div class="osb-participating-schools">
    <h2 class="osb-schools-title">Participating Schools</h2>

    <?php if (!empty($approved_schools)): ?>
        <div class="osb-schools-scroll">
            <div class="osb-schools-track">
                <?php foreach ($approved_schools as $school): ?>
                    <div class="osb-school-item">
                        <?php if (!empty($school->logo_url)): ?>
                            <img src="<?php echo esc_url($school->logo_url); ?>"
                                 alt="<?php echo esc_attr($school->school_name); ?>"
                                 class="osb-school-logo">
                        <?php else: ?>
                            <div class="osb-school-placeholder">
                                <span class="osb-school-initials">
                                    <?php
                                    $words = explode(' ', $school->school_name);
                                    echo esc_html(substr($words[0], 0, 1));
                                    if (isset($words[1])) {
                                        echo esc_html(substr($words[1], 0, 1));
                                    }
                                    ?>
                                </span>
                            </div>
                        <?php endif; ?>

                        <div class="osb-school-info">
                            <h3 class="osb-school-name"><?php echo esc_html($school->school_name); ?></h3>
                            <?php if (!empty($school->address)): ?>
                                <p class="osb-school-address"><?php echo esc_html($school->address); ?></p>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php else: ?>
        <div class="osb-schools-placeholder">
            <div class="osb-schools-track">
                <?php
                // Show placeholder schools when no registrations yet
                $placeholder_schools = [
                    'Excellence Academy',
                    'Bright Future School',
                    'Victory High School',
                    'Unity College',
                    'Success Academy',
                    'Hope International',
                    'Glory High School',
                    'Future Leaders Academy'
                ];
                ?>

                <?php foreach ($placeholder_schools as $school): ?>
                    <div class="osb-school-item osb-placeholder">
                        <div class="osb-school-placeholder">
                            <span class="osb-school-initials">
                                <?php
                                $words = explode(' ', $school);
                                echo esc_html(substr($words[0], 0, 1));
                                if (isset($words[1])) {
                                    echo esc_html(substr($words[1], 0, 1));
                                }
                                ?>
                            </span>
                        </div>

                        <div class="osb-school-info">
                            <h3 class="osb-school-name"><?php echo esc_html($school); ?></h3>
                            <p class="osb-school-address">Awaiting Registration</p>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="osb-registration-notice">
            <p>These are sample schools. Real participating schools will appear here once they register for the competition.</p>
        </div>
    <?php endif; ?>
</div>

<style>
/* Participating Schools Styles */
.osb-participating-schools {
    max-width: 1200px;
    margin: 0 auto;
    padding: 40px 20px;
    overflow: hidden;
    width: 100%;
    box-sizing: border-box;
}

.osb-schools-title {
    text-align: center;
    font-size: 2.5rem;
    font-weight: bold;
    color: #0073aa;
    margin-bottom: 40px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.osb-schools-scroll {
    overflow: hidden;
    position: relative;
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    border-radius: 15px;
    padding: 30px 0;
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    width: 100%;
    max-width: 100%;
}

.osb-schools-track {
    display: flex;
    animation: scrollHorizontal 30s linear infinite;
    gap: 30px;
    padding: 0 20px;
    width: 200%;
    will-change: transform;
    contain: layout style paint;
}

@keyframes scrollHorizontal {
    0% {
        transform: translateX(0);
    }
    100% {
        transform: translateX(-50%);
    }
}

.osb-school-item {
    display: flex;
    flex-direction: column;
    align-items: center;
    min-width: 200px;
    max-width: 200px;
    padding: 25px;
    background: #fff;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    border: 2px solid transparent;
    transition: all 0.3s ease;
    text-align: center;
    position: relative;
    flex-shrink: 0;
}

.osb-school-item:hover {
    transform: translateY(-8px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    border-color: #0073aa;
}

.osb-school-item.osb-placeholder {
    opacity: 0.7;
    border-style: dashed;
}

.osb-school-logo {
    width: 80px;
    height: 80px;
    object-fit: contain;
    border-radius: 50%;
    border: 3px solid #e9ecef;
    padding: 10px;
    background: #fff;
    margin-bottom: 15px;
    transition: all 0.3s ease;
}

.osb-school-item:hover .osb-school-logo {
    border-color: #0073aa;
    transform: scale(1.1);
}

.osb-school-placeholder {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #0073aa, #005177);
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 15px;
    transition: all 0.3s ease;
    box-shadow: 0 4px 15px rgba(0,115,170,0.3);
}

.osb-school-item:hover .osb-school-placeholder {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(0,115,170,0.4);
}

.osb-school-initials {
    font-size: 1.8rem;
    font-weight: bold;
    color: white;
    text-transform: uppercase;
}

.osb-school-info {
    text-align: center;
}

.osb-school-name {
    font-size: 1.1rem;
    font-weight: 600;
    color: #2c3e50;
    margin: 0 0 8px 0;
    line-height: 1.3;
    min-height: 2.6em;
    display: flex;
    align-items: center;
    justify-content: center;
}

.osb-school-address {
    font-size: 0.9rem;
    color: #6c757d;
    margin: 0;
    line-height: 1.4;
    font-style: italic;
}

.osb-school-item.osb-placeholder .osb-school-address {
    color: #adb5bd;
}

.osb-registration-notice {
    text-align: center;
    margin-top: 30px;
    padding: 20px;
    background: #e3f2fd;
    border-radius: 8px;
    border-left: 4px solid #0073aa;
}

.osb-registration-notice p {
    margin: 0;
    color: #1565c0;
    font-style: italic;
    font-size: 1rem;
}

/* Pause animation on hover */
.osb-schools-scroll:hover .osb-schools-track {
    animation-play-state: paused;
}

/* Responsive Design */
@media (max-width: 1024px) {
    .osb-schools-title {
        font-size: 2rem;
        margin-bottom: 30px;
    }

    .osb-school-item {
        min-width: 180px;
        max-width: 180px;
        padding: 20px;
        flex-shrink: 0;
    }

    .osb-schools-track {
        gap: 20px;
    }
}

@media (max-width: 768px) {
    .osb-participating-schools {
        padding: 30px 10px;
        overflow-x: hidden;
    }

    .osb-schools-scroll {
        margin: 0 -10px;
        padding-left: 10px;
        padding-right: 10px;
        overflow-x: hidden;
    }

    .osb-schools-title {
        font-size: 1.8rem;
        margin-bottom: 25px;
    }

    .osb-school-item {
        min-width: 160px;
        max-width: 160px;
        padding: 15px;
        flex-shrink: 0;
    }

    .osb-school-logo,
    .osb-school-placeholder {
        width: 60px;
        height: 60px;
    }

    .osb-school-initials {
        font-size: 1.4rem;
    }

    .osb-school-name {
        font-size: 1rem;
    }

    .osb-school-address {
        font-size: 0.8rem;
    }

    .osb-schools-track {
        animation-duration: 25s;
        padding: 0 10px;
    }
}

@media (max-width: 480px) {
    .osb-participating-schools {
        padding: 20px 5px;
        overflow-x: hidden;
    }

    .osb-schools-scroll {
        margin: 0 -5px;
        padding-left: 5px;
        padding-right: 5px;
        overflow-x: hidden;
        width: calc(100% + 10px);
        max-width: calc(100% + 10px);
    }

    .osb-schools-title {
        font-size: 1.5rem;
    }

    .osb-school-item {
        min-width: 140px;
        max-width: 140px;
        padding: 12px;
        flex-shrink: 0;
    }

    .osb-school-logo,
    .osb-school-placeholder {
        width: 50px;
        height: 50px;
        margin-bottom: 10px;
    }

    .osb-school-initials {
        font-size: 1.2rem;
    }

    .osb-school-name {
        font-size: 0.9rem;
        min-height: 2.2em;
    }

    .osb-schools-track {
        gap: 15px;
        animation-duration: 20s;
        padding: 0 5px;
    }
}

/* Print Styles */
@media print {
    .osb-schools-track {
        animation: none !important;
        flex-wrap: wrap;
        justify-content: center;
    }

    .osb-school-item {
        break-inside: avoid;
        margin-bottom: 20px;
    }
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
    .osb-schools-track {
        animation: none;
    }

    .osb-school-item {
        transition: none;
    }

    .osb-school-logo,
    .osb-school-placeholder {
        transition: none;
    }
}

/* High contrast mode */
@media (prefers-contrast: high) {
    .osb-school-item {
        border: 2px solid #000;
    }

    .osb-school-placeholder {
        background: #000;
    }

    .osb-school-name {
        color: #000;
    }
}
</style>