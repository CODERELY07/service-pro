// Admin Dashboard JavaScript functionality

let codeReader;
let currentScannedBooking = null;

// Initialize dashboard on page load
document.addEventListener('DOMContentLoaded', function() {
    loadAllBookings();
});

// Load all bookings for admin view
async function loadAllBookings() {
    try {
        const response = await fetch('../actions/admin_fetch_bookings.php');
        const data = await response.json();

        if (response.ok) {
            updateActiveBookingsTable(data.active);
            updateCompletedBookingsTable(data.completed);
            updateStats(data.stats);
        } else {
            console.error('Error loading bookings:', data.error);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function updateActiveBookingsTable(bookings) {
    const tbody = document.getElementById('active-bookings-table-body');
    if (bookings.length === 0) {
        tbody.innerHTML = '<tr><td colspan="7" class="px-6 py-12 text-center text-slate-400">No active bookings found.</td></tr>';
        return;
    }

    tbody.innerHTML = bookings.map(booking => `
        <tr>
            <td class="px-6 py-4 font-mono font-bold text-slate-900">${booking.tracking_id}</td>
            <td class="px-6 py-4">
                <div class="font-medium text-slate-900">${booking.username}</div>
                <div class="text-sm text-slate-500">${booking.user_email || 'N/A'}</div>
            </td>
            <td class="px-6 py-4">
                <div class="font-medium text-slate-900">${booking.model}</div>
                <div class="text-sm text-slate-500">${booking.category}</div>
            </td>
            <td class="px-6 py-4 text-slate-700">${booking.description}</td>
            <td class="px-6 py-4">
                <span class="px-2 py-1 text-xs font-medium rounded-full ${
                    booking.status === 'Pending' ? 'bg-yellow-100 text-yellow-800' :
                    booking.status === 'In Progress' ? 'bg-blue-100 text-blue-800' :
                    booking.status === 'Ready' ? 'bg-green-100 text-green-800' :
                    'bg-gray-100 text-gray-800'
                }">${booking.status}</span>
            </td>
            <td class="px-6 py-4 text-sm text-slate-500">${new Date(booking.created_at).toLocaleDateString()}</td>
            <td class="px-6 py-4 text-center">
                <div class="flex gap-2 justify-center">
                    <button onclick="updateBookingStatus(${booking.id}, 'In Progress')" class="text-blue-600 hover:text-blue-800 text-sm font-medium px-2 py-1 rounded hover:bg-blue-50">
                        Start
                    </button>
                    <button onclick="updateBookingStatus(${booking.id}, 'Ready')" class="text-green-600 hover:text-green-800 text-sm font-medium px-2 py-1 rounded hover:bg-green-50">
                        Ready
                    </button>
                    <button onclick="updateBookingStatus(${booking.id}, 'Claimed')" class="text-purple-600 hover:text-purple-800 text-sm font-medium px-2 py-1 rounded hover:bg-purple-50">
                        Claimed
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function updateCompletedBookingsTable(bookings) {
    const tbody = document.getElementById('completed-bookings-table-body');
    if (bookings.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-12 text-center text-slate-400">No completed bookings found.</td></tr>';
        return;
    }

    tbody.innerHTML = bookings.map(booking => `
        <tr>
            <td class="px-6 py-4 font-mono font-bold text-slate-900">${booking.tracking_id}</td>
            <td class="px-6 py-4">
                <div class="font-medium text-slate-900">${booking.username}</div>
                <div class="text-sm text-slate-500">${booking.user_email || 'N/A'}</div>
            </td>
            <td class="px-6 py-4">
                <div class="font-medium text-slate-900">${booking.model}</div>
                <div class="text-sm text-slate-500">${booking.category}</div>
            </td>
            <td class="px-6 py-4 text-slate-700">${booking.description}</td>
            <td class="px-6 py-4 text-sm text-slate-500">${new Date(booking.created_at).toLocaleDateString()}</td>
            <td class="px-6 py-4 text-center">
                <div class="flex gap-2 justify-center">
                    <button onclick="viewBookingDetails(${booking.id})" class="text-blue-600 hover:text-blue-800 text-sm font-medium px-2 py-1 rounded hover:bg-blue-50">
                        View
                    </button>
                    <button onclick="editCompletedBooking(${booking.id})" class="text-orange-600 hover:text-orange-800 text-sm font-medium px-2 py-1 rounded hover:bg-orange-50">
                        Edit
                    </button>
                    <button onclick="deleteCompletedBooking(${booking.id})" class="text-red-600 hover:text-red-800 text-sm font-medium px-2 py-1 rounded hover:bg-red-50">
                        Delete
                    </button>
                </div>
            </td>
        </tr>
    `).join('');
}

function updateStats(stats) {
    document.getElementById('total-count').textContent = stats.total;
    document.getElementById('pending-count').textContent = stats.pending;
    document.getElementById('in-progress-count').textContent = stats.in_progress;
    document.getElementById('completed-count').textContent = stats.completed;
}

// Update booking status
async function updateBookingStatus(bookingId, status) {
    try {
        const response = await fetch('../actions/admin_update_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ booking_id: bookingId, status: status })
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Booking status updated successfully!', 'success');
            loadAllBookings(); // Reload the tables
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    }
}

// QR Code Scanner Functions
function openScanModal() {
    document.getElementById('scanModal').classList.remove('hidden');
    document.getElementById('scan-result').classList.add('hidden');
    currentScannedBooking = null;
}

function closeScanModal() {
    document.getElementById('scanModal').classList.add('hidden');
    stopScanning();
}

function startScanning() {
    const videoElement = document.getElementById('qr-video');
    const startBtn = document.getElementById('start-scan-btn');
    const stopBtn = document.getElementById('stop-scan-btn');

    codeReader = new ZXing.BrowserQRCodeReader();

    codeReader.decodeFromVideoDevice(null, videoElement, (result, err) => {
        if (result) {
            console.log('QR Code detected:', result.text);
            processScannedQR(result.text);
        }
        if (err && !(err instanceof ZXing.NotFoundException)) {
            console.error('QR Code scan error:', err);
        }
    }).then(() => {
        startBtn.classList.add('hidden');
        stopBtn.classList.remove('hidden');
    }).catch(err => {
        console.error('Error starting scanner:', err);
        showNotification('Error accessing camera. Please check permissions.', 'error');
    });
}

function stopScanning() {
    if (codeReader) {
        codeReader.reset();
        codeReader = null;
    }
    document.getElementById('start-scan-btn').classList.remove('hidden');
    document.getElementById('stop-scan-btn').classList.add('hidden');
}

function processScannedQR(qrText) {
    // Parse QR code data
    // Expected format: "ServicePro Tracking ID: 1001\nCategory: Laptop\nModel: Dell XPS 13"
    const lines = qrText.split('\n');
    let trackingId = null;

    for (const line of lines) {
        if (line.startsWith('ServicePro Tracking ID:')) {
            trackingId = parseInt(line.split(':')[1].trim());
            break;
        }
    }

    if (trackingId) {
        // Stop scanning
        stopScanning();

        // Fetch booking details
        fetchBookingByTrackingId(trackingId);
    } else {
        showNotification('Invalid QR code format.', 'error');
    }
}

async function fetchBookingByTrackingId(trackingId) {
    try {
        const response = await fetch(`../actions/admin_get_booking.php?tracking_id=${trackingId}`);
        const result = await response.json();

        if (result.success) {
            currentScannedBooking = result.booking;
            displayScanResult(result.booking);
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error fetching booking details.', 'error');
    }
}

function displayScanResult(booking) {
    const scanResult = document.getElementById('scan-result');
    const scanDetails = document.getElementById('scan-details');

    scanDetails.innerHTML = `
        <strong>ID:</strong> ${booking.tracking_id} |
        <strong>Device:</strong> ${booking.model} (${booking.category}) |
        <strong>Customer:</strong> ${booking.username}
    `;

    scanResult.classList.remove('hidden');
}

async function markAsComplete() {
    if (!currentScannedBooking) return;

    try {
        const response = await fetch('../actions/admin_update_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                booking_id: currentScannedBooking.id,
                status: 'Claimed'
            })
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Booking marked as completed!', 'success');
            closeScanModal();
            loadAllBookings(); // Reload the tables
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    }
}

function viewBookingDetails(bookingId) {
    // For now, just show an alert. Could be expanded to show a modal with full details
    alert('View booking details functionality can be implemented here.');
}

// View booking details
async function viewBookingDetails(bookingId) {
    try {
        const response = await fetch(`../actions/admin_get_booking.php?id=${bookingId}`);
        const result = await response.json();

        if (result.success) {
            const booking = result.booking;
            displayBookingDetails(booking);
            document.getElementById('viewBookingModal').classList.remove('hidden');
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error loading booking details.', 'error');
    }
}

function closeViewModal() {
    document.getElementById('viewBookingModal').classList.add('hidden');
}

function displayBookingDetails(booking) {
    const contentDiv = document.getElementById('bookingDetailsContent');

    const statusColor = {
        'Pending': 'bg-yellow-100 text-yellow-800',
        'In Progress': 'bg-blue-100 text-blue-800',
        'Ready': 'bg-green-100 text-green-800',
        'Claimed': 'bg-purple-100 text-purple-800'
    };

    contentDiv.innerHTML = `
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="space-y-4">
                <div>
                    <h4 class="font-bold text-slate-900 mb-2">Booking Information</h4>
                    <div class="bg-slate-50 rounded-lg p-4 space-y-2">
                        <p><span class="font-medium">Tracking ID:</span> <span class="font-mono font-bold">${booking.tracking_id}</span></p>
                        <p><span class="font-medium">Status:</span> <span class="px-2 py-1 text-xs font-medium rounded-full ${statusColor[booking.status]}">${booking.status}</span></p>
                        <p><span class="font-medium">Created:</span> ${new Date(booking.created_at).toLocaleString()}</p>
                    </div>
                </div>

                <div>
                    <h4 class="font-bold text-slate-900 mb-2">Customer Information</h4>
                    <div class="bg-slate-50 rounded-lg p-4 space-y-2">
                        <p><span class="font-medium">Name:</span> ${booking.username}</p>
                        <p><span class="font-medium">Email:</span> ${booking.email}</p>
                    </div>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <h4 class="font-bold text-slate-900 mb-2">Device Information</h4>
                    <div class="bg-slate-50 rounded-lg p-4 space-y-2">
                        <p><span class="font-medium">Category:</span> ${booking.category}</p>
                        <p><span class="font-medium">Model:</span> ${booking.model}</p>
                        <p><span class="font-medium">Description:</span> ${booking.description}</p>
                    </div>
                </div>

                ${booking.qr_code_path ? `
                <div>
                    <h4 class="font-bold text-slate-900 mb-2">QR Code</h4>
                    <div class="bg-slate-50 rounded-lg p-4 flex justify-center">
                        <img src="../${booking.qr_code_path}" alt="QR Code" class="w-32 h-32 border rounded">
                    </div>
                </div>
                ` : ''}
            </div>
        </div>
    `;
}

// Edit completed booking
async function editCompletedBooking(bookingId) {
    try {
        const response = await fetch(`../actions/admin_get_booking.php?id=${bookingId}`);
        const result = await response.json();

        if (result.success) {
            const booking = result.booking;

            // Populate the edit form
            document.getElementById('editBookingId').value = booking.id;
            document.getElementById('editCategory').value = booking.category;
            document.getElementById('editModel').value = booking.model;
            document.getElementById('editDescription').value = booking.description;
            document.getElementById('editStatus').value = booking.status;

            // Show the modal
            document.getElementById('editBookingModal').classList.remove('hidden');
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error loading booking details.', 'error');
    }
}

function closeEditModal() {
    document.getElementById('editBookingModal').classList.add('hidden');
}

// Delete completed booking
let bookingToDelete = null;

async function deleteCompletedBooking(bookingId) {
    try {
        const response = await fetch(`../actions/admin_get_booking.php?id=${bookingId}`);
        const result = await response.json();

        if (result.success) {
            const booking = result.booking;
            bookingToDelete = booking;

            // Populate delete confirmation details
            const detailsDiv = document.getElementById('deleteBookingDetails');
            detailsDiv.innerHTML = `
                <div class="space-y-2">
                    <p><strong>Tracking ID:</strong> ${booking.tracking_id}</p>
                    <p><strong>Customer:</strong> ${booking.username}</p>
                    <p><strong>Device:</strong> ${booking.model} (${booking.category})</p>
                    <p><strong>Description:</strong> ${booking.description}</p>
                </div>
            `;

            // Show the modal
            document.getElementById('deleteBookingModal').classList.remove('hidden');
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('Error loading booking details.', 'error');
    }
}

function closeDeleteModal() {
    document.getElementById('deleteBookingModal').classList.add('hidden');
    bookingToDelete = null;
}

async function confirmDeleteBooking() {
    if (!bookingToDelete) return;

    try {
        const response = await fetch('../actions/admin_delete_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ booking_id: bookingToDelete.id })
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Booking deleted successfully!', 'success');
            closeDeleteModal();
            loadAllBookings(); // Reload the tables
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    }
}

// Create booking functionality
function openCreateBookingModal() {
    document.getElementById('createBookingModal').classList.remove('hidden');
}

function closeCreateModal() {
    document.getElementById('createBookingModal').classList.add('hidden');
    document.getElementById('createBookingForm').reset();
}

// Handle create form submission
document.addEventListener('DOMContentLoaded', function() {
    loadAllBookings();

    // Edit form submission
    const editForm = document.getElementById('editBookingForm');
    if (editForm) {
        editForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(editForm);
            const data = Object.fromEntries(formData);

            try {
                const response = await fetch('../actions/admin_update_booking.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    showNotification('Booking updated successfully!', 'success');
                    closeEditModal();
                    loadAllBookings(); // Reload the tables
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            }
        });
    }

    // Create form submission
    const createForm = document.getElementById('createBookingForm');
    if (createForm) {
        createForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(createForm);
            const data = Object.fromEntries(formData);

            try {
                const response = await fetch('../actions/admin_create_booking.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const result = await response.json();

                if (result.success) {
                    showNotification('Booking created successfully!', 'success');
                    closeCreateModal();
                    loadAllBookings(); // Reload the tables
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            }
        });
    }
});

// Notification system
function showNotification(message, type = 'info') {
    // Remove any existing notifications
    const existingNotifications = document.querySelectorAll('.notification');
    existingNotifications.forEach(notification => notification.remove());

    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification fixed top-4 right-4 z-50 px-6 py-4 rounded-xl shadow-lg transform translate-x-full transition-transform duration-300 ${
        type === 'success' ? 'bg-green-500 text-white' :
        type === 'error' ? 'bg-red-500 text-white' :
        'bg-blue-500 text-white'
    }`;

    notification.innerHTML = `
        <div class="flex items-center gap-3">
            <span class="text-lg">${
                type === 'success' ? '✓' :
                type === 'error' ? '✕' :
                'ℹ'
            }</span>
            <span class="font-medium">${message}</span>
        </div>
    `;

    // Add to page
    document.body.appendChild(notification);

    // Animate in
    setTimeout(() => {
        notification.classList.remove('translate-x-full');
    }, 100);

    // Auto remove after 5 seconds
    setTimeout(() => {
        notification.classList.add('translate-x-full');
        setTimeout(() => {
            if (notification.parentNode) {
                notification.parentNode.removeChild(notification);
            }
        }, 300);
    }, 5000);
}