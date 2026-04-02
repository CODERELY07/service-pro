<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../../../config/functions.php';
require_once BASE_PATH . 'config/db.php';

$tracking_id = $_POST['tracking_id'] ?? '';
$action = $_POST['action'] ?? '';

if (empty($tracking_id) || empty($action)) {
    echo json_encode(['success' => false, 'message' => 'Missing required data!']);
    exit();
}

$new_status = ($action === 'cancel') ? 'Cancelled' : 'Pending';

try {
    $stmt = $pdo->prepare("UPDATE bookings SET status = ? WHERE tracking_id = ?");
    $stmt->execute([$new_status, $tracking_id]);

    if ($stmt->rowCount() > 0) {
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