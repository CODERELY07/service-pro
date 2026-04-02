<?php
    session_start();
    require_once __DIR__ . '/../../config/functions.php';
    require_once BASE_PATH . 'config/db.php';
    require_once BASE_PATH . 'vendor/autoload.php';

    use chillerlan\QRCode\QRCode;
    use chillerlan\QRCode\QROptions;

    header('Content-Type: application/json');

    if($_SERVER['REQUEST_METHOD'] === "POST"){
        $category = htmlspecialchars(trim($_POST['category'] ?? ''));
        $model = htmlspecialchars(trim($_POST['model'] ?? ''));
        $description = htmlspecialchars(trim($_POST['description'] ?? ''));
        $service_type = htmlspecialchars(trim($_POST['service_type'] ?? ''));

        if(empty($category) || empty($model) || empty($description) || empty($service_type)){
            echo json_encode(['success' => false, 'message' => 'All fields are required!']);
            exit();
        }

        try {
            $stmt = $pdo->query("SELECT MAX(tracking_id) as max_id FROM bookings");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $next_id = ($result['max_id'] ?? 999) + 1;
            $tracking_id = $next_id;

            $qr_data = "ServicePro ID: " . $tracking_id . "\nModel: " . $model . "\nService: " . $service_type;
         
            $qr_folder = BASE_PATH . 'assets/qr_codes/';
            if (!file_exists($qr_folder)) {
                mkdir($qr_folder, 0777, true);
            }

            $qr_filename = 'qr_' . $tracking_id . '.png';
            $qr_path = $qr_folder . $qr_filename;
            
            // 4. QR Options - Using VERSION_AUTO to prevent overflow
            $options = new QROptions([
                'version'      => QRCode::VERSION_AUTO, // This fixes the overflow error
                'outputType'   => QRCode::OUTPUT_IMAGE_PNG,
                'eccLevel'     => QRCode::ECC_L, // Level L allows for more data
                'scale'        => 5,
                'imageTransparent' => false,
            ]);
            
            $qrcode = new QRCode($options);
            $qrcode->render($qr_data, $qr_path);
            
            // 5. Database Insertion
            $relative_qr_path = 'assets/qr_codes/' . $qr_filename;
            
            $stmt = $pdo->prepare("INSERT INTO bookings (user_id, tracking_id, description, model, category, qr_code_path, service_type) VALUES(?, ?, ?, ?, ?, ?, ?)");
            $book = $stmt->execute([
                $_SESSION['user_id'] ?? null, 
                $tracking_id, 
                $description, 
                $model, 
                $category, 
                $relative_qr_path, 
                $service_type
            ]);

            if($book){
                echo json_encode([
                    'success' => true, 
                    'message' => 'Booking submitted successfully!',
                    'tracking_id' => $tracking_id,
                    'qr_code' => BASE_URL . $relative_qr_path // Use BASE_URL for the frontend
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to save to database.']);
            }

        } catch (Exception $e) {
            error_log("BOOKING_ERROR: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method.']);
    }