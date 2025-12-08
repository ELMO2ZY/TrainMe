<?php
/**
 * Email Helper for TrainMe
 * Handles all email sending via SMTP
 */

// Check if PHPMailer is available, if not, use fallback
if (!class_exists('PHPMailer\PHPMailer\PHPMailer')) {
    // Try to load PHPMailer from vendor directory
    $phpmailer_path = __DIR__ . '/vendor/autoload.php';
    if (file_exists($phpmailer_path)) {
        require_once $phpmailer_path;
    }
}

class TrainMeEmail {
    private $smtp_host = 'smtp.gmail.com';
    private $smtp_port = 587;
    private $smtp_username = 'ststicket2525@gmail.com';
    private $smtp_password = ''; // Will be set via environment or config
    private $from_email = 'ststicket2525@gmail.com';
    private $from_name = 'TrainMe Security Platform';
    
    public function __construct() {
        // You can set password via environment variable or config file
        // For security, don't hardcode it here
        $this->smtp_password = getenv('SMTP_PASSWORD') ?: '';
        
        // If password is not set, try to read from config
        if (empty($this->smtp_password)) {
            $config_file = __DIR__ . '/smtp_config.php';
            if (file_exists($config_file)) {
                $config = include $config_file;
                $this->smtp_password = $config['password'] ?? '';
                // Remove any spaces from the password (Gmail app passwords sometimes have spaces)
                $this->smtp_password = str_replace(' ', '', trim($this->smtp_password));
                if (empty($this->smtp_password)) {
                    error_log("SMTP password is empty in smtp_config.php. Please set a valid Gmail App Password.");
                } else {
                    error_log("SMTP password loaded from config file (length: " . strlen($this->smtp_password) . " characters)");
                }
            } else {
                error_log("SMTP config file not found at: $config_file");
            }
        } else {
            // Remove spaces from environment variable password too
            $this->smtp_password = str_replace(' ', '', trim($this->smtp_password));
            error_log("SMTP password loaded from environment variable");
        }
    }
    
    /**
     * Initialize PHPMailer with SMTP settings
     */
    private function initMailer() {
        // Check if SMTP password is configured
        if (empty($this->smtp_password)) {
            error_log("SMTP password is not configured. Please set it in smtp_config.php or via SMTP_PASSWORD environment variable.");
            return null;
        }
        
        try {
            if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                
                // Disable verbose debug for faster sending (only log errors)
                $mail->SMTPDebug = 0; // Disable debug output for performance
                // Only log errors, not all SMTP communication
                
                // Server settings
                $mail->isSMTP();
                $mail->Host = $this->smtp_host;
                $mail->SMTPAuth = true;
                $mail->Username = $this->smtp_username;
                $mail->Password = $this->smtp_password;
                $mail->SMTPSecure = \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = $this->smtp_port;
                $mail->CharSet = 'UTF-8';
                
                // Timeout settings - reduced for faster sending
                $mail->Timeout = 10;
                $mail->SMTPKeepAlive = true; // Keep connection alive for multiple emails
                
                // Sender
                $mail->setFrom($this->from_email, $this->from_name);
                // Allow custom reply-to for campaigns
                $mail->addReplyTo($this->from_email, $this->from_name);
                
                return $mail;
            } else {
                error_log("PHPMailer is not installed. Install it with: composer require phpmailer/phpmailer");
            }
        } catch (\Exception $e) {
            error_log("PHPMailer initialization error: " . $e->getMessage());
        } catch (\Throwable $e) {
            error_log("PHPMailer initialization fatal error: " . $e->getMessage());
        }
        
