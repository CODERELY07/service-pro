<?php
    require_once __DIR__ . '/../../config/functions.php'; 
    include BASE_PATH . 'includes/forms/includes.php';
?>

<div class="bg-white p-8 rounded-2xl shadow-2xl w-full max-w-md border border-slate-100 mx-4">
    <p class="text-center mb-4">
        <?php 
            include BASE_PATH . 'includes/components/logo.php'; 
        ?>
    </p>
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-lg font-bold">Welcome Back</h2>
        <?php 
            $current_page = basename($_SERVER['PHP_SELF']);
            if($current_page !== "login.php"): 
        ?>
            <button onclick="closeModal('loginModal')" class="text-slate-400 hover:text-slate-600 text-2xl">&times;</button>
        <?php endif;?>
    </div>
    
    <form id="loginForm" class="space-y-5">
       <div id="responseMessage" class="hidden p-4 rounded-xl text-sm mb-6 font-medium border"></div>
        <div>
            <label class="block text-sm font-semibold mb-2">Email</label>
            <input type="email" name="email" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition" placeholder="Enter email" required>
        </div>
        <div class="relative">
            <label class="block text-sm font-semibold mb-2">Password</label>
            <input type="password" name="password" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition" placeholder="••••••••" required>
            <div class="text-right mt-1">
                <a href="<?= BASE_URL ?>forgot-password.php" class="text-xs text-blue-600 hover:underline">Forgot Password?</a>
            </div>
        </div>

        <div class="flex justify-start gap-1 items-center">
            <input type="checkbox" name="remember_me" id="remember_me" value="1" <?= isset($_POST['remember_me']) ? 'checked' : '' ?>>
            <label class="text-xs text-slate-600 font-medium cursor-pointer" for="remember_me">Remember Me</label>
        </div>
        
        <button type="submit" class="w-full bg-blue-600 text-white py-4 rounded-xl font-bold hover:bg-blue-700 shadow-lg shadow-blue-200 transition-all active:scale-[0.98]">
            Sign In
        </button>
    </form>

    <p class="text-center mt-6 text-slate-500 text-sm">
        New here? <a href="<?= BASE_URL ?>signup.php" class="text-blue-600 font-semibold hover:underline">Create an account</a>
    </p>
</div>