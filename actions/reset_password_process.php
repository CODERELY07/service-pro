<?php
    session_start();
    require './../config/db.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    require './../vendor/autoload.php';

    $mail = new PHPMailer(true);
    $mail->SMTPDebug = 0;

    $step = isset($_GET['step']) ? (int)$_GET['step'] : 1;

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {

        if ($step == 1) {
            $email = $_POST['email'];

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $user = $stmt->execute([$email]);

            if($stmt->rowCount() > 0){
                $generated_code = rand(100000, 999999);
                
                $_SESSION['reset_email'] = $email;
                $_SESSION['otp_code'] = $generated_code;

                $mail->SMTPDebug = SMTP::DEBUG_SERVER;                 
                $mail->isSMTP();                                        
                $mail->Host       = 'smtp.gmail.com';                 
                $mail->SMTPAuth   = true;                                  
                $mail->Username   = 'calipjo.markely@gmail.com';                  
                $mail->Password   = 'darl ersz rcje luwl';                            
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;           
                $mail->Port       = 465;                                   

                //Recipients
                $mail->setFrom('calipjo.markely@gmail.com', 'Mailer');
                $mail->addAddress($email, '');    
       
                $mail->isHTML(true);                               
                $mail->Subject = 'Reset Password';
                $mail->Body    = "
                            
                        $generated_code
                ";
                                    
                $mail->AltBody = 'Please register again';
                $mail->send();
                echo 'Message has been sent';
            }   
            header("Location: ./../forgot-password.php?step=2");
            exit();

        } elseif ($step == 2) {
            $user_input_code = $_POST['verify_code'];

            // Check if code matches session
            if ($user_input_code == $_SESSION['otp_code']) {
                $_SESSION['code_verified'] = true;
                header("Location: ./../forgot-password.php?step=3");
            } else {
                header("Location: ./../forgot-password.php?step=2&error=invalid_code");
            }
            exit();

        } elseif ($step == 3) {
            $pass = $_POST['new_password'];
            $confirm = $_POST['confirm_password'];

            if ($pass === $confirm && isset($_SESSION['code_verified'])) {
                $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
               
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->execute([$hashed_password, $_SESSION['reset_email']]);

                unset($_SESSION['reset_email']);
                unset($_SESSION['otp_code']);
                header("Location: ./../index.php?reset=success");
            } else {
                header("Location: ./../forgot-password.php?step=3&error=mismatch");
            }
            exit();
        }
    } else {
        // If someone tries to access this file directly without POST
        header("Location: forgot-password.php");
        exit();
    }

?>