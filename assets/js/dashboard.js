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

        if (booking.status === 'Cancelled') {
            actionButton = `<button onclick="statusHandler('${booking.tracking_id}', 'undo')" class="text-blue-600 hover:text-blue-800 text-sm font-bold">Undo Cancel</button>`;
        } else if (booking.status === 'Pending') {
            actionButton = `<button onclick="statusHandler('${booking.tracking_id}', 'cancel')" class="text-red-600 hover:text-red-800 text-sm font-medium">Cancel</button>`;
        } else {
            actionButton = `<span class="text-slate-400 text-xs italic">Locked</span>`;
        }

        return `
        <tr>
            <td class="px-6 py-4 font-mono font-bold text-slate-900">${booking.tracking_id}</td>
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
        'in-progress': 0,
        ready: 0
    };

    bookings.forEach(booking => {
        if (booking.status === 'Pending') stats.pending++;
        else if (booking.status === 'In Progress') stats['in-progress']++;
        else if (booking.status === 'Ready') stats.ready++;
    });

    document.getElementById('pending-count').textContent = stats.pending;
    document.getElementById('in-progress-count').textContent = stats['in-progress'];
    document.getElementById('ready-count').textContent = stats.ready;
}

document.addEventListener('DOMContentLoaded', loadBookings);

window.updateStats = updateStats;
window.updateTable = updateTable;