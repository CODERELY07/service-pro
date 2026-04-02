
export function openModal(modalId) {
    const modal = document.getElementById(modalId);
    const overlay = document.getElementById(modalId + '-overlay');

    if (modal) modal.classList.remove('hidden');
    if (overlay) overlay.classList.remove('hidden');

    document.body.style.overflow = 'hidden';
}

export function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    const overlay = document.getElementById(modalId + '-overlay');

    if (modal) modal.classList.add('hidden');
    if (overlay) overlay.classList.add('hidden');

    document.body.style.overflow = 'auto';
}


window.openModal = openModal;
window.closeModal = closeModal;


document.addEventListener('DOMContentLoaded', function() {
    const overlays = document.querySelectorAll('[id$="-overlay"]');
    overlays.forEach(overlay => {
        overlay.addEventListener('click', function() {
            const modalId = this.id.replace('-overlay', '');
            closeModal(modalId);
        });
    });
});

