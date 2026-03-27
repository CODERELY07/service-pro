// Dashboard specific JavaScript functionality

async function loadBookings() {
    try {
        const response = await fetch('./../actions/fetch_bookings.php');
        const bookings = await response.json();

        if (response.ok) {
            updateTable(bookings);
            updateStats(bookings);
        } else {
            console.error('Error loading bookings:', bookings.error);
        }
    } catch (error) {
        console.error('Error:', error);
    }
}

function updateTable(bookings) {
    const tbody = document.getElementById('bookings-table-body');
    if (bookings.length === 0) {
        tbody.innerHTML = '<tr><td colspan="6" class="px-6 py-12 text-center text-slate-400">No active bookings found.</td></tr>';
        return;
    }

    tbody.innerHTML = bookings.map(booking => `
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
                    'bg-gray-100 text-gray-800'
                }">${booking.status}</span>
            </td>
            <td class="px-6 py-4 text-center">
                ${booking.qr_code_path ? `<img src="./../${booking.qr_code_path}" alt="QR Code" class="w-12 h-12 mx-auto rounded border">` : '-'}
            </td>
            <td class="px-6 py-4 text-right">
                <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">View Details</button>
            </td>
        </tr>
    `).join('');
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

// Load bookings on page load
document.addEventListener('DOMContentLoaded', loadBookings);