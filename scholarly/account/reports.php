<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "scholarlyexchangedb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['ban_user'])) {
    $usernameToBan = $_POST['ban_user'];
    $stmt = $conn->prepare("SELECT banned FROM users WHERE user_name = ?");
    $stmt->bind_param("s", $usernameToBan);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    $currentStatus = $user['banned'] ?? "No";

    $newStatus = ($currentStatus == "Yes") ? "No" : "Yes";

    $stmt->close();
    $stmt = $conn->prepare("UPDATE users SET banned = ? WHERE user_name = ?");
    $stmt->bind_param("ss", $newStatus, $usernameToBan);
    $stmt->execute();
    $stmt->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reports</title>
    <link rel="stylesheet" href="home.css">
</head>
<body>
<div class="header">
    <div class="logo">Scholarly Exchange</div>
    <div class="search-container" style="display: flex; align-items: center;">
        <form action="search.php" method="POST" class="search-bar">
    <input type="text" id="searchInput" name="searchInput" placeholder="Search Scholarly Exchange" style="width: 300px;">
    <button type="submit" class="btn btn-primary" name="search">Search</button>
    </div>
    <div class="menu1">
        <a href="home.php"><button>Home</button></a>
        <a href="logout.php"><button>Logout</button></a>
        <a href="profile.php"><button>Profile</button></a>
    </div>
</div>
<div class="sidebar">
    <div class="logo-container">
        <h2>Reports</h2>
    </div>
</div>
<div class="main-content">
    <?php
    $stmt = $conn->prepare("SELECT * FROM report");
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $reportedUser = $row['rep_subj'];
            $reporter = $row['rep_sender'];

            // Check banned status
            $stmt2 = $conn->prepare("SELECT banned FROM users WHERE user_name = ?");
            $stmt2->bind_param("s", $reportedUser);
            $stmt2->execute();
            $result2 = $stmt2->get_result();
            $user = $result2->fetch_assoc();
            $bannedStatus = $user['banned'] ?? "No";
            $stmt2->close();

            // Fetch reported question details
            $stmt1 = $conn->prepare("SELECT * FROM question WHERE post_id = ?");
            $stmt1->bind_param("s", $row['post_id']);
            $stmt1->execute();
            $result1 = $stmt1->get_result();
            $row1 = $result1->fetch_assoc();
            $stmt1->close();

            // Check if reported user is verified
            $stmt3 = $conn->prepare("SELECT email FROM users WHERE user_name = ?");
            $stmt3->bind_param("s", $reportedUser);
            $stmt3->execute();
            $result3 = $stmt3->get_result();
            $email = $result3->fetch_assoc()['email'] ?? null;
            $stmt3->close();

            $subjverified = false;
            if ($email) {
                $stmt4 = $conn->prepare("SELECT status FROM prof_ver WHERE email = ?");
                $stmt4->bind_param("s", $email);
                $stmt4->execute();
                $result4 = $stmt4->get_result();
                $stat = $result4->fetch_assoc()['status'] ?? null;
                $stmt4->close();

                if ($stat === "Approved") {
                    $subjverified = true;
                }
            }

            echo '<div class="post">';
            echo '<h2>Reported User: ' . htmlspecialchars($reportedUser);
            if ($subjverified) {
                echo ' ✅';
            }
            echo '</h2>';
            // Check if reported user is verified
            $stmt3 = $conn->prepare("SELECT email FROM users WHERE user_name = ?");
            $stmt3->bind_param("s", $reporter);
            $stmt3->execute();
            $result3 = $stmt3->get_result();
            $email = $result3->fetch_assoc()['email'] ?? null;
            $stmt3->close();

            $repverified = false;
            if ($email) {
                $stmt4 = $conn->prepare("SELECT status FROM prof_ver WHERE email = ?");
                $stmt4->bind_param("s", $email);
                $stmt4->execute();
                $result4 = $stmt4->get_result();
                $stat = $result4->fetch_assoc()['status'] ?? null;
                $stmt4->close();

                if ($stat === "Approved") {
                    $repverified = true;
                }
            }
            echo '<p><strong>Reported by:</strong> ' . htmlspecialchars($reporter);
            if ($repverified) {
                echo ' ✅';
            }
            echo '</p>';
            echo '<p><strong>Reported question:</strong></p>';
            echo '<h3>Title: ' . htmlspecialchars($row1['post_title'] ?? 'N/A') . '</h3>';
            echo '<h4>Question content: "' . htmlspecialchars($row1['post_cont'] ?? 'N/A') . '"</h4>';
            echo '<p><strong>Report Type:</strong> ' . htmlspecialchars($row['rep_type']) . '</p>';
            echo '<p><strong>Details:</strong> ' . htmlspecialchars($row['rep_details']) . '</p>';
            echo '<form method="POST" action="">';
            echo '<input type="hidden" name="ban_user" value="' . htmlspecialchars($reportedUser) . '">';
            if ($bannedStatus == "Yes") {
                echo '<button type="submit" class="btn btn-danger">Unban</button>';
            } else {
                echo '<button type="submit" class="btn btn-primary">Ban</button>';
            }
            echo '</form>';
            echo '</div>';
        }
    } else {
        echo '<p>No reports found.</p>';
    }
    ?>
</div>
</body>
</html>
