<?php
    session_start();
    require_once __DIR__ . '/../../config/functions.php';
    require_once BASE_PATH . 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['error' => 'Unauthorized']);
    exit();
}

try {
    // Get active bookings (not completed)
    $stmt = $pdo->prepare("
        SELECT b.*, u.username, u.email as user_email
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        WHERE b.status IN ( 'Pending','In Progress','Ready' )
        ORDER BY b.created_at DESC
    ");
   
    $stmt->execute();
    $activeBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get completed bookings
    $stmt = $pdo->prepare("
        SELECT b.*, u.username, u.email as user_email
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        WHERE b.status = 'Claimed'
        ORDER BY b.created_at DESC
    ");
    $stmt->execute();
    $completedBookings = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get stats
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings");
    $total = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    $stmt = $pdo->query("SELECT COUNT(*) as pending FROM bookings WHERE status = 'Pending'");
    $pending = $stmt->fetch(PDO::FETCH_ASSOC)['pending'];

    $stmt = $pdo->query("SELECT COUNT(*) as in_progress FROM bookings WHERE status = 'In Progress'");
    $inProgress = $stmt->fetch(PDO::FETCH_ASSOC)['in_progress'];

    $stmt = $pdo->query("SELECT COUNT(*) as completed FROM bookings WHERE status = 'Claimed'");
    $completed = $stmt->fetch(PDO::FETCH_ASSOC)['completed'];

    echo json_encode([
        'active' => $activeBookings,
        'completed' => $completedBookings,
        'stats' => [
            'total' => $total,
            'pending' => $pending,
            'in_progress' => $inProgress,
            'completed' => $completed
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>