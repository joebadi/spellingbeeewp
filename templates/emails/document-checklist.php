<?php
/**
 * Document Checklist Email Template
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
    <title>Document Checklist - <?php echo esc_html($event_title); ?></title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">

    <div style="background: linear-gradient(135deg, #0052cc 0%, #003d99 100%); color: white; padding: 30px; border-radius: 10px; text-align: center; margin-bottom: 30px;">
        <h1 style="margin: 0; font-size: 24px;">📋 Document Checklist</h1>
        <p style="margin: 10px 0 0 0; opacity: 0.9;"><?php echo esc_html($organization_name); ?></p>
    </div>

    <div style="background: #f8f9fa; padding: 25px; border-radius: 10px; margin-bottom: 25px;">
        <h2 style="color: #0052cc; margin-top: 0;">Hello <?php echo esc_html($contact_person); ?>,</h2>
        <p>Thank you for registering <strong><?php echo esc_html($school_name); ?></strong> for the <strong><?php echo esc_html($event_title); ?></strong>!</p>

        <p>To complete your registration, please submit the following required documents:</p>
    </div>

    <div style="background: white; border: 2px solid #e9ecef; border-radius: 10px; padding: 25px; margin-bottom: 25px;">
        <h3 style="color: #0052cc; margin-top: 0; border-bottom: 2px solid #0052cc; padding-bottom: 10px;">📝 Required Documents Checklist</h3>

        <div style="margin: 20px 0;">
            <div style="display: flex; align-items: center; margin: 15px 0; padding: 10px; background: #f8f9fa; border-radius: 5px;">
                <span style="font-size: 20px; margin-right: 10px;">🏫</span>
                <strong>School Registration Certificate</strong>
            </div>

            <div style="display: flex; align-items: center; margin: 15px 0; padding: 10px; background: #f8f9fa; border-radius: 5px;">
                <span style="font-size: 20px; margin-right: 10px;">🆔</span>
                <strong>Student Birth Certificates/Age Verification (for each student)</strong>
            </div>

            <div style="display: flex; align-items: center; margin: 15px 0; padding: 10px; background: #f8f9fa; border-radius: 5px;">
                <span style="font-size: 20px; margin-right: 10px;">✍️</span>
                <strong>Signed Parental Consent Forms</strong>
            </div>

            <div style="display: flex; align-items: center; margin: 15px 0; padding: 10px; background: #f8f9fa; border-radius: 5px;">
                <span style="font-size: 20px; margin-right: 10px;">👨‍💼</span>
                <strong>School Endorsement Letter (from Principal/Administrator)</strong>
            </div>

            <div style="display: flex; align-items: center; margin: 15px 0; padding: 10px; background: #f8f9fa; border-radius: 5px;">
                <span style="font-size: 20px; margin-right: 10px;">📋</span>
                <strong>Completed Student Registration Forms</strong>
            </div>

            <?php if (isset($medical_required) && $medical_required): ?>
            <div style="display: flex; align-items: center; margin: 15px 0; padding: 10px; background: #f8f9fa; border-radius: 5px;">
                <span style="font-size: 20px; margin-right: 10px;">🏥</span>
                <strong>Medical Clearance (if applicable)</strong>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div style="background: #e3f2fd; border-left: 4px solid #0052cc; padding: 20px; margin: 25px 0; border-radius: 5px;">
        <h3 style="color: #0052cc; margin-top: 0;">⏰ Important Deadlines</h3>
        <p><strong>Document Submission Deadline:</strong> <?php echo esc_html($submission_deadline ?? 'Please check your registration portal'); ?></p>
        <p><strong>Event Date:</strong> <?php echo esc_html($event_date); ?></p>
        <?php if (isset($event_time)): ?>
        <p><strong>Event Time:</strong> <?php echo esc_html($event_time); ?></p>
        <?php endif; ?>
    </div>

    <div style="text-align: center; margin: 30px 0;">
        <a href="<?php echo esc_url($registration_url); ?>"
           style="background: linear-gradient(135deg, #ff6b6b, #ee5a24); color: white; text-decoration: none; padding: 15px 30px; border-radius: 25px; font-weight: bold; display: inline-block; font-size: 16px;">
            📤 Upload Documents Now
        </a>
    </div>

    <div style="background: #fff3cd; border: 1px solid #ffeaa7; border-radius: 5px; padding: 15px; margin: 25px 0;">
        <h4 style="color: #856404; margin-top: 0;">💡 Pro Tips for Document Submission:</h4>
        <ul style="color: #856404; margin: 0;">
            <li>Scan documents in high quality (PDF or JPG format)</li>
            <li>Ensure all text is clearly readable</li>
            <li>Maximum file size: 5MB per document</li>
            <li>Label files clearly (e.g., "SchoolName_BirthCertificate_StudentName")</li>
        </ul>
    </div>

    <div style="border-top: 2px solid #e9ecef; padding-top: 20px; margin-top: 30px; text-align: center; color: #6c757d;">
        <p><strong>Need Help?</strong></p>
        <p>Contact us at: <a href="mailto:<?php echo esc_attr($contact_email); ?>" style="color: #0052cc;"><?php echo esc_html($contact_email); ?></a></p>
        <p>Registration Token: <code style="background: #f8f9fa; padding: 2px 6px; border-radius: 3px;"><?php echo esc_html($registration_token); ?></code></p>

        <div style="margin-top: 20px; padding-top: 20px; border-top: 1px solid #e9ecef;">
            <p style="margin: 0; font-size: 12px;">© <?php echo date('Y'); ?> <?php echo esc_html($organization_name); ?>. All rights reserved.</p>
        </div>
    </div>

</body>
</html>