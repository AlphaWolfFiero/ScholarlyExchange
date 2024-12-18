<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "scholarlyexchangedb"; 


require 'vendor/autoload.php';

$conn = new mysqli($servername, $username, $password, $dbname);


$mail = new PHPMailer(true);

function generateSixDigitCode() {
    
    $prcode = mt_rand(100000, 999999);
    return $prcode;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];

    
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = '$email'");
    $stmt->execute();
    $result=$stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $username = $row["user_name"];
                $email = $row["email"];
            }
        }else{
            echo '<script>alert("Account Not Found.")
            window.location.href = "reset_password.php";</script>';
        }
        $stmt->close();

    
try {
    $stmt = $conn->prepare("DELETE FROM user_info");
    $stmt->execute();
    $stmt->close();

    session_start();
    $prcode = generateSixDigitCode(); 
    $_SESSION['username'] = $username;
    $stmt = $conn->prepare("INSERT INTO vercode (email, code) VALUES (?, ?)");
    $stmt->bind_param("si", $email, $prcode);
    $stmt->execute();
    $stmt->close();

    $stmt = $conn->prepare("SELECT user_name, email, pass_word FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();  
    $result=$stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $username = $row["user_name"];
            $email = $row["email"];
            $password = $row["pass_word"];
        }
    $stmt = $conn->prepare("INSERT INTO user_info (user_name, email, pass_word) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $username, $email, $password);
    $stmt->execute();
    }else{
        echo '<script>alert("Account Not Found.")
        window.location.href = "reset_password.php";</script>';
    }
    $stmt->close();
    
    $mail->SMTPDebug = 0;                      
    $mail->isSMTP();                                            
    $mail->Host       = 'smtp.gmail.com';                     
    $mail->SMTPAuth   = true;                                   
    $mail->Username   = '23-39744@g.batstate-u.edu.ph';                     
    $mail->Password   = 'risf uoai zdlf pwdl';                               
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            
    $mail->Port       = 465;                                    

    
    $mail->setFrom('23-39744@g.batstate-u.edu.ph', 'Scholarly Exchange');
    $mail->addAddress($email, $username);     
    
    
    
    

    
    
    

    
    $mail->isHTML(true);                                  
    $mail->Subject = 'Scholarly Exchange Password Reset Code';
    $mail->Body    = "Your 6-digit code is <b>'$prcode'</b>";

    $mail->send();
    $conn->close();
    header("Location: rpassconf.php");
    exit();
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}
}
?>