        // Fallback to PHP mail() if PHPMailer is not available
        return null;
    }
    
    /**
     * Send welcome email to new user
     * @param string $userEmail User's email address
     * @param string $userName User's name
     * @param string $role User's role (employee/admin)
     * @param string|null $temporaryPassword Temporary password (optional, for admin-created accounts)
     */
    public function sendWelcomeEmail($userEmail, $userName, $role, $temporaryPassword = null) {
        $subject = 'Welcome to TrainMe - Your Security Training Journey Begins!';
        
        // Build login credentials section if password is provided
        $credentialsSection = '';
        $credentialsText = '';
        if (!empty($temporaryPassword)) {
            $credentialsSection = "
                    <div style='background: #fef3c7; border-left: 4px solid #f59e0b; padding: 1rem; margin: 20px 0; border-radius: 4px;'>
                        <h3 style='margin: 0 0 0.5rem 0; color: #92400e; font-size: 1rem;'>🔐 Your Login Credentials</h3>
                        <p style='margin: 0.5rem 0; color: #78350f;'><strong>Email:</strong> {$userEmail}</p>
                        <p style='margin: 0.5rem 0; color: #78350f;'><strong>Temporary Password:</strong> <code style='background: white; padding: 0.25rem 0.5rem; border-radius: 3px; font-family: monospace; font-size: 0.9rem;'>{$temporaryPassword}</code></p>
                        <p style='margin: 0.75rem 0 0 0; color: #92400e; font-size: 0.875rem;'><strong>⚠️ Important:</strong> Please change your password after your first login for security.</p>
                    </div>
            ";
            $credentialsText = "
Your Login Credentials:
Email: {$userEmail}
Temporary Password: {$temporaryPassword}

⚠️ Important: Please change your password after your first login for security.
            ";
        }
        
        $htmlBody = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8fafc; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; background: #2563eb; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #6b7280; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🛡️ Welcome to TrainMe!</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$userName},</h2>
                    <p>Welcome to TrainMe, your comprehensive security awareness training platform!</p>
                    <p>We're excited to have you on board. Your account has been successfully created with the role: <strong>" . ucfirst($role) . "</strong>.</p>
                    {$credentialsSection}
                    <p><strong>What's next?</strong></p>
                    <ul>
                        <li>Complete your security training modules</li>
                        <li>Learn to identify and prevent phishing attacks</li>
                        <li>Master password security and data protection</li>
                        <li>Stay updated with the latest security best practices</li>
                    </ul>
                    <p style='text-align: center;'>
                        <a href='" . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'] . "/login.php' class='button'>Login to Your Dashboard</a>
                    </p>
                    <p>If you have any questions or need assistance, feel free to reach out to our security team.</p>
                    <p>Stay secure!<br><strong>The TrainMe Team</strong></p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from TrainMe Security Platform.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $textBody = "
Welcome to TrainMe!

Hello {$userName},

Welcome to TrainMe, your comprehensive security awareness training platform!

We're excited to have you on board. Your account has been successfully created with the role: " . ucfirst($role) . ".

{$credentialsText}
What's next?
- Complete your security training modules
- Learn to identify and prevent phishing attacks
- Master password security and data protection
- Stay updated with the latest security best practices

Login to your dashboard: " . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'] . "/login.php

If you have any questions or need assistance, feel free to reach out to our security team.

Stay secure!
The TrainMe Team
        ";
        
        return $this->sendEmail($userEmail, $subject, $htmlBody, $textBody);
    }
    
    /**
     * Send training results email
     */
    public function sendTestResultsEmail($userEmail, $userName, $moduleName, $score, $totalQuestions) {
        $subject = "Your {$moduleName} Training Results - TrainMe";
        
        $scoreColor = '#10b981'; // Green
        $scoreMessage = 'Excellent work!';
        if ($score == 0) {
            $scoreColor = '#dc2626'; // Red
            $scoreMessage = 'Don\'t worry, you can retake the training anytime!';
        } elseif ($score < 70) {
            $scoreColor = '#f59e0b'; // Yellow
            $scoreMessage = 'Good effort! Consider reviewing the module to improve your score.';
        }
        
        $htmlBody = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8fafc; padding: 30px; border-radius: 0 0 10px 10px; }
                .score-box { background: white; border: 3px solid {$scoreColor}; border-radius: 10px; padding: 20px; text-align: center; margin: 20px 0; }
                .score { font-size: 48px; font-weight: bold; color: {$scoreColor}; margin: 10px 0; }
                .button { display: inline-block; background: #2563eb; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #6b7280; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>📊 Training Results</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$userName},</h2>
                    <p>You've completed the <strong>{$moduleName}</strong> training module!</p>
                    <div class='score-box'>
                        <div style='font-size: 18px; color: #6b7280;'>Your Score</div>
                        <div class='score'>{$score}%</div>
                        <div style='color: {$scoreColor}; font-weight: bold;'>{$scoreMessage}</div>
                    </div>
                    <p><strong>Module:</strong> {$moduleName}</p>
                    <p><strong>Questions Answered:</strong> {$totalQuestions}</p>
                    <p style='text-align: center;'>
                        <a href='" . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'] . "/training.php' class='button'>View All Modules</a>
                    </p>
                    <p>Keep up the great work! Continue with other training modules to strengthen your security awareness.</p>
                    <p>Stay secure!<br><strong>The TrainMe Team</strong></p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from TrainMe Security Platform.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $textBody = "
