<?php

if (empty($_POST['name'])) { die('Name is required'); }
if (empty($_POST['email'])) { die('Email is required'); }
if (empty($_POST['password'])) { die('Password is required'); }
if (empty($_POST['repeat-password'])) { die('Repeat password is required'); }

if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    die('Valid email is required');
}

if (strlen($_POST['password']) < 8) {
    die('Password must be at least 8 characters');
}

if (!preg_match('/[a-z]/i', $_POST['password'])) {
    die('Password must contain at least one letter');
}

if (!preg_match('/[0-9]/', $_POST['password'])) {
    die('Password must contain at least one letter');
}

if ($_POST['password'] !== $_POST['repeat-password']) {
    die('Passwords must have the same value');
}

require __DIR__ . '/data/user.php';
$user = new User();
$newUserID = $user->add($_POST['name'], $_POST['email'], $_POST['password']);

if ($newUserID === -1) {
    echo $user->lastErrorMessage;
} else {
    header('Location: signup-success.html');
    exit();
}