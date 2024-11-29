<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require __DIR__ . '/vendor/autoload.php';

$mail = new PHPMailer(true);    // true activates exceptions
$mail->SMTPDebug = SMTP::DEBUG_SERVER;  // Remove in production

$mail->isSMTP();    // Use the configuration below instead of the local mail server
$mail->SMTPAuth = true;

$mail->Host = 'smtp.mailersend.net';
$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
$mail->Port = 587;
$mail->Username = 'MS_WwZvxS@trial-ynrw7gyz7mn42k8e.mlsender.net';
$mail->Password = 'gFce5S0i6gjeyONz';

$mail->isHtml(true);    // Allows for HTML content in emails

return $mail;