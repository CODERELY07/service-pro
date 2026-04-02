<?php
session_start();
require_once './../../config/db.php';
$id = $_GET['id'] ?? null;

if (!$id) {
    echo json_encode(['error' => 'No ID provided']);
    exit;
}

// Fetch the booking. 
// For Client side, you'd add: AND user_id = $_SESSION['user_id']
$stmt = $pdo->prepare("SELECT * FROM bookings WHERE id = ?");
$stmt->execute([$id]);
$booking = $stmt->fetch(PDO::FETCH_ASSOC);

echo json_encode($booking);