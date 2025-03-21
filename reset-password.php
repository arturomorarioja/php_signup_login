<?php

// Resetting the password is not allowed if a user is already logged in.
// This measure prevents security attacks (e.g., accessing this URL directly)
session_start();
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

$success = false;
$errorMessages = [];
$showForm = true;

// The page has been requested via email
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $token = $_GET['token'] ?? '';
    
    if ($token === '') {
        $errorMessages[] = 'User token not received';
        // If a user token is not received, the form should not even be shown.
        // It could be a security attack.
        $showForm = false;  
    } else {
        require_once 'data/user.php';
        $user = new User();
        if (!$user->validatePasswordsToken($token)) {
            $errorMessages[] = $user->lastErrorMessage;
            // If the token is incorrect, the form should not even be shown.
            // It could be a security attack.
            $showForm = false; 
        }
    }
// The form has been sent
} else {
    $token = trim($_POST['token'] ?? '');
    if ($token === '') { $errorMessages[] = 'Token not received'; }
        
    // Although the token was validated when this page was loaded,
    // it is validated again, as a malicious user could tamper with 
    // the hidden input in the form
    require_once 'data/user.php';
    $user = new User();
    $userID = $user->validatePasswordsToken($token);
    if (!$userID) { $errorMessages[] = $user->lastErrorMessage; }

    $newPassword = $_POST['new-password'];
    $errorMessages = array_merge(
        $errorMessages, 
        User::validatePasswords($newPassword, $_POST['repeat-new-password'])
    );
    
    if ($errorMessages === []) {
        if ($user->resetPassword($userID, $newPassword)) {
            $success = true;
        } else {
            $errorMessages[] = $user->lastErrorMessage;
        }    
    }
}
$headerText = 'Reset Password';
include 'views/header.php';

?>
    <main>
        <?php if ($errorMessages !== []): ?>
            <section class="error">
                <?php foreach ($errorMessages as $errorMessage): ?>
                    <p><?=$errorMessage; ?></p>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
        <?php if ($success): ?>
            <section>
                <h2>Password reset successful</h2>
                <p>You can now <a href="login.php" title="Log in">log in</a></p>
            </section>
        <?php else: ?>
            <?php if ($showForm): ?>
                <form method="POST" action="reset-password.php" novalidate>
                    <input type="hidden" name="token" value="<?=htmlspecialchars($token) ?>">

                    <div>
                        <label for="txtNewPassword">New password</label>
                        <input type="password" id="txtNewPassword" name="new-password">
                    </div>
                    <div>
                        <label for="txtRepeatNewPassword">Repeat new password</label>
                        <input type="password" id="txtRepeatNewPassword" name="repeat-new-password">
                    </div>
                    <div>
                        <input type="submit" value="Send">
                    </div>
                </form>
            <?php endif; ?>
        <?php endif; ?>
    </main>

<?php include 'views/footer.php'; ?>