<?php
session_start();
require_once './../../config/db.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
        SELECT b.*, 
               CASE WHEN b.canceled_by IS NOT NULL THEN u.role ELSE NULL END as canceled_by_role
        FROM bookings b
        LEFT JOIN users u ON b.canceled_by = u.id
        WHERE b.user_id = ? AND b.deleted_at IS NULL 
        ORDER BY b.created_at DESC
    ");
    $stmt->execute([$user_id]);
    $bookings = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($bookings);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>