Training Results - TrainMe

Hello {$userName},

You've completed the {$moduleName} training module!

Your Score: {$score}%
{$scoreMessage}

Module: {$moduleName}
Questions Answered: {$totalQuestions}

View all modules: " . (isset($_SERVER['HTTPS']) ? 'https' : 'http') . "://" . $_SERVER['HTTP_HOST'] . "/training.php

Keep up the great work! Continue with other training modules to strengthen your security awareness.

Stay secure!
The TrainMe Team
        ";
        
        return $this->sendEmail($userEmail, $subject, $htmlBody, $textBody);
    }
    
    /**
     * Send security incident report email
     */
    public function sendSecurityReport($userEmail, $userName, $reportDetails) {
        $subject = 'Security Incident Report - TrainMe';
        
        $htmlBody = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #dc2626 0%, #ef4444 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8fafc; padding: 30px; border-radius: 0 0 10px 10px; }
                .alert-box { background: #fee2e2; border-left: 4px solid #dc2626; padding: 15px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #6b7280; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🚨 Security Incident Report</h1>
                </div>
                <div class='content'>
                    <h2>Report Received</h2>
                    <p>Hello {$userName},</p>
                    <p>We have received your security incident report. Our security team will investigate this matter immediately.</p>
                    <div class='alert-box'>
                        <strong>Your Report:</strong><br>
                        " . nl2br(htmlspecialchars($reportDetails)) . "
                    </div>
                    <p><strong>What happens next?</strong></p>
                    <ul>
                        <li>Our security team will review your report</li>
                        <li>We will investigate the incident</li>
                        <li>You will be contacted if we need additional information</li>
                        <li>We will take appropriate action to protect our systems</li>
                    </ul>
                    <p>Thank you for reporting this incident. Your vigilance helps keep everyone safe.</p>
                    <p>If you have any additional information, please reply to this email.</p>
                    <p>Stay secure!<br><strong>The TrainMe Security Team</strong></p>
                </div>
                <div class='footer'>
                    <p>This is an automated confirmation from TrainMe Security Platform.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $textBody = "
Security Incident Report - TrainMe

Hello {$userName},

We have received your security incident report. Our security team will investigate this matter immediately.

Your Report:
{$reportDetails}

What happens next?
- Our security team will review your report
- We will investigate the incident
- You will be contacted if we need additional information
- We will take appropriate action to protect our systems

Thank you for reporting this incident. Your vigilance helps keep everyone safe.

If you have any additional information, please reply to this email.

Stay secure!
The TrainMe Security Team
        ";
        
        // Send confirmation to user
        $this->sendEmail($userEmail, $subject, $htmlBody, $textBody);
        
        // Also send notification to security team
        $securitySubject = "Security Incident Report from {$userName}";
        $securityBody = "
        <h2>New Security Incident Report</h2>
        <p><strong>Reporter:</strong> {$userName} ({$userEmail})</p>
        <p><strong>Report Details:</strong></p>
        <div style='background: #fee2e2; padding: 15px; border-left: 4px solid #dc2626; margin: 20px 0;'>
            " . nl2br(htmlspecialchars($reportDetails)) . "
        </div>
        <p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>
        ";
        
        return $this->sendEmail($this->from_email, $securitySubject, $securityBody, strip_tags($securityBody));
    }
    
    /**
     * Send security question/help request email
     */
    public function sendSecurityQuestion($userEmail, $userName, $question) {
        $subject = 'Security Question - TrainMe';
        
        $htmlBody = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8fafc; padding: 30px; border-radius: 0 0 10px 10px; }
                .question-box { background: white; border-left: 4px solid #2563eb; padding: 15px; margin: 20px 0; }
                .footer { text-align: center; margin-top: 30px; color: #6b7280; font-size: 12px; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>💬 Security Question Received</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$userName},</h2>
                    <p>Thank you for reaching out to our security team. We have received your question and will respond as soon as possible.</p>
                    <div class='question-box'>
                        <strong>Your Question:</strong><br>
                        " . nl2br(htmlspecialchars($question)) . "
                    </div>
                    <p>Our security team typically responds within 24 hours. If your question is urgent, please mark it as such in your follow-up.</p>
                    <p>Stay secure!<br><strong>The TrainMe Security Team</strong></p>
                </div>
                <div class='footer'>
                    <p>This is an automated confirmation from TrainMe Security Platform.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $textBody = "
Security Question - TrainMe

Hello {$userName},

Thank you for reaching out to our security team. We have received your question and will respond as soon as possible.

Your Question:
{$question}

Our security team typically responds within 24 hours. If your question is urgent, please mark it as such in your follow-up.

Stay secure!
The TrainMe Security Team
        ";
        
        // Send confirmation to user
        $this->sendEmail($userEmail, $subject, $htmlBody, $textBody);
        
        // Also send notification to security team
        $securitySubject = "Security Question from {$userName}";
        $securityBody = "
        <h2>New Security Question</h2>
        <p><strong>From:</strong> {$userName} ({$userEmail})</p>
        <p><strong>Question:</strong></p>
        <div style='background: #eff6ff; padding: 15px; border-left: 4px solid #2563eb; margin: 20px 0;'>
            " . nl2br(htmlspecialchars($question)) . "
        </div>
        <p><strong>Time:</strong> " . date('Y-m-d H:i:s') . "</p>
        ";
        
        return $this->sendEmail($this->from_email, $securitySubject, $securityBody, strip_tags($securityBody));
    }
    
    /**
     * Send campaign email (phishing simulation)
     * Note: For Gmail, the From address must match the authenticated account
     * We'll use the authenticated email but set Reply-To to the campaign sender
     */
    public function sendCampaignEmail($userEmail, $subject, $htmlBody, $senderName = 'Security Team', $senderEmail = 'noreply@trainme.com') {
        // For Gmail, From address must match authenticated account (ststicket2525@gmail.com)
        // But we can use Reply-To to show the campaign sender
        $originalFromEmail = $this->from_email;
        $originalFromName = $this->from_name;
        
        // Use authenticated email as From, but use campaign sender name
        // Gmail requires From to match authenticated account
        $this->from_email = 'ststicket2525@gmail.com'; // Must match SMTP username
        $this->from_name = $senderName; // Can use campaign sender name
        
        // Create text version from HTML
        $textBody = strip_tags($htmlBody);
        
        // Send email with custom Reply-To
        $result = $this->sendEmailWithReplyTo($userEmail, $subject, $htmlBody, $textBody, $senderEmail);
        
        // Restore original sender info
        $this->from_email = $originalFromEmail;
        $this->from_name = $originalFromName;
        
        return $result;
    }
    
    /**
     * Send password reset email
     * @param string $userEmail User's email address
     * @param string $userName User's name
     * @param string $resetToken Password reset token
     */
    public function sendPasswordResetEmail($userEmail, $userName, $resetToken) {
        $subject = 'Reset Your TrainMe Password';
        
        // Generate reset link
        $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $resetLink = $protocol . '://' . $host . '/reset_password.php?token=' . urlencode($resetToken);
        
        $htmlBody = "
        <!DOCTYPE html>
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #2563eb 0%, #3b82f6 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f8fafc; padding: 30px; border-radius: 0 0 10px 10px; }
                .button { display: inline-block; background: #2563eb; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; margin: 20px 0; }
                .warning-box { background: #fef3c7; border-left: 4px solid #f59e0b; padding: 1rem; margin: 20px 0; border-radius: 4px; }
                .footer { text-align: center; margin-top: 30px; color: #6b7280; font-size: 12px; }
                .token-box { background: #f3f4f6; padding: 1rem; border-radius: 4px; margin: 15px 0; font-family: monospace; word-break: break-all; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🔐 Password Reset Request</h1>
                </div>
                <div class='content'>
                    <h2>Hello {$userName},</h2>
                    <p>We received a request to reset your password for your TrainMe account.</p>
                    <p style='text-align: center;'>
                        <a href='{$resetLink}' class='button'>Reset My Password</a>
                    </p>
                    <p>Or copy and paste this link into your browser:</p>
                    <div class='token-box'>{$resetLink}</div>
                    <div class='warning-box'>
                        <p style='margin: 0; color: #92400e;'><strong>⚠️ Security Notice:</strong></p>
                        <ul style='margin: 0.5rem 0 0 0; padding-left: 1.5rem; color: #78350f;'>
                            <li>This link will expire in 1 hour</li>
                            <li>If you didn't request this, please ignore this email</li>
                            <li>Never share this link with anyone</li>
                        </ul>
                    </div>
                    <p>If you have any questions or concerns, please contact our security team.</p>
                    <p>Stay secure!<br><strong>The TrainMe Team</strong></p>
                </div>
                <div class='footer'>
                    <p>This is an automated message from TrainMe Security Platform.</p>
                </div>
            </div>
        </body>
        </html>
        ";
        
        $textBody = "
Password Reset Request - TrainMe

Hello {$userName},

We received a request to reset your password for your TrainMe account.

Click this link to reset your password:
{$resetLink}

⚠️ Security Notice:
- This link will expire in 1 hour
- If you didn't request this, please ignore this email
- Never share this link with anyone

If you have any questions or concerns, please contact our security team.

Stay secure!
The TrainMe Team
        ";
        
        return $this->sendEmail($userEmail, $subject, $htmlBody, $textBody);
    }
    
    /**
     * Send email with custom Reply-To address
     */
    private function sendEmailWithReplyTo($to, $subject, $htmlBody, $textBody = '', $replyToEmail = '') {
        try {
            // Validate email address
            if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
                error_log("Invalid email address: $to");
                return ['success' => false, 'message' => 'Invalid email address'];
            }
            
            // Check if SMTP password is configured
            if (empty($this->smtp_password)) {
                $errorMsg = "SMTP password is not configured. Please set it in smtp_config.php";
                error_log($errorMsg);
                return ['success' => false, 'message' => $errorMsg];
            }
            
            $mail = $this->initMailer();
            
            if ($mail !== null) {
                // Using PHPMailer
                try {
                    $mail->addAddress($to);
                    
                    // Set Reply-To if provided
                    if (!empty($replyToEmail) && filter_var($replyToEmail, FILTER_VALIDATE_EMAIL)) {
                        $mail->addReplyTo($replyToEmail, $this->from_name);
                    }
                    
                    $mail->isHTML(true);
                    $mail->Subject = $subject;
                    $mail->Body = $htmlBody;
                    $mail->AltBody = $textBody ?: strip_tags($htmlBody);
                    
                    $mail->send();
                    error_log("Email sent successfully to: $to (From: {$this->from_email}, Reply-To: $replyToEmail)");
                    return ['success' => true, 'message' => 'Email sent successfully'];
                } catch (\PHPMailer\PHPMailer\Exception $e) {
                    $errorMsg = "PHPMailer Error: " . $e->getMessage() . " | ErrorInfo: " . $mail->ErrorInfo;
                    error_log($errorMsg);
                    
                    // Provide more helpful error messages for common issues
                    $userMessage = 'Email sending failed: ' . $e->getMessage();
                    if (strpos($e->getMessage(), 'SMTP connect()') !== false || strpos($e->getMessage(), 'SMTP authentication') !== false) {
                        $userMessage = 'Email sending failed: SMTP authentication error. Please verify your Gmail App Password is correct and not expired.';
                    } elseif (strpos($e->getMessage(), 'password') !== false) {
                        $userMessage = 'Email sending failed: Invalid SMTP password. Please check your Gmail App Password in smtp_config.php.';
                    }
                    
                    return ['success' => false, 'message' => $userMessage];
                }
            } else {
                // PHPMailer not available - provide helpful error message
                $errorMsg = "PHPMailer is not installed. Please install it with: composer require phpmailer/phpmailer. PHP mail() fallback is not reliable.";
                error_log($errorMsg);
                return ['success' => false, 'message' => 'Email service is not properly configured. PHPMailer is required for SMTP email sending.'];
            }
        } catch (\Exception $e) {
            $errorMsg = "Email sending error: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString();
            error_log($errorMsg);
            return ['success' => false, 'message' => 'Email sending failed: ' . $e->getMessage()];
        } catch (\Throwable $e) {
            $errorMsg = "Email sending fatal error: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString();
            error_log($errorMsg);
            return ['success' => false, 'message' => 'Email sending failed'];
        }
    }
    
    /**
     * Core email sending function
     */
    private function sendEmail($to, $subject, $htmlBody, $textBody = '') {
        try {
            // Validate email address
            if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
                error_log("Invalid email address: $to");
                return ['success' => false, 'message' => 'Invalid email address'];
            }
            
            // Check if SMTP password is configured
            if (empty($this->smtp_password)) {
                $errorMsg = "SMTP password is not configured. Please set it in smtp_config.php";
                error_log($errorMsg);
                return ['success' => false, 'message' => $errorMsg];
            }
            
            $mail = $this->initMailer();
            
            if ($mail !== null) {
                // Using PHPMailer
                try {
                    $mail->addAddress($to);
                    $mail->isHTML(true);
                    $mail->Subject = $subject;
                    $mail->Body = $htmlBody;
                    $mail->AltBody = $textBody ?: strip_tags($htmlBody);
                    
                    $mail->send();
                    error_log("Email sent successfully to: $to");
                    return ['success' => true, 'message' => 'Email sent successfully'];
                } catch (\PHPMailer\PHPMailer\Exception $e) {
                    $errorMsg = "PHPMailer Error: " . $e->getMessage() . " | ErrorInfo: " . $mail->ErrorInfo;
                    error_log($errorMsg);
                    
                    // Provide more helpful error messages for common issues
                    $userMessage = 'Email sending failed: ' . $e->getMessage();
                    if (strpos($e->getMessage(), 'SMTP connect()') !== false || strpos($e->getMessage(), 'SMTP authentication') !== false) {
                        $userMessage = 'Email sending failed: SMTP authentication error. Please verify your Gmail App Password is correct and not expired.';
                    } elseif (strpos($e->getMessage(), 'password') !== false) {
                        $userMessage = 'Email sending failed: Invalid SMTP password. Please check your Gmail App Password in smtp_config.php.';
                    }
                    
                    return ['success' => false, 'message' => $userMessage];
                }
            } else {
                // PHPMailer not available - provide helpful error message
                $errorMsg = "PHPMailer is not installed. Please install it with: composer require phpmailer/phpmailer. PHP mail() fallback is not reliable.";
                error_log($errorMsg);
                return ['success' => false, 'message' => 'Email service is not properly configured. PHPMailer is required for SMTP email sending.'];
            }
        } catch (\Exception $e) {
            $errorMsg = "Email sending error: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString();
            error_log($errorMsg);
            return ['success' => false, 'message' => 'Email sending failed: ' . $e->getMessage()];
        } catch (\Throwable $e) {
            $errorMsg = "Email sending fatal error: " . $e->getMessage() . " | Trace: " . $e->getTraceAsString();
            error_log($errorMsg);
            return ['success' => false, 'message' => 'Email sending failed'];
        }
    }
}