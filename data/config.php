<?php

class Config
{
    // Database credentials
    const DB_HOST = 'localhost';
    const DB_NAME = 'login_db';
    const DB_USER = 'root';
    const DB_PASSWORD = '';

    // Mailer class configuration
    const ACCOUNT_ACTIVATION_TARGET = '/activate-account.php';
    const PWD_RESET_TARGET = '/reset-password.php';

    /**
     * Reads the environment variables from .env into $_ENV
     * 
     * Reused from Walter Nascimento:
     * https://dev.to/walternascimentobarroso/dotenv-in-php-45mn
     */
    public function __construct()
    {
        $lines = file('.env');
        foreach ($lines as $line) {
            [$key, $value] = explode('=', $line, 2);
            $_ENV[$key] = trim((string)$value);
        }
    }
}