<?php

// Login is not allowed if a user is already logged in.
// This measure prevents security attacks (e.g., accessing this URL directly)
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$password = trim($_POST['password'] ?? '');
$userInfo = null;

// If the request method is GET, the page has been called from a link.
// If it is POST, if has been called from its own form submission.
if ($_SERVER['REQUEST_METHOD'] === 'POST'
    && $email !== '' 
    && $password !== '') {

    require_once 'data/user.php';
    $user = new User();
    $userInfo = $user->validateLogin($email, $password);

    if ($userInfo) {
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
$headerText = 'Login';
include 'views/header.php';

?>
    <main>
        <section class="error">
            <p><?= $errorMessage ?? ''; ?></p>
        </section>
        <form method="POST" novalidate>
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

<?php include 'views/footer.php'; ?>    
