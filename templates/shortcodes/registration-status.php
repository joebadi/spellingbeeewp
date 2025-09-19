<?php
/**
 * Registration Status Shortcode Template
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Variables passed from shortcode
$registration = isset($registration) ? $registration : null;
$students = isset($students) ? $students : array();
?>

<div class="osb-registration-status">
    <?php if ($registration): ?>
        <div class="osb-status-header">
            <h2 class="osb-status-title">Registration Status</h2>
            <div class="osb-status-badge">
                <span class="osb-status osb-status-<?php echo esc_attr($registration->status); ?>">
                    <?php echo esc_html(ucfirst($registration->status)); ?>
                </span>
            </div>
        </div>

        <div class="osb-status-content">
            <!-- School Information -->
            <div class="osb-section osb-school-info">
                <h3>School Information</h3>
                <div class="osb-info-grid">
                    <div class="osb-info-item">
                        <label>School Name</label>
                        <span><?php echo esc_html($registration->school_name); ?></span>
                    </div>
                    <div class="osb-info-item">
                        <label>Registration Date</label>
                        <span><?php echo date('F j, Y', strtotime($registration->created_at)); ?></span>
                    </div>
                    <div class="osb-info-item">
                        <label>Contact Person</label>
                        <span><?php echo esc_html($registration->contact_name); ?></span>
                    </div>
                    <div class="osb-info-item">
                        <label>Email</label>
                        <span><?php echo esc_html($registration->contact_email); ?></span>
                    </div>
                </div>
            </div>

            <!-- Registration Details -->
            <?php if ($registration->status === 'approved'): ?>
                <div class="osb-section osb-approval-info">
                    <div class="osb-success-message">
                        <div class="osb-success-icon">✅</div>
                        <div class="osb-success-content">
                            <h3>Registration Approved!</h3>
                            <p>Congratulations! Your school has been approved to participate in the competition.</p>
                            <?php if (!empty($registration->approved_at)): ?>
                                <small>Approved on <?php echo date('F j, Y', strtotime($registration->approved_at)); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

            <?php elseif ($registration->status === 'pending'): ?>
                <div class="osb-section osb-pending-info">
                    <div class="osb-pending-message">
                        <div class="osb-pending-icon">⏳</div>
                        <div class="osb-pending-content">
                            <h3>Registration Under Review</h3>
                            <p>Your registration is currently being reviewed by our team. You will receive an email notification once a decision has been made.</p>
                            <small>Submitted on <?php echo date('F j, Y', strtotime($registration->created_at)); ?></small>
                        </div>
                    </div>
                </div>

            <?php elseif ($registration->status === 'rejected'): ?>
                <div class="osb-section osb-rejection-info">
                    <div class="osb-error-message">
                        <div class="osb-error-icon">❌</div>
                        <div class="osb-error-content">
                            <h3>Registration Not Approved</h3>
                            <p>Unfortunately, your registration was not approved for this competition.</p>
                            <?php if (!empty($registration->rejection_reason)): ?>
                                <div class="osb-rejection-reason">
                                    <strong>Reason:</strong> <?php echo esc_html($registration->rejection_reason); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($registration->rejected_at)): ?>
                                <small>Decision made on <?php echo date('F j, Y', strtotime($registration->rejected_at)); ?></small>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Students List -->
            <?php if (!empty($students)): ?>
                <div class="osb-section osb-students-section">
                    <h3>Registered Students (<?php echo count($students); ?>)</h3>
                    <div class="osb-students-grid">
                        <?php foreach ($students as $student): ?>
                            <div class="osb-student-card">
                                <div class="osb-student-header">
                                    <div class="osb-student-avatar">
                                        <?php echo strtoupper(substr($student->first_name, 0, 1) . substr($student->last_name, 0, 1)); ?>
                                    </div>
                                    <div class="osb-student-info">
                                        <h4><?php echo esc_html($student->first_name . ' ' . $student->last_name); ?></h4>
                                        <?php if (!empty($student->student_id)): ?>
                                            <span class="osb-student-id">ID: <?php echo esc_html($student->student_id); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <div class="osb-student-details">
                                    <?php if (!empty($student->age)): ?>
                                        <div class="osb-detail">
                                            <label>Age:</label>
                                            <span><?php echo intval($student->age); ?> years</span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($student->grade)): ?>
                                        <div class="osb-detail">
                                            <label>Grade:</label>
                                            <span><?php echo esc_html($student->grade); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <?php if (!empty($student->previous_participation)): ?>
                                        <div class="osb-detail">
                                            <label>Previous Participation:</label>
                                            <span class="osb-yes">Yes</span>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Next Steps -->
            <?php if ($registration->status === 'approved'): ?>
                <div class="osb-section osb-next-steps">
                    <h3>Next Steps</h3>
                    <div class="osb-steps-list">
                        <div class="osb-step">
                            <div class="osb-step-number">1</div>
                            <div class="osb-step-content">
                                <h4>Prepare Your Students</h4>
                                <p>Download study materials and practice words from the resources section.</p>
                            </div>
                        </div>
                        <div class="osb-step">
                            <div class="osb-step-number">2</div>
                            <div class="osb-step-content">
                                <h4>Event Details</h4>
                                <p>You will receive detailed event information and logistics via email closer to the competition date.</p>
                            </div>
                        </div>
                        <div class="osb-step">
                            <div class="osb-step-number">3</div>
                            <div class="osb-step-content">
                                <h4>Competition Day</h4>
                                <p>Bring all students and required documentation on the day of the competition.</p>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Contact Information -->
            <div class="osb-section osb-contact-info">
                <h3>Need Help?</h3>
                <div class="osb-contact-grid">
                    <div class="osb-contact-item">
                        <div class="osb-contact-icon">📧</div>
                        <div class="osb-contact-content">
                            <label>Email Support</label>
                            <a href="mailto:support@omafarufoundation.org">support@omafarufoundation.org</a>
                        </div>
                    </div>
                    <div class="osb-contact-item">
                        <div class="osb-contact-icon">📞</div>
                        <div class="osb-contact-content">
                            <label>Phone Support</label>
                            <a href="tel:+1234567890">+1 (234) 567-8900</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <div class="osb-no-registration">
            <div class="osb-empty-state">
                <div class="osb-empty-icon">📋</div>
                <h3>Registration Not Found</h3>
                <p>The registration you're looking for could not be found. Please check your registration link or contact support.</p>
                <a href="#contact" class="osb-contact-btn">Contact Support</a>
            </div>
        </div>
    <?php endif; ?>
</div>

<style>
/* Registration Status Styles */
.osb-registration-status {
    max-width: 900px;
    margin: 0 auto;
    padding: 20px;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

.osb-status-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 40px;
    padding-bottom: 20px;
    border-bottom: 2px solid #e9ecef;
    flex-wrap: wrap;
    gap: 20px;
}

