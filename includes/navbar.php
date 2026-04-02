<nav class="bg-white border-b border-slate-200 py-4">
    <div class="container mx-auto px-6 flex justify-between items-center">
        <?php 
            include BASE_PATH . 'includes/components/logo.php'; 
        ?>
        <div class="flex items-center gap-6">
            <?php if(isset($_SESSION['user_id'])): ?>
                <div class="relative">
                    <button id="userMenuButton" class="flex items-center gap-2 text-slate-700 hover:text-slate-900 focus:outline-none">
                        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <span class="text-sm font-medium hidden md:block"><?= htmlspecialchars($_SESSION['username']) ?></span>
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    
                    <div id="userDropdown" class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-slate-200 py-2 z-50 hidden">
                     <?php if($_SESSION['role'] === 'admin'): ?>
                        <a href="<?= BASE_URL ?>admin/dashboard.php" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900 font-semibold">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                            Admin Dashboard
                        </a>

                        <div class="border-t border-slate-100 my-1"></div>
                        <div class="px-4 py-1 text-[10px] font-bold text-slate-400 uppercase tracking-wider">Manage Bookings</div>
                        
                        <a href="<?= BASE_URL ?>admin/bookings.php?status=active" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Active Bookings</a>
                        <a href="<?= BASE_URL ?>admin/bookings.php?status=completed" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Completed</a>
                        <a href="<?= BASE_URL ?>admin/bookings.php?status=cancelled" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Cancelled</a>
                    <?php else: ?>
                            <a href="<?= BASE_URL ?>user/dashboard.php" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5a2 2 0 012-2h4a2 2 0 012 2v2H8V5z"></path>
                                </svg>
                                Dashboard
                            </a>
                            <a href="<?= BASE_URL ?>user/profile.php" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50 hover:text-slate-900">
                                <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                </svg>
                                Profile
                            </a>
                        <?php endif; ?>
                        
                        <div class="border-t border-slate-200 my-1"></div>
                        
                        <a href="<?= BASE_URL ?>logout.php" class="block px-4 py-2 text-sm text-red-600 hover:bg-red-50 hover:text-red-700">
                            <svg class="w-4 h-4 inline mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
                            </svg>
                            Logout
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?= BASE_URL ?>login.php" class="text-sm font-semibold hover:text-blue-600">Login</a>
                <a href="<?= BASE_URL ?>signup.php" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-blue-700 transition">Get Started</a>
            <?php endif; ?>
        </div>
    </div>
</nav>