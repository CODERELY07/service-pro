<?php
    session_start();
    require_once __DIR__ . '/../../config/functions.php';
    require_once BASE_PATH . 'config/db.php';

    require_once BASE_PATH . 'vendor/autoload.php';

    use chillerlan\QRCode\QRCode;
    use chillerlan\QRCode\QROptions;

    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Unauthorized']);
        exit();
    }

    header('Content-Type: application/json');

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);

        $customerEmail = trim($input['customer_email'] ?? '');
        $category = htmlspecialchars(trim($input['category'] ?? ''));
        $model = htmlspecialchars(trim($input['model'] ?? ''));
        $description = htmlspecialchars(trim($input['description'] ?? ''));
        $status = $input['status'] ?? 'Pending';

        // Validation
        if (empty($customerEmail) || empty($category) || empty($model) || empty($description)) {
            echo json_encode(['success' => false, 'message' => 'All fields are required']);
            exit();
        }

        if (!filter_var($customerEmail, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['success' => false, 'message' => 'Invalid email format']);
            exit();
        }

        // Validate status
        $validStatuses = ['Pending', 'In Progress', 'Ready', 'Claimed'];
        if (!in_array($status, $validStatuses)) {
            echo json_encode(['success' => false, 'message' => 'Invalid status']);
            exit();
        }

        try {
            // Check if user exists, if not create them
            $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$customerEmail]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                // Create new user with a generated username
                $username = explode('@', $customerEmail)[0] . rand(100, 999);
                $hashedPassword = password_hash('temp' . rand(10000, 99999), PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'client')");
                $stmt->execute([$username, $customerEmail, $hashedPassword]);
                $userId = $pdo->lastInsertId();
            } else {
                $userId = $user['id'];
            }

            // Generate tracking ID
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

            // Insert booking
            $stmt = $pdo->prepare("INSERT INTO bookings (user_id, tracking_id, description, model, category, qr_code_path, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $result = $stmt->execute([$userId, $tracking_id, $description, $model, $category, $relative_qr_path, $status]);

            if ($result) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Booking created successfully! Tracking ID: ' . $tracking_id,
                    'tracking_id' => $tracking_id
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Failed to create booking']);
            }
        } catch (PDOException $e) {
            echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    }
?>