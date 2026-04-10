<?php
session_start();
require_once __DIR__ . '/../../config/functions.php';
require_once BASE_PATH . 'config/db.php';


header('Content-Type: application/json');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once BASE_PATH . 'vendor/autoload.php';
$mail = new PHPMailer(true);

$data = json_decode(file_get_contents('php://input'), true);
$tracking_id = $data['tracking_id'] ?? null;
$user_id = $_SESSION['user_id']; 

if (!$tracking_id) {
    echo json_encode(['success' => false, 'message' => 'No Tracking ID provided.']);
    exit;
}

$stmt = $pdo->prepare("SELECT id, status FROM bookings WHERE tracking_id = ? AND user_id = ?");
$stmt->execute([$tracking_id, $user_id]);
$booking = $stmt->fetch();

if (!$booking) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized or invalid ID.']);
    exit;
}


if ($booking['status'] !== 'Ready') {
    echo json_encode(['success' => false, 'message' => 'This device is not marked as Ready for pickup yet.']);
    exit;
}

$old_status = $booking['status']; 
$booking_id = $booking['id'];


$update = $pdo->prepare("UPDATE bookings SET status = 'Claimed' WHERE id = ?");
$success = $update->execute([$booking_id]);

if ($success) {    

    $log = $pdo->prepare("INSERT INTO audit_logs (booking_id, user_id, action, old_value, new_value) VALUES (?, ?, ?, ?, ?)");
    $log->execute([$booking_id, $user_id, 'Status Change', $old_status, 'Claimed']);
}

echo json_encode(['success' => $success, 'message' => $success ? 'Claimed successfully' : 'Database error']);