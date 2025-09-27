<?php
/**
 * School Dashboard Access Email Template
 * Sent when a school completes initial registration and receives dashboard access
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
    <title>Welcome to <?php echo esc_html($organization_name); ?></title>
</head>
<body style="margin: 0; padding: 0; font-family: Arial, Helvetica, sans-serif; background-color: #f4f4f4;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">

        <!-- Header -->
        <div style="background: linear-gradient(135deg, #0052cc 0%, #0066ff 100%); color: white; padding: 40px 30px; text-align: center;">
            <h1 style="margin: 0; font-size: 28px; font-weight: bold;">🎉 Welcome to <?php echo esc_html($organization_name); ?>!</h1>
            <p style="margin: 10px 0 0 0; font-size: 16px; opacity: 0.9;">Registration Complete - Dashboard Access Granted</p>
        </div>

        <!-- Content -->
        <div style="padding: 40px 30px;">
            <!-- Greeting -->
            <div style="margin-bottom: 30px;">
                <h2 style="color: #333; margin: 0 0 15px 0; font-size: 24px;">Dear <?php echo esc_html($contact_person); ?>,</h2>
                <p style="color: #666; font-size: 16px; line-height: 1.6; margin: 0;">
                    Congratulations! Your school registration has been completed successfully, and you now have immediate access to your competition dashboard.
                </p>
            </div>

            <!-- School Info Card -->
            <div style="background-color: #f8f9fa; border-left: 4px solid #0052cc; padding: 20px; margin: 30px 0; border-radius: 4px;">
                <h3 style="margin: 0 0 15px 0; color: #333; font-size: 18px;">📚 School Information</h3>
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="padding: 8px 0; color: #666; font-weight: bold; width: 40%;">School Name:</td>
                        <td style="padding: 8px 0; color: #333;"><?php echo esc_html($school_name); ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 8px 0; color: #666; font-weight: bold;">Contact Person:</td>
                        <td style="padding: 8px 0; color: #333;"><?php echo esc_html($contact_person); ?></td>
                    </tr>
                </table>
            </div>

            <!-- Dashboard Access Card -->
            <div style="background-color: #e8f5e8; border-left: 4px solid #28a745; padding: 25px; margin: 30px 0; border-radius: 4px;">
                <h3 style="margin: 0 0 20px 0; color: #28a745; font-size: 20px;">🎯 Access Your Dashboard</h3>

                <div style="margin: 20px 0;">
                    <a href="<?php echo esc_url($dashboard_url); ?>" style="display: inline-block; background-color: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 16px; transition: background-color 0.3s;">
                        🚀 ACCESS DASHBOARD NOW
                    </a>
                </div>

                <p style="color: #666; margin: 15px 0; font-size: 14px;">
                    <strong>Important:</strong> Bookmark this link for easy access to your dashboard.
                </p>
            </div>

            <!-- Next Steps -->
            <div style="background-color: #fff3cd; border-left: 4px solid #ffc107; padding: 20px; margin: 30px 0; border-radius: 4px;">
                <h3 style="margin: 0 0 15px 0; color: #856404; font-size: 18px;">📋 What's Next?</h3>
                <ol style="color: #666; padding-left: 20px; margin: 10px 0;">
                    <li style="margin-bottom: 8px;">Select your competition event</li>
                    <li style="margin-bottom: 8px;">Submit your Expression of Interest</li>
                    <li style="margin-bottom: 8px;">Register your students</li>
                    <li style="margin-bottom: 8px;">Upload required documents</li>
                    <li style="margin-bottom: 8px;">Submit for final approval</li>
                </ol>
                <p style="color: #856404; margin: 15px 0 0 0; font-size: 14px;">
                    <strong>💡 Tip:</strong> Complete all steps to ensure your registration is processed quickly!
                </p>
            </div>

            <!-- Support Section -->
            <div style="background-color: #d1ecf1; border-left: 4px solid #17a2b8; padding: 20px; margin: 30px 0; border-radius: 4px;">
                <h3 style="margin: 0 0 15px 0; color: #0c5460; font-size: 18px;">💬 Need Help?</h3>
                <p style="color: #0c5460; margin: 0; font-size: 14px;">
                    If you have any questions or need assistance, please don't hesitate to contact us:
                </p>
                <p style="color: #0c5460; margin: 10px 0 0 0; font-size: 14px;">
                    <strong>Email:</strong> <a href="mailto:<?php echo esc_attr($contact_email); ?>" style="color: #17a2b8; text-decoration: none;"><?php echo esc_html($contact_email); ?></a>
                </p>
            </div>
        </div>

        <!-- Footer -->
        <div style="background-color: #f8f9fa; padding: 30px; text-align: center; border-top: 1px solid #dee2e6;">
            <p style="margin: 0 0 10px 0; color: #666; font-size: 14px;">
                Thank you for joining <?php echo esc_html($organization_name); ?>!
            </p>
            <p style="margin: 0; color: #999; font-size: 12px;">
                This email was sent from <?php echo esc_html($organization_name); ?><br>
                Please do not reply to this automated email.
            </p>
        </div>
    </div>
</body>
</html>