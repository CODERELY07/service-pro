<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../../../config/functions.php';
require_once BASE_PATH . 'config/db.php';

$tracking_id = $_POST['tracking_id'] ?? '';
$action = $_POST['action'] ?? '';

if (empty($tracking_id) || empty($action)) {
    echo json_encode(['success' => false, 'message' => 'Missing required data!']);
    exit();
}

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$current_user_id = $_SESSION['user_id'];
$new_status = ($action === 'cancel') ? 'Cancelled' : 'Pending';

try {
    // Get booking details
    $stmt = $pdo->prepare("SELECT id, user_id, status, canceled_by, reject_reason FROM bookings WHERE tracking_id = ?");
    $stmt->execute([$tracking_id]);
    $booking = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$booking) {
        echo json_encode(['success' => false, 'message' => 'Booking not found']);
        exit();
    }

    $booking_id = $booking['id'];
    $old_status = $booking['status'];
    $booking_user_id = $booking['user_id'];

    // Handle cancel action
    if ($action === 'cancel') {
        // Only the booking owner can cancel
        if ($current_user_id !== $booking_user_id) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'You can only cancel your own bookings']);
            exit();
        }

        $stmt = $pdo->prepare("UPDATE bookings SET status = ?, canceled_by = ? WHERE tracking_id = ?");
        $stmt->execute(['Cancelled', $current_user_id, $tracking_id]);
    } 
    // Handle undo/revert to pending action
    else {
        // Only the booking owner can undo
        if ($current_user_id !== $booking_user_id) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'You can only modify your own bookings']);
            exit();
        }

        // Check if the booking was cancelled by an admin
        if ($booking['canceled_by'] && $old_status === 'Cancelled') {
            // Get the details of the user who cancelled it
            $cancel_user_stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
            $cancel_user_stmt->execute([$booking['canceled_by']]);
            $cancel_user = $cancel_user_stmt->fetch(PDO::FETCH_ASSOC);

            if ($cancel_user && $cancel_user['role'] === 'admin') {
                // Return info about admin cancellation instead of forbidding
                echo json_encode([
                    'success' => false,
                    'cancelled_by_admin' => true,
                    'message' => 'This booking was cancelled by an administrator',
                    'reject_reason' => $booking['reject_reason'],
                    'tracking_id' => $tracking_id
                ]);
                exit();
            }
        }

        $stmt = $pdo->prepare("UPDATE bookings SET status = ?, canceled_by = NULL WHERE tracking_id = ?");
        $stmt->execute([$new_status, $tracking_id]);
    }

    if ($stmt->rowCount() > 0) {
        // Log the status change in audit logs
        $log_stmt = $pdo->prepare("INSERT INTO audit_logs (booking_id, user_id, action, old_value, new_value) VALUES (?, ?, ?, ?, ?)");
        $log_stmt->execute([$booking_id, $current_user_id, 'Status Change', $old_status, $new_status]);

        echo json_encode([
            'success' => true,
            'new_status' => $new_status,
            'tracking_id' => $tracking_id
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No changes made. Booking might not exist.']);
    }

} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    exit();
}