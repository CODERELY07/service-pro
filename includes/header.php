<?php 
    require_once 'auth.php' 
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ServicePro | Repair Management</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <link href="./assets/css/main.css" rel="stylesheet">
    <script src="./assets/js/main.js" defer></script>
    <?php 
        if(isset($page_scripts) && is_array($page_scripts)):
          foreach($page_scripts as $script):    
    ?>
        <script src="<?= $script ?>" defer></script>
    <?php endforeach;
        endif;
    ?>
    
</head>
<body class="bg-slate-50 text-slate-900">
