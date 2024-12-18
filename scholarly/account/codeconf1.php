<?php
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "scholarlyexchangedb"; 

$conn = new mysqli($servername, $username, $password, $dbname);

$stmp = $conn->prepare("SELECT code FROM vercode");

    
    $stmp->execute();

    
    $result = $stmp->get_result();

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $code = $row["code"];
        }
    }

$code_in = $_POST['vcode'];

if ($code_in == $code) {
    $stmp = $conn->prepare("DELETE FROM vercode");
    $stmp->execute();
    $conn->close();
    header('Location: accdeets.php');
} else {
    $stmt = $conn->prepare("UPDATE vercode SET err = 1");

    
    $stmt->execute();
    
    $stmt->close();
    $conn->close();
    header('Location: codeconf.php');
}