import { handleFormSubmit } from "./utils.js";

const forgotPassword = document.getElementById('forgotPassword');


if (forgotPassword) {
    const step = forgotPassword.dataset.step;
    handleFormSubmit(forgotPassword, './actions/auth/reset_password_process.php',  (result) => {
        if (result.redirectTo) {
            setTimeout(() => {
                window.location.href = projectPath+result.redirectTo;
            }, 1000);
        }
    }, step);
}

const signupForm = document.getElementById('signupForm');
if (signupForm) {
    handleFormSubmit(signupForm, './actions/auth/signup_process.php');
}

const loginForm = document.getElementById('loginForm');
if (loginForm) {
    handleFormSubmit(loginForm, './actions/auth/login_process.php', (result) => {
        if (result.redirectTo) {
            setTimeout(() => {
                window.location.href = projectPath+result.redirectTo;
            }, 1000);
        }
    });
}