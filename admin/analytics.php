<?php

    require_once __DIR__ . '/../config/functions.php';
    add_script('assets/js/admin-analytics.js');
    require_once BASE_PATH . 'config/db.php';
    require_once BASE_PATH . 'includes/header.php';
    require_once BASE_PATH . 'includes/navbar.php';
   
     if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../index.php");
        exit();
    }


?>

<main class="container mx-auto px-6 py-12">
  <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Total Bookings</p>
        <h3 id="total-bookings" class="text-3xl font-black text-slate-900 mt-2">0</h3>
    </div>
    <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 border-l-4 border-l-green-500">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Completed</p>
        <h3 id="completed-bookings" class="text-3xl font-black text-slate-900 mt-2">0</h3>
    </div>
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100 border-l-4 border-l-red-500">
        <p class="text-xs font-bold text-slate-400 uppercase tracking-widest">Cancelled</p>
        <h3 id="cancelled-bookings" class="text-3xl font-black text-slate-900 mt-2">0</h3>
    </div>
    <div class="bg-blue-600 p-6 rounded-2xl shadow-lg text-white">
        <p class="text-xs font-bold text-blue-100 uppercase tracking-widest">Daily Earnings</p>
        <h3 id="daily-earn" class="text-3xl font-black mt-2">₱0.00</h3>
    </div>
    <div class="bg-green-600 p-6 rounded-2xl shadow-lg text-white">
        <p class="text-xs font-bold text-green-100 uppercase tracking-widest">Weekly Earnings</p>
        <h3 id="weekly-earn" class="text-3xl font-black mt-2">₱0.00</h3>
    </div>
    <div class="bg-purple-600 p-6 rounded-2xl shadow-lg text-white">
        <p class="text-xs font-bold text-purple-100 uppercase tracking-widest">Monthly Earnings</p>
        <h3 id="monthly-earn" class="text-3xl font-black mt-2">₱0.00</h3>
    </div>
   </div>

   <!-- Charts Section -->
   <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
       <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
           <h4 class="text-lg font-bold text-slate-900 mb-4">Monthly Earnings (Current Year)</h4>
           <canvas id="earningsChart"></canvas>
       </div>
       <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-100">
           <h4 class="text-lg font-bold text-slate-900 mb-4">Monthly Bookings (Current Year)</h4>
           <canvas id="bookingsChart"></canvas>
       </div>
   </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
