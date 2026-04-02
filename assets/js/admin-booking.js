import {loadAllBookings} from './admin-dashboard.js';
import { showNotification } from './utils.js';

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

async function updateStatus(id, newStatus) {
    if (!confirm(`Move this booking to ${newStatus}?`)) return;
    
    await updateBookingStatus(id, newStatus); 
    closeModal('detailsModal');
    loadAllBookings();
}

window.updateStatus = updateStatus;
window.updateBookingStatus = updateBookingStatus;