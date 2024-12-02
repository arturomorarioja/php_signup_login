<?php

/**
 * Mailer class
 * 
 * Manages sending emails to users
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require dirname(__DIR__) . '../vendor/autoload.php';

require_once 'config.php';

class Mailer
{
    public PHPMailer $mailer;
    public string $lastErrorMessage = '';

    public function __construct()
    {       
        $mailer = new PHPMailer(true);              // true activates exceptions
        // $mailer->SMTPDebug = SMTP::DEBUG_SERVER;    // Remove in production
        
        $mailer->isSMTP();    // Use the configuration below instead of the local mail server
        $mailer->SMTPAuth = true;
        
        $mailer->Host = Config::MAILER_HOST;
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->Port = Config::MAILER_PORT;
        $mailer->Username = Config::MAILER_USERNAME;
        $mailer->Password = config::MAILER_PASSWORD;
        
        $mailer->isHtml(true);    // Allows for HTML content in emails
        
        $this->mailer = $mailer;
    }

    /**
     * Sends a reset password link to a user
     * 
     * @param $email The recipient's email address
     * @param $token The reset token
     * @return 1 if successfull, 0 if an error happens
     */
    public function sendResetPassword(string $email, string $token): int
    {
        $this->mailer->setFrom(Config::MAILER_USERNAME);
        $this->mailer->addAddress($email);
        $this->mailer->Subject = 'Password Reset';
        $this->mailer->Body =<<<MAIL
        <h1>Password Reset</h1>
        <p>
            Click <a href="http://localhost/php_signup_login/reset-password.php?token=$token" title="Reset password">here</a> to reset your password.
        </p>    
        MAIL;
    
        try {
            $this->mailer->send();
            return 1;
        } catch (Exception $e) {
            $this->lastErrorMessage = "The message could not be send. Mailer error: {$e->errorMessage()}.";
            return 0;
        }    
    }
}