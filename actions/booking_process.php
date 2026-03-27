<?php
    session_start();
    require_once './../config/db.php';
    require_once './../vendor/autoload.php';

    use chillerlan\QRCode\QRCode;
    use chillerlan\QRCode\QROptions;

    header('Content-Type: application/json');

    if($_SERVER['REQUEST_METHOD'] === "POST"){
        $category = htmlspecialchars(trim($_POST['category']));
        $model = htmlspecialchars(trim($_POST['model']));
        $description = htmlspecialchars(trim($_POST['description']));

        if(empty($category) || empty($model) || empty($description)){
            echo json_encode(['success' => false, 'message' => 'All fields are required!']);
            exit();
            
        }
        try {
            // Generate numeric tracking ID starting from 1000
            $stmt = $pdo->query("SELECT MAX(tracking_id) as max_id FROM bookings");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $next_id = ($result['max_id'] ?? 999) + 1;
            $tracking_id = $next_id;
            
            // Generate QR code
            $qr_data = "ServicePro Tracking ID: " . $tracking_id . "\nCategory: " . $category . "\nModel: " . $model;
            $qr_filename = 'qr_' . $tracking_id . '.png';
            $qr_path = __DIR__ . '/../assets/qr_codes/' . $qr_filename;
            
            $options = new QROptions([
                'version' => 5,
                'outputType' => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel' => QRCode::ECC_L,
                'scale' => 5,
                'imageTransparent' => false,
            ]);
            
            $qrcode = new QRCode($options);
            $qrcode->render($qr_data, $qr_path);
            
            $relative_qr_path = 'assets/qr_codes/' . $qr_filename;
            
            $stmt = $pdo->prepare("INSERT INTO bookings (user_id, tracking_id, description, model, category, qr_code_path) VALUES(?, ?, ?, ?, ?, ?)");
            $book = $stmt->execute([$_SESSION['user_id'], $tracking_id, $description, $model, $category, $relative_qr_path]);

            if($book){
                echo json_encode([
                    'success' => true, 
                    'message' => 'Booking submitted successfully! Tracking ID: ' . $tracking_id,
                    'tracking_id' => $tracking_id,
                    'qr_code' => $relative_qr_path
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to submit booking.']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    }
?>