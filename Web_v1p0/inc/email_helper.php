<?php
/**
 * Email Helper for DT Web 2.0
 * Uses Resend SDK for sending emails
 */

if (basename(__FILE__) == basename($_SERVER['PHP_SELF'])) {
    header("Location:../error.php");
    exit();
}

require_once __DIR__ . '/../vendor/autoload.php';

// Note: The Resend class is in the global namespace, not Resend\Resend
// The SDK declares class Resend in global namespace

/**
 * Send an email using Resend API
 * 
 * @param string $to Email address of recipient
 * @param string $toName Name of recipient
 * @param string $subject Email subject
 * @param string $body Email body (HTML)
 * @param string $altBody Plain text alternative body
 * @return array ['success' => bool, 'message' => string]
 */
function send_email($to, $toName, $subject, $body, $altBody = '') {
    global $option;
    
    // Check if email is enabled
    $email_env = getenv('EMAIL_ENABLED');
    $email_enabled = ($email_env === true || $email_env === 'true' || $email_env === '1' || $email_env === 1);
    
    if (!$email_enabled) {
        return [
            'success' => false,
            'message' => 'Email sending is disabled'
        ];
    }
    
    // Get API key
    $api_key = getenv('EMAIL_API_KEY');
    if (empty($api_key)) {
        return [
            'success' => false,
            'message' => 'Email API key is not configured'
        ];
    }
    
    // Get email settings
    // Use getenv directly to ensure Docker variables are used
    $from_email = getenv('EMAIL_FROM') ?: 'noreply@danangmu.com';
    $from_name = getenv('EMAIL_FROM_NAME') ?: 'MU Da Nang Support';

    try {
        $resend = Resend::client($api_key);
        
        // The Resend PHP SDK uses send() or create()
        $result = $resend->emails->send([
            'from' => $from_email,
            'to' => $to,
            'subject' => $subject,
            'html' => $body,
        ]);
        
        return [
            'success' => true,
            'message' => 'Email sent successfully',
            'id' => $result['id'] ?? ''
        ];
        
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => "Email could not be sent. Error: " . $e->getMessage()
        ];
    }
}

/**
 * Send registration confirmation email with account details
 * 
 * @param string $account Username
 * @param string $password Password (plain text)
 * @param string $email User's email address
 * @return array
 */
function send_registration_email($account, $password, $email) {
    global $option;
    
    $server_name = !empty($option['server_name']) ? $option['server_name'] : 'MU Online';
    $web_address = $option['web_address'];
    
    $subject = "Welcome to $server_name - Account Registration";
    
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color: #fff; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .credentials { background: #fff; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #e94560; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Welcome to $server_name</h1>
            </div>
            <div class='content'>
                <p>Thank you for registering! Your account has been created successfully.</p>
                
                <div class='credentials'>
                    <h3>Your Account Details:</h3>
                    <p><strong>Username:</strong> $account</p>
                    <p><strong>Password:</strong> $password</p>
                </div>
                
                <p>Please keep this information safe. You will need it to login to the game.</p>
                
                <p>Login at: <a href='$web_address'>$web_address</a></p>
            </div>
            <div class='footer'>
                <p>This is an automated message, please do not reply.</p>
                <p>&copy; $server_name</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return send_email($email, $account, $subject, $body);
}

/**
 * Send password reset email with new password
 * 
 * @param string $account Username
 * @param string $email User's email address
 * @param string $new_password New password
 * @return array
 */
function send_password_reset_email($account, $email, $new_password) {
    global $option;
    
    $server_name = !empty($option['server_name']) ? $option['server_name'] : 'MU Online';
    $web_address = $option['web_address'];
    
    $subject = "$server_name - Password Reset";
    
    $body = "
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); color: #fff; padding: 20px; text-align: center; }
            .content { padding: 20px; background: #f9f9f9; }
            .credentials { background: #fff; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #e94560; }
            .footer { text-align: center; padding: 20px; color: #666; font-size: 12px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>Password Reset</h1>
            </div>
            <div class='content'>
                <p>Hello <strong>$account</strong>,</p>
                
                <p>Your password has been reset. Here is your new login password:</p>
                
                <div class='credentials'>
                    <h3>New Password:</h3>
                    <p><strong>$new_password</strong></p>
                </div>
                
                <p>Please login with this password and change it immediately.</p>
                
                <p>Login at: <a href='$web_address'>$web_address</a></p>
            </div>
            <div class='footer'>
                <p>This is an automated message, please do not reply.</p>
                <p>&copy; $server_name</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    return send_email($email, $account, $subject, $body);
}
