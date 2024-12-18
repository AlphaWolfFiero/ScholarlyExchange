<?php
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "scholarlyexchangedb"; 


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


require 'vendor/autoload.php';


$mail = new PHPMailer(true);


$conn = new mysqli($servername, $username, $password, $dbname);


function generateSixDigitCode() {
    
    $code = mt_rand(100000, 999999);
    return $code;
}

try {
    $generatedCode = generateSixDigitCode();

    $stmp = $conn->prepare("SELECT user_name, email FROM user_info");

    
    $stmp->execute();

    
    $result = $stmp->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
        $email = $row["email"];
        $username = $row["user_name"];
        }
    }

    
    $stmt = $conn->prepare("INSERT INTO vercode (email, code) VALUES (?, ?)");
    $stmt->bind_param("si", $email, $generatedCode);
    $stmt->execute();

    
    $mail->SMTPDebug = 0;                      
    $mail->isSMTP();                                            
    $mail->Host       = 'smtp.gmail.com';                     
    $mail->SMTPAuth   = true;                                   
    $mail->Username   = '23-39744@g.batstate-u.edu.ph';                     
    $mail->Password   = 'risf uoai zdlf pwdl';                               
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            
    $mail->Port       = 465;                                    

    
    $mail->setFrom('23-39744@g.batstate-u.edu.ph', 'Scholarly');
    $mail->addAddress($email, $username);     

    
    $mail->isHTML(true);                                  
    $mail->Subject = 'Scholarly Exchange Verification Code';
    $mail->Body    = "Your 6-digit code is <b>'$generatedCode'</b>";

    $mail->send();
    $conn->close();
    header("Location: codeconf.php");
    exit();
} catch (Exception $e) {
    echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
}