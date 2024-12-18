<?php
session_start();

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "scholarlyexchangedb";


$conn = new mysqli($servername, $username, $password, $dbname);


if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$stmt = $conn->prepare("SELECT email FROM current LIMIT 1");
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $email = $row["email"];

    
    $delete_stmt = $conn->prepare("DELETE FROM users WHERE email = ?");
    $delete_stmt->bind_param("s", $email);

    if ($delete_stmt->execute()) {
        
        $clear_stmt = $conn->prepare("DELETE FROM current WHERE email = ?");
        $clear_stmt->bind_param("s", $email);
        $clear_stmt->execute();
        
        
        session_destroy();
        $stmt = $conn->prepare("DELETE FROM current");
        $stmt->execute();
        $stmt->close();
        header("Location: goodbye.php"); 
        exit();
    } else {
        echo "Error: Unable to delete account.";
    }

    $delete_stmt->close();
} else {
    echo "Error: No user found to delete.";
}

$stmt->close();
$conn->close();
?>
