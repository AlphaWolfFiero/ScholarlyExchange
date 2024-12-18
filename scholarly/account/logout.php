<?php
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "scholarlyexchangedb"; 

$conn = new mysqli($servername, $username, $password, $dbname);

session_destroy();

$stmt = $conn->prepare("DELETE FROM current");
        $stmt->execute();
        $stmt->close();

header('Location: scholarly.php');
exit();
?>
