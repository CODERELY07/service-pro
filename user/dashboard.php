<?php 
    require './../includes/header.php'; 
    require './../includes/navbar.php';

    if (!isset($_SESSION['user_id'])) {
        header("Location: index.php");
        exit();
    }

    require __DIR__ . '../../config/db.php';
?>

<?php include __DIR__ . '/../includes/modals/booking.php'; ?>

<main class="container mx-auto px-6 py-12">
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
        <div>
            <h1 class="text-4xl font-bold text-slate-900">Service Dashboard</h1>
            <p class="text-slate-500 mt-2">Manage your repair requests and track progress.</p>
        </div>
        
        <?php if($_SESSION['role'] == 'client'): ?>
            <button onclick="openModal('bookingModal')" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-200 transition">
                + New Repair Request
            </button>
        <?php endif; ?>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
            <p class="text-slate-500 text-sm font-medium">Pending Requests</p>
            <p class="text-3xl font-bold mt-1" id="pending-count">0</p>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm text-blue-600">
            <p class="text-slate-500 text-sm font-medium">In Progress</p>
            <p class="text-3xl font-bold mt-1 text-slate-900" id="in-progress-count">0</p>
        </div>
        <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm text-green-600">
            <p class="text-slate-500 text-sm font-medium">Ready for Pickup</p>
            <p class="text-3xl font-bold mt-1 text-slate-900" id="ready-count">0</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4 text-sm font-bold text-slate-500 uppercase">Service ID</th>
                    <th class="px-6 py-4 text-sm font-bold text-slate-500 uppercase">Device</th>
                    <th class="px-6 py-4 text-sm font-bold text-slate-500 uppercase">Description</th>
                    <th class="px-6 py-4 text-sm font-bold text-slate-500 uppercase">Status</th>
                    <th class="px-6 py-4 text-sm font-bold text-slate-500 uppercase text-center">QR Code</th>
                    <th class="px-6 py-4 text-sm font-bold text-slate-500 uppercase text-right">Action</th>
                </tr>
            </thead>
            <tbody id="bookings-table-body" class="divide-y divide-slate-100">
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-400">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>
</main>

<script src="./../assets/js/modal.js"></script>
<script src="./../assets/js/booking.js"></script>
<script src="./../assets/js/dashboard.js"></script>