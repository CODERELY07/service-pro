<?php

    require_once __DIR__ . '/../config/functions.php';
   
    add_script('assets/js/admin-dashboard.js');
    add_script('assets/js/modal.js');

    require_once BASE_PATH . 'config/db.php';
    require_once BASE_PATH . 'includes/header.php';
    require_once BASE_PATH . 'includes/navbar.php';
   
     if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../index.php");
        exit();
    }
?>

<main class="container mx-auto px-6 py-12">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
        <div>
            <h1 class="text-4xl font-bold text-slate-900">Admin Dashboard</h1>
            <p class="text-slate-500 mt-2">Manage all repair requests and scan QR codes for completion.</p>
        </div>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <p class="text-slate-500 text-sm font-medium">Total Bookings</p>
            <p class="text-3xl font-bold mt-1" id="total-count">0</p>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <p class="text-slate-500 text-sm font-medium">Pending</p>
            <p class="text-3xl font-bold mt-1 text-yellow-600" id="pending-count">0</p>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <p class="text-slate-500 text-sm font-medium">In Progress</p>
            <p class="text-3xl font-bold mt-1 text-blue-600" id="in-progress-count">0</p>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <p class="text-slate-500 text-sm font-medium">Completed</p>
            <p class="text-3xl font-bold mt-1 text-green-600" id="completed-count">0</p>
        </div>
    </div>

   <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-12">
    <div class="px-6 py-4 border-b border-slate-200 flex justify-between items-center">
        <h2 class="text-xl font-bold text-slate-900">All Repair Bookings</h2>
    </div>
    <table class="w-full text-left">
        <thead class="bg-slate-50 border-b border-slate-200">
            <tr>
                <th class="px-6 py-4 text-sm font-bold text-slate-500 uppercase">Service ID</th>
                <th class="px-6 py-4 text-sm font-bold text-slate-500 uppercase">Customer</th>
                <th class="px-6 py-4 text-sm font-bold text-slate-500 uppercase">Device</th>
                <th class="px-6 py-4 text-sm font-bold text-slate-500 uppercase">Status</th>
                <th class="px-6 py-4 text-sm font-bold text-slate-500 uppercase">Date</th>
            </tr>
        </thead>
        <tbody id="all-bookings-table-body" class="divide-y divide-slate-100">
            <tr>
                <td colspan="6" class="px-6 py-12 text-center text-slate-400">Loading bookings...</td>
            </tr>
        </tbody>
    </table>
</div>
</main>
<script src="https://unpkg.com/@zxing/library@latest"></script>