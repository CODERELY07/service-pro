<?php
session_start();
require_once __DIR__ . '/../../config/functions.php';
require_once BASE_PATH . 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $bookingId = $_GET['id'] ?? null;
    $trackingId = $_GET['tracking_id'] ?? null;

    if (!$bookingId && !$trackingId) {
        echo json_encode(['success' => false, 'message' => 'Booking ID or Tracking ID is required']);
        exit();
    }

    try {
        $query = "
            SELECT b.*, u.username, u.email
            FROM bookings b
            JOIN users u ON b.user_id = u.id
            WHERE " . ($bookingId ? "b.id = ?" : "b.tracking_id = ?");
        
        $stmt = $pdo->prepare($query);
        $stmt->execute([$bookingId ?: $trackingId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($booking) {
            echo json_encode([
                'success' => true,
                'booking' => $booking
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Booking not found']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>