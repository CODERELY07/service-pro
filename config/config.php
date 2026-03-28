<?php

   
    ini_set('display_errors', 0);

    ini_set('log_errors', 1);

    ini_set('error_log', __DIR__ . '/php-error.log');

    define('BASE_PATH', dirname(__DIR__) . DIRECTORY_SEPARATOR);
    define('BASE_URL', 'http://localhost/service-pro/');

    // die(BASE_PATH . 'includes\auth.php');