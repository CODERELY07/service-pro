

export function resetButton(btn, text) {
    btn.disabled = false;
    btn.innerHTML = text;
}

export async function handleFormSubmit(form, endpoint, onSuccess, step) {
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();
     

        submitBtn.disabled = true;
        submitBtn.innerHTML = `<svg class="animate-spin h-5 w-5 mr-3 border-b-2 border-white rounded-full inline-block" viewBox="0 0 24 24"></svg> Processing...`;

        try {
            const formData = new FormData(form);

           if (step !== null && step !== undefined){
                formData.append('step', step);
            }

            const response = await fetch(endpoint, {
                method: 'POST',
                body: formData
            });
                        
           
            const result = await response.json();
            // console.log(result);
            if (result.status === 'success') {
                showNotification(result.message, 'success');
                if (onSuccess) onSuccess(result);
                form.reset();
            } else {
                showNotification(result.message, 'error');
            }
        } catch (error) {
            console.error("Fetch Error:", error);
            showNotification("A connection error occurred.", 'error');
        } finally {
            resetButton(submitBtn, originalBtnText);
        }
    });
}

async function openBookingDetails(id, isAdmin = false) {
      console.log('Fetching audit trail for booking ID:', id);
    const modal = document.getElementById('detailsModal');
    const content = document.getElementById('modalContent');
    const footer = document.getElementById('modalFooter');

   
    if (!modal || !content) return;

    modal.classList.remove('hidden');
    if (footer) {
        footer.innerHTML = '';
        footer.classList.add('hidden');
    }
    
    content.innerHTML = `<div class="animate-pulse p-10 text-center text-slate-400 font-medium">Loading details...</div>`;
     console.log('hello');
    try {
        const fetchPath = isAdmin 
            ? `../actions/admin/admin_get_booking.php?id=${id}` 
            : `/service-pro/actions/book/get_booking_details.php?id=${id}`;

        const response = await fetch(fetchPath);
        const result = await response.json();
        
    
        const b = (isAdmin && result.booking) ? result.booking : result;

        if (b) {
            content.innerHTML = `
                <div class="space-y-6">
                    <div class="flex justify-between items-start">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest">Tracking ID</p>
                            <p class="text-xl font-mono font-bold text-blue-600">#${b.tracking_id}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                            ${b.status}
                        </span>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        ${isAdmin ? `
                        <div class="col-span-2 pb-2 border-b border-slate-50">
                            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest">Customer</p>
                            <p class="font-bold text-slate-900">${b.username || 'Client ID: ' + b.user_id}</p>
                        </div>` : ''}
                        <div>
                            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest">Device Model</p>
                            <p class="font-bold text-slate-900">${b.model}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-slate-400 text-[10px] font-bold uppercase tracking-widest">Category</p>
                            <p class="font-bold text-slate-900">${b.category}</p>
                        </div>
                    </div>

                    <div class="p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <p class="text-[10px] text-slate-400 font-bold uppercase tracking-widest mb-1">Issue Description</p>
                        <p class="text-slate-700 italic text-sm leading-relaxed">"${b.description}"</p>
                    </div>

                    ${b.total_price && b.total_price > 0 ? `
                    <div class="p-4 bg-green-50 rounded-2xl border border-green-100">
                        <p class="text-[10px] text-green-600 font-bold uppercase tracking-widest mb-1">Service Price</p>
                        <p class="text-green-700 font-bold text-lg">₱${parseFloat(b.total_price).toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})}</p>
                    </div>
                    ` : ''}

                    ${b.reject_reason ? `
                    <div class="p-4 bg-red-50 rounded-2xl border border-red-100">
                        <p class="text-[10px] text-red-600 font-bold uppercase tracking-widest mb-1">Reject Reason</p>
                        <p class="text-red-700 text-sm leading-relaxed">${b.reject_reason}</p>
                    </div>
                    ` : ''}

                    <div class="flex flex-col items-center pt-4 border-t border-slate-100">
                        <p class="text-[10px] font-bold text-slate-400 uppercase mb-4">
                            ${isAdmin ? 'Database Reference QR' : 'Present this for Pickup'}
                        </p>
                        <div class="bg-white p-4 rounded-3xl border-4 border-slate-50 shadow-sm transition hover:shadow-md">
                            <img src="/service-pro/${b.qr_code_path}" alt="QR" class="w-44 h-44 object-contain">
                        </div>
                        <p class="mt-3 font-mono text-[10px] text-slate-400 font-bold tracking-tighter">${b.tracking_id}</p>
                    </div>
                </div>
            `;

            if (isAdmin && footer) {
                renderAdminActions(b, footer);
            }
        }


    } catch (error) {
        content.innerHTML = `<p class="text-red-500 text-center font-medium">Error: Could not retrieve data.</p>`;
    }

    if (isAdmin) {
        await loadAdminAudit(id);
    } else {
        await loadClientTimeline(id);
    }
}

