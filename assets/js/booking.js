// Booking form AJAX functionality

// Handle booking form submission
document.addEventListener('DOMContentLoaded', function() {
    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) {
        bookingForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            // Show loading state
            const submitButton = bookingForm.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.textContent = 'Submitting...';
            submitButton.disabled = true;

            const formData = new FormData(bookingForm);

            try {
                const response = await fetch('./actions/booking_process.php', {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    // Show success modal with tracking details
                    showSuccessModal(result.tracking_id, result.qr_code);

                    // Close booking modal and reset form
                    closeModal('bookingModal');
                    bookingForm.reset();

                    // Reload bookings if on dashboard page
                    if (typeof loadBookings === 'function') {
                        loadBookings();
                    }
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            } finally {
                // Reset button state
                submitButton.textContent = originalText;
                submitButton.disabled = false;
            }
        });
    }
});

// Notification system for user feedback
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

// Success modal for booking confirmation
function showSuccessModal(trackingId, qrCodePath) {
    // Create modal overlay
    const overlay = document.createElement('div');
    overlay.className = 'fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4';
    overlay.id = 'successModal-overlay';
    
    overlay.innerHTML = `
        <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-8 transform scale-95 transition-transform duration-300" id="successModalCard">
            <div class="text-center">
                <div class="w-16 h-16 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-slate-900 mb-2">Booking Confirmed!</h3>
                <p class="text-slate-600 mb-6">Your repair request has been submitted successfully.</p>
                
                <div class="bg-slate-50 rounded-xl p-4 mb-6">
                    <p class="text-sm text-slate-500 mb-2">Tracking ID</p>
                    <p class="text-2xl font-bold text-slate-900 font-mono">${trackingId}</p>
                </div>
                
                <div class="mb-6">
                    <p class="text-sm text-slate-500 mb-2">QR Code for Pickup</p>
                    <div class="bg-white p-4 rounded-xl border border-slate-200 inline-block">
                        <img src="${qrCodePath}" alt="QR Code" class="w-32 h-32">
                    </div>
                </div>
                
                <button onclick="closeSuccessModal()" class="w-full bg-blue-600 text-white py-3 rounded-xl font-bold hover:bg-blue-700 transition">
                    Continue
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(overlay);
    
    // Animate in
    setTimeout(() => {
        document.getElementById('successModalCard').classList.remove('scale-95');
    }, 100);
}

function closeSuccessModal() {
    const overlay = document.getElementById('successModal-overlay');
    if (overlay) {
        document.getElementById('successModalCard').classList.add('scale-95');
        setTimeout(() => {
            overlay.remove();
        }, 300);
    }
}