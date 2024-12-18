<?php

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "scholarlyexchangedb";


$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


$stmt = $conn->prepare("SELECT email FROM current");
$stmt->execute();
$result = $stmt->get_result();
$email = $result->fetch_assoc()['email'];
$stmt->close();
$stmt = $conn->prepare("SELECT user_name FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$current_user = $user['user_name'];
$stmt->close();


$stmt = $conn->prepare("SELECT user_priv FROM current");
$stmt->execute();
$result = $stmt->get_result();
$user_priv = $result->fetch_assoc()['user_priv'];
$stmt->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - Scholarly Exchange</title>
    <!-- Link to home.css -->
    <link rel="stylesheet" href="home.css">
</head>
<body>
<div class="header">
    <div class="logo">Scholarly Exchange</div>

    <!-- Search Bar and Button (Updated to be inline) -->
    <div class="search-container" style="display: flex; align-items: center;">
    <form action="search.php" method="POST" class="search-bar">
        <input type="text" id="searchInput" name="searchInput" placeholder="Search Scholarly Exchange" style="width: 300px;">
        <button type="submit" class="btn btn-primary" name="search">Search</button>
    </form>
</div>


    <div class="menu1">
    <a href="home.php"><button>Home</button></a>
    <a href="logout.php"><button>Logout</button></a>
    <a href="profile.php"><button>Profile</button></a>
    
    <?php if ($user_priv === 'Admin' || $user_priv === 'Moderator'): ?>
        <a href="reports.php"><button>Reports</button></a>
    <?php endif; ?>
    
    <?php if ($user_priv === 'Admin'): ?>
        <a href="verification.php"><button>Professional Verification</button></a>
    <?php endif; ?>
    
</div>

</div>
<div class="sidebar">
    <div class="logo-container">
        <h2>Scholarly Exchange</h2>
    </div>
    <ul class="menu-items">
        <li>
            <a href="home.php">
                <span class="icon">🏠</span> Home
            </a>
        </li>
        <li>
            <a href="home.php?section=Mathematics">
                <span class="icon">📊</span> Mathematics
            </a>
        </li>
        <li>
            <a href="home.php?section=Science">
                <span class="icon">🧪</span> Science
            </a>
        </li>
        <li>
            <a href="home.php?section=English">
                <span class="icon">📝</span> English
            </a>
        </li>
        <li>
            <a href="home.php?section=Filipino">
                <span class="icon">📖</span> Filipino
            </a>
        </li>
        <li>
            <a href="home.php?section=AP">
                <span class="icon">🌏</span> AP
            </a>
        </li>
        <li>
            <a href="home.php?section=History">
                <span class="icon">📜</span> History
            </a>
        </li>
        <li>
            <a href="home.php?section=TLE">
                <span class="icon">💻</span> TLE
            </a>
        </li>
        <li>
            <a href="home.php?section=Physical Education">
                <span class="icon">🏃</span> Physical Education
            </a>
        </li>
    </ul>
</div>

    <div class="menu">
    <a href="home.php"><button>Home</button></a>
    <a href="logout.php"><button>Logout</button></a>
    <a href="profile.php"><button>Profile</button></a>
    
    <?php if ($user_priv === 'Admin' || $user_priv === 'Moderator'): ?>
        <a href="reports.php"><button>Reports</button></a>
    <?php endif; ?>
    
    <?php if ($user_priv === 'Admin'): ?>
        <a href="verification.php"><button>Professional Verification</button></a>
    <?php endif; ?>
    
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#askQuestionModal">Ask Question</button>
</div>

</div>

    <!-- Main Content Section -->
    <div class="main-content">
        <h2>Search Results</h2>
        <?php
        $servername = "localhost";
        $username = "root";
        $password = "";
        $dbname = "scholarlyexchangedb";

        
        $conn = new mysqli($servername, $username, $password, $dbname);

        
        if ($conn->connect_error) {
            die("<p>Connection failed: " . htmlspecialchars($conn->connect_error) . "</p>");
        }

        if (isset($_POST['search'])) {
            
            $query = trim($_POST['searchInput']);

            if (!empty($query)) {
                
                $stmt = $conn->prepare("
                    SELECT post_id, subj, post_title, post_cont, user_name, tags,
                           (SELECT COUNT(*) FROM answer WHERE answer.parent_post = question.post_id) AS answer_count
                    FROM question
                    WHERE post_title LIKE ? OR post_cont LIKE ? OR tags LIKE ?
                ");
                
                if ($stmt) {
                    
                    $searchTerm = "%" . $query . "%";
                    $stmt->bind_param("sss", $searchTerm, $searchTerm, $searchTerm);
                    
                    
                    $stmt->execute();
                    $result = $stmt->get_result();

                    
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<div class='post'>";
                            echo "<a href='post.php?postid=" . htmlspecialchars($row['post_id']) . "'>";
                            echo "<h3>" . htmlspecialchars($row['post_title']) . "</h3>";
                            echo "<p>" . htmlspecialchars($row['post_cont']) . "</p>";
                            echo "<small>Posted by: " . htmlspecialchars($row['user_name']) . "</small><br>";
                            echo "<small>Tag: " . htmlspecialchars($row['tags']) . "</small><br>";
                            echo "<small>" . $row['answer_count'] . " Answers</small>";
                            echo "</a></div>";
                        }
                    } else {
                        echo "<p>No results found for '" . htmlspecialchars($query) . "'.</p>";
                    }

                    
                    $stmt->close();
                } else {
                    echo "<p>Error preparing the query.</p>";
                }
            } else {
                echo "<p>Please enter a search term.</p>";
            }
        } else {
            echo "<p>Invalid request.</p>";
        }

        
        $conn->close();
        ?>
    </div>
</body>
</html>