function renderAdminActions(b, footer) {
    footer.classList.remove('hidden');
    let buttons = '';

    const btnClass = "px-6 py-2 rounded-xl font-bold text-sm transition shadow-sm active:scale-95";

    if (b.status === 'Pending') {
        buttons = `
            <button onclick="openRejectModal(${b.id})" class="${btnClass} text-red-600 hover:bg-red-50">Reject</button>
            <button onclick="openAcceptModal(${b.id})" class="${btnClass} bg-blue-600 text-white hover:bg-blue-700">Accept & Quote</button>
        `;
    } else if (b.status === 'In Progress') {
        buttons = `
            <button onclick="updateStatus(${b.id}, 'Pending')" class="${btnClass} text-slate-500 hover:bg-slate-100">Re-queue</button>
            <button onclick="updateStatus(${b.id}, 'Ready')" class="${btnClass} bg-green-600 text-white hover:bg-green-700">Mark Ready</button>
        `;
    } else if (b.status === 'Ready') {
        buttons = `
            <button onclick="updateStatus(${b.id}, 'In Progress')" class="${btnClass} bg-orange-500 text-white hover:bg-orange-600">Back to Progress</button>
        `;
    } else if (b.status === 'Waiting Client Confirmation') {
        buttons = `
            <button onclick="updateStatus(${b.id}, 'Pending')" class="${btnClass} text-slate-500 hover:bg-slate-100">Cancel Quote</button>
        `;
    }

    footer.innerHTML = buttons;
}

export function showNotification(message, type = 'info') {
    // Remove existing  s
    const existing = document.querySelectorAll('.notification');
    existing.forEach(n => n.remove());

    const notification = document.createElement('div');

    const baseClasses = "notification fixed top-6 right-6 z-[100] px-6 py-4 rounded-2xl shadow-2xl shadow-slate-200/50 transform translate-x-[120%] transition-all duration-500 ease-out flex items-center gap-3 border";
    
    const typeClasses = {
        success: 'bg-green-500/90 backdrop-blur-md text-white border-green-400',
        error: 'bg-red-500/90 backdrop-blur-md text-white border-red-400',
        info: 'bg-slate-900/90 backdrop-blur-md text-white border-slate-700',
        warning: 'bg-yellow-500/90 backdrop-blur-md text-white border-yellow-400'
    };

    notification.className = `${baseClasses} ${typeClasses[type] || typeClasses.info}`;
    
    const iconSvg = {
        success: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>',
        error: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>',
        info: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
        warning: '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>'
    };

    notification.innerHTML = `
        ${iconSvg[type] || iconSvg.info}
        <span class="font-medium">${message}</span>
    `;

    document.body.appendChild(notification);
    
    // Trigger animation
    setTimeout(() => notification.classList.remove('translate-x-[120%]'), 10);

    setTimeout(() => {
        notification.classList.add('translate-x-[120%]');
        setTimeout(() => notification.remove(), 500);
    }, 4000);
}


async function loadClientTimeline(id) {
    const container = document.getElementById('modalContent');
    const res = await fetch('/service-pro/actions/get_client_timeline.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id: id })
    });
    const logs = await res.json();

    let html = `<div class="mt-8 pt-6 border-t border-slate-100"><h4 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-6">Repair Progress</h4><div class="relative pl-8 space-y-8"><div class="absolute left-[11px] top-2 bottom-2 w-0.5 bg-slate-100"></div>`;

    logs.forEach((log, i) => {
        const isLatest = i === 0;
        const date = new Date(log.created_at).toLocaleString('en-PH', { month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' });
        html += `
            <div class="relative">
                <div class="absolute -left-[26px] mt-1.5 w-3 h-3 rounded-full ${isLatest ? 'bg-blue-600 ring-4 ring-blue-50' : 'bg-slate-300'}"></div>
                <div>
                    <p class="text-sm ${isLatest ? 'text-slate-900 font-bold' : 'text-slate-500'}">${log.status}</p>
                    <p class="text-[10px] text-slate-400">${date}</p>
                </div>
            </div>`;
    });
    container.innerHTML += html + `</div></div>`;
}

async function loadAdminAudit(id) {
   
    const container = document.getElementById('modalContent');
    const res = await fetch('/service-pro/actions/get_admin_audit.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ id: id })
    });
    const logs = await res.json();

    let html = `<div class="mt-8 pt-6 border-t border-slate-100"><h4 class="text-xs font-bold text-slate-900 mb-4">Internal Audit Log</h4><div class="space-y-3">`;
    
    logs.forEach(log => {
        html += `
            <div class="bg-slate-50 p-3 rounded-xl border border-slate-100">
                <p class="text-xs font-bold text-slate-700">${log.old_value} → ${log.new_value}</p>
                <p class="text-[10px] text-slate-500 mt-1">Updated by ${log.username} on ${new Date(log.created_at).toLocaleString()}</p>
            </div>`;
    });
    container.innerHTML += html + `</div></div>`;
}

