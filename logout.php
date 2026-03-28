<?php

    require_once __DIR__ . '/config/functions.php';

    require_once BASE_PATH . 'config/db.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION = array();

    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }

    if (isset($_COOKIE['remember_me'])) {
        $parts = explode(':', $_COOKIE['remember_me']);
        $selector = $parts[0];

        $stmt = $pdo->prepare("DELETE FROM user_tokens WHERE selector = ?");
        $stmt->execute([$selector]);

        setcookie('remember_me', '', time() - 3600, '/');
    }

    session_unset();
    session_destroy();

    header("Location: index.php?msg=Logged out safely");
    exit();
?>