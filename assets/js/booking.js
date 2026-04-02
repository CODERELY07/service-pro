import { loadBookings } from "./dashboard.js";
import { closeModal } from "./modal.js";
import { showNotification } from "./utils.js";

document.addEventListener('DOMContentLoaded', function() {
    const bookingForm = document.getElementById('bookingForm');
    if (bookingForm) {
        bookingForm.addEventListener('submit', async function(e) {
            e.preventDefault();

            const submitButton = bookingForm.querySelector('button[type="submit"]');
            const originalText = submitButton.textContent;
            submitButton.textContent = 'Submitting...';
            submitButton.disabled = true;

            let path = "./actions/book/booking_process.php";
            const formData = new FormData(bookingForm);

            try {
                if(projectPath == '/service-pro/user') {
                    path = './../actions/book/booking_process.php';
                }   
                console.log(path, projectPath);
                const response = await fetch(path, {
                    method: 'POST',
                    body: formData
                });

                const result = await response.json();

                if (result.success) {
                    showSuccessModal(result.tracking_id, result.qr_code);

                    closeModal('bookingModal');
                    bookingForm.reset();

                 
                        loadBookings();
                    
                } else {
                    showNotification(result.message, 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                showNotification('An error occurred. Please try again.', 'error');
            } finally {
                submitButton.textContent = originalText;
                submitButton.disabled = false;
            }
        });
    }
});


function showSuccessModal(trackingId, qrCodePath) {
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

let currentTrackingId = null;
let currentAction = null;

function statusHandler(trackingId, action) {
    currentTrackingId = trackingId;
    currentAction = action;

    const modal = document.getElementById('statusModal');
    const title = document.getElementById('statusModalTitle');
    const desc = document.getElementById('statusModalDesc');
    const icon = document.getElementById('statusModalIcon');
    const confirmBtn = document.getElementById('statusConfirmBtn');


    if (action === 'cancel') {
        title.innerText = "Cancel Repair?";
        desc.innerText = "This will stop the process. You can undo this later if the tech hasn't started.";
        icon.innerHTML = "✕";
        icon.className = "w-16 h-16 rounded-2xl mb-6 flex items-center justify-center text-2xl bg-red-100 text-red-600";
        confirmBtn.className = "flex-1 px-6 py-3 rounded-xl font-bold text-white bg-red-600 hover:bg-red-700 shadow-lg shadow-red-200";
    } else {
        title.innerText = "Re-activate?";
        desc.innerText = "This will move your request back to 'Pending' for the admin to see.";
        icon.innerHTML = "↺";
        icon.className = "w-16 h-16 rounded-2xl mb-6 flex items-center justify-center text-2xl bg-blue-100 text-blue-600";
        confirmBtn.className = "flex-1 px-6 py-3 rounded-xl font-bold text-white bg-blue-600 hover:bg-blue-700 shadow-lg shadow-blue-200";
    }

    confirmBtn.onclick = executeStatusChange;

    modal.classList.remove('hidden');
}


async function executeStatusChange() {
    const btn = document.getElementById('statusConfirmBtn');
    btn.disabled = true;
    btn.innerText = "Processing...";

    try {
        const response = await fetch("./../actions/book/user/status_handler.php", {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `tracking_id=${encodeURIComponent(currentTrackingId)}&action=${encodeURIComponent(currentAction)}`
        });

        const result = await response.json();
        if (result.success) {
            closeModal('statusModal');
            loadBookings(); 
        }
    } catch (error) {
        console.error("Error:", error);
    } finally {
        btn.disabled = false;
        btn.innerText = "Yes, Confirm";
    }
}


window.statusHandler = statusHandler;
window.showSuccessModal = showSuccessModal;
window.closeSuccessModal = closeSuccessModal;
