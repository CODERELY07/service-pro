<?php
    require_once 'config.php';

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