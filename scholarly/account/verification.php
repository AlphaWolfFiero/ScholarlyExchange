<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "scholarlyexchangedb";


$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}


$sql = "SELECT * FROM prof_ver WHERE status = 'Pending'"; 
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Professional Verification</title>
    <link rel="stylesheet" href="home.css">
</head>
<body>
<div class="header">
    <div class="logo">Scholarly Exchange</div>

    <!-- Search Bar and Button (Updated to be inline) -->
    <div class="search-container" style="display: flex; align-items: center;">
        <input type="text" id="searchInput" placeholder="Search Scholarly Exchange" onkeyup="searchQuestions()" style="margin-right: 10px;">
        <button type="button" class="btn btn-primary" onclick="searchQuestions()">Search</button>
    </div>

    <div class="menu">
        <a href="home.php"><button>Home</button></a>
        <a href="logout.php"><button>Logout</button></a>
        <a href="profile.php"><button>Profile</button></a>
    </div>
</div>
<div class="sidebar">
    <div class="logo-container">
        <h2> </h2>
    </div>
</div>
<div class="main-content">
    <h2>Professional Verification</h2>
    <?php
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $email = $row['email'];
            
            $stmt1 = $conn->prepare("SELECT user_name FROM users WHERE email = ?");
            $stmt1->bind_param("s", $email);
            $stmt1->execute();
            $result1 = $stmt1->get_result();
            $row1 = $result1->fetch_assoc();
            
            
            $imgur = htmlspecialchars($row['imgur']);
            echo '<div class="post">';
            echo '<h3>Username: ' . htmlspecialchars($row1['user_name']) . '</h3>';
            echo '<p><strong>Verification Status:</strong> Not Verified</p>';
            echo '<p><strong>Imgur Link:</strong> <a href="' . $imgur . '" target="_blank">here</a></p>';
            echo '<form method="POST" action="verify.php">';
            echo '<input type="hidden" name="ver_username" value="' . htmlspecialchars($row1['user_name']) . '">';
            echo '<button type="submit" class="btn btn-primary">Verify</button>';
            echo '</form>';
            echo '</div>';
        }
    } else {
        echo '<p>No professionals need verification.</p>';
    }
    ?>
</div>
</body>
</html>
