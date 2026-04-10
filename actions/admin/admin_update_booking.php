<?php
    
    session_start();
    require_once __DIR__ . '/../../config/functions.php';
    require_once BASE_PATH . 'config/db.php';

        
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    require_once BASE_PATH . 'vendor/autoload.php';
     $mail = new PHPMailer(true);
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
        $reject_reason = $input['reject_reason'] ?? null;
        $total_price = $input['total_price'] ?? null;
        $admin_id = $_SESSION['user_id'];
    
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
                
                // If status is being changed to 'Cancelled' by admin, set canceled_by
                if ($status === 'Cancelled') {
                    $updateFields[] = "canceled_by = ?";
                    $params[] = $admin_id;
                }
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

            if ($reject_reason !== null) {
                $updateFields[] = "reject_reason = ?";
                $params[] = $reject_reason;
            }

            if ($total_price !== null) {
                $updateFields[] = "total_price = ?";
                $params[] = $total_price;
            }

            if (empty($updateFields)) {
                echo json_encode(['success' => false, 'message' => 'No fields to update']);
                exit();
            }

            // Get booking details for notifications
            $stmt = $pdo->prepare("SELECT user_id, status, tracking_id FROM bookings WHERE id = ?");
            $stmt->execute([$bookingId]);
            $booking = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$booking) {
                echo json_encode(['success' => false, 'message' => 'Booking not found']);
                exit();
            }

            $old_status = $booking['status'];
            $user_id = $booking['user_id'];
            $tracking_id = $booking['tracking_id'];
            
            $params[] = $bookingId; 

            $query = "UPDATE bookings SET " . implode(', ', $updateFields) . " WHERE id = ?";
            $stmt = $pdo->prepare($query);
            $result = $stmt->execute($params);

            if ($result) {
                // Log the status change
                if ($old_status !== $status) {
                    $log = $pdo->prepare("INSERT INTO audit_logs (booking_id, user_id, action, old_value, new_value) VALUES (?, ?, ?, ?, ?)");
                    $log->execute([$bookingId, $admin_id, 'Status Change', $old_status, $status]);
                }

                // Create notifications based on status change
                if ($status === 'Cancelled' && $reject_reason) {
                    error_log("Creating notification for user $user_id about cancellation of booking $bookingId with reason: $reject_reason");
                    
                    create_notification(
                        $user_id,
                        'Service Request Rejected',
                        "Your service request #$tracking_id has been rejected. Click to view the reason.",
                        'error',
                        $bookingId
                    );
                } elseif ($status === 'Waiting Client Confirmation' && $total_price) {
                    create_notification(
                        $user_id,
                        'Service Quote Available',
                        "A quote of ₱" . number_format($total_price, 2) . " has been provided for your service request #$tracking_id. Please review and confirm.",
                        'info',
                        $bookingId
                    );
                } elseif ($status === 'Ready') {
                    $stmt = $pdo->prepare("
                        SELECT b.*, u.username, u.email
                        FROM bookings b
                        JOIN users u ON b.user_id = u.id
                        WHERE b.id = ?
                    ");
                    $stmt->execute([$bookingId]);
                    $full_booking_data = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($full_booking_data) {
                        sendCompletionEmail($full_booking_data);
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
?>