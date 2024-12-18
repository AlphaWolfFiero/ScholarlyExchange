<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "scholarlyexchangedb";


$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ver_username'])) {
    $username = $_POST['ver_username'];

    
    $stmt = $conn->prepare("UPDATE prof_ver SET status = 'Approved' WHERE email = (SELECT email FROM users WHERE user_name = ?)");
    $stmt->bind_param("s", $username);
    
    if ($stmt->execute()) {
        
        echo "<script>
            alert('Verification successful!');
            window.location.href = 'verification.php';
        </script>";
    } else {
        
        echo "<script>
            alert('Error verifying user.');
            window.location.href = 'verification.php';
        </script>";
    }

    $stmt->close();
} else {
    
    header("Location: verification.php");
    exit();
}

$conn->close();
?>
