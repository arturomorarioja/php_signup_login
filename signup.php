<?php

// Signing up is not allowed if a user is already logged in.
// This measure prevents security attacks (e.g., accessing this URL directly)
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$success = false;
$errorMessages = [];

// If the request method is GET, the page has been called from a link.
// If it is POST, if has been called from its own form submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $repeatPassword = trim($_POST['repeat-password'] ?? '');

    if (empty($name)) { $errorMessages[] = 'Name is required'; }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errorMessages[] = 'Valid email is required';
    }
    if (strlen($password) < 8) {
        $errorMessages[] = 'Password must be at least 8 characters';
    }
    
    if (!preg_match('/[a-z]/i', $password)) {
        $errorMessages[] = 'Password must contain at least one letter';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        $errorMessages[] = 'Password must contain at least one number';
    }
    
    if ($password !== $repeatPassword) {
        $errorMessages[] = 'Passwords must have the same value';
    }
    
    if ($errorMessages === []) {
        require_once 'data/user.php';
        $user = new User;
        $accountActivationHash = $user->add($name, $email, $password);
        
        if (!$accountActivationHash) {
            $errorMessages[] = $user->lastErrorMessage;
        } else {
            require_once 'data/mailer.php';
            $mailer = new Mailer;
            if (!$mailer->sendAccountActivation($email, $accountActivationHash)) {
                $errorMessages[] = $mailer->lastErrorMessage;
            } else {
                $success = true;
            }
        }
    }
}
$headerText = 'Signup';
include 'views/header.php';

?>
    <main>
        <?php if ($success): ?>
            <section>
                <h2>Signup successful</h2>
                <p>Please check your email to activate your account.</p>
            </section>
        <?php else: ?>
            <?php if ($errorMessages): ?>
                <section class="error">
                    <?php foreach ($errorMessages as $errorMessage): ?>
                        <p><?=$errorMessage; ?></p>
                    <?php endforeach; ?>
                </section>
            <?php endif; ?>
            <form action="signup.php" method="POST" novalidate>
                <div>
                    <label for="txtName">Name</label>
                    <input type="text" id="txtName" name="name" required
                        value="<?=htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>
                <div>
                    <label for="txtEmail">Email</label>
                    <input type="text" id="txtEmail" name="email" required
                        value="<?=htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                <div>
                    <label for="txtPassword">Password</label>
                    <input type="password" id="txtPassword" name="password" required>
                    </div>
                <div>
                    <label for="txtRepeatPassword">RepeatPassword</label>
                    <input type="password" id="txtRepeatPassword" name="repeat-password" required>
                </div>
                <div>
                    <input type="submit" value="Sign up">
                </div>
            </form>
        <?php endif; ?>
    </main>
<?php
include 'views/footer.php';
?>