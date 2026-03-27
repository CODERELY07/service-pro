// Common modal functionality
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    const overlay = document.getElementById(modalId + '-overlay');

    if (modal) {
        modal.classList.remove('hidden');
    }

    if (overlay) {
        overlay.classList.remove('hidden');
    }

    // Prevent body scrolling when modal is open
    document.body.style.overflow = 'hidden';
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    const overlay = document.getElementById(modalId + '-overlay');

    if (modal) {
        modal.classList.add('hidden');
    }

    if (overlay) {
        overlay.classList.add('hidden');
    }

    // Restore body scrolling
    document.body.style.overflow = 'auto';
}

// Close modal when clicking on overlay
document.addEventListener('DOMContentLoaded', function() {
    const overlays = document.querySelectorAll('[id$="-overlay"]');
    overlays.forEach(overlay => {
        overlay.addEventListener('click', function() {
            const modalId = this.id.replace('-overlay', '');
            closeModal(modalId);
        });
    });
});