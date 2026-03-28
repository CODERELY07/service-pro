<?php
    require_once __DIR__ . '/config.php';
    // die(__DIR__ . 'config/');
    $page_scripts = [];

    function add_script($path) {
        global $page_scripts;
        $page_scripts[] = $path;
    }
    
   
?>