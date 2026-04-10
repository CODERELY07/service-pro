export async function loadBookings() {
    try {
  
      const response = await fetch('/service-pro/actions/book/fetch_bookings.php');
        
        const text = await response.text();

        try {
            const bookings = JSON.parse(text);
            if (response.ok) {
                updateTable(bookings);
                updateStats(bookings);
            }
        } catch (jsonError) {
            console.error("PHP sent back HTML instead of JSON. Here is the error:");
            console.log(text); 
        }
    } catch (error) {
        console.error('Network Error:', error);
    }
}

function updateTable(bookings) {
    const tbody = document.getElementById('bookings-table-body');
    
    if (bookings.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-12 text-center text-slate-400">No active bookings found.</td></tr>';
        return;
    }

    tbody.innerHTML = bookings.map(booking => {
        let actionButton = '';
        let priceDisplay = '';

        if (booking.total_price && booking.total_price > 0) {
            priceDisplay = `<div class="text-sm font-bold text-green-600 mt-1">₱${parseFloat(booking.total_price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</div>`;
        }

        if (booking.status === 'Cancelled') {
            // Check if cancelled by admin
            if (booking.canceled_by_role === 'admin') {
                actionButton = `<button onclick="showCancelReasonModal('${booking.tracking_id}', '${booking.reject_reason || 'No reason provided'}')" class="text-slate-600 hover:text-slate-800 text-sm font-bold">View Reason</button>`;
            } else {
                actionButton = `<button onclick="statusHandler('${booking.tracking_id}', 'undo')" class="text-blue-600 hover:text-blue-800 text-sm font-bold">Undo Cancel</button>`;
            }
        } else if (booking.status === 'Pending') {
            actionButton = `<button onclick="statusHandler('${booking.tracking_id}', 'cancel')" class="text-red-600 hover:text-red-800 text-sm font-medium">Cancel</button>`;
        } else if (booking.status === 'Waiting Client Confirmation') {
            actionButton = `
                <div class="flex gap-2">
                    <button onclick="showOfferModal(${booking.id})" class="text-green-600 hover:text-green-800 text-sm font-bold">Accept Offer</button>
                    <button onclick="showRejectOfferModal(${booking.id})" class="text-red-600 hover:text-red-800 text-sm font-medium">Reject Offer</button>
                </div>
            `;
        } else {
            actionButton = `<span class="text-slate-400 text-xs italic">Locked</span>`;
        }

        return `
        <tr>
            <td class="px-6 py-4 font-mono font-bold text-slate-900">${booking.tracking_id}</td>
            <td class="px-6 py-4">
                <div class="font-medium text-slate-900">${booking.model}</div>
                <div class="text-sm text-slate-500">${booking.category}</div>
                ${priceDisplay}
            </td>
            <td class="px-6 py-4 text-slate-700">${booking.description}</td>
            <td class="px-6 py-4">
                <span class="px-2 py-1 text-xs font-medium rounded-full ${
                    booking.status === 'Pending' ? 'bg-yellow-100 text-yellow-800' :
                    booking.status === 'Waiting Client Confirmation' ? 'bg-purple-100 text-purple-800' :
                    booking.status === 'In Progress' ? 'bg-blue-100 text-blue-800' :
                    booking.status === 'Ready' ? 'bg-green-100 text-green-800' :
                    booking.status === 'Cancelled' ? 'bg-red-100 text-red-800' :
                    'bg-gray-100 text-gray-800'
                }">${booking.status}</span>
            </td>
            <td class="px-6 py-4 text-center">
                ${booking.qr_code_path ? `<img src="./../${booking.qr_code_path}" alt="QR Code" class="w-12 h-12 mx-auto rounded border">` : '-'}
            </td>
            <td class="px-6 py-4 text-right flex justify-end gap-3">
                <button onclick="openBookingDetails(${booking.id})" class="text-blue-600 hover:text-blue-800 text-sm font-bold transition flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                </svg>
                View
            </button>
                ${actionButton}
            </td>
        </tr>
        `;
    }).join('');
}

function updateStats(bookings) {
    const stats = {
        pending: 0,
        'waiting-confirmation': 0,
        'in-progress': 0,
        ready: 0
    };

    bookings.forEach(booking => {
        if (booking.status === 'Pending') stats.pending++;
        else if (booking.status === 'Waiting Client Confirmation') stats['waiting-confirmation']++;
        else if (booking.status === 'In Progress') stats['in-progress']++;
        else if (booking.status === 'Ready') stats.ready++;
    });

    document.getElementById('pending-count').textContent = stats.pending;
    document.getElementById('waiting-confirmation-count').textContent = stats['waiting-confirmation'];
    document.getElementById('in-progress-count').textContent = stats['in-progress'];
    document.getElementById('ready-count').textContent = stats.ready;
}

function showRejectOfferModal(bookingId) {
    window.currentBookingId = bookingId;
    const modal = document.getElementById('rejectOfferModal');
    if (modal) {
        // Reset radio buttons
        const radios = document.getElementsByName('rejectOfferReason');
        radios.forEach(radio => radio.checked = false);
        document.getElementById('otherReasonContainer').classList.add('hidden');
        document.getElementById('otherRejectReason').value = '';
        modal.classList.remove('hidden');
    }
}

async function showOfferModal(bookingId) {
    window.currentBookingId = bookingId;
    try {
        const response = await fetch(`/service-pro/actions/book/get_booking_details.php?id=${bookingId}`);
        
        if (!response.ok) {
            console.error('Failed to fetch booking details:', response.status);
            return;
        }
        
        const booking = await response.json();
        
        if (booking && booking.total_price) {
            const modal = document.getElementById('offerModal');
            const content = document.getElementById('offerContent');

            content.innerHTML = `
                <p class="text-slate-600 mb-4">A quote has been provided for your service request #${booking.tracking_id}.</p>
                <div class="p-4 bg-green-50 rounded-2xl border border-green-100">
                    <p class="text-sm font-bold text-green-700 mb-2">Service Price:</p>
                    <p class="text-2xl font-bold text-green-700">₱${parseFloat(booking.total_price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                </div>
                <p class="text-sm text-slate-500 mt-4">Would you like to accept or reject this quote?</p>
            `;

            modal.classList.remove('hidden');
        }
    } catch (error) {
        console.error('Error loading offer:', error);
    }
}

function showCancelReasonModal(trackingId, reason) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm px-4';
    modal.innerHTML = `
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-100">
            <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                <h3 class="text-xl font-bold text-slate-900">Cancellation Notice</h3>
                <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
            </div>
            <div class="p-6">
                <p class="text-slate-600 mb-4">Your service request #${trackingId} has been cancelled by an administrator.</p>
                <div class="p-4 bg-red-50 rounded-2xl border border-red-100">
                    <p class="text-sm font-bold text-red-700 mb-2">Reason:</p>
                    <p class="text-red-700">${reason}</p>
                </div>
                <p class="text-sm text-slate-500 mt-4">If you have questions, please contact support.</p>
            </div>
            <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end">
                <button onclick="this.parentElement.parentElement.parentElement.remove()" class="px-6 py-2 bg-slate-900 text-white rounded-xl font-bold hover:bg-slate-800 transition">Close</button>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
}

document.addEventListener('DOMContentLoaded', loadBookings);

window.updateStats = updateStats;
window.updateTable = updateTable;
window.showOfferModal = showOfferModal;
window.showRejectOfferModal = showRejectOfferModal;
window.showCancelReasonModal = showCancelReasonModal;