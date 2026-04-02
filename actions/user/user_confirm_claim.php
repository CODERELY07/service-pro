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

    $stmt = $pdo->prepare("
        SELECT b.*, u.username, u.email
        FROM bookings b
        JOIN users u ON b.user_id = u.id
        WHERE b.id = ?
    ");
    $stmt->execute([$booking_id]);
    $full_booking_data = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($full_booking_data) {
        sendCompletionEmail($full_booking_data);
    }

    $log = $pdo->prepare("INSERT INTO audit_logs (booking_id, user_id, action, old_value, new_value) VALUES (?, ?, ?, ?, ?)");
    $log->execute([$booking_id, $user_id, 'Status Change', $old_status, 'Claimed']);
}

echo json_encode(['success' => $success, 'message' => $success ? 'Claimed successfully' : 'Database error']);

function sendCompletionEmail($booking) {

    global $mail; 
    try {
        
        $mail->isSMTP();                                        
        $mail->Host       = 'smtp.gmail.com';                 
        $mail->SMTPAuth   = true;                                  
        $mail->Username   = 'calipjo.markely@gmail.com';                  
        $mail->Password   = 'darl ersz rcje luwl';                            
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;           
        $mail->Port       = 465;     

        // Recipients
        $mail->setFrom('calipjo.markely@gmail.com', 'Mailer');
        $mail->addAddress($booking['email'], $booking['username']);    

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your Repair is Complete - ServicePro';

        $mail->Body = "
        <div style='font-family: Arial, sans-serif; max-width: 600px; margin: 0 auto;'>
            <h2 style='color: #2563eb;'>Repair Completed Successfully!</h2>
            <p>Dear {$booking['username']},</p>
            <p>Your device repair has been completed and is ready for pickup.</p>

            <div style='background: #f8fafc; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                <h3>Repair Details:</h3>
                <p><strong>Tracking ID:</strong> {$booking['tracking_id']}</p>
                <p><strong>Device:</strong> {$booking['model']} ({$booking['category']})</p>
                <p><strong>Description:</strong> {$booking['description']}</p>
                <p><strong>Completed Date:</strong> " . date('F j, Y') . "</p>
            </div>

            <p>Please bring your QR code or tracking ID to pick up your device.</p>
            <p>Thank you for choosing ServicePro!</p>

            <div style='margin-top: 30px; padding-top: 20px; border-top: 1px solid #e2e8f0; color: #64748b; font-size: 14px;'>
                <p>ServicePro | Professional Repair Management</p>
            </div>
        </div>
        ";

        $mail->send();
        error_log("Completion email sent to: " . $booking['email']);
    } catch (Exception $e) {
        error_log("Email could not be sent. Mailer Error: {$mail->ErrorInfo}");
    }
}