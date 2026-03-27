<?php
    session_start();
    require_once './../config/db.php';

    // if(isset($_SESSION['username'])){
    //     header("Location: index.php");
    // }

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $remember_me = isset($_POST['remember_me']);


        // die(var_dump($email, $password));
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();


        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];

            if($remember_me){
                $selector = bin2hex(random_bytes(12));
                $validator = bin2hex(random_bytes(32)); 
                $expiry = date('Y-m-d H:i:s', time() + 86400 * 30); // 30 days

                $hashedValidator = password_hash($validator, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare("INSERT INTO user_tokens (selector, hashed_validator, user_id, expiry)  VALUES (?, ?, ?, ?)");
                $stmt->execute([$selector, $hashedValidator,$user['id'],$expiry]);

                setcookie(
                    'remember_me',$selector . ':' . $validator,time() + 86400 * 30,
                    '/','', true, true
                );
            }

            // die(var_dump($_SESSION));
            if($_SESSION['role'] === 'admin'){
                header("Location: ./../admin/dashboard.php");
            }else{
                header("Location: ./../");
            }
          
            // exit();
        } else {
           echo "Invalid credentials";
        }
    }
?>