<?php

    if ($_SERVER['HTTP_HOST'] == 'localhost') {
        define('BASE_URL', '/service-pro'); 
    } else {
        define('BASE_URL', '/'); 
    }
