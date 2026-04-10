<?php
    require_once __DIR__ . '/config.php';
    // die(__DIR__ . 'config/');
    $page_scripts = [];

    function add_script($path) {
        global $page_scripts;
        $page_scripts[] = $path;
    }

    function create_notification($user_id, $title, $message, $type = 'info', $booking_id = null) {
        global $pdo;
        
        // If $pdo is not set, try to require db.php
        if (!isset($pdo)) {
            require_once BASE_PATH . 'config/db.php';
        }

        // If still not set, cannot proceed
        if (!isset($pdo)) {
            error_log("Failed to create notification: PDO connection not available");
            return false;
        }

        try {
            $stmt = $pdo->prepare("
                INSERT INTO notifications (user_id, booking_id, title, message, type)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$user_id, $booking_id, $title, $message, $type]);
            return $pdo->lastInsertId();
        } catch (PDOException $e) {
            error_log("Failed to create notification: " . $e->getMessage());
            return false;
        }
    }

?>