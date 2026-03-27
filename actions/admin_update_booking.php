<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit();
}

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $bookingId = $input['booking_id'] ?? null;
    $status = $input['status'] ?? null;
    $category = $input['category'] ?? null;
    $model = $input['model'] ?? null;
    $description = $input['description'] ?? null;

    if (!$bookingId) {
        echo json_encode(['success' => false, 'message' => 'Booking ID is required']);
        exit();
    }

    try {
        // Build update query dynamically based on provided fields
        $updateFields = [];
        $params = [];

        if ($status !== null) {
            $updateFields[] = "status = ?";
            $params[] = $status;
        }

        if ($category !== null) {
            $updateFields[] = "category = ?";
            $params[] = $category;
        }

        if ($model !== null) {
            $updateFields[] = "model = ?";
            $params[] = $model;
        }

        if ($description !== null) {
            $updateFields[] = "description = ?";
            $params[] = $description;
        }

        if (empty($updateFields)) {
            echo json_encode(['success' => false, 'message' => 'No fields to update']);
            exit();
        }

        $params[] = $bookingId; // Add booking ID at the end

        $query = "UPDATE bookings SET " . implode(', ', $updateFields) . " WHERE id = ?";
        $stmt = $pdo->prepare($query);
        $result = $stmt->execute($params);

        if ($result) {
            // If marking as claimed, we could send an email notification here
            if ($status === 'Claimed') {
                // Get booking details for email
                $stmt = $pdo->prepare("
                    SELECT b.*, u.username, u.email
                    FROM bookings b
                    JOIN users u ON b.user_id = u.id
                    WHERE b.id = ?
                ");
                $stmt->execute([$bookingId]);
                $booking = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($booking) {
                    // Send completion email
                    sendCompletionEmail($booking);
                }
            }

            echo json_encode(['success' => true, 'message' => 'Booking updated successfully']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to update booking']);
        }
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}

function sendCompletionEmail($booking) {
    require_once '../vendor/autoload.php';

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // Configure your SMTP server
        $mail->SMTPAuth = true;
        $mail->Username = 'your-email@gmail.com'; // Configure your email
        $mail->Password = 'your-app-password'; // Configure your app password
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Recipients
        $mail->setFrom('noreply@servicepro.com', 'ServicePro');
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
?>