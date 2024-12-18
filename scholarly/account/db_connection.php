<?php
$servername = "localhost"; // or your server name
$username = "root"; // your MySQL username
$password = "YES"; // your MySQL password
$dbname = "scholarly"; // the name of your database

try {
    // Create connection
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Set the PDO error mode to exception
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully";

    // Your SQL query here
    $sql = "SELECT * FROM your_table"; // replace 'your_table' with your actual table name
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    // Set the resulting array to associative
    $result = $stmt->setFetchMode(PDO::FETCH_ASSOC);
    foreach ($stmt->fetchAll() as $row) {
        echo "id: " . $row["id"] . " - Name: " . $row["name"] . "<br>"; // adjust based on your columns
    }
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

$conn = null; // Close the connection
?>
