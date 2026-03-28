<?php
    require_once __DIR__ . '/config/functions.php';
    add_script('assets/js/auth.js'); 

    include BASE_PATH . 'includes/header.php';

    $step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

    if ($step > 1 && !isset($_SESSION['reset_email'])) {
        header("Location: forgot-password.php?step=1");
        exit();
    }

    if(($step === 1 || $step === 3) && isset($_SESSION['email_sent'])){
        header("Location: forgot-password.php?step=2");
        exit();
    }

    if(($step === 1 || $step === 2) && isset($_SESSION['resetting_password'])){
        header("Location: forgot-password.php?step=3");
        exit();
    }

?>
<div class="flex items-center justify-center min-h-[80vh] px-4">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl p-8 border border-gray-100">
        
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-900">Reset Password</h2>
        </div>

        <form id="forgotPassword" data-step="<?= $step ?>" class="space-y-6">
            <div id="responseMessage" class="hidden p-4 rounded-xl text-sm mb-6 font-medium border"></div>
            <?php if ($step == 1): ?>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Email Address</label>
                    <input type="email" name="email" required placeholder="name@example.com" 
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl shadow-lg transition-all">
                    Send Verification Code
                </button>

            <?php elseif ($step == 2): ?>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1 text-center">6-Digit Code</label>
                    <input type="text" name="verify_code" maxlength="6" inputmode="numeric" placeholder="000000" required
                        class="w-full px-4 py-3 text-center text-2xl tracking-[0.5em] font-mono rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                </div>
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl shadow-lg transition-all">
                    Verify Code
                </button>
                <div class="text-center">
                    <a href="#" class="text-xs text-indigo-600 hover:underline">Resend Code?</a>
                </div>

            <?php elseif ($step == 3): ?>
                <div class="space-y-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">New Password</label>
                        <input type="password" name="new_password" placeholder="••••••••" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wider text-gray-400 mb-1">Confirm Password</label>
                        <input type="password" name="confirm_password" placeholder="••••••••" required
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
                    </div>
                </div>
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-semibold py-3 rounded-xl shadow-lg transition-all">
                    Update Password
                </button>
            <?php endif; ?>

        </form>

        <div class="mt-8 text-center">
            <a href=<?= BASE_URL . "login.php"?> class="text-sm text-gray-400 hover:text-indigo-600 transition-colors">Return to Login</a>
        </div>
    </div>
</div>