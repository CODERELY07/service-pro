<?php 
    include_once 'config/functions.php';
    add_script('./assets/js/signup.js'); 
    require 'includes/header.php'; 
    require 'includes/navbar.php'; 
?>
<div class="min-h-[85vh] flex items-center justify-center px-4">
    <div class="bg-white p-8 rounded-3xl shadow-2xl shadow-slate-200 w-full max-w-md border border-slate-100">
        <div class="text-center mb-8">
            <h2 class="text-3xl font-bold text-slate-900">Create Account</h2>
            <p class="text-slate-500 mt-2">Join ServicePro to track your repairs.</p>
        </div>
        <div id="responseMessage" class="hidden p-4 rounded-xl text-sm mb-6 font-medium border"></div>
        <form id="signupForm" class="space-y-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Username</label>
                <input type="text" name="username" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition bg-slate-50 focus:bg-white" placeholder="Choose a username" required>
            </div>
             <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Email</label>
                <input type="text" name="email" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition bg-slate-50 focus:bg-white" placeholder="Choose a email" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
                <input type="password" name="password" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition bg-slate-50 focus:bg-white" placeholder="••••••••" required>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">Confirm Password</label>
                <input type="password" name="confirm_password" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-blue-500 outline-none transition bg-slate-50 focus:bg-white" placeholder="••••••••" required>
            </div>
            
            <button type="submit" class="w-full bg-slate-900 text-white py-4 rounded-xl font-bold hover:bg-black shadow-lg transition-all active:scale-[0.98] mt-2">
                Create Account
            </button>
        </form>
        
        <p class="text-center mt-8 text-slate-500 text-sm">
            Already have an account? 
            <a href="index.php" class="text-blue-600 font-bold hover:underline">Sign In</a>
        </p>
    </div>
</div>  