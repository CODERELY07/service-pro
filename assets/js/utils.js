

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
            <button onclick="updateStatus(${b.id}, 'Cancelled')" class="${btnClass} text-red-600 hover:bg-red-50">Reject</button>
            <button onclick="updateStatus(${b.id}, 'In Progress')" class="${btnClass} bg-blue-600 text-white hover:bg-blue-700">Accept Repair</button>
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
    }
    
    footer.innerHTML = buttons;
}

export function showNotification(message, type = 'info') {  
    const existing = document.querySelectorAll('.notification');
    existing.forEach(n => n.remove());

    const notification = document.createElement('div');

    const baseClasses = "notification fixed top-6 right-6 z-[100] px-6 py-4 rounded-2xl shadow-2xl shadow-slate-200/50 transform translate-x-[120%] transition-all duration-500 ease-out flex items-center gap-3 border";
    
    const typeClasses = {
        success: 'bg-green-500/90 backdrop-blur-md text-white border-green-400',
        error: 'bg-red-500/90 backdrop-blur-md text-white border-red-400',
        info: 'bg-slate-900/90 backdrop-blur-md text-white border-slate-700'
    };

    notification.className = `${baseClasses} ${typeClasses[type] || typeClasses.info}`;

    const icons = {
        success: '✓',
        error: '✕',
        info: 'ℹ'
    };

    notification.innerHTML = `
        <span class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-full bg-white/20 text-sm font-bold">
            ${icons[type] || icons.info}
        </span>
        <span class="font-bold tracking-tight text-sm">${message}</span>
    `;

    document.body.appendChild(notification);


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
