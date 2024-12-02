<?php

// Showing this page is not allowed if a user is already logged in.
// This measure prevents security attacks (e.g., accessing this URL directly)
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$errorMessage = '';
$token = $_GET['token'] ?? '';

if ($token === '') {
    $errorMessage = 'User token not received';
} else {
    require_once 'data/user.php';
    $user = new User;
    if (!$user->validateAccountActivationToken($token)) {
        $errorMessage = $user->lastErrorMessage;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Account Activation</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <h1>Account Activation</h1>
    </header>
    <main>
        <?php if ($errorMessage !== ''): ?>
            <section class="error">
                <p><?=$errorMessage; ?></p>
                <p>You can try to <a href="signup.php" title="Sign up">sign up</a> again.</p>
            </section>
        <?php else: ?>            
            <section>
                <p>Your account has been successfully activated. You can now <a href="login.php" title="Log in">log in</a>.</p>
            </section>
        <?php endif; ?>
    </main>
</body>
</html>