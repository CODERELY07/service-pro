<?php
session_start();
require_once __DIR__ . '/../config/functions.php';
require_once BASE_PATH . 'config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'client') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $booking_id = $input['booking_id'] ?? null;
    $action = $input['action'] ?? null; // 'accept' or 'reject'
    $reject_reason = $input['reject_reason'] ?? null;

    if (!$booking_id || !$action) {
        echo json_encode(['success' => false, 'message' => 'Booking ID and action are required']);
        exit();
    }

    try {
        // Verify the booking belongs to the user and is in the correct status
        $stmt = $pdo->prepare("SELECT status, tracking_id FROM bookings WHERE id = ? AND user_id = ?");
        $stmt->execute([$booking_id, $user_id]);
        $booking = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$booking) {
            echo json_encode(['success' => false, 'message' => 'Booking not found']);
            exit();
        }

        if ($booking['status'] !== 'Waiting Client Confirmation') {
            echo json_encode(['success' => false, 'message' => 'Booking is not awaiting confirmation']);
            exit();
        }

        if ($action === 'accept') {
            // Update status to In Progress
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'In Progress' WHERE id = ?");
            $result = $stmt->execute([$booking_id]);

            if ($result) {
                // Log the action
                $log = $pdo->prepare("INSERT INTO audit_logs (booking_id, user_id, action, old_value, new_value) VALUES (?, ?, ?, ?, ?)");
                $log->execute([$booking_id, $user_id, 'Client Accepted Offer', 'Waiting Client Confirmation', 'In Progress']);

                // Create notification for admin
                $admin_stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
                $admin_stmt->execute();
                $admin = $admin_stmt->fetch(PDO::FETCH_ASSOC);

                if ($admin) {
                    create_notification(
                        $admin['id'],
                        'Client Accepted Quote',
                        "Client has accepted the quote for service request #" . $booking['tracking_id'] . " and work can now begin.",
                        'success',
                        $booking_id
                    );
                }

                echo json_encode(['success' => true, 'message' => 'Offer accepted successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to accept offer']);
            }

        } elseif ($action === 'reject') {
            if (!$reject_reason) {
                echo json_encode(['success' => false, 'message' => 'Reject reason is required']);
                exit();
            }

            // Update status to Cancelled, set reject reason and track who cancelled
            $stmt = $pdo->prepare("UPDATE bookings SET status = 'Cancelled', reject_reason = ?, canceled_by = ? WHERE id = ?");
            $result = $stmt->execute([$reject_reason, $user_id, $booking_id]);

            if ($result) {
                // Log the action
                $log = $pdo->prepare("INSERT INTO audit_logs (booking_id, user_id, action, old_value, new_value) VALUES (?, ?, ?, ?, ?)");
                $log->execute([$booking_id, $user_id, 'Client Rejected Offer', 'Waiting Client Confirmation', 'Cancelled']);

                // Create notification for admin
                $admin_stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
                $admin_stmt->execute();
                $admin = $admin_stmt->fetch(PDO::FETCH_ASSOC);

                if ($admin) {
                    create_notification(
                        $admin['id'],
                        'Client Rejected Quote',
                        "Client has rejected the quote for service request #" . $booking['tracking_id'] . ". Reason: " . $reject_reason,
                        'warning',
                        $booking_id
                    );
                }

                echo json_encode(['success' => true, 'message' => 'Offer rejected successfully']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to reject offer']);
            }

        } else {
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }

    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>