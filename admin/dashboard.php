<?php

    require __DIR__ . '/../config/db.php';
    require '../includes/header.php';
    require '../includes/navbar.php';
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

        <div class="flex gap-4">
            <button onclick="openScanModal()" class="bg-green-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-green-700 shadow-lg shadow-green-200 transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 15h4.01M12 21h4.01M12 12h4.01M12 15h4.01M12 21h4.01M12 12h4.01M12 15h4.01M12 21h4.01"></path>
                </svg>
                Scan QR Code
            </button>
        </div>
    </div>

    <!-- Stats Cards -->
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

    <!-- Active Bookings Table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden mb-12">
        <div class="px-6 py-4 border-b border-slate-200">
            <h2 class="text-xl font-bold text-slate-900">Active Bookings</h2>
        </div>
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4 text-sm font-bold text-slate-500 uppercase">Service ID</th>
                    <th class="px-6 py-4 text-sm font-bold text-slate-500 uppercase">Customer</th>
                    <th class="px-6 py-4 text-sm font-bold text-slate-500 uppercase">Device</th>
                    <th class="px-6 py-4 text-sm font-bold text-slate-500 uppercase">Description</th>
                    <th class="px-6 py-4 text-sm font-bold text-slate-500 uppercase">Status</th>
                    <th class="px-6 py-4 text-sm font-bold text-slate-500 uppercase">Date</th>
                    <th class="px-6 py-4 text-sm font-bold text-slate-500 uppercase text-center">Actions</th>
                </tr>
            </thead>
            <tbody id="active-bookings-table-body" class="divide-y divide-slate-100">
                <tr>
                    <td colspan="7" class="px-6 py-12 text-center text-slate-400">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Completed Bookings Table -->
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <h2 class="text-xl font-bold text-slate-900">Completed Bookings</h2>
        </div>
        <table class="w-full text-left">
            <thead class="bg-slate-50 border-b border-slate-200">
                <tr>
                    <th class="px-6 py-4 text-sm font-bold text-slate-500 uppercase">Service ID</th>
                    <th class="px-6 py-4 text-sm font-bold text-slate-500 uppercase">Customer</th>
                    <th class="px-6 py-4 text-sm font-bold text-slate-500 uppercase">Device</th>
                    <th class="px-6 py-4 text-sm font-bold text-slate-500 uppercase">Description</th>
                    <th class="px-6 py-4 text-sm font-bold text-slate-500 uppercase">Completed Date</th>
                    <th class="px-6 py-4 text-sm font-bold text-slate-500 uppercase text-center">Actions</th>
                </tr>
            </thead>
            <tbody id="completed-bookings-table-body" class="divide-y divide-slate-100">
                <tr>
                    <td colspan="6" class="px-6 py-12 text-center text-slate-400">Loading...</td>
                </tr>
            </tbody>
        </table>
    </div>
</main>

<!-- Edit Completed Booking Modal -->
<div id="editBookingModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-slate-900/60 backdrop-blur-sm px-4">
    <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl relative p-8 border border-slate-100">
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-3xl font-extrabold text-slate-900">Edit Completed Booking</h3>
                <p class="text-slate-500 mt-2">Update booking details and status.</p>
            </div>
            <button onclick="closeEditModal()" class="absolute top-6 right-6 text-slate-400 hover:text-slate-600 text-2xl p-2 rounded-full hover:bg-slate-50">&times;</button>
        </div>

        <form id="editBookingForm" class="space-y-6">
            <input type="hidden" id="editBookingId" name="booking_id">

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold mb-2 text-slate-700">Device Category</label>
                    <select id="editCategory" name="category" class="w-full px-5 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition bg-slate-50 focus:bg-white" required>
                        <option value="Laptop">Laptop / Desktop</option>
                        <option value="Mobile">Smartphone / Tablet</option>
                        <option value="Appliance">Home Appliance</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2 text-slate-700">Model / Name</label>
                    <input type="text" id="editModel" name="model" placeholder="e.g. iPhone 13 Pro" class="w-full px-5 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition bg-slate-50 focus:bg-white" required>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2 text-slate-700">Detailed Issue Description</label>
                <textarea id="editDescription" name="description" rows="4" placeholder="Please provide specific details about the issue..." class="w-full px-5 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition bg-slate-50 focus:bg-white resize-none" required></textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2 text-slate-700">Status</label>
                <select id="editStatus" name="status" class="w-full px-5 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition bg-slate-50 focus:bg-white" required>
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Ready">Ready for Pickup</option>
                    <option value="Claimed">Claimed</option>
                </select>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="flex-1 bg-blue-600 text-white py-4 rounded-xl font-bold hover:bg-blue-700 shadow-lg transition-all active:scale-[0.98]">
                    Update Booking
                </button>
                <button type="button" onclick="closeEditModal()" class="flex-1 bg-slate-600 text-white py-4 rounded-xl font-bold hover:bg-slate-700 transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="deleteBookingModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-slate-900/60 backdrop-blur-sm px-4">
    <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl relative p-8 border border-slate-100">
        <div class="text-center">
            <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </div>
            <h3 class="text-2xl font-bold text-slate-900 mb-2">Delete Booking</h3>
            <p class="text-slate-600 mb-6">Are you sure you want to delete this completed booking? This action cannot be undone.</p>

            <div id="deleteBookingDetails" class="bg-slate-50 rounded-xl p-4 mb-6 text-left">
                <!-- Booking details will be populated here -->
            </div>

            <div class="flex gap-4">
                <button onclick="confirmDeleteBooking()" class="flex-1 bg-red-600 text-white py-3 rounded-xl font-bold hover:bg-red-700 transition">
                    Delete Booking
                </button>
                <button onclick="closeDeleteModal()" class="flex-1 bg-slate-600 text-white py-3 rounded-xl font-bold hover:bg-slate-700 transition">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- View Booking Details Modal -->
