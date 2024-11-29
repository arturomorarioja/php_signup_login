<?php

$token = $_POST['token'] ?? '';
if ($token === '') {
    echo 'Token not received';
    exit;
}

$newPassword = $_POST['new-password'] ?? '';
if ($newPassword === '') {
    echo 'Password not received';
    exit;
}

require_once 'db/user.php';
$user = new User;
$userID = $user->validateToken($token);
if (!$userID) {
    echo $user->lastErrorMessage;
    exit;
}

if (empty($_POST['new-password'])) { die('Password is required'); }
if (empty($_POST['repeat-new-password'])) { die('Repeat password is required'); }

if (strlen($_POST['new-password']) < 8) {
    die('Password must be at least 8 characters');
}

if (!preg_match('/[a-z]/i', $_POST['new-password'])) {
    die('Password must contain at least one letter');
}

if (!preg_match('/[0-9]/', $_POST['new-password'])) {
    die('Password must contain at least one letter');
}

if ($_POST['new-password'] !== $_POST['repeat-new-password']) {
    die('Passwords must have the same value');
}

if ($user->resetPassword($userID, $newPassword)) {
    echo 'Password successfully reset';
} else {
    echo 'There was a problem while resetting the password';
}