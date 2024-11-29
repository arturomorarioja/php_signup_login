<?php

$token = $_GET['token'] ?? '';

if ($token !== '') {
    require_once 'db/user.php';
    $user = new User;
    if ($user->validateToken($token)) {
        echo 'Token found!';
    } else {
        echo $user->lastErrorMessage;
    }
} else {
    echo 'Token not received';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
</head>
<body>
    <header>
        <h1>Reset Password</h1>
    </header>    
    <main>
        <form method="POST" action="process-reset-password.php">
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
    </main>
</body>
</html>