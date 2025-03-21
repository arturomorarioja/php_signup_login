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
        // $mailer->SMTPDebug = SMTP::DEBUG_SERVER;    // Comment in production
        
        $mailer->isSMTP();    // Use the configuration below instead of the local mail server
        $mailer->SMTPAuth = true;
        
        // Upon instancing Config, .env is read into $_ENV
        $config = new Config();

        $mailer->Host = $_ENV['MAILER_HOST'];
        $mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mailer->Port = $_ENV['MAILER_PORT'];
        $mailer->Username = $_ENV['MAILER_USERNAME'];
        $mailer->Password = $_ENV['MAILER_PASSWORD'];
        
        $mailer->isHtml(true);    // Allows for HTML content in emails
        
        $this->mailer = $mailer;
    }

    /**
     * Sends an account activation link to a user
     * 
     * @param $email The recipient's email address
     * @param $token The reset token
     * @return 1 if successfull, 0 if an error happens,
     *         whose message will be logged into $this->lastErrorMessage
     */
    public function sendAccountActivation(string $email, string $token): int
    {
        return $this->sendEmail(
            $email, $token, 
            'Account Activation', Config::ACCOUNT_ACTIVATION_TARGET, 
            'Activate account', 'activate your account'
        );
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
        return $this->sendEmail(
            $email, $token, 
            'Password Reset', Config::PWD_RESET_TARGET, 
            'Reset password', 'reset your password'
        );
    }

    /**
     * Sends an email including a link with a token to a user
     * 
     * @param $email            The recipient's email address
     * @param $token            The reset token
     * @param $subject          The email's subject
     * @param $link             The local part of the link's URL
     * @param $title            The title of the link
     * @param $actionMessage    The explanatory part of the text to be displayed
     * @return 1 if successfull, 0 if an error happens,
     *         whose message will be logged into $this->lastErrorMessage
     */
    private function sendEmail(string $email, string $token, string $subject, 
        string $link, string $title, string $actionMessage): int
    {
        $target = $_ENV['APP_BASE_URL'] . $link;

        $this->mailer->setFrom($_ENV['MAILER_USERNAME']);
        $this->mailer->addAddress($email);
        $this->mailer->Subject = $subject;
        $this->mailer->Body =<<<MAIL
        <h1>$subject</h1>
        <p>
            Click <a href="{$target}?token=$token" title="$title">here</a> to $actionMessage.
        </p>    
        MAIL;
    
        try {
            $this->mailer->send();
            return 1;
        } catch (Exception $e) {
            $this->lastErrorMessage = "The message could not be sent. Mailer error: {$e->errorMessage()}.";
            return 0;
        }    
    }
}