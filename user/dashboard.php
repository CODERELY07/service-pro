    <?php 
    
        require_once __DIR__ . '/../config/functions.php'; 

        add_script('assets/js/utils.js');
        add_script('assets/js/booking.js');
        add_script('assets/js/dashboard.js');
        add_script('assets/js/modal.js'); 
        add_script('assets/js/client-scanner.js');
        
        require_once BASE_PATH . 'includes/forms/includes.php';
        require_once BASE_PATH . 'config/db.php';
        require_once BASE_PATH . 'includes/header.php'; 
        require_once BASE_PATH . 'includes/navbar.php';
        
        if (!isset($_SESSION['user_id'])) {
            header("Location: " . BASE_URL . "index.php");
            exit();
        }
    ?>

    <?php 
        include BASE_PATH . 'includes/modals/booking.php'; 
        include BASE_PATH . 'includes/components/statusModal.php';
         require_once BASE_PATH . 'includes/modals/view_booking_modals.php';
         require_once BASE_PATH . 'includes/modals/admin_actions.php';
    ?>

    <main class="container mx-auto px-6 py-12">
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10 gap-4">
            <div>
                <h1 class="text-4xl font-bold text-slate-900">Service Dashboard</h1>
                <p class="text-slate-500 mt-2">Manage your repair requests and track progress.</p>
            </div>
            <button onclick="openClientScanner()" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-bold shadow-lg flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M12 15h4.01M12 21h4.01"></path>
            </svg>
            Scan to Confirm Claim
        </button>

        <div id="clientScanModal" class="fixed inset-0 z-50 flex items-center justify-center hidden bg-slate-900/60 backdrop-blur-sm px-4">
            <div class="bg-white w-full max-w-md rounded-3xl p-6 relative">
                <h3 class="text-xl font-bold mb-4">Confirm Pickup</h3>
                <p class="text-sm text-slate-500 mb-4">Scan the QR code provided by the technician to confirm you have received your device.</p>
                
                <div class="relative bg-slate-100 rounded-xl overflow-hidden mb-4">
                    <video id="client-qr-video" class="w-full h-64 object-cover"></video>
                </div>

                <button onclick="closeClientScanner()" class="w-full py-3 bg-slate-100 text-slate-600 rounded-xl font-bold">Cancel</button>
            </div>
        </div>
            <?php if($_SESSION['role'] == 'client'): ?>
                <button onclick="openModal('bookingModal')" class="bg-blue-600 text-white px-6 py-3 rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-200 transition">
                    + New Repair Request
                </button>
            <?php endif; ?>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-12">
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm">
                <p class="text-slate-500 text-sm font-medium">Pending Requests</p>
                <p class="text-3xl font-bold mt-1" id="pending-count">0</p>
            </div>
            <div class="bg-white p-6 rounded-2xl border border-slate-100 shadow-sm text-purple-600">
                <p class="text-slate-500 text-sm font-medium">Awaiting Confirmation</p>
                <p class="text-3xl font-bold mt-1 text-slate-900" id="waiting-confirmation-count">0</p>
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
    <script src="https://unpkg.com/@zxing/library@latest"></script>