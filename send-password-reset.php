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

require_once 'data/user.php';
$user = new User;
if ($user->resetToken($email, $tokenHash, $expiry)) {
    require_once 'data/mailer.php';
    $mailer = new Mailer;
    if ($mailer->sendResetPassword($email, $tokenHash)) {
        echo 'Message sent. Please check your inbox.';
    } else {
        echo "The message could not be send. Mailer error: {$mailer->lastErrorMessage}.";
    }
}