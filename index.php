<?php
    require_once __DIR__ . '/config/functions.php';
    
    add_script('assets/js/modal.js'); 
    add_script('assets/js/booking.js');

    require_once BASE_PATH . 'includes/header.php';
    require_once  BASE_PATH . 'includes/navbar.php';
?>
<section class="bg-white py-20 px-6">
    <div class="container mx-auto flex flex-col md:flex-row items-center justify-around">
        <div class="md:w-1/2 mb-10 md:mb-0">
            <span class="text-blue-600 font-bold tracking-widest uppercase text-sm">Fast • Reliable • Secure</span>
            <h1 class="text-5xl md:text-6xl font-extrabold text-slate-900 leading-tight mt-4">
                Professional Repair <br> <span class="text-blue-600">Simplified.</span>
            </h1>
            <p class="text-slate-500 text-lg mt-6 mb-10 max-w-lg">
                Track your device repairs in real-time. From booking to secure QR-code pickup, we’ve modernized the service lifecycle.
            </p>
            <div class="flex gap-4">
                <?php if(isset($_SESSION['user_id']) && isset($_SESSION['username']) && $_SESSION['role'] !== 'admin'):?>
                    <button onclick="openModal('bookingModal')" class="bg-blue-600 text-white px-8 py-4 rounded-2xl font-bold hover:bg-blue-700 shadow-xl shadow-blue-200 transition-all transform hover:-translate-y-1">
                        Book a Repair Now
                    </button>
                <?php else:?>
                    <button onclick="openModal('loginModal')" class="bg-blue-600 text-white px-8 py-4 rounded-2xl font-bold hover:bg-blue-700 shadow-xl shadow-blue-200 transition-all transform hover:-translate-y-1">
                        Book a Repair Now
                    </button>
                <?php endif;?>
                <a href="#features" class="bg-slate-100 text-slate-700 px-8 py-4 rounded-2xl font-bold hover:bg-slate-200 transition">
                    Learn More
                </a>
            </div>
        </div>
        
        <div class="md:w-1/3 w-full">
            <div class="relative max-w-lg lg:max-w-none mx-auto">
                <img src="https://images.unsplash.com/photo-1591405351990-4726e331f141?auto=format&fit=crop&q=80&w=1000" 
                        alt="Tech Repair" 
                        class="rounded-3xl shadow-2xl border border-slate-100 transform -rotate-2 hover:rotate-0 transition duration-500">
                
                <div class="absolute -bottom-6 -left-6 bg-white p-6 rounded-2xl shadow-xl border border-slate-50 hidden md:block">
                    <div class="flex items-center gap-4">
                        <div class="p-3 bg-green-100 text-green-600 rounded-xl font-bold text-xl">✓</div>
                        <div>
                            <p class="text-sm font-bold text-slate-900">QR Secure</p>
                            <p class="text-xs text-slate-500">Pickup Verification</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php
    include  BASE_PATH . 'includes/modals/sections/service-features.php';
    include  BASE_PATH . 'includes/modals/login.php';
    include  BASE_PATH . 'includes/modals/booking.php';
?>

<div class="fixed inset-0 bg-black bg-opacity-40 hidden" id="loginModal-overlay"></div>
<div class="fixed inset-0 bg-black bg-opacity-40 hidden" id="bookingModal-overlay"></div>