<div id="viewBookingModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-slate-900/60 backdrop-blur-sm px-4">
    <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl relative p-8 border border-slate-100">
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-3xl font-extrabold text-slate-900">Booking Details</h3>
                <p class="text-slate-500 mt-2">Complete information about this booking.</p>
            </div>
            <button onclick="closeViewModal()" class="absolute top-6 right-6 text-slate-400 hover:text-slate-600 text-2xl p-2 rounded-full hover:bg-slate-50">&times;</button>
        </div>

        <div id="bookingDetailsContent" class="space-y-6">
            <!-- Booking details will be populated here -->
        </div>

        <div class="flex justify-end mt-8">
            <button onclick="closeViewModal()" class="bg-slate-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-slate-700 transition">
                Close
            </button>
        </div>
    </div>
</div>

<!-- Create New Booking Modal -->
<div id="createBookingModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-slate-900/60 backdrop-blur-sm px-4">
    <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl relative p-8 border border-slate-100">
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-3xl font-extrabold text-slate-900">Create New Booking</h3>
                <p class="text-slate-500 mt-2">Add a new booking entry to the system.</p>
            </div>
            <button onclick="closeCreateModal()" class="absolute top-6 right-6 text-slate-400 hover:text-slate-600 text-2xl p-2 rounded-full hover:bg-slate-50">&times;</button>
        </div>

        <form id="createBookingForm" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold mb-2 text-slate-700">Customer Email</label>
                    <input type="email" name="customer_email" placeholder="customer@example.com" class="w-full px-5 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition bg-slate-50 focus:bg-white" required>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-2 text-slate-700">Device Category</label>
                    <select name="category" class="w-full px-5 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition bg-slate-50 focus:bg-white" required>
                        <option value="" disabled selected>Select category</option>
                        <option value="Laptop">Laptop / Desktop</option>
                        <option value="Mobile">Smartphone / Tablet</option>
                        <option value="Appliance">Home Appliance</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2 text-slate-700">Model / Name</label>
                <input type="text" name="model" placeholder="e.g. iPhone 13 Pro" class="w-full px-5 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition bg-slate-50 focus:bg-white" required>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2 text-slate-700">Detailed Issue Description</label>
                <textarea name="description" rows="4" placeholder="Please provide specific details about the issue..." class="w-full px-5 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition bg-slate-50 focus:bg-white resize-none" required></textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold mb-2 text-slate-700">Status</label>
                <select name="status" class="w-full px-5 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition bg-slate-50 focus:bg-white" required>
                    <option value="Pending">Pending</option>
                    <option value="In Progress">In Progress</option>
                    <option value="Ready">Ready for Pickup</option>
                    <option value="Claimed">Claimed</option>
                </select>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="flex-1 bg-purple-600 text-white py-4 rounded-xl font-bold hover:bg-purple-700 shadow-lg transition-all active:scale-[0.98]">
                    Create Booking
                </button>
                <button type="button" onclick="closeCreateModal()" class="flex-1 bg-slate-600 text-white py-4 rounded-xl font-bold hover:bg-slate-700 transition">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<!-- QR Code Scanner Modal -->
<div id="scanModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-slate-900/60 backdrop-blur-sm px-4">
    <div class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl relative p-8 border border-slate-100">
        <div class="flex justify-between items-center mb-6 pb-4 border-b border-slate-100">
            <div>
                <h3 class="text-3xl font-extrabold text-slate-900">Scan QR Code</h3>
                <p class="text-slate-500 mt-2">Scan the QR code on the customer's device to mark as completed.</p>
            </div>
            <button onclick="closeScanModal()" class="absolute top-6 right-6 text-slate-400 hover:text-slate-600 text-2xl p-2 rounded-full hover:bg-slate-50">&times;</button>
        </div>

        <div class="space-y-6">
            <div id="scanner-container" class="relative bg-slate-100 rounded-xl overflow-hidden">
                <video id="qr-video" class="w-full h-64 object-cover"></video>
                <div class="absolute inset-0 border-2 border-blue-500 rounded-xl pointer-events-none">
                    <div class="absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 w-32 h-32 border-2 border-white rounded-lg"></div>
                </div>
            </div>

            <div class="text-center">
                <button id="start-scan-btn" onclick="startScanning()" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-blue-700 transition">
                    Start Scanning
                </button>
                <button id="stop-scan-btn" onclick="stopScanning()" class="bg-red-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-red-700 transition ml-4 hidden">
                    Stop Scanning
                </button>
            </div>

            <div id="scan-result" class="hidden bg-green-50 border border-green-200 rounded-xl p-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center">
                        <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div>
                        <p class="font-bold text-green-800">Booking Found!</p>
                        <p id="scan-details" class="text-green-700"></p>
                    </div>
                </div>
                <div class="mt-4 flex gap-3">
                    <button id="mark-complete-btn" onclick="markAsComplete()" class="bg-green-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-green-700 transition">
                        Mark as Completed
                    </button>
                    <button onclick="closeScanModal()" class="bg-slate-600 text-white px-4 py-2 rounded-lg font-bold hover:bg-slate-700 transition">
                        Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="../assets/js/modal.js"></script>
<script src="../assets/js/admin-dashboard.js"></script>
<script src="../assets/js/modal.js"></script>
<script src="../assets/js/admin-dashboard.js"></script>
<script src="https://unpkg.com/@zxing/library@latest"></script>