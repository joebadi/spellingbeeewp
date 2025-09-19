<?php
/**
 * Deadline Reminder Email Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

$urgency_colors = [
    '7_days' => ['bg' => '#e3f2fd', 'border' => '#0052cc', 'text' => '#0052cc'],
    '3_days' => ['bg' => '#fff3e0', 'border' => '#ff9800', 'text' => '#f57c00'],
    'final' => ['bg' => '#ffebee', 'border' => '#f44336', 'text' => '#d32f2f'],
    '1_day' => ['bg' => '#ffebee', 'border' => '#f44336', 'text' => '#d32f2f']
];

$reminder_type = $reminder_type ?? '7_days';
$colors = $urgency_colors[$reminder_type] ?? $urgency_colors['7_days'];

$icons = [
    '7_days' => '📅',
    '3_days' => '⏰',
    'final' => '🚨',
    '1_day' => '🚨'
];

$titles = [
    '7_days' => 'Document Submission Reminder',
    '3_days' => 'Urgent: 3 Days Left!',
    'final' => 'FINAL NOTICE: Action Required!',
    '1_day' => 'FINAL NOTICE: 1 Day Left!'
];
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo esc_html($titles[$reminder_type]); ?> - <?php echo esc_html($event_title); ?></title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">

    <div style="background: linear-gradient(135deg, <?php echo $colors['border']; ?> 0%, <?php echo $colors['text']; ?> 100%); color: white; padding: 30px; border-radius: 10px; text-align: center; margin-bottom: 30px;">
        <h1 style="margin: 0; font-size: 24px;"><?php echo $icons[$reminder_type]; ?> <?php echo esc_html($titles[$reminder_type]); ?></h1>
        <p style="margin: 10px 0 0 0; opacity: 0.9;"><?php echo esc_html($organization_name); ?></p>
    </div>

    <div style="background: <?php echo $colors['bg']; ?>; border-left: 4px solid <?php echo $colors['border']; ?>; padding: 25px; border-radius: 10px; margin-bottom: 25px;">
        <h2 style="color: <?php echo $colors['text']; ?>; margin-top: 0;">Hello <?php echo esc_html($contact_person); ?>,</h2>

        <?php if ($reminder_type === '7_days'): ?>
            <p>This is a friendly reminder that your document submission deadline for <strong><?php echo esc_html($school_name); ?></strong> is approaching.</p>
        <?php elseif ($reminder_type === '3_days'): ?>
            <p><strong>Urgent reminder:</strong> You have only <strong>3 days left</strong> to submit your required documents for <strong><?php echo esc_html($school_name); ?></strong>.</p>
        <?php else: ?>
            <p><strong>FINAL NOTICE:</strong> Your document submission deadline is <?php echo $reminder_type === '1_day' ? 'TOMORROW' : 'TODAY'; ?>! Immediate action is required to complete your registration for <strong><?php echo esc_html($school_name); ?></strong>.</p>
        <?php endif; ?>
    </div>

    <div style="background: white; border: 2px solid <?php echo $colors['border']; ?>; border-radius: 10px; padding: 25px; margin-bottom: 25px;">
        <h3 style="color: <?php echo $colors['text']; ?>; margin-top: 0;">📊 Registration Status</h3>

        <div style="margin: 20px 0;">
            <p><strong>Event:</strong> <?php echo esc_html($event_title); ?></p>
            <p><strong>School:</strong> <?php echo esc_html($school_name); ?></p>
            <p><strong>Registration Token:</strong> <code style="background: #f8f9fa; padding: 2px 6px; border-radius: 3px;"><?php echo esc_html($registration_token); ?></code></p>

            <?php if (isset($documents_submitted) && isset($total_documents)): ?>
            <div style="margin: 15px 0;">
                <p><strong>Documents Submitted:</strong> <?php echo intval($documents_submitted); ?> of <?php echo intval($total_documents); ?> required</p>
                <div style="background: #e9ecef; height: 10px; border-radius: 5px; overflow: hidden;">
                    <?php $percentage = ($documents_submitted / $total_documents) * 100; ?>
                    <div style="background: <?php echo $colors['border']; ?>; height: 100%; width: <?php echo min(100, $percentage); ?>%;"></div>
                </div>
            </div>
            <?php endif; ?>

            <?php if (isset($missing_documents) && !empty($missing_documents)): ?>
            <div style="background: #ffebee; border: 1px solid #f44336; border-radius: 5px; padding: 15px; margin: 15px 0;">
                <h4 style="color: #d32f2f; margin-top: 0;">❌ Missing Documents:</h4>
                <ul style="color: #d32f2f; margin: 0;">
                    <?php foreach ($missing_documents as $doc): ?>
                    <li><?php echo esc_html($doc); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div style="background: #f8f9fa; border-radius: 10px; padding: 25px; margin-bottom: 25px;">
        <h3 style="color: #0052cc; margin-top: 0;">⏰ Critical Deadlines</h3>
        <div style="background: white; border-radius: 5px; padding: 15px; margin: 10px 0;">
            <p style="margin: 0;"><strong>Document Submission Deadline:</strong></p>
            <p style="margin: 5px 0 0 0; font-size: 18px; color: <?php echo $colors['text']; ?>; font-weight: bold;">
                <?php echo esc_html($submission_deadline ?? 'Check your registration portal'); ?>
            </p>
        </div>
        <div style="background: white; border-radius: 5px; padding: 15px; margin: 10px 0;">
            <p style="margin: 0;"><strong>Competition Date:</strong></p>
            <p style="margin: 5px 0 0 0; font-size: 16px; color: #0052cc; font-weight: bold;">
                <?php echo esc_html($event_date); ?>
                <?php if (isset($event_time)): ?>
                at <?php echo esc_html($event_time); ?>
                <?php endif; ?>
            </p>
        </div>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="<?php echo esc_url($registration_url); ?>"
           style="background: linear-gradient(135deg, <?php echo $colors['border']; ?>, <?php echo $colors['text']; ?>); color: white; text-decoration: none; padding: 18px 35px; border-radius: 25px; font-weight: bold; display: inline-block; font-size: 18px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
            <?php if ($reminder_type === 'final' || $reminder_type === '1_day'): ?>
            🚨 SUBMIT DOCUMENTS NOW
            <?php else: ?>
            📤 Complete Registration
            <?php endif; ?>
        </a>
    </div>

    <?php if ($reminder_type === 'final' || $reminder_type === '1_day'): ?>
    <div style="background: #ffebee; border: 2px solid #f44336; border-radius: 10px; padding: 20px; margin: 25px 0; text-align: center;">
        <h3 style="color: #d32f2f; margin-top: 0;">⚠️ IMPORTANT WARNING</h3>
        <p style="color: #d32f2f; font-weight: bold; margin: 0;">
            Failure to submit all required documents by the deadline will result in automatic cancellation of your registration.
            <?php if ($reminder_type === '1_day'): ?>
            <br><br>This is your final opportunity to complete the process.
            <?php endif; ?>
        </p>
    </div>
    <?php endif; ?>

    <div style="background: #e8f5e8; border-left: 4px solid #4caf50; padding: 15px; margin: 25px 0; border-radius: 5px;">
        <h4 style="color: #2e7d32; margin-top: 0;">💡 Quick Help:</h4>
        <ul style="color: #2e7d32; margin: 0;">
            <li>Check your spam/junk folder for previous emails</li>
            <li>Use your registration token to access your portal</li>
            <li>Contact us immediately if you need assistance</li>
            <li>Ensure documents are clear and under 5MB each</li>
        </ul>
    </div>

    <div style="border-top: 2px solid #e9ecef; padding-top: 20px; margin-top: 30px; text-align: center; color: #6c757d;">
        <p><strong>Need Immediate Help?</strong></p>
        <p>Email: <a href="mailto:<?php echo esc_attr($contact_email); ?>" style="color: #0052cc; font-weight: bold;"><?php echo esc_html($contact_email); ?></a></p>
        <?php if (isset($support_phone)): ?>
        <p>Phone: <a href="tel:<?php echo esc_attr($support_phone); ?>" style="color: #0052cc; font-weight: bold;"><?php echo esc_html($support_phone); ?></a></p>
        <?php endif; ?>

        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e9ecef;">
            <p style="margin: 0; font-size: 12px;">© <?php echo date('Y'); ?> <?php echo esc_html($organization_name); ?>. All rights reserved.</p>
        </div>
    </div>

</body>
</html>