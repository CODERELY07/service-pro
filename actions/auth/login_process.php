<?php
    declare(strict_types=1);
    header("Content-Type: application/json");
    session_start();
    require_once __DIR__ . '/../../config/functions.php';
    require_once BASE_PATH . 'config/db.php';

    
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $remember_me = isset($_POST['remember_me']);

        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        $response = ["status" => "error", "message" => "something went wrong!", "redirectTo" => "/"];

        // error_log("LOGIN_PROCESS_ERROR: " . $e->getMessage() . " in " . $e->getFile() . " on line " . $e->getLine());
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if($remember_me){
                $selector = bin2hex(random_bytes(12));
                $validator = bin2hex(random_bytes(32)); 
                $expiry = date('Y-m-d H:i:s', time() + 86400 * 30); 

                $hashedValidator = password_hash($validator, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("INSERT INTO user_tokens (selector, hashed_validator, user_id, expiry)  VALUES (?, ?, ?, ?)");
                $stmt->execute([$selector, $hashedValidator,$user['id'],$expiry]);

                setcookie(
                    'remember_me',$selector . ':' . $validator,time() + 86400 * 30,
                    '/','', true, true
                );
              
            }

            if($_SESSION['role'] === 'admin'){
                $response['redirectTo'] = "/admin/dashboard.php";
            }else{
                $response['redirectTo'] = "/";
            }
            $response['message'] = "Login Successfully!";
            $response['status'] = "success";
        } else {
           $response['message'] = "Invalid credentials";
        }
    }

    echo json_encode($response);
    exit();
?>