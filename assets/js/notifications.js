// Notification system
document.addEventListener('DOMContentLoaded', function() {
    const notificationButton = document.getElementById('notificationButton');
    const notificationDropdown = document.getElementById('notificationDropdown');
    const notificationBadge = document.getElementById('notificationBadge');

    if (notificationButton && notificationDropdown) {
        // Toggle dropdown
        notificationButton.addEventListener('click', function(e) {
            e.stopPropagation();
            notificationDropdown.classList.toggle('hidden');
        });

        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!notificationButton.contains(e.target) && !notificationDropdown.contains(e.target)) {
                notificationDropdown.classList.add('hidden');
            }
        });

        loadNotifications();
    }
});

async function loadNotifications() {
    try {
        const response = await fetch('/service-pro/actions/get_notifications.php');
        const text = await response.text();
        
        if (!response.ok) {
            console.error('HTTP Error:', response.status, response.statusText);
            console.error('Response:', text);
            return;
        }
        
        try {
            const data = JSON.parse(text);
            updateNotificationBadge(data.unread_count);
            renderNotifications(data.notifications);
        } catch (jsonError) {
            console.error('JSON Parse Error:', jsonError);
            console.error('Raw response:', text);
        }
    } catch (error) {
        console.error('Network Error:', error);
    }
}

function updateNotificationBadge(count) {
    const badge = document.getElementById('notificationBadge');
    if (badge) {
        if (count > 0) {
            badge.textContent = count > 99 ? '99+' : count;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }
}

function renderNotifications(notifications) {
    const container = document.getElementById('notificationList');
    if (!container) return;

    if (notifications.length === 0) {
        container.innerHTML = '<div class="px-4 py-3 text-center text-slate-500 text-sm">No notifications</div>';
        return;
    }

    container.innerHTML = notifications.map(notification => {
        const typeClasses = {
            'info': 'bg-blue-50 border-blue-200',
            'success': 'bg-green-50 border-green-200',
            'warning': 'bg-yellow-50 border-yellow-200',
            'error': 'bg-red-50 border-red-200'
        };

        const typeColors = {
            'info': 'text-blue-700',
            'success': 'text-green-700',
            'warning': 'text-yellow-700',
            'error': 'text-red-700'
        };

        const bgClass = typeClasses[notification.type] || 'bg-slate-50 border-slate-200';
        const textClass = typeColors[notification.type] || 'text-slate-700';

        return `
            <div class="px-4 py-3 border-l-4 ${bgClass} cursor-pointer hover:bg-opacity-75 transition notification-item ${!notification.is_read ? 'font-semibold' : ''}"
                 data-notification-id="${notification.id}"
                 onclick="handleNotificationClick(${notification.id}, '${notification.type}', ${notification.booking_id || 'null'})">
                <div class="flex justify-between items-start">
                    <div class="flex-1">
                        <p class="text-sm ${textClass}">${notification.title}</p>
                        <p class="text-xs text-slate-500 mt-1">${notification.message}</p>
                        <p class="text-xs text-slate-400 mt-1">${new Date(notification.created_at).toLocaleString()}</p>
                    </div>
                    ${!notification.is_read ? '<div class="w-2 h-2 bg-blue-500 rounded-full ml-2 flex-shrink-0"></div>' : ''}
                </div>
            </div>
        `;
    }).join('');
}

async function handleNotificationClick(notificationId, type, bookingId) {
    // Mark as read
    try {
        const response = await fetch('/service-pro/actions/mark_notification_read.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ notification_id: notificationId })
        });
        
        if (!response.ok) {
            console.error('Failed to mark notification as read:', response.status);
            return;
        }
    } catch (error) {
        console.error('Error marking notification as read:', error);
        return;
    }


    loadNotifications();
}

async function showRejectReasonModal(bookingId) {
    try {
        const response = await fetch(`/service-pro/actions/book/get_booking_details.php?id=${bookingId}`);
        
        if (!response.ok) {
            console.error('Failed to fetch booking details:', response.status);
            return;
        }
        
        const booking = await response.json();
        
        if (booking && booking.reject_reason) {
            const modal = document.createElement('div');
            modal.className = 'fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm px-4';
            modal.innerHTML = `
                <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full overflow-hidden border border-slate-100">
                    <div class="p-6 border-b border-slate-50 flex justify-between items-center">
                        <h3 class="text-xl font-bold text-slate-900">Service Request Rejected</h3>
                        <button onclick="this.parentElement.parentElement.parentElement.remove()" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
                    </div>
                    <div class="p-6">
                        <p class="text-slate-600 mb-4">Your service request #${booking.tracking_id} has been rejected.</p>
                        <div class="p-4 bg-red-50 rounded-2xl border border-red-100">
                            <p class="text-sm font-bold text-red-700 mb-2">Reason:</p>
                            <p class="text-red-700">${booking.reject_reason}</p>
                        </div>
                    </div>
                    <div class="p-6 bg-slate-50 border-t border-slate-100 flex justify-end">
                        <button onclick="this.parentElement.parentElement.parentElement.remove()" class="px-6 py-2 bg-slate-900 text-white rounded-xl font-bold hover:bg-slate-800 transition">Close</button>
                    </div>
                </div>
            `;
            document.body.appendChild(modal);
        }
    } catch (error) {
        console.error('Error loading reject reason:', error);
    }
}

async function showOfferModal(bookingId) {
    try {
        const response = await fetch(`/service-pro/actions/book/get_booking_details.php?id=${bookingId}`);
        
        if (!response.ok) {
            console.error('Failed to fetch booking details:', response.status);
            return;
        }
        
        const booking = await response.json();
        
        if (booking && booking.total_price) {
            window.currentBookingId = bookingId;
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

// Export functions for global use
window.loadNotifications = loadNotifications;
window.showRejectReasonModal = showRejectReasonModal;
window.showOfferModal = showOfferModal;