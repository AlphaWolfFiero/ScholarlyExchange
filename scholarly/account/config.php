<?php
$host = 'localhost';  // Change this if you're using a different host
$db = 'scholarly_db'; // Your database name
$user = 'root';       // Your database username
$pass = '';           // Your database password (leave blank for XAMPP default)

$conn = new mysqli($host, $user, $pass, $db);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
