<?php
/**
 * Status Update Email Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$status_configs = [
    'pending' => [
        'bg' => '#fff3e0',
        'border' => '#ff9800',
        'text' => '#f57c00',
        'icon' => '⏳',
        'title' => 'Registration Under Review',
        'message' => 'We have received your registration and are currently reviewing your submission.'
    ],
    'documents_submitted' => [
        'bg' => '#e3f2fd',
        'border' => '#2196f3',
        'text' => '#1976d2',
        'icon' => '📋',
        'title' => 'Documents Received',
        'message' => 'Thank you! We have received all your documents and will review them shortly.'
    ],
    'under_review' => [
        'bg' => '#f3e5f5',
        'border' => '#9c27b0',
        'text' => '#7b1fa2',
        'icon' => '🔍',
        'title' => 'Application Under Review',
        'message' => 'Our team is carefully reviewing your application and supporting documents.'
    ],
    'approved' => [
        'bg' => '#e8f5e8',
        'border' => '#4caf50',
        'text' => '#2e7d32',
        'icon' => '✅',
        'title' => 'Registration Approved!',
        'message' => 'Congratulations! Your registration has been approved and confirmed.'
    ],
    'rejected' => [
        'bg' => '#ffebee',
        'border' => '#f44336',
        'text' => '#d32f2f',
        'icon' => '❌',
        'title' => 'Registration Requires Attention',
        'message' => 'We need to discuss some aspects of your registration. Please review the details below.'
    ],
    'confirmed' => [
        'bg' => '#e8f5e8',
        'border' => '#4caf50',
        'text' => '#2e7d32',
        'icon' => '🎉',
        'title' => 'Registration Confirmed!',
        'message' => 'Your participation is officially confirmed! We look forward to seeing you at the competition.'
    ]
];

$current_status = $new_status ?? 'pending';
$config = $status_configs[$current_status] ?? $status_configs['pending'];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($config['title']); ?> - <?php echo esc_html($event_title); ?></title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">

    <div style="background: linear-gradient(135deg, <?php echo $config['border']; ?> 0%, <?php echo $config['text']; ?> 100%); color: white; padding: 30px; border-radius: 10px; text-align: center; margin-bottom: 30px;">
        <h1 style="margin: 0; font-size: 24px;"><?php echo $config['icon']; ?> <?php echo esc_html($config['title']); ?></h1>
        <p style="margin: 10px 0 0 0; opacity: 0.9;"><?php echo esc_html($organization_name); ?></p>
    </div>

    <div style="background: <?php echo $config['bg']; ?>; border-left: 4px solid <?php echo $config['border']; ?>; padding: 25px; border-radius: 10px; margin-bottom: 25px;">
        <h2 style="color: <?php echo $config['text']; ?>; margin-top: 0;">Hello <?php echo esc_html($contact_person); ?>,</h2>
        <p style="font-size: 16px;"><?php echo esc_html($config['message']); ?></p>
    </div>

    <div style="background: white; border: 2px solid <?php echo $config['border']; ?>; border-radius: 10px; padding: 25px; margin-bottom: 25px;">
        <h3 style="color: <?php echo $config['text']; ?>; margin-top: 0;">📊 Registration Details</h3>

        <div style="background: #f8f9fa; border-radius: 8px; padding: 20px; margin: 15px 0;">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px; margin-bottom: 15px;">
                <div>
                    <p style="margin: 0; font-weight: bold; color: #555;">School:</p>
                    <p style="margin: 5px 0 0 0; color: #0052cc;"><?php echo esc_html($school_name); ?></p>
                </div>
                <div>
                    <p style="margin: 0; font-weight: bold; color: #555;">Event:</p>
                    <p style="margin: 5px 0 0 0; color: #0052cc;"><?php echo esc_html($event_title); ?></p>
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div>
                    <p style="margin: 0; font-weight: bold; color: #555;">Status:</p>
                    <p style="margin: 5px 0 0 0; color: <?php echo $config['text']; ?>; font-weight: bold;">
                        <?php echo $config['icon']; ?> <?php echo esc_html(ucfirst(str_replace('_', ' ', $current_status))); ?>
                    </p>
                </div>
                <div>
                    <p style="margin: 0; font-weight: bold; color: #555;">Updated:</p>
                    <p style="margin: 5px 0 0 0;"><?php echo esc_html(date('F j, Y g:i A')); ?></p>
                </div>
            </div>
        </div>

        <?php if (isset($previous_status) && $previous_status !== $current_status): ?>
        <div style="background: #e3f2fd; border-left: 3px solid #2196f3; padding: 15px; margin: 15px 0; border-radius: 5px;">
            <p style="margin: 0; color: #1976d2;"><strong>Status Change:</strong>
                <?php echo esc_html(ucfirst(str_replace('_', ' ', $previous_status))); ?>
                → <?php echo esc_html(ucfirst(str_replace('_', ' ', $current_status))); ?>
            </p>
        </div>
        <?php endif; ?>
    </div>

    <?php if (isset($admin_notes) && !empty($admin_notes)): ?>
    <div style="background: #fff9c4; border: 1px solid #f9a825; border-radius: 10px; padding: 20px; margin-bottom: 25px;">
        <h3 style="color: #f57c00; margin-top: 0;">💬 Message from Admin</h3>
        <div style="background: white; border-radius: 5px; padding: 15px; color: #333;">
            <?php echo wp_kses_post(nl2br($admin_notes)); ?>
        </div>
    </div>
    <?php endif; ?>

    <?php if ($current_status === 'approved' || $current_status === 'confirmed'): ?>
    <!-- Success Status Content -->
    <div style="background: #e8f5e8; border: 2px solid #4caf50; border-radius: 10px; padding: 25px; margin-bottom: 25px;">
        <h3 style="color: #2e7d32; margin-top: 0;">🎯 Next Steps</h3>
        <ul style="color: #2e7d32; margin: 0;">
            <li>Save this email for your records</li>
            <li>Mark your calendar for <?php echo esc_html($event_date); ?></li>
            <li>Prepare your students for the competition</li>
            <li>Check for updates and materials in your portal</li>
            <?php if (isset($venue_name)): ?>
            <li>Note the venue: <?php echo esc_html($venue_name); ?></li>
            <?php endif; ?>
        </ul>
    </div>

    <div style="background: #f8f9fa; border-radius: 10px; padding: 25px; margin-bottom: 25px;">
        <h3 style="color: #0052cc; margin-top: 0;">📅 Event Information</h3>
        <div style="background: white; border-radius: 8px; padding: 20px;">
            <p><strong>Date:</strong> <?php echo esc_html($event_date); ?></p>
            <?php if (isset($event_time)): ?>
            <p><strong>Time:</strong> <?php echo esc_html($event_time); ?></p>
            <?php endif; ?>
            <?php if (isset($venue_name)): ?>
            <p><strong>Venue:</strong> <?php echo esc_html($venue_name); ?></p>
            <?php endif; ?>
            <?php if (isset($venue_address)): ?>
            <p><strong>Address:</strong> <?php echo esc_html($venue_address); ?></p>
            <?php endif; ?>
        </div>
    </div>

    <?php elseif ($current_status === 'rejected'): ?>
    <!-- Rejection Status Content -->
    <div style="background: #ffebee; border: 2px solid #f44336; border-radius: 10px; padding: 25px; margin-bottom: 25px;">
        <h3 style="color: #d32f2f; margin-top: 0;">🔄 How to Proceed</h3>
        <ul style="color: #d32f2f; margin: 0;">
            <li>Review the admin notes above carefully</li>
            <li>Address any issues mentioned</li>
            <li>Contact us for clarification if needed</li>
            <li>Resubmit corrected documents if applicable</li>
            <li>Check your registration portal for updates</li>
        </ul>
    </div>

    <?php else: ?>
    <!-- Pending/Review Status Content -->
    <div style="background: #f8f9fa; border-radius: 10px; padding: 25px; margin-bottom: 25px;">
        <h3 style="color: #0052cc; margin-top: 0;">⏰ What Happens Next?</h3>
        <div style="background: white; border-radius: 8px; padding: 20px;">
            <p>Our review process typically takes <strong>2-5 business days</strong>. We will notify you immediately when your status changes.</p>

            <?php if ($current_status === 'pending' || $current_status === 'documents_submitted'): ?>
            <p>During this time, please:</p>
            <ul style="margin: 10px 0;">
                <li>Keep an eye on your email (including spam folder)</li>
                <li>Ensure all contact information is current</li>
                <li>Prepare your students for the upcoming competition</li>
            </ul>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>

    <div style="text-align: center; margin: 30px 0;">
        <a href="<?php echo esc_url($registration_url); ?>"
           style="background: linear-gradient(135deg, <?php echo $config['border']; ?>, <?php echo $config['text']; ?>); color: white; text-decoration: none; padding: 15px 30px; border-radius: 25px; font-weight: bold; display: inline-block; font-size: 16px;">
            🔍 View Registration Portal
        </a>
    </div>

    <div style="border-top: 2px solid #e9ecef; padding-top: 20px; margin-top: 30px; text-align: center; color: #6c757d;">
        <p><strong>Questions or Concerns?</strong></p>
        <p>Contact us at: <a href="mailto:<?php echo esc_attr($contact_email); ?>" style="color: #0052cc;"><?php echo esc_html($contact_email); ?></a></p>
        <p>Registration Token: <code style="background: #f8f9fa; padding: 2px 6px; border-radius: 3px;"><?php echo esc_html($registration_token); ?></code></p>

        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e9ecef;">
            <p style="margin: 0; font-size: 12px;">© <?php echo date('Y'); ?> <?php echo esc_html($organization_name); ?>. All rights reserved.</p>
        </div>
    </div>

</body>
</html>