let codeReader;
let currentScannedBooking = null;

document.addEventListener('DOMContentLoaded', function() {
    loadAllBookings();
});


export async function loadAllBookings() {
    try {
        const response = await fetch('./../actions/admin/admin_fetchAll_booking.php');
        const data = await response.json();

        if (response.ok) {
            const allBookings = [...data.active, ...data.completed];
            allBookings.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
            renderAllBookingsTable(allBookings);
            updateStats(data.stats);
        } else {
            console.error('Error loading bookings:', data.error);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

export function renderAllBookingsTable(bookings) {
    const tbody = document.getElementById('all-bookings-table-body');
    
    if (!tbody) return; 


    if (bookings.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-12 text-center text-slate-400">No bookings found.</td></tr>';
        return;
    }

    tbody.innerHTML = bookings.map(booking => {
        const statusClasses = {
            'Pending': 'bg-yellow-100 text-yellow-800',
            'In Progress': 'bg-blue-100 text-blue-800',
            'Ready': 'bg-green-100 text-green-800',
            'Claimed': 'bg-purple-100 text-purple-800'
        };
        const badgeClass = statusClasses[booking.status] || 'bg-gray-100 text-gray-800';

        return `
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
                <td class="px-6 py-4">
                    <span class="px-2 py-1 text-xs font-medium rounded-full ${badgeClass}">
                        ${booking.status}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-slate-500">
                    ${new Date(booking.created_at).toLocaleDateString()}
                </td>
            </tr>
        `;
    }).join('');
}


function updateStats(stats) {
    const total = document.getElementById('total-count');
    const pending = document.getElementById('pending-count');
    const progress = document.getElementById('in-progress-count');
    const completed = document.getElementById('completed-count');


    if (total) total.textContent = stats.total;
    if (pending) pending.textContent = stats.pending;
    if (progress) progress.textContent = stats.in_progress;
    if (completed) completed.textContent = stats.completed;
}

// Update booking status
export async function updateBookingStatus(bookingId, status) {
    try {
        const response = await fetch('./../actions/admin/admin_update_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ booking_id: bookingId, status: status })
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Booking status updated successfully!', 'success');
            loadAllBookings(); 
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    }
}
// View booking details
export async function viewBookingDetails(bookingId) {
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

function closeEditModal() {
    document.getElementById('editBookingModal').classList.add('hidden');
}


let bookingToDelete = null;

function closeDeleteModal() {
    document.getElementById('deleteBookingModal').classList.add('hidden');
    bookingToDelete = null;
}

function closeCreateModal() {
    document.getElementById('createBookingModal').classList.add('hidden');
    document.getElementById('createBookingForm').reset();
}

// Handle create form submission
document.addEventListener('DOMContentLoaded', function() {
    loadAllBookings();

    const editForm = document.getElementById('editBookingForm');
    if (editForm) {
        editForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const formData = new FormData(editForm);
            const data = Object.fromEntries(formData);

            try {
                const response = await fetch('./../actions/admin/admin_update_booking.php', {
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
                const response = await fetch('./../actions/admin/admin_create_booking.php', {
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
                    loadAllBookings(); 
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


window.updateBookingStatus = updateBookingStatus;