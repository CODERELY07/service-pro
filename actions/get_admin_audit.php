<?php
    session_start();
    require_once __DIR__ . '/../config/db.php';
    header('Content-Type: application/json');

    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;

    $stmt = $pdo->prepare("
        SELECT a.*, u.username 
        FROM audit_logs a 
        JOIN users u ON a.user_id = u.id 
        WHERE a.booking_id = ? 
        ORDER BY a.created_at DESC
    ");

    $stmt->execute([$id]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));