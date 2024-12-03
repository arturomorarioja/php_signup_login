<?php

$success = false;
$errorMessage = '';

// If the request method is GET, the page has been called from a link.
// If it is POST, if has been called from its own form submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {    
    $email = trim($_POST['email'] ?? '');
    
    if ($email === '') {
        $errorMessage = 'No email address has been provided';
    } else {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errorMessage = 'Valid email is required';
        } else {           
            require_once 'data/user.php';
            $user = new User;
            $tokenHash = $user->resetPasswordResetToken($email);
            if ($tokenHash) {
                require_once 'data/mailer.php';
                $mailer = new Mailer;
                if ($mailer->sendResetPassword($email, $tokenHash)) {
                    $success = true;
                } else {
                    $errorMessage = "The message could not be sent. Mailer error: {$mailer->lastErrorMessage}.";
                }
            } else {
                $errorMessage = $user->lastErrorMessage;
            }
        }
    }
}
$headerText = 'Forgot Password';
include 'views/header.php';
    
?>
    <main>
        <?php if ($success): ?>
            <section>
                <h2>Password reset link sent</h2>
                <p>Please check your email inbox</p>
            </section>
        <?php else: ?>
            <?php if ($errorMessage): ?>
                <section class="error">
                    <p><?=$errorMessage; ?></p>
                </section>
            <?php endif; ?>
            <form method="POST" action="forgot-password.php" novalidate>
                <div>
                    <label for="txtEmail">Email</label>
                    <input type="email" id="txtEmail" name="email">
                </div>
                <div>
                    <input type="submit" value="Send">
                </div>
            </form>
        <?php endif; ?>
    </main>
<?php
include 'views/footer.php';
?>