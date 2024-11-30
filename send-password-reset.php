<?php

$email = $_POST['email'] ?? '';

require_once 'data/user.php';
$user = new User;
$tokenHash = $user->resetToken($email);
if ($tokenHash) {
    require_once 'data/mailer.php';
    $mailer = new Mailer;
    if ($mailer->sendResetPassword($email, $tokenHash)) {
        echo 'Message sent. Please check your inbox.';
    } else {
        echo "The message could not be send. Mailer error: {$mailer->lastErrorMessage}.";
    }
}