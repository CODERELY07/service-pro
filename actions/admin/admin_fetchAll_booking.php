<?php
session_start();
    require_once __DIR__ . '/../../config/functions.php';
    require_once BASE_PATH . 'config/db.php';

// Security Check
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    $stmt = $pdo->prepare("
        SELECT b.*, u.username, u.email as user_email 
        FROM bookings b 
        JOIN users u ON b.user_id = u.id 
        ORDER BY b.created_at DESC
    ");
    $stmt->execute();
    $allBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $active = [];
    $completed = [];

    foreach ($allBookings as $booking) {
        if ($booking['status'] === 'Claimed') {
            $completed[] = $booking;
        } else {
            $active[] = $booking;
        }
    }

  
    $statsQuery = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'Pending' THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN status = 'In Progress' THEN 1 ELSE 0 END) as in_progress,
            SUM(CASE WHEN status = 'Claimed' THEN 1 ELSE 0 END) as completed
        FROM bookings
    ");
    $stats = $statsQuery->fetch(PDO::FETCH_ASSOC);

    echo json_encode([
        'active' => $active,
        'completed' => $completed,
        'stats' => [
            'total' => (int)$stats['total'],
            'pending' => (int)$stats['pending'],
            'in_progress' => (int)$stats['in_progress'],
            'completed' => (int)$stats['completed']
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>