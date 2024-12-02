<?php

// Login is not allowed if a user is already logged in.
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

    if (empty($_POST['name'])) { $errorMessages[] = 'Name is required'; }
    if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $errorMessages[] = 'Valid email is required';
    }
    if (strlen($_POST['password']) < 8) {
        $errorMessages[] = 'Password must be at least 8 characters';
    }
    
    if (!preg_match('/[a-z]/i', $_POST['password'])) {
        $errorMessages[] = 'Password must contain at least one letter';
    }
    
    if (!preg_match('/[0-9]/', $_POST['password'])) {
        $errorMessages[] = 'Password must contain at least one number';
    }
    
    if ($_POST['password'] !== $_POST['repeat-password']) {
        $errorMessages[] = 'Passwords must have the same value';
    }
    
    if ($errorMessages === []) {
        require 'data/user.php';
        $user = new User();
        $newUserID = $user->add($_POST['name'], $_POST['email'], $_POST['password']);
        
        if (!$newUserID) {
            $errorMessages[] = $user->lastErrorMessage;
        } else {
            $success = true;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signup</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <h1>Signup</h1>
    </header>
    <main>
        <?php if ($success): ?>
            <section>
                <h2>Signup successful</h2>
                <p>You can now <a href="login.php" title="Log in">log in</a></p>
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
</body>
</html>