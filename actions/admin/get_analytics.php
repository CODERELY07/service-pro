<?php
session_start();
  require_once __DIR__ . '/../../config/functions.php';
    require_once BASE_PATH . 'config/db.php';
header('Content-Type: application/json');


if ($_SESSION['role'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

try {
    $stmt = $pdo->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN status = 'Claimed' THEN 1 ELSE 0 END) as completed,
            SUM(CASE WHEN status = 'Cancelled' THEN 1 ELSE 0 END) as cancelled
        FROM bookings
    ");
    $counts = $stmt->fetch(PDO::FETCH_ASSOC);

    $earningsStmt = $pdo->query("
        SELECT 
            SUM(CASE WHEN DATE(updated_at) = CURDATE() THEN total_price ELSE 0 END) as daily,
            SUM(CASE WHEN YEARWEEK(updated_at, 1) = YEARWEEK(CURDATE(), 1) THEN total_price ELSE 0 END) as weekly,
            SUM(CASE WHEN MONTH(updated_at) = MONTH(CURDATE()) AND YEAR(updated_at) = YEAR(CURDATE()) THEN total_price ELSE 0 END) as monthly
        FROM bookings 
        WHERE status = 'Claimed'
    ");
    $earnings = $earningsStmt->fetch(PDO::FETCH_ASSOC);

    // Monthly earnings for current year
    $monthlyEarningsStmt = $pdo->prepare("
        SELECT 
            MONTH(updated_at) as month,
            SUM(total_price) as earnings
        FROM bookings 
        WHERE status = 'Claimed' AND YEAR(updated_at) = YEAR(CURDATE())
        GROUP BY MONTH(updated_at)
        ORDER BY MONTH(updated_at)
    ");
    $monthlyEarningsStmt->execute();
    $monthlyEarnings = $monthlyEarningsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Monthly bookings for current year
    $monthlyBookingsStmt = $pdo->prepare("
        SELECT 
            MONTH(created_at) as month,
            COUNT(*) as bookings
        FROM bookings 
        WHERE YEAR(created_at) = YEAR(CURDATE())
        GROUP BY MONTH(created_at)
        ORDER BY MONTH(created_at)
    ");
    $monthlyBookingsStmt->execute();
    $monthlyBookings = $monthlyBookingsStmt->fetchAll(PDO::FETCH_ASSOC);

    // Prepare arrays for 12 months
    $earningsData = array_fill(1, 12, 0);
    foreach ($monthlyEarnings as $row) {
        $earningsData[$row['month']] = (float)$row['earnings'];
    }

    $bookingsData = array_fill(1, 12, 0);
    foreach ($monthlyBookings as $row) {
        $bookingsData[$row['month']] = (int)$row['bookings'];
    }

    echo json_encode([
        'success' => true,
        'stats' => $counts,
        'earnings' => $earnings,
        'monthlyEarnings' => array_values($earningsData),
        'monthlyBookings' => array_values($bookingsData)
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    error_log("Analytics error: " . $e->getMessage());
}