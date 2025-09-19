<?php
/**
 * Donation Form Shortcode Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Variables passed from shortcode
$event = isset($event) ? $event : null;
$suggested_amounts = isset($suggested_amounts) ? $suggested_amounts : array(10, 25, 50, 100, 250, 500);
?>

<div class="osb-donation-form" id="donation-form">
    <?php if ($event): ?>
        <div class="osb-donation-header">
            <h2 class="osb-donation-title">Support the Competition</h2>
            <p class="osb-donation-subtitle">
                Help us make <?php echo esc_html($event->title); ?> a memorable experience for all participants.
            </p>
        </div>

        <form id="osb-donation-form" class="osb-form" method="post">
            <?php wp_nonce_field('osb_donation_nonce', 'donation_nonce'); ?>
            <input type="hidden" name="event_id" value="<?php echo $event->id; ?>">
            <input type="hidden" name="action" value="osb_process_donation">

            <!-- Amount Selection -->
            <div class="osb-form-section">
                <label class="osb-section-label">Choose Your Donation Amount</label>
                <div class="osb-amount-grid">
                    <?php foreach ($suggested_amounts as $amount): ?>
                        <button type="button" class="osb-amount-btn" data-amount="<?php echo $amount; ?>">
                            $<?php echo number_format($amount); ?>
                        </button>
                    <?php endforeach; ?>
                    <button type="button" class="osb-amount-btn osb-custom-btn" data-amount="custom">
                        Custom
                    </button>
                </div>

                <div class="osb-custom-amount" style="display: none;">
                    <label for="custom-amount">Custom Amount ($)</label>
                    <input type="number" id="custom-amount" name="custom_amount" min="1" step="0.01"
                           placeholder="Enter amount">
                </div>

                <input type="hidden" id="selected-amount" name="amount" required>
            </div>

            <!-- Donor Information -->
            <div class="osb-form-section">
                <label class="osb-section-label">Your Information</label>

                <div class="osb-form-row">
                    <div class="osb-form-group">
                        <label for="donor-name">Full Name *</label>
                        <input type="text" id="donor-name" name="donor_name" required
                               placeholder="Enter your full name">
                    </div>
                    <div class="osb-form-group">
                        <label for="donor-email">Email Address *</label>
                        <input type="email" id="donor-email" name="donor_email" required
                               placeholder="your@email.com">
                    </div>
                </div>

                <div class="osb-form-row">
                    <div class="osb-form-group">
                        <label for="donor-phone">Phone Number</label>
                        <input type="tel" id="donor-phone" name="donor_phone"
                               placeholder="(555) 123-4567">
                    </div>
                    <div class="osb-form-group">
                        <label for="donor-organization">Organization (Optional)</label>
                        <input type="text" id="donor-organization" name="donor_organization"
                               placeholder="Your company or organization">
                    </div>
                </div>

                <div class="osb-form-group">
                    <label for="donor-message">Message (Optional)</label>
                    <textarea id="donor-message" name="donor_message" rows="3"
                              placeholder="Leave a message of support for the participants..."></textarea>
                </div>

                <div class="osb-form-group osb-checkbox-group">
                    <label class="osb-checkbox-label">
                        <input type="checkbox" id="is-anonymous" name="is_anonymous" value="1">
                        <span class="osb-checkbox-custom"></span>
                        Make this donation anonymous
                    </label>
                </div>

                <div class="osb-form-group osb-checkbox-group">
                    <label class="osb-checkbox-label">
                        <input type="checkbox" id="send-updates" name="send_updates" value="1" checked>
                        <span class="osb-checkbox-custom"></span>
                        Send me updates about the competition
                    </label>
                </div>
            </div>

            <!-- Payment Method -->
            <div class="osb-form-section">
                <label class="osb-section-label">Payment Method</label>
                <div class="osb-payment-methods">
                    <label class="osb-payment-option">
                        <input type="radio" name="payment_method" value="card" checked>
                        <span class="osb-payment-radio"></span>
                        <div class="osb-payment-info">
                            <span class="osb-payment-title">Credit/Debit Card</span>
                            <span class="osb-payment-desc">Secure payment via Stripe</span>
                        </div>
                        <div class="osb-payment-icons">💳</div>
                    </label>

                    <label class="osb-payment-option">
                        <input type="radio" name="payment_method" value="paypal">
                        <span class="osb-payment-radio"></span>
                        <div class="osb-payment-info">
                            <span class="osb-payment-title">PayPal</span>
                            <span class="osb-payment-desc">Pay with your PayPal account</span>
                        </div>
                        <div class="osb-payment-icons">💙</div>
                    </label>

                    <label class="osb-payment-option">
                        <input type="radio" name="payment_method" value="bank_transfer">
                        <span class="osb-payment-radio"></span>
                        <div class="osb-payment-info">
                            <span class="osb-payment-title">Bank Transfer</span>
                            <span class="osb-payment-desc">Direct bank transfer</span>
                        </div>
                        <div class="osb-payment-icons">🏦</div>
                    </label>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="osb-form-section">
                <button type="submit" class="osb-donate-btn" disabled>
                    <span class="osb-btn-text">Donate Now</span>
                    <span class="osb-btn-amount"></span>
                </button>

                <div class="osb-form-footer">
                    <p class="osb-security-note">
                        🔒 Your payment information is secure and encrypted
                    </p>
                    <p class="osb-tax-note">
                        This donation may be tax-deductible. You will receive a receipt for your records.
                    </p>
                </div>
            </div>
        </form>

        <!-- Success Message -->
        <div id="osb-donation-success" class="osb-success-message" style="display: none;">
            <div class="osb-success-icon">✅</div>
            <h3>Thank You for Your Donation!</h3>
            <p>Your support helps make this competition possible. You will receive a confirmation email shortly.</p>
        </div>

        <!-- Error Message -->
        <div id="osb-donation-error" class="osb-error-message" style="display: none;">
            <div class="osb-error-icon">❌</div>
            <h3>Donation Failed</h3>
            <p id="osb-error-text">There was an error processing your donation. Please try again.</p>
        </div>

    <?php else: ?>
        <div class="osb-no-event">
            <div class="osb-empty-state">
                <div class="osb-empty-icon">💰</div>
                <h3>No Active Event</h3>
                <p>Donations will be available when an event is active.</p>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
/* Donation Form Styles */
.osb-donation-form {
    max-width: 600px;
    margin: 0 auto;
    padding: 20px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

.osb-donation-header {
    text-align: center;
    margin-bottom: 40px;
}

.osb-donation-title {
    font-size: 2.2rem;
    font-weight: bold;
    color: #0073aa;
    margin: 0 0 15px 0;
}

.osb-donation-subtitle {
    font-size: 1.1rem;
    color: #666;
    line-height: 1.6;
    margin: 0;
}

.osb-form {
    background: white;
    border-radius: 15px;
    padding: 40px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.osb-form-section {
    margin-bottom: 35px;
}

.osb-section-label {
    display: block;
    font-size: 1.2rem;
    font-weight: 600;
    color: #333;
    margin-bottom: 20px;
}

.osb-amount-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 15px;
    margin-bottom: 20px;
}

.osb-amount-btn {
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    padding: 20px;
    font-size: 1.1rem;
    font-weight: 600;
    color: #333;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
}

.osb-amount-btn:hover {
    background: #e9ecef;
    border-color: #0073aa;
}

.osb-amount-btn.selected {
    background: #0073aa;
    border-color: #0073aa;
    color: white;
}

.osb-custom-btn {
    background: linear-gradient(135deg, #28a745, #20c997);
    border-color: #28a745;
    color: white;
}

.osb-custom-btn:hover {
    background: linear-gradient(135deg, #218838, #1ea187);
}

.osb-custom-btn.selected {
    background: linear-gradient(135deg, #1e7e34, #17a2b8);
}

.osb-custom-amount {
    margin-top: 15px;
}

.osb-custom-amount label {
    display: block;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.osb-custom-amount input {
    width: 100%;
    padding: 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1.1rem;
    transition: border-color 0.3s ease;
}

.osb-custom-amount input:focus {
    outline: none;
    border-color: #0073aa;
}

.osb-form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.osb-form-group {
    margin-bottom: 20px;
}

.osb-form-group label {
    display: block;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.osb-form-group input,
.osb-form-group textarea {
    width: 100%;
    padding: 15px;
    border: 2px solid #e9ecef;
    border-radius: 8px;
    font-size: 1rem;
    transition: border-color 0.3s ease;
    box-sizing: border-box;
}

.osb-form-group input:focus,
.osb-form-group textarea:focus {
    outline: none;
    border-color: #0073aa;
}

.osb-checkbox-group {
    display: flex;
    align-items: center;
    margin-bottom: 15px;
}

.osb-checkbox-label {
    display: flex;
    align-items: center;
    cursor: pointer;
    font-size: 0.95rem;
    color: #666;
}

.osb-checkbox-label input[type="checkbox"] {
    display: none;
}

.osb-checkbox-custom {
    width: 20px;
    height: 20px;
    border: 2px solid #e9ecef;
    border-radius: 4px;
    margin-right: 10px;
    position: relative;
    transition: all 0.3s ease;
}

.osb-checkbox-label input[type="checkbox"]:checked + .osb-checkbox-custom {
    background: #0073aa;
    border-color: #0073aa;
}

.osb-checkbox-label input[type="checkbox"]:checked + .osb-checkbox-custom::after {
    content: '✓';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    color: white;
    font-weight: bold;
    font-size: 12px;
}

.osb-payment-methods {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.osb-payment-option {
    display: flex;
    align-items: center;
    padding: 20px;
    background: #f8f9fa;
    border: 2px solid #e9ecef;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.osb-payment-option:hover {
    background: #e9ecef;
    border-color: #0073aa;
}

.osb-payment-option input[type="radio"] {
    display: none;
}

.osb-payment-radio {
    width: 20px;
    height: 20px;
    border: 2px solid #e9ecef;
    border-radius: 50%;
    margin-right: 15px;
    position: relative;
    transition: all 0.3s ease;
}

.osb-payment-option input[type="radio"]:checked + .osb-payment-radio {
    border-color: #0073aa;
    background: #0073aa;
}

.osb-payment-option input[type="radio"]:checked + .osb-payment-radio::after {
    content: '';
    width: 8px;
    height: 8px;
    background: white;
    border-radius: 50%;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

.osb-payment-info {
    flex: 1;
}

.osb-payment-title {
    display: block;
    font-weight: 600;
    color: #333;
    margin-bottom: 3px;
}

.osb-payment-desc {
    font-size: 0.9rem;
    color: #666;
}

.osb-payment-icons {
    font-size: 1.5rem;
}

.osb-donate-btn {
    width: 100%;
    background: linear-gradient(135deg, #28a745, #20c997);
    color: white;
    border: none;
    padding: 20px;
    font-size: 1.2rem;
    font-weight: 600;
    border-radius: 10px;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.osb-donate-btn:hover:not(:disabled) {
    background: linear-gradient(135deg, #218838, #1ea187);
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(40,167,69,0.4);
}

.osb-donate-btn:disabled {
    background: #e9ecef;
    color: #666;
    cursor: not-allowed;
}

.osb-btn-amount {
    background: rgba(255,255,255,0.2);
    padding: 5px 15px;
    border-radius: 20px;
    font-size: 1rem;
}

.osb-form-footer {
    text-align: center;
    margin-top: 25px;
}

.osb-security-note,
.osb-tax-note {
    font-size: 0.9rem;
    color: #666;
    margin: 8px 0;
}

.osb-security-note {
    color: #28a745;
    font-weight: 500;
}

.osb-success-message,
.osb-error-message {
    text-align: center;
    padding: 40px;
    border-radius: 15px;
    margin-top: 20px;
}

.osb-success-message {
    background: #d4edda;
    border: 1px solid #c3e6cb;
    color: #155724;
}

.osb-error-message {
    background: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.osb-success-icon,
.osb-error-icon {
    font-size: 3rem;
    margin-bottom: 15px;
}

.osb-success-message h3,
.osb-error-message h3 {
    margin: 0 0 10px 0;
    font-size: 1.5rem;
}

.osb-success-message p,
.osb-error-message p {
    margin: 0;
    font-size: 1.1rem;
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
    .osb-donation-form {
        padding: 15px;
    }

    .osb-form {
        padding: 25px;
    }

    .osb-donation-title {
        font-size: 1.8rem;
    }

    .osb-amount-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .osb-form-row {
        grid-template-columns: 1fr;
        gap: 15px;
    }

    .osb-payment-option {
        padding: 15px;
    }

    .osb-donate-btn {
        padding: 18px;
        font-size: 1.1rem;
    }
}

@media (max-width: 600px) {
    .osb-amount-grid {
        grid-template-columns: 1fr;
    }

    .osb-form {
        padding: 20px;
    }

    .osb-payment-option {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }

    .osb-payment-radio {
        margin-right: 0;
        margin-bottom: 10px;
    }
}
</style>

<script>
jQuery(document).ready(function($) {
    // Amount selection
    $('.osb-amount-btn').on('click', function() {
        const $btn = $(this);
        const amount = $btn.data('amount');

        $('.osb-amount-btn').removeClass('selected');
        $btn.addClass('selected');

        if (amount === 'custom') {
            $('.osb-custom-amount').show();
            $('#selected-amount').val('');
            $('.osb-btn-amount').text('');
            $('.osb-donate-btn').prop('disabled', true);
        } else {
            $('.osb-custom-amount').hide();
            $('#selected-amount').val(amount);
            $('.osb-btn-amount').text('$' + amount);
            $('.osb-donate-btn').prop('disabled', false);
        }
    });

    // Custom amount input
    $('#custom-amount').on('input', function() {
        const amount = parseFloat($(this).val());
        if (amount && amount > 0) {
            $('#selected-amount').val(amount);
            $('.osb-btn-amount').text('$' + amount.toFixed(2));
            $('.osb-donate-btn').prop('disabled', false);
        } else {
            $('#selected-amount').val('');
            $('.osb-btn-amount').text('');
            $('.osb-donate-btn').prop('disabled', true);
        }
    });

    // Form submission
    $('#osb-donation-form').on('submit', function(e) {
        e.preventDefault();

        const $form = $(this);
        const $btn = $('.osb-donate-btn');
        const originalText = $btn.find('.osb-btn-text').text();

        // Disable button and show loading
        $btn.prop('disabled', true);
        $btn.find('.osb-btn-text').text('Processing...');

        // Simulate form submission (replace with actual payment processing)
        setTimeout(function() {
            // Hide form and show success message
            $form.hide();
            $('#osb-donation-success').show();

            // Scroll to success message
            $('html, body').animate({
                scrollTop: $('#osb-donation-success').offset().top - 100
            }, 500);
        }, 2000);
    });

    // Initialize first amount button
    $('.osb-amount-btn').first().click();
});
</script>