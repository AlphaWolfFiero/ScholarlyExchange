<?php
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "scholarlyexchangedb"; 


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    
    if ($password === $confirm_password) {
        
        
        $stmt_check = $conn->prepare("SELECT email FROM users WHERE email = ?");
        $stmt_check->bind_param("s", $email);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows > 0) {
            
            echo '<script>alert("An account already uses that email. Please login or try a different email."); window.location.href = "scholarly.php";</script>';
        } else {
            
            $stmt = $conn->prepare("INSERT INTO user_info (email, user_name, pass_word) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $email, $username, $password);
            
            
            $stmt->execute();
            
            $stmt->close();
            
            
            header("Location: vercode_sent.php");
            exit(); 
        }
        
        
        $stmt_check->close();
    } else {
        echo '<script>alert("Passwords do not match. Please try again."); window.location.href = "register.php";</script>';
    }
}

$conn->close();
?>
