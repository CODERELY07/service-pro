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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $bookingId = $input['booking_id'] ?? null;

    if (!$bookingId) {
        echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
        exit();
    }

    try {
        // First, get the booking details to check if QR code file exists
        $stmt = $pdo->prepare("SELECT qr_code_path FROM bookings WHERE id = ?");
        $stmt->execute([$bookingId]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            echo json_encode(['success' => false, 'message' => 'Booking not found']);
            exit();
        }

        // Delete the QR code file if it exists
        if ($booking['qr_code_path'] && file_exists(__DIR__ . '/../' . $booking['qr_code_path'])) {
            unlink(__DIR__ . '/../' . $booking['qr_code_path']);
        }

        // Delete the booking from database
        $stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
        $result = $stmt->execute([$bookingId]);

        if ($result) {
            echo json_encode(['success' => true, 'message' => 'Booking deleted successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to delete booking']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>