<?php

require_once __DIR__ . '/config/functions.php';
require_once BASE_PATH . 'config/db.php';

function seedAdmin($pdo, $username = 'admin', $email = 'admin@servicepro.com', $password = 'admin123') {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR username = ?');
    $stmt->execute([$email, $username]);
    $exists = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($exists) {
        echo "Admin already exists (id = {$exists['id']}).\n";
        return $exists['id'];
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?);');
    $stmt->execute([$username, $email, $hashed, 'admin']);

    $id = $pdo->lastInsertId();
    echo "Created admin user: $username ($email) id=$id.\n";
    return $id;
}

function seedClient($pdo, $username = 'client', $email = 'client@servicepro.com', $password = 'user1234') {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? OR username = ?');
    $stmt->execute([$email, $username]);
    $exists = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($exists) {
        echo "Client already exists (id = {$exists['id']}).\n";
        return $exists['id'];
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, ?);');
    $stmt->execute([$username, $email, $hashed, 'client']);

    $id = $pdo->lastInsertId();
    echo "Created client user: $username ($email) id=$id.\n";
    return $id;
}

/**
 * Updated seedBooking to handle prices and custom dates for Analytics
 */
function seedBooking($pdo, $userId, $category, $model, $description, $status = 'Pending', $price = 0, $date = null) {
    // Default to current time if no date provided
    $targetDate = $date ?? date('Y-m-d H:i:s');

    $stmt = $pdo->query('SELECT IFNULL(MAX(tracking_id), 999) AS max_id FROM bookings');
    $max = $stmt->fetch(PDO::FETCH_ASSOC)['max_id'];
    $trackingId = $max + 1;

    $qrCodePath = "assets/qr_codes/qr_{$trackingId}.png";

    // We manually set created_at and updated_at to populate the analytics dashboard
    $stmt = $pdo->prepare('
        INSERT INTO bookings (user_id, tracking_id, description, model, category, qr_code_path, status, total_price, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ');
    
    $stmt->execute([
        $userId, 
        $trackingId, 
        $description, 
        $model, 
        $category, 
        $qrCodePath, 
        $status, 
        $price, 
        $targetDate, 
        $targetDate
    ]);

    echo "Created booking $trackingId ($status) for date $targetDate.\n";
}

$options = getopt('', ['admin::', 'client::', 'bookings::', 'all::']);

if (empty($options)) {
    echo "Usage: php seed.php [--admin] [--client] [--bookings] [--all]\n";
    exit;
}

$doAdmin = isset($options['admin']) || isset($options['all']);
$doClient = isset($options['client']) || isset($options['all']);
$doBookings = isset($options['bookings']) || isset($options['all']);

if ($doAdmin) {
    $adminId = seedAdmin($pdo);
}

if ($doClient) {
    $clientId = seedClient($pdo);
}

if ($doBookings) {
    if (!isset($clientId)) {
        $clientId = seedClient($pdo);
    }

    echo "--- Seeding Analytics Data ---\n";

    // 1. DAILY DATA (Today)
    seedBooking($pdo, $clientId, 'Laptop', 'Dell XPS 13', 'Battery Replacement', 'Claimed', 150.00, date('Y-m-d H:i:s'));
    seedBooking($pdo, $clientId, 'Mobile', 'iPhone 13', 'Screen Repair', 'Claimed', 200.00, date('Y-m-d H:i:s'));

    // 2. WEEKLY DATA (Within last 7 days)
    seedBooking($pdo, $clientId, 'Appliance', 'Air Fryer', 'Heating element', 'Claimed', 80.00, date('Y-m-d H:i:s', strtotime('-2 days')));
    seedBooking($pdo, $clientId, 'Console', 'PS5', 'Cleaning', 'Claimed', 50.00, date('Y-m-d H:i:s', strtotime('-4 days')));

    // 3. MONTHLY DATA (Within this month)
    seedBooking($pdo, $clientId, 'Desktop', 'Custom PC', 'CPU Upgrade', 'Claimed', 450.00, date('Y-m-d H:i:s', strtotime('-15 days')));
    
    // 4. NON-EARNINGS DATA (For Status Counts)
    seedBooking($pdo, $clientId, 'Laptop', 'Dell Inspiron 15', 'Screen flickering', 'Pending');
    seedBooking($pdo, $clientId, 'Mobile', 'iPhone 12', 'Battery drains fast', 'Cancelled', 0, date('Y-m-d H:i:s'));
}

echo "Seeding completed.\n";