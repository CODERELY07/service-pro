<?php
    session_start();
    require_once __DIR__ . '/../config/db.php';
    header('Content-Type: application/json');

    $data = json_decode(file_get_contents('php://input'), true);
    $id = $data['id'] ?? null;
    $uid = $_SESSION['user_id'];

    $stmt = $pdo->prepare("
        SELECT a.new_value as status, a.created_at 
        FROM audit_logs a 
        JOIN bookings b ON a.booking_id = b.id
        WHERE a.booking_id = ? AND b.user_id = ?
        ORDER BY a.created_at DESC
    ");
    
    $stmt->execute([$id, $uid]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));