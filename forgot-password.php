<?php
include './includes/header.php';

// Get the current step from URL, default to 1
$step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

// Security: If user tries to skip to Step 2 or 3 without a session, kick them back
if ($step > 1 && !isset($_SESSION['reset_email'])) {
    header("Location: forgot-password.php?step=1");
    exit();
}
?>

<div class="flex items-center justify-center min-h-[80vh] px-4">
    <div class="max-w-md w-full bg-white rounded-3xl shadow-xl p-8 border border-gray-100">
        
        <div class="text-center mb-8">
            <h2 class="text-2xl font-bold text-gray-900">Reset Password</h2>
            <p class="text-gray-500 text-sm mt-2">
                <?php 
                    if($step == 1) echo "Enter your email to get a verification code.";
                    if($step == 2) echo "We sent a 6-digit code to <br><b class='text-indigo-600'>".htmlspecialchars($_SESSION['reset_email'])."</b>";
                    if($step == 3) echo "Create a new secure password for your account.";
                ?>
            </p>
        </div>

        <form method="POST" action="actions/reset_password_process.php?step=<?php echo $step; ?>" class="space-y-6">
            
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
                    <a href="process-reset.php?resend=1" class="text-xs text-indigo-600 hover:underline">Resend Code?</a>
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
            <a href="login.php" class="text-sm text-gray-400 hover:text-indigo-600 transition-colors">Return to Login</a>
        </div>
    </div>
</div>