<?php
// Disable displaying errors to the user/browser
ini_set('display_errors', 0);

// Enable error logging
ini_set('log_errors', 1);

// Set the path where the log file will be saved
ini_set('error_log', __DIR__ . '/php-error.log');

    $host = '127.0.0.1';
    $port = '3307';
    $db   = 'service-pro';
    $user = 'root';
    $pass = ''; 

    $dsn = "mysql:host=$host;port=$port;dbname=$db";

    try {
        $pdo = new PDO($dsn, $user, $pass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        // echo "Success! You are connected to the database.";
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
?>