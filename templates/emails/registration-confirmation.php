<?php
/**
 * Registration Confirmation Email Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registration Confirmation - <?php echo esc_html($event_title); ?></title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">

    <div style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); color: white; padding: 30px; border-radius: 10px; text-align: center; margin-bottom: 30px;">
        <h1 style="margin: 0; font-size: 28px;">🎉 Registration Confirmed!</h1>
        <p style="margin: 10px 0 0 0; opacity: 0.9; font-size: 18px;"><?php echo esc_html($organization_name); ?></p>
    </div>

    <div style="background: #f8f9fa; padding: 25px; border-radius: 10px; margin-bottom: 25px;">
        <h2 style="color: #28a745; margin-top: 0;">Hello <?php echo esc_html($contact_person); ?>,</h2>
        <p><strong>Congratulations!</strong> Your school <strong><?php echo esc_html($school_name); ?></strong> has been successfully registered for the <strong><?php echo esc_html($event_title); ?></strong>.</p>

        <div style="background: white; border: 2px solid #28a745; border-radius: 8px; padding: 20px; margin: 20px 0;">
            <h3 style="color: #28a745; margin-top: 0;">📋 Your Registration Details</h3>
            <p><strong>Registration Token:</strong> <code style="background: #e9ecef; padding: 4px 8px; border-radius: 4px; font-family: monospace;"><?php echo esc_html($registration_token); ?></code></p>
            <p><strong>Event Date:</strong> <?php echo esc_html($event_date); ?></p>
            <p><strong>School Name:</strong> <?php echo esc_html($school_name); ?></p>
            <p><em>⚠️ Please save your registration token for future reference!</em></p>
        </div>
    </div>

    <div style="background: #fff3cd; border: 2px solid #ffc107; border-radius: 10px; padding: 25px; margin-bottom: 25px;">
        <h3 style="color: #856404; margin-top: 0;">📋 What Happens Next?</h3>

        <div style="margin: 20px 0;">
            <div style="display: flex; align-items: center; margin: 15px 0; padding: 15px; background: white; border-radius: 8px; border-left: 4px solid #ffc107;">
                <span style="font-size: 24px; margin-right: 15px;">1️⃣</span>
                <div>
                    <strong>Document Checklist Email (1 hour)</strong>
                    <p style="margin: 5px 0 0 0; font-size: 14px; color: #666;">You'll receive a detailed list of required documents to submit.</p>
                </div>
            </div>

            <div style="display: flex; align-items: center; margin: 15px 0; padding: 15px; background: white; border-radius: 8px; border-left: 4px solid #ffc107;">
                <span style="font-size: 24px; margin-right: 15px;">2️⃣</span>
                <div>
                    <strong>Submit Required Documents</strong>
                    <p style="margin: 5px 0 0 0; font-size: 14px; color: #666;">Upload school certificate, student details, and consent forms.</p>
                </div>
            </div>

            <div style="display: flex; align-items: center; margin: 15px 0; padding: 15px; background: white; border-radius: 8px; border-left: 4px solid #ffc107;">
                <span style="font-size: 24px; margin-right: 15px;">3️⃣</span>
                <div>
                    <strong>Registration Review (2-3 business days)</strong>
                    <p style="margin: 5px 0 0 0; font-size: 14px; color: #666;">Our team will review and approve your registration.</p>
                </div>
            </div>

            <div style="display: flex; align-items: center; margin: 15px 0; padding: 15px; background: white; border-radius: 8px; border-left: 4px solid #ffc107;">
                <span style="font-size: 24px; margin-right: 15px;">4️⃣</span>
                <div>
                    <strong>Final Confirmation & Event Details</strong>
                    <p style="margin: 5px 0 0 0; font-size: 14px; color: #666;">Receive venue information, schedule, and competition guidelines.</p>
                </div>
            </div>
        </div>
    </div>

    <div style="background: white; border: 2px solid #e9ecef; border-radius: 10px; padding: 25px; margin-bottom: 25px;">
        <h3 style="color: #0052cc; margin-top: 0;">🔗 Useful Links</h3>

        <p><strong>📊 Access Your Dashboard:</strong><br>
        <a href="<?php echo esc_url($registration_url); ?>" style="color: #0052cc; text-decoration: none; background: #f8f9fa; padding: 8px 12px; border-radius: 4px; border: 1px solid #dee2e6; display: inline-block; margin-top: 5px;">Access SpellingBee Dashboard</a></p>

        <p><strong>📞 Need Help?</strong><br>
        Contact us at: <a href="mailto:<?php echo esc_attr($contact_email); ?>" style="color: #0052cc;"><?php echo esc_html($contact_email); ?></a></p>
    </div>

    <div style="background: #e9f7ef; border: 2px solid #28a745; border-radius: 10px; padding: 20px; text-align: center;">
        <p style="margin: 0; color: #155724;"><strong>🎯 Important:</strong> Keep this email for your records. Your registration token will be required for any future correspondence.</p>
    </div>

    <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #e9ecef; color: #6c757d; font-size: 14px;">
        <p>© <?php echo date('Y'); ?> <?php echo esc_html($organization_name); ?>. All rights reserved.</p>
        <p>This is an automated message. Please do not reply to this email.</p>
    </div>

</body>
</html>