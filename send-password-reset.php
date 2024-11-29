<?php

$email = $_POST['email'] ?? '';

// random_bytes() generates a cryptographically secure sequence 
//      of bytes with the length it receives as a parameter
// bin2hex() converts binary data into its hexadecimal representation
$token = bin2hex(random_bytes(16));
// hash() generates a hash value, in this case using the sha256 algorithm
$tokenHash = hash('sha256', $token);
// As the token could be figured out via a brute force attack,
//      it is set to expire in 30 minutes
$expiry = date('Y-m-d H:i:s', time() + (60 * 30));

require_once 'db/user.php';
$user = new User;
if ($user->resetToken($email, $tokenHash, $expiry)) {
    $mail = require __DIR__ . '/mailer.php';

    $mail->setFrom('MS_WwZvxS@trial-ynrw7gyz7mn42k8e.mlsender.net');
    $mail->addAddress($email);
    $mail->Subject = 'Password Reset';
    $mail->Body =<<<MAIL
    
    Click <a href="http://localhost/php_signup_login/reset-password.php?token=$token" title="Reset password">here</a> to reset your password

    MAIL;

    try {
        $mail->send();
        echo 'Message sent. Please check your inbox.';
    } catch (Exception $e) {
        echo "The message could not be send. Mailer error: {$mail->ErrorInfo}.";
    }
}