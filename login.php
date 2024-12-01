<?php

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';
$userInfo = null;

// If the request method is GET, the page has been called from a link.
// If it is POST, if has been called from its own form submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && $email !== '' 
    && $password !== '') {

    require_once 'data/user.php';
    $user = new User;
    $userInfo = $user->validateLogin($email, $password);

    if ($userInfo) {
        session_start();
        // Generates a new session ID to prevent session fixation attacks
        session_regenerate_id();

        $_SESSION['user_id'] = $userInfo['user_id'];
        $_SESSION['username'] = $userInfo['name'];
        header('Location: index.php');
        exit;
    } else {
        $errorMessage = $user->lastErrorMessage;
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
    <link rel="stylesheet" href="css/styles.css">
</head>
<body>
    <header>
        <h1>Login</h1>
    </header>
    <main>
        <section class="error">
            <p><?= $errorMessage ?? ''; ?></p>
        </section>
        <form method="POST">
            <div>
                <label for="txtEmail">Email</label>
                <input type="email" id="txtEmail" name="email" 
                    value="<?=htmlspecialchars($email); ?>">
            </div>
            <div>
                <label for="txtPassword">Password</label>
                <input type="password" id="txtPassword" name="password">
            </div>
            <div>
                <input type="submit" value="Log in">
            </div>
        </form>
        <section>
            <p><a href="forgot-password.php" title="Forgot password?">Forgot password?</a></p>
        </section>
    </main>
</body>
</html>