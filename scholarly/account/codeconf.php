<?php
    $servername = "localhost"; 
    $username = "root"; 
    $password = ""; 
    $dbname = "scholarlyexchangedb"; 
    
    $conn = new mysqli($servername, $username, $password, $dbname);
    
    $stmp = $conn->prepare("SELECT err FROM vercode");
    
        
        $stmp->execute();
    
        
        $result = $stmp->get_result();
    
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $ver_error = $row["err"];
            }
        }
    
    if($ver_error == 1){
        echo '<script>alert("Incorrect Code. Please Try Again.")</script>';
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Veification - Scholarly</title>
    <link rel="stylesheet" href="scholarly.css">
</head>
<body>

<div class="login-container">
    <div class="login-box">
        <h1 class="logo">Scholarly</h1>
        <p>A 6-digit verification code had been sent to your email</p>

        <form action="codeconf1.php" method="POST" class="login-form">
            <label for="vercode">Verification Code</label>
            <input type="text" id="vcode" name="vcode" placeholder="Enter 6-digit code" required>

            <button type="submit" class="login-button">Confim Code</button>
        </form>

        <!--<p>Did not receive an email?</p><a href="vercode_sent.php"> Resend now. </a>-->
    </div>
</div>

</body>
</html>

