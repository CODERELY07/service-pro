<?php
    header('Content-Type: application/json');

    require './../config/db.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\SMTP;
    use PHPMailer\PHPMailer\Exception;

    require './../vendor/autoload.php';

    $response = ['status' => 'error' , 'message' => 'something went wrong!', 'redirectTo' => '/'];

    $mail = new PHPMailer(true);
    // $mail->SMTPDebug = 0;

    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = trim($_POST['username']);
        $password = $_POST['password'];
        $email = $_POST['email'];
        $confirm_password = $_POST['confirm_password'];

        if ($password !== $confirm_password) {
            $response['message'] = "Passwords do not match!";
        } else {
            // Check email
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
            $stmt->execute([$email]);
            $emailExists = $stmt->fetchColumn();

            // Check username
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE username = ?");
            $stmt->execute([$username]);
            $usernameExists = $stmt->fetchColumn();

            if ($emailExists) {
                $response['message'] = "Email already taken";
            } elseif ($usernameExists) {
                $response['message'] = "Username already taken";
            } else {
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $verification_code = md5(uniqid(rand(), true));

                $stmt = $pdo->prepare("INSERT INTO users (username, password ,email, verification_code) VALUES (?, ? , ?, ?)");

                if($stmt->execute([$username, $hashed_password, $email, $verification_code])){
                    try{
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
                        $mail->addAddress($email, $username);    
                        //Content
                        $mail->isHTML(true);                               
                        $mail->Subject = 'Email Verification';
                        $mail->Body    = "
                                            Please click the link below to verify your email address:\n\n
                                            
                                            Verify now: http://localhost/service-pro/verify.php?code=$verification_code";
                                            
                        $mail->AltBody = 'Please register again';
                        $mail->send();
                        $response['status'] = 'success';
                        $response['message'] = 'Account created! Please check your email to verify.';
                        // $response['redirectTo'] = '/login.php';
                    }catch(Exception $e){
                        $response['message'] = "Account created, but email failed to send.";
                    }
                }            
            }   
        }
    }


    echo json_encode($response); 
    exit;
?>