window.openBookingDetails = openBookingDetails;
window.showNotification = showNotification;

// Admin modal functions
let currentBookingId = null;

function openRejectModal(bookingId) {
    currentBookingId = bookingId;
    document.getElementById('rejectReason').value = '';
    document.getElementById('rejectModal').classList.remove('hidden');
}

function openAcceptModal(bookingId) {
    currentBookingId = bookingId;
    document.getElementById('servicePrice').value = '';
    document.getElementById('acceptModal').classList.remove('hidden');
}

async function confirmReject() {
    const reason = document.getElementById('rejectReason').value.trim();
    if (!reason) {
        showNotification('Please enter a reject reason', 'error');
        return;
    }

    try {
        const response = await fetch('./../actions/admin/admin_update_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                booking_id: currentBookingId,
                status: 'Cancelled',
                reject_reason: reason
            })
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Booking rejected successfully', 'success');
            closeModal('rejectModal');
            closeModal('detailsModal');
            // Refresh the page or reload bookings
            if (typeof loadAllBookings === 'function') {
                loadAllBookings();
            } else {
                location.reload();
            }
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    }
}

async function confirmAccept() {
    const price = parseFloat(document.getElementById('servicePrice').value);
    if (!price || price <= 0) {
        showNotification('Please enter a valid service price', 'error');
        return;
    }

    try {
        const response = await fetch('./../actions/admin/admin_update_booking.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                booking_id: currentBookingId,
                status: 'Waiting Client Confirmation',
                total_price: price
            })
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Quote sent to client successfully', 'success');
            closeModal('acceptModal');
            closeModal('detailsModal');
            // Refresh the page or reload bookings
            if (typeof loadAllBookings === 'function') {
                loadAllBookings();
            } else {
                location.reload();
            }
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    }
}

// Client offer functions
async function acceptOffer() {
    const bookingId = window.currentBookingId || currentBookingId;
    if (!bookingId) {
        showNotification('Booking information not loaded. Please try again.', 'error');
        return;
    }

    try {
        const response = await fetch('/service-pro/actions/client_offer_response.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                booking_id: bookingId,
                action: 'accept'
            })
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Offer accepted successfully', 'success');
            closeModal('offerModal');
            location.reload();
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    }
}

function rejectOffer() {
    closeModal('offerModal');
    document.getElementById('rejectOfferModal').classList.remove('hidden');
    // Reset radio buttons
    const radios = document.getElementsByName('rejectOfferReason');
    radios.forEach(radio => radio.checked = false);
    document.getElementById('otherReasonContainer').classList.add('hidden');
    document.getElementById('otherRejectReason').value = '';
}

async function confirmRejectOffer() {
    const bookingId = window.currentBookingId || currentBookingId;
    if (!bookingId) {
        showNotification('Booking information not loaded. Please try again.', 'error');
        return;
    }

    const selectedReason = document.querySelector('input[name="rejectOfferReason"]:checked');
    if (!selectedReason) {
        showNotification('Please select a reason', 'error');
        return;
    }

    let reason = selectedReason.value;
    if (reason === 'other') {
        const otherReason = document.getElementById('otherRejectReason').value.trim();
        if (!otherReason) {
            showNotification('Please specify the other reason', 'error');
            return;
        }
        reason = otherReason;
    } else if (reason === 'price') {
        reason = 'Client rejected due to price';
    }

    try {
        const response = await fetch('/service-pro/actions/client_offer_response.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                booking_id: bookingId,
                action: 'reject',
                reject_reason: reason
            })
        });

        const result = await response.json();

        if (result.success) {
            showNotification('Offer rejected successfully', 'success');
            closeModal('rejectOfferModal');
            location.reload();
        } else {
            showNotification(result.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showNotification('An error occurred. Please try again.', 'error');
    }
}

// Event listeners for radio buttons
document.addEventListener('DOMContentLoaded', function() {
    const radios = document.getElementsByName('rejectOfferReason');
    radios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'other') {
                document.getElementById('otherReasonContainer').classList.remove('hidden');
            } else {
                document.getElementById('otherReasonContainer').classList.add('hidden');
            }
        });
    });
});

window.openRejectModal = openRejectModal;
window.openAcceptModal = openAcceptModal;
window.confirmReject = confirmReject;
window.confirmAccept = confirmAccept;
window.acceptOffer = acceptOffer;
window.rejectOffer = rejectOffer;
window.confirmRejectOffer = confirmRejectOffer;
