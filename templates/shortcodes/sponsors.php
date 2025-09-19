<?php
/**
 * Sponsors Shortcode Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Variables passed from shortcode
$sponsors = isset($sponsors) ? $sponsors : array();
$atts = isset($atts) ? $atts : array();
?>

<div class="osb-sponsors">
    <div class="osb-sponsors-header">
        <h2 class="osb-sponsors-title">Our Sponsors</h2>
        <p class="osb-sponsors-subtitle">
            Thank you to our generous sponsors who make this competition possible
        </p>
    </div>

    <?php if (!empty($sponsors)): ?>
        <?php
        // Group sponsors by tier
        $sponsor_tiers = array();
        foreach ($sponsors as $sponsor) {
            $tier = $sponsor->tier ?: 'bronze';
            if (!isset($sponsor_tiers[$tier])) {
                $sponsor_tiers[$tier] = array();
            }
            $sponsor_tiers[$tier][] = $sponsor;
        }

        // Define tier order and names
        $tier_order = array('platinum', 'gold', 'silver', 'bronze');
        $tier_names = array(
            'platinum' => 'Platinum Sponsors',
            'gold' => 'Gold Sponsors',
            'silver' => 'Silver Sponsors',
            'bronze' => 'Bronze Sponsors'
        );
        ?>

        <?php foreach ($tier_order as $tier): ?>
            <?php if (!empty($sponsor_tiers[$tier])): ?>
                <div class="osb-sponsor-tier osb-tier-<?php echo esc_attr($tier); ?>">
                    <h3 class="osb-tier-title"><?php echo esc_html($tier_names[$tier]); ?></h3>
                    <div class="osb-sponsors-grid osb-grid-<?php echo esc_attr($tier); ?>">
                        <?php foreach ($sponsor_tiers[$tier] as $sponsor): ?>
                            <div class="osb-sponsor-item">
                                <?php if (!empty($sponsor->logo_url)): ?>
                                    <div class="osb-sponsor-logo">
                                        <?php if (!empty($sponsor->website_url)): ?>
                                            <a href="<?php echo esc_url($sponsor->website_url); ?>" target="_blank" rel="noopener">
                                                <img src="<?php echo esc_url($sponsor->logo_url); ?>"
                                                     alt="<?php echo esc_attr($sponsor->name); ?>">
                                            </a>
                                        <?php else: ?>
                                            <img src="<?php echo esc_url($sponsor->logo_url); ?>"
                                                 alt="<?php echo esc_attr($sponsor->name); ?>">
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>

                                <div class="osb-sponsor-info">
                                    <h4 class="osb-sponsor-name">
                                        <?php if (!empty($sponsor->website_url)): ?>
                                            <a href="<?php echo esc_url($sponsor->website_url); ?>" target="_blank" rel="noopener">
                                                <?php echo esc_html($sponsor->name); ?>
                                            </a>
                                        <?php else: ?>
                                            <?php echo esc_html($sponsor->name); ?>
                                        <?php endif; ?>
                                    </h4>

                                    <?php if (!empty($sponsor->description)): ?>
                                        <p class="osb-sponsor-description">
                                            <?php echo esc_html($sponsor->description); ?>
                                        </p>
                                    <?php endif; ?>

                                    <?php if (!empty($sponsor->amount) && $sponsor->show_amount): ?>
                                        <div class="osb-sponsor-amount">
                                            Contributed: $<?php echo number_format($sponsor->amount, 0); ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <div class="osb-become-sponsor">
            <div class="osb-sponsor-cta">
                <h3>Become a Sponsor</h3>
                <p>Support education and academic excellence in our community by sponsoring this competition.</p>
                <a href="#contact" class="osb-sponsor-btn">Learn More</a>
            </div>
        </div>

    <?php else: ?>
        <div class="osb-no-sponsors">
            <div class="osb-empty-state">
                <div class="osb-empty-icon">🤝</div>
                <h3>Sponsor Opportunities Available</h3>
                <p>We're looking for sponsors to support this educational initiative. Contact us to learn about sponsorship opportunities.</p>
                <a href="#contact" class="osb-sponsor-btn">Become a Sponsor</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
/* Sponsors Styles */
.osb-sponsors {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

.osb-sponsors-header {
    text-align: center;
    margin-bottom: 50px;
}

.osb-sponsors-title {
    font-size: 2.5rem;
    font-weight: bold;
    color: #0073aa;
    margin: 0 0 15px 0;
}

.osb-sponsors-subtitle {
    font-size: 1.2rem;
    color: #666;
    line-height: 1.6;
    max-width: 600px;
    margin: 0 auto;
}

.osb-sponsor-tier {
    margin-bottom: 50px;
}

.osb-tier-title {
    font-size: 1.8rem;
    font-weight: 600;
    text-align: center;
    margin-bottom: 30px;
    padding-bottom: 10px;
    border-bottom: 3px solid;
}

.osb-tier-platinum .osb-tier-title {
    color: #b8860b;
    border-bottom-color: #b8860b;
}

.osb-tier-gold .osb-tier-title {
    color: #ffd700;
    border-bottom-color: #ffd700;
}

.osb-tier-silver .osb-tier-title {
    color: #c0c0c0;
    border-bottom-color: #c0c0c0;
}

.osb-tier-bronze .osb-tier-title {
    color: #cd7f32;
    border-bottom-color: #cd7f32;
}

.osb-sponsors-grid {
    display: grid;
    gap: 30px;
    align-items: center;
}

.osb-grid-platinum {
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
}

.osb-grid-gold {
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
}

.osb-grid-silver {
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
}

.osb-grid-bronze {
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
}

.osb-sponsor-item {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
    text-align: center;
    transition: all 0.3s ease;
    border: 2px solid transparent;
}

.osb-sponsor-item:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.osb-tier-platinum .osb-sponsor-item:hover { border-color: #b8860b; }
.osb-tier-gold .osb-sponsor-item:hover { border-color: #ffd700; }
.osb-tier-silver .osb-sponsor-item:hover { border-color: #c0c0c0; }
.osb-tier-bronze .osb-sponsor-item:hover { border-color: #cd7f32; }

.osb-sponsor-logo {
    margin-bottom: 20px;
}

.osb-sponsor-logo img {
    max-width: 100%;
    height: auto;
    max-height: 120px;
    object-fit: contain;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.osb-tier-platinum .osb-sponsor-logo img { max-height: 150px; }
.osb-tier-gold .osb-sponsor-logo img { max-height: 120px; }
.osb-tier-silver .osb-sponsor-logo img { max-height: 100px; }
.osb-tier-bronze .osb-sponsor-logo img { max-height: 80px; }

.osb-sponsor-logo a:hover img {
    transform: scale(1.05);
}

.osb-sponsor-name {
    font-size: 1.3rem;
    font-weight: 600;
    color: #333;
    margin: 0 0 10px 0;
}

.osb-sponsor-name a {
    color: inherit;
    text-decoration: none;
    transition: color 0.3s ease;
}

.osb-tier-platinum .osb-sponsor-name a:hover { color: #b8860b; }
.osb-tier-gold .osb-sponsor-name a:hover { color: #ffd700; }
.osb-tier-silver .osb-sponsor-name a:hover { color: #c0c0c0; }
.osb-tier-bronze .osb-sponsor-name a:hover { color: #cd7f32; }

.osb-sponsor-description {
    font-size: 0.95rem;
    color: #666;
    line-height: 1.6;
    margin: 0 0 15px 0;
}

.osb-sponsor-amount {
    font-size: 0.9rem;
    color: #28a745;
    font-weight: 600;
    background: #f8f9fa;
    padding: 8px 15px;
    border-radius: 20px;
    display: inline-block;
}

.osb-become-sponsor {
    margin-top: 60px;
}

.osb-sponsor-cta {
    background: linear-gradient(135deg, #0073aa 0%, #005177 100%);
    border-radius: 20px;
    padding: 50px;
    text-align: center;
    color: white;
}

.osb-sponsor-cta h3 {
    font-size: 2rem;
    margin: 0 0 15px 0;
    font-weight: 600;
}

.osb-sponsor-cta p {
    font-size: 1.2rem;
    margin: 0 0 30px 0;
    opacity: 0.9;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

.osb-sponsor-btn {
    background: #28a745;
    color: white;
    text-decoration: none;
    padding: 15px 40px;
    font-size: 1.1rem;
    font-weight: 600;
    border-radius: 25px;
    display: inline-block;
    transition: all 0.3s ease;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.osb-sponsor-btn:hover {
    background: #218838;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(40,167,69,0.4);
    text-decoration: none;
    color: white;
}

.osb-no-sponsors {
    margin: 60px 0;
}

.osb-empty-state {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 20px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.08);
}

.osb-empty-icon {
    font-size: 64px;
    margin-bottom: 20px;
    opacity: 0.7;
}

.osb-empty-state h3 {
    color: #333;
    margin-bottom: 15px;
    font-size: 2rem;
    font-weight: 600;
}

.osb-empty-state p {
    color: #666;
    font-size: 1.2rem;
    line-height: 1.6;
    margin-bottom: 30px;
    max-width: 500px;
    margin-left: auto;
    margin-right: auto;
}

/* Responsive Design */
@media (max-width: 768px) {
    .osb-sponsors {
        padding: 15px;
    }

    .osb-sponsors-title {
        font-size: 2rem;
    }

    .osb-sponsors-grid {
        grid-template-columns: 1fr !important;
        gap: 20px;
    }

    .osb-sponsor-item {
        padding: 25px;
    }

    .osb-sponsor-cta {
        padding: 40px 20px;
    }

    .osb-sponsor-cta h3 {
        font-size: 1.6rem;
    }

    .osb-sponsor-cta p {
        font-size: 1.1rem;
    }
}

@media (max-width: 600px) {
    .osb-sponsors-title {
        font-size: 1.8rem;
    }

    .osb-tier-title {
        font-size: 1.5rem;
    }

    .osb-sponsor-item {
        padding: 20px;
    }

    .osb-sponsor-logo img {
        max-height: 80px !important;
    }

    .osb-sponsor-name {
        font-size: 1.1rem;
    }

    .osb-empty-state {
        padding: 60px 15px;
    }

    .osb-empty-state h3 {
        font-size: 1.6rem;
    }
}
</style>