.osb-status-title {
    font-size: 2.2rem;
    font-weight: bold;
    color: #0073aa;
    margin: 0;
}

.osb-status-badge {
    flex-shrink: 0;
}

.osb-status {
    display: inline-block;
    padding: 12px 24px;
    border-radius: 25px;
    font-size: 1rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.osb-status-approved { background: #d4edda; color: #155724; border: 2px solid #c3e6cb; }
.osb-status-pending { background: #fff3cd; color: #856404; border: 2px solid #ffeaa7; }
.osb-status-rejected { background: #f8d7da; color: #721c24; border: 2px solid #f5c6cb; }

.osb-status-content {
    display: flex;
    flex-direction: column;
    gap: 30px;
}

.osb-section {
    background: white;
    border-radius: 15px;
    padding: 30px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.08);
}

.osb-section h3 {
    font-size: 1.5rem;
    color: #333;
    margin: 0 0 20px 0;
    font-weight: 600;
    border-bottom: 2px solid #e9ecef;
    padding-bottom: 10px;
}

.osb-info-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.osb-info-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.osb-info-item label {
    font-weight: 600;
    color: #666;
    font-size: 0.9rem;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.osb-info-item span {
    font-size: 1rem;
    color: #333;
    font-weight: 500;
}

.osb-success-message,
.osb-pending-message,
.osb-error-message {
    display: flex;
    align-items: center;
    gap: 20px;
    padding: 25px;
    border-radius: 12px;
    margin: 0;
}

.osb-success-message {
    background: #d4edda;
    border-left: 5px solid #28a745;
}

.osb-pending-message {
    background: #fff3cd;
    border-left: 5px solid #ffc107;
}

.osb-error-message {
    background: #f8d7da;
    border-left: 5px solid #dc3545;
}

.osb-success-icon,
.osb-pending-icon,
.osb-error-icon {
    font-size: 2.5rem;
    flex-shrink: 0;
}

.osb-success-content h3,
.osb-pending-content h3,
.osb-error-content h3 {
    margin: 0 0 10px 0;
    font-size: 1.3rem;
    border: none;
    padding: 0;
}

.osb-success-content p,
.osb-pending-content p,
.osb-error-content p {
    margin: 0 0 10px 0;
    font-size: 1rem;
    line-height: 1.6;
}

.osb-rejection-reason {
    background: rgba(255,255,255,0.5);
    padding: 15px;
    border-radius: 8px;
    margin: 15px 0;
    font-size: 0.95rem;
}

.osb-students-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 20px;
}

.osb-student-card {
    background: #f8f9fa;
    border-radius: 12px;
    padding: 20px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.osb-student-card:hover {
    border-color: #0073aa;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,115,170,0.15);
}

.osb-student-header {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 15px;
}

.osb-student-avatar {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #0073aa;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.1rem;
}

.osb-student-info h4 {
    margin: 0 0 5px 0;
    font-size: 1.1rem;
    color: #333;
}

.osb-student-id {
    font-size: 0.85rem;
    color: #666;
    font-family: monospace;
}

.osb-student-details {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.osb-detail {
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-size: 0.9rem;
}

.osb-detail label {
    color: #666;
    font-weight: 500;
}

.osb-detail span {
    color: #333;
    font-weight: 600;
}

.osb-yes {
    color: #28a745 !important;
}

.osb-steps-list {
    display: flex;
    flex-direction: column;
    gap: 25px;
}

.osb-step {
    display: flex;
    align-items: flex-start;
    gap: 20px;
}

.osb-step-number {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #0073aa;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: bold;
    font-size: 1.1rem;
    flex-shrink: 0;
}

.osb-step-content h4 {
    margin: 0 0 8px 0;
    font-size: 1.1rem;
    color: #333;
}

.osb-step-content p {
    margin: 0;
    color: #666;
    line-height: 1.6;
}

.osb-contact-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
}

.osb-contact-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 10px;
    border: 2px solid #e9ecef;
    transition: all 0.3s ease;
}

.osb-contact-item:hover {
    border-color: #0073aa;
    background: #e3f2fd;
}

.osb-contact-icon {
    font-size: 1.8rem;
    opacity: 0.8;
}

.osb-contact-content label {
    display: block;
    font-weight: 600;
    color: #333;
    margin-bottom: 3px;
    font-size: 0.9rem;
}

.osb-contact-content a {
    color: #0073aa;
    text-decoration: none;
    font-weight: 500;
}

.osb-contact-content a:hover {
    text-decoration: underline;
}

.osb-no-registration {
    margin: 40px 0;
}

.osb-empty-state {
    text-align: center;
    padding: 80px 20px;
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
    margin-bottom: 15px;
    font-size: 1.8rem;
    font-weight: 600;
}

.osb-empty-state p {
    color: #666;
    font-size: 1.1rem;
    line-height: 1.6;
    margin-bottom: 25px;
}

.osb-contact-btn {
    background: #0073aa;
    color: white;
    padding: 12px 30px;
    border-radius: 20px;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s ease;
    display: inline-block;
}

.osb-contact-btn:hover {
    background: #005177;
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(0,115,170,0.4);
    text-decoration: none;
    color: white;
}

/* Responsive Design */
@media (max-width: 768px) {
    .osb-registration-status {
        padding: 15px;
    }

    .osb-status-header {
        flex-direction: column;
        align-items: stretch;
        text-align: center;
    }

    .osb-status-title {
        font-size: 1.8rem;
    }

    .osb-section {
        padding: 25px;
    }

    .osb-info-grid {
        grid-template-columns: 1fr;
    }

    .osb-students-grid {
        grid-template-columns: 1fr;
    }

    .osb-success-message,
    .osb-pending-message,
    .osb-error-message {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }

    .osb-contact-grid {
        grid-template-columns: 1fr;
    }
}

@media (max-width: 600px) {
    .osb-status-title {
        font-size: 1.6rem;
    }

    .osb-section {
        padding: 20px;
    }

    .osb-student-header {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }

    .osb-step {
        flex-direction: column;
        text-align: center;
        gap: 15px;
    }

    .osb-contact-item {
        flex-direction: column;
        text-align: center;
        gap: 10px;
    }
}
</style>