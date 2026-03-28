<?php

require_once __DIR__ . '/../config/functions.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
    
    $parts = explode(':', $_COOKIE['remember_me']);

    if (count($parts) === 2) {
        $selector = $parts[0];
        $validator = $parts[1];

        require_once BASE_PATH . 'config/db.php'; 

        $stmt = $pdo->prepare("
            SELECT user_id, hashed_validator 
            FROM user_tokens 
            WHERE selector = ? AND expiry > NOW() 
            LIMIT 1
        ");
        $stmt->execute([$selector]);
        $tokenRow = $stmt->fetch();

        if ($tokenRow && password_verify($validator, $tokenRow['hashed_validator'])) {

            $stmt = $pdo->prepare("SELECT id, username, role FROM users WHERE id = ? LIMIT 1");
            $stmt->execute([$tokenRow['user_id']]);
            $user = $stmt->fetch();

            if ($user) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['role'] = $user['role'];
            
                session_regenerate_id(true);
            }
        } else {
            setcookie('remember_me', '', time() - 3600, '/', '', true, true);
        }
    }
}
?>