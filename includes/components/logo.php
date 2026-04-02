<?php 
    // Determine the Home URL based on session and role
    $home_path = 'index.php';
    
    if (isset($_SESSION['user_id'])) {
        // Redirect to the correct dashboard folder based on role
        $home_path = ($_SESSION['role'] === 'admin') ? 'admin/dashboard.php' : 'user/dashboard.php';
    }
    $final_url = BASE_URL . $home_path;
?>

<a href="<?= $final_url ?>" class="text-xl font-bold tracking-tight text-blue-600">
    Service<span class="text-slate-900">Pro</span>
</a>    