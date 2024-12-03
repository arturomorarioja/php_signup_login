<?php

require_once 'config.php';

abstract class Database
{
    public PDO $conn;
    public string $lastErrorMessage = '';

    public function __construct()
    {
        try {
            $dsn = 'mysql:host=' . Config::DB_HOST .
                ';dbname=' . Config::DB_NAME .
                ';charset=utf8';
            $db = new PDO($dsn, Config::DB_USER, Config::DB_PASSWORD);
            
            // Throw an exception when an error occurs
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn = $db;
        } catch (PDOException $e) {
            $this->lastErrorMessage = "Error <strong>{$e->getMessage()}</strong> in model " . get_called_class();
        }
    }
}
