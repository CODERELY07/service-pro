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

function seedBooking($pdo, $userId, $category, $model, $description, $status = 'Pending') {
    $stmt = $pdo->prepare('SELECT id FROM bookings WHERE user_id = ? AND category = ? AND model = ? AND description = ? LIMIT 1');
    $stmt->execute([$userId, $category, $model, $description]);
    if ($stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Booking already exists for user_id=$userId, model=$model.\n";
        return;
    }

    $stmt = $pdo->query('SELECT IFNULL(MAX(tracking_id), 999) AS max_id FROM bookings');
    $max = $stmt->fetch(PDO::FETCH_ASSOC)['max_id'];
    $trackingId = $max + 1;

    $qrCodePath = "assets/qr_codes/qr_{$trackingId}.png";

    $stmt = $pdo->prepare('INSERT INTO bookings (user_id, tracking_id, description, model, category, qr_code_path, status) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $stmt->execute([$userId, $trackingId, $description, $model, $category, $qrCodePath, $status]);

    echo "Created booking $trackingId for user_id=$userId.\n";
}

$options = getopt('', ['admin::', 'client::', 'bookings::', 'all::']);

if (empty($options)) {
    echo "Usage: php seed.php [--admin] [--client] [--bookings] [--all]\n";
    echo "  --admin     Create default admin user\n";
    echo "  --client    Create default client user\n";
    echo "  --bookings  Create sample bookings (requires client user)\n";
    echo "  --all       Run admin, client, and sample bookings\n";
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

    seedBooking($pdo, $clientId, 'Laptop', 'Dell Inspiron 15', 'Screen flickering, power cycle issue', 'Pending');
    seedBooking($pdo, $clientId, 'Mobile', 'iPhone 12', 'Battery drains fast', 'In Progress');
    seedBooking($pdo, $clientId, 'Appliance', 'Samsung Washer', 'Not spinning properly', 'Ready');
}

echo "Seeding completed.\n";