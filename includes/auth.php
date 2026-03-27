<?php
    session_start();
    require_once __DIR__ . '/../config/db.php'; 

    if (!isset($_SESSION['user_id']) && isset($_COOKIE['remember_me'])) {
        $parts = explode(':', $_COOKIE['remember_me']);
        if (count($parts) === 2) {
            $selector = $parts[0];
            $validator = $parts[1];

      
            $stmt = $pdo->prepare("SELECT * FROM user_tokens WHERE selector = ? AND expiry > NOW()");
            $stmt->execute([$selector]);
            $tokenRow = $stmt->fetch();

            if ($tokenRow && password_verify($validator, $tokenRow['hashed_validator'])) {

                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$tokenRow['user_id']]);
                $user = $stmt->fetch();

                if ($user) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['username'] = $user['username'];
                    $_SESSION['role'] = $user['role'];
                }
            }
        }
    }
?>