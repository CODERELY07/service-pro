
document.addEventListener('DOMContentLoaded', () => {
    const signupForm = document.getElementById('signupForm');

    if (!signupForm) return;

    const responseMsg = document.getElementById('responseMessage');
    const submitBtn = signupForm.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;

    signupForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        submitBtn.disabled = true;
        submitBtn.innerHTML = `
            <svg class="animate-spin h-5 w-5 mr-3 border-b-2 border-white rounded-full inline-block" viewBox="0 0 24 24"></svg>
            Processing...
        `;
        
        responseMsg.classList.add('hidden');

        const formData = new FormData(signupForm);

        try {
            const response = await fetch('./actions/signup_process.php', {
                method: 'POST',
                body: formData
            });

        
            const result = await response.json();

            if (result.status === 'success') {
                showFeedback(responseMsg, result.message, 'success');
                resetButton(submitBtn, originalBtnText);
                signupForm.reset();
            } else {
                showFeedback(responseMsg, result.message, 'error');
                resetButton(submitBtn, originalBtnText);
            }

           
        } catch (error) {
            console.error("Fetch Error:", error);
            showFeedback(responseMsg, "A connection error occurred. Please try again.", 'error');
            resetButton(submitBtn, originalBtnText);
        }
    });
});

/** * Helper function for clean UI updates
 */
function showFeedback(el, message, type) {
    el.innerText = message;
    el.classList.remove('hidden', 'bg-red-50', 'text-red-600', 'border-red-100', 'bg-green-50', 'text-green-600', 'border-green-100');
    
    if (type === 'error') {
        el.classList.add('bg-red-50', 'text-red-600', 'border-red-100');
    } else {
        el.classList.add('bg-green-50', 'text-green-600', 'border-green-100');
    }
}

function resetButton(btn, text) {
    btn.disabled = false;
    btn.innerHTML = '';
    btn.innerHTML = text;
}
