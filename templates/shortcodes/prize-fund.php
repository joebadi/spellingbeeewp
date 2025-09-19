<?php
/**
 * Prize Fund Shortcode Template - Mockup Design
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Variables passed from shortcode
$event = isset($event) ? $event : null;
$total_donations = isset($total_donations) ? $total_donations : 110000;
$breakdown = isset($breakdown) ? $breakdown : array();
$leaderboard = isset($leaderboard) ? $leaderboard : array();
$atts = isset($atts) ? $atts : array();

// Mock data for mockup design
$current_total = 290000;
$goal_amount = 500000;
$donors_count = 110;
$donation_amount = 110000;
$progress_percentage = ($current_total / $goal_amount) * 100;
?>

<section class="prize-fund" id="donate">
    <div class="container">
        <h2 class="section-title">💰 Prize Fund & Donations</h2>

        <div class="prize-grid">
            <div class="prize-card first">
                <div class="prize-position">🥇 1st Place</div>
                <div class="prize-amount">₦145,000</div>
                <div class="prize-increase">+₦45,000 from donations</div>
            </div>
            <div class="prize-card second">
                <div class="prize-position">🥈 2nd Place</div>
                <div class="prize-amount">₦87,000</div>
                <div class="prize-increase">+₦37,000 from donations</div>
            </div>
            <div class="prize-card third">
                <div class="prize-position">🥉 3rd Place</div>
                <div class="prize-amount">₦58,000</div>
                <div class="prize-increase">+₦28,000 from donations</div>
            </div>
        </div>

        <div class="donation-section">
            <h3 style="text-align: center; margin-bottom: 1rem;">Add to Prize Pot</h3>
            <p style="text-align: center; margin-bottom: 1rem; color: #667eea; font-weight: 600;">Empower young minds and reward excellence in education across Nigeria</p>
            <p style="text-align: center; margin-bottom: 1rem;">Current: ₦<?php echo number_format($current_total); ?> | Goal: ₦<?php echo number_format($goal_amount); ?></p>
            <div class="donation-progress">
                <div class="progress-bar" style="width: <?php echo $progress_percentage; ?>%;"></div>
            </div>
            <p style="text-align: center; color: #6c757d; margin-bottom: 1.5rem;">
                <?php echo $donors_count; ?> generous donors have contributed ₦<?php echo number_format($donation_amount); ?> to boost our prize fund
            </p>

            <div class="donation-buttons">
                <button class="donation-btn">₦5,000</button>
                <button class="donation-btn">₦10,000</button>
                <button class="donation-btn">₦25,000</button>
                <button class="donation-btn">₦50,000</button>
                <button class="donation-btn">Custom Amount</button>
            </div>
        </div>
    </div>
</section>

<style>
.prize-fund {
    background: white;
    margin: 2rem 0;
    padding: 2rem;
    border-radius: 15px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 0 20px;
}

.section-title {
    font-size: 2.2rem;
    text-align: center;
    margin-bottom: 2rem;
    color: #333;
    font-weight: 700;
}

.prize-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 2rem;
    margin-bottom: 2rem;
}

.prize-card {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    padding: 2rem;
    border-radius: 15px;
    text-align: center;
    position: relative;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.prize-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 30px rgba(0,0,0,0.15);
}

.prize-card.first {
    background: linear-gradient(135deg, #ffd700 0%, #ffed4e 100%);
    color: #333;
}

.prize-card.second {
    background: linear-gradient(135deg, #c0c0c0 0%, #e8e8e8 100%);
    color: #333;
}

.prize-card.third {
    background: linear-gradient(135deg, #cd7f32 0%, #deb887 100%);
    color: white;
}

.prize-position {
    font-size: 1.2rem;
    margin-bottom: 0.5rem;
    font-weight: 600;
}

.prize-amount {
    font-size: 2.5rem;
    font-weight: bold;
    margin-bottom: 0.5rem;
}

.prize-increase {
    font-size: 0.9rem;
    opacity: 0.8;
}

.donation-section {
    background: #f8f9fa;
    padding: 2rem;
    border-radius: 15px;
    margin-top: 2rem;
}

.donation-progress {
    background: #e9ecef;
    height: 20px;
    border-radius: 10px;
    margin: 1rem 0;
    overflow: hidden;
    position: relative;
}

.progress-bar {
    background: linear-gradient(90deg, #667eea, #764ba2);
    height: 100%;
    border-radius: 10px;
    transition: width 0.3s ease;
    position: relative;
}

.donation-buttons {
    display: flex;
    gap: 1rem;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 1rem;
}

.donation-btn {
    background: #667eea;
    color: white;
    padding: 10px 20px;
    border: none;
    border-radius: 25px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
    font-size: 0.9rem;
}

.donation-btn:hover {
    background: #764ba2;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
}

/* Responsive Design */
@media (max-width: 768px) {
    .prize-fund {
        padding: 1.5rem;
    }

    .section-title {
        font-size: 1.8rem;
    }

    .prize-grid {
        grid-template-columns: 1fr;
        gap: 1.5rem;
    }

    .prize-amount {
        font-size: 2rem;
    }

    .donation-section {
        padding: 1.5rem;
    }

    .donation-buttons {
        flex-direction: column;
        align-items: center;
    }

    .donation-btn {
        width: 100%;
        max-width: 200px;
    }
}

@media (max-width: 480px) {
    .prize-fund {
        margin: 1rem 0;
        padding: 1rem;
    }

    .section-title {
        font-size: 1.6rem;
        margin-bottom: 1.5rem;
    }

    .prize-card {
        padding: 1.5rem;
    }

    .prize-amount {
        font-size: 1.8rem;
    }

    .donation-section {
        padding: 1rem;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers for donation buttons
    document.querySelectorAll('.donation-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.textContent === 'Custom Amount') {
                const amount = prompt('Enter your donation amount (₦):');
                if (amount && !isNaN(amount)) {
                    alert(`Thank you for your generous donation of ₦${parseInt(amount).toLocaleString()}!`);
                }
            } else {
                const amount = this.textContent.replace('₦', '').replace(',', '');
                alert(`Thank you for your generous donation of ${this.textContent}!`);
            }
        });
    });

    // Animate progress bar on load
    const progressBar = document.querySelector('.progress-bar');
    if (progressBar) {
        const targetWidth = progressBar.style.width;
        progressBar.style.width = '0%';
        setTimeout(() => {
            progressBar.style.width = targetWidth;
        }, 500);
    }
});
</script>