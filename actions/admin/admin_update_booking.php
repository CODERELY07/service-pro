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

            $stmt = $pdo->prepare("SELECT status FROM bookings WHERE id = ?");
            $stmt->execute([$bookingId]);
            $old_status = $stmt->fetchColumn();
            
            if ($old_status !== $status) {
                $params[] = $bookingId; 

                $query = "UPDATE bookings SET " . implode(', ', $updateFields) . " WHERE id = ?";
                $stmt = $pdo->prepare($query);
                $result = $stmt->execute($params);

                $log = $pdo->prepare("INSERT INTO audit_logs (booking_id, user_id, action, old_value, new_value) VALUES (?, ?, ?, ?, ?)");
                $log->execute([$bookingId, $admin_id, 'Status Change', $old_status, $status]);
         
                if ($result) {
                    echo json_encode(['success' => true, 'message' => 'Booking updated successfully']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Failed to update booking']);
                }
            } else {
                echo json_encode(['success' => false, 'message' => 'Status is the same, no update made.']);
            }

        
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }


    ?>