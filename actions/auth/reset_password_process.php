<?php
    header("Content-Type: application/json");
    session_start();

    require_once __DIR__ . '/../../config/functions.php';

    require_once BASE_PATH . 'config/db.php';
    

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    require_once BASE_PATH . 'vendor/autoload.php';

    $mail = new PHPMailer(true);
    // $mail->SMTPDebug = 0;

    $step = isset($_POST['step']) 
    ? (int)$_POST['step'] 
    : (isset($_GET['step']) ? (int)$_GET['step'] : 1);

    $response = ['message' => 'Something is wrong!', 'status' => 'error', 'redirectTo' => '/'];
  
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        if ($step == 1) {
            $email = $_POST['email'];

            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $user = $stmt->execute([$email]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                $generated_code = rand(100000, 999999);
                
                $_SESSION['reset_email'] = $email;
                $_SESSION['otp_code'] = $generated_code;

                // $mail->SMTPDebug = SMTP::DEBUG_SERVER;                 
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
                $_SESSION['email_sent'] = true;
                $response['message'] = 'Message has been sent';
                $response['status'] = 'success'; 
                $response['redirectTo'] = '/forgot-password.php?step=2';
            }else{
                $response['message'] = "You doesn't have account yet! Create your account first";
            }

            

        }  elseif ($step == 2) {
               $user_input_code = $_POST['verify_code'] ?? '';

            // Check if code matches session
            if ($user_input_code == $_SESSION['otp_code']) {

                $_SESSION['code_verified'] = true;
                $response['message'] = 'Success!';
                $response['status'] = 'success'; 
                $response['redirectTo'] = "/forgot-password.php?step=3";
                 
            } else {
                $response['message'] = "Invalid Code!";
            }
            $_SESSION['email_sent'] = false;
            unset($_SESSION['email_sent']);
            $_SESSION['resetting_password'] = true;
        } elseif ($step == 3) {
            $pass = $_POST['new_password'];
            $confirm = $_POST['confirm_password'];

            if ($pass === $confirm && isset($_SESSION['code_verified'])) {
                $hashed_password = password_hash($pass, PASSWORD_DEFAULT);
               
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
                $stmt->execute([$hashed_password, $_SESSION['reset_email']]);

                unset($_SESSION['reset_email']);
                unset($_SESSION['otp_code']);

                $response['message'] = 'Reset Passwrod Successfully!';
                $response['status'] = 'success'; 
                $response['redirectTo'] = "/login.php";
            } else {
                $response['message'] = "Password doesn't match !";
            }
            unset($_SESSION['resetting_password']);
        }
    }else {
        $response['redirectTo'] = "/forgot-password.php";
    }
    echo json_encode($response);
    exit();
?>