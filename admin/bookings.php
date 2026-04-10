<?php
    require_once __DIR__ . '/../config/functions.php';
    require_once BASE_PATH . 'config/db.php';

    add_script('assets/js/admin-booking.js');
    add_script('assets/js/utils.js');
    add_script('assets/js/modal.js');

    require_once BASE_PATH . 'includes/header.php';
    require_once BASE_PATH . 'includes/navbar.php';
    
    // Check authentication and admin role
    if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: " . BASE_URL . "index.php");
        exit();
    }

    $status_filter = $_GET['status'] ?? 'active';


    switch ($status_filter) {
        case 'completed':
            $page_title = "Completed Repairs";
            $sql = "SELECT b.*, u.username FROM bookings b LEFT JOIN users u ON b.user_id = u.id WHERE b.status IN ('Ready', 'Claimed') ORDER BY b.created_at DESC";
            break;
        case 'cancelled':
            $page_title = "Cancelled Bookings";
            $sql = "SELECT b.*, u.username FROM bookings b LEFT JOIN users u ON b.user_id = u.id WHERE b.status = 'Cancelled' ORDER BY b.created_at DESC";
            break;
        default: 
            $page_title = "Active Repairs";
            $sql = "SELECT b.*, u.username FROM bookings b LEFT JOIN users u ON b.user_id = u.id WHERE b.status IN ('Pending', 'In Progress') ORDER BY b.created_at DESC";
            $status_filter = 'active';
            break;
    }


    $stmt = $pdo->query($sql);
    $bookings = $stmt->fetchAll();


    require_once BASE_PATH . 'includes/modals/view_booking_modals.php';
    require_once BASE_PATH . 'includes/modals/admin_actions.php';

    
?>



<main class="container mx-auto px-6 py-10">
    <div class="flex justify-between items-center mb-8">
        <div>
            <h1 class="text-3xl font-bold text-slate-900"><?= $page_title ?></h1>
            <p class="text-slate-500 text-sm mt-1">Managing all <?= strtolower($page_title) ?> in the system.</p>
        </div>
        
        <div class="flex bg-slate-100 p-1 rounded-xl">
            <a href="?status=active" class="px-4 py-2 rounded-lg text-sm font-medium <?= $status_filter === 'active' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-500' ?>">Active</a>
            <a href="?status=completed" class="px-4 py-2 rounded-lg text-sm font-medium <?= $status_filter === 'completed' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-500' ?>">Completed</a>
            <a href="?status=cancelled" class="px-4 py-2 rounded-lg text-sm font-medium <?= $status_filter === 'cancelled' ? 'bg-white shadow-sm text-blue-600' : 'text-slate-500' ?>">Cancelled</a>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-3xl overflow-hidden shadow-sm">
        <table class="w-full text-left border-collapse">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Tracking ID</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Customer</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Device</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase">Status</th>
                    <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center text-slate-400 italic">No <?= $status_filter ?> bookings found.</td>
                    </tr>
                <?php else: foreach ($bookings as $row): ?>
                    <tr class="hover:bg-slate-50/50 transition">
                        <td class="px-6 py-4 font-mono text-sm font-bold text-blue-600">#<?= $row['tracking_id'] ?></td>
                        <td class="px-6 py-4 text-sm text-slate-900"><?= isset($row['username']) ? htmlspecialchars($row['username']) : 'Guest' ?></td>
                        <td class="px-6 py-4">
                            <div class="text-sm font-semibold text-slate-900"><?= $row['model'] ?></div>
                            <div class="text-xs text-slate-500"><?= $row['category'] ?></div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium 
                                <?= $row['status'] === 'Cancelled' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' ?>">
                                <?= $row['status'] ?>
                            </span>
                        </td>
                        <td class="px-6 py-4 text-right">
                           <button onclick="openBookingDetails(<?= $row['id'] ?>, true)" class="text-slate-400 hover:text-blue-600 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>
                        </td>
                    </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</main>
<?php require_once BASE_PATH . 'includes/footer.php'; ?>