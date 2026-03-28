export function showFeedback(el, message, type) {
    el.innerText = message;
    el.classList.remove('hidden', 'bg-red-50', 'text-red-600', 'border-red-100', 'bg-green-50', 'text-green-600', 'border-green-100');
    const classes = type === 'error' 
        ? ['bg-red-50', 'text-red-600', 'border-red-100'] 
        : ['bg-green-50', 'text-green-600', 'border-green-100'];
    el.classList.add(...classes);
}

export function resetButton(btn, text) {
    btn.disabled = false;
    btn.innerHTML = text;
}

export async function handleFormSubmit(form, endpoint, onSuccess, step) {
    const responseMsg = document.getElementById('responseMessage');
    const submitBtn = form.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        submitBtn.disabled = true;
        submitBtn.innerHTML = `<svg class="animate-spin h-5 w-5 mr-3 border-b-2 border-white rounded-full inline-block" viewBox="0 0 24 24"></svg> Processing...`;
        responseMsg.classList.add('hidden');

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
                showFeedback(responseMsg, result.message, 'success');
                if (onSuccess) onSuccess(result);
                form.reset();
            } else {
                showFeedback(responseMsg, result.message, 'error');
            }
        } catch (error) {
            console.error("Fetch Error:", error);
            showFeedback(responseMsg, "A connection error occurred.", 'error');
        } finally {
            resetButton(submitBtn, originalBtnText);
        }
    });
}