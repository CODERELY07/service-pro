<?php
    include_once 'config/functions.php';
    add_script('assets/js/utils.js'); 
    add_script('assets/js/auth.js'); 
    require_once BASE_PATH . 'includes/header.php';
    
?>
<section class="flex flex-column h-[100vh] justify-center items-center">
    <?php  require_once BASE_PATH . 'includes/forms/login.php';?>
</section>
