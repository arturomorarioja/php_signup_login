<?php

class Config
{
    // Application path
    const APP_BASE_URL = 'http://localhost/php_signup_login';

    // Database credentials
    const DB_HOST = 'localhost';
    const DB_NAME = 'login_db';
    const DB_USER = 'root';
    const DB_PASSWORD = '';

    // SMTP configuration from Mailersend
    const MAILER_HOST = 'smtp.mailersend.net';
    const MAILER_PORT = 587;
    const MAILER_USERNAME = 'MS_WwZvxS@trial-ynrw7gyz7mn42k8e.mlsender.net';
    const MAILER_PASSWORD = 'gFce5S0i6gjeyONz';

    // Mailer class configuration
    const ACCOUNT_ACTIVATION_TARGET = '/activate-account.php';
    const PWD_RESET_TARGET = '/reset-password.php';
}