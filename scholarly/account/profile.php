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

// Fetch the current user's email
$stmt = $conn->prepare("SELECT email FROM current");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $email = $result->fetch_assoc()['email'];
}
$stmt->close();

$stmt = $conn->prepare("SELECT user_priv FROM current");
$stmt->execute();
$result = $stmt->get_result();
$user_priv = $result->fetch_assoc()['user_priv'];
$stmt->close();

// Check if the user is verified as a professional
function is_verified_as_professional($email, $conn) {
    $stmt = $conn->prepare("SELECT email FROM prof_ver WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->num_rows > 0;
}

// Fetch user details
$sql = "SELECT 
            user_name, 
            profile_name,
            email, 
            bio, 
            bdate, 
            user_sp, 
            profession, 
            spec_sub1, 
            spec_sub2, 
            spec_sub3, 
            bigbrainp 
        FROM users 
        WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
    $birthdate = new DateTime($user['bdate']);
    $currentDate = new DateTime();
    $age = $currentDate->diff($birthdate)->y;
} else {
    $user = [
        "user_name" => "Unknown User",
        "profile_name" => "N/A",
        "email" => "Not Available",
        "bio" => "No bio available.",
        "bdate" => "N/A",
        "user_sp" => "N/A",
        "profession" => "N/A",
        "spec_sub1" => "N/A",
        "spec_sub2" => "N/A",
        "spec_sub3" => "N/A",
        "bigbrainp" => 0
    ];
    $age = "N/A";
}

// Map subjects
$subject_mapping = [
    "math" => "Mathematics",
    "tle" => "Technology and Livelihood Education",
    "pe" => "Physical Education",
    "his" => "History",
    "sci" => "Science",
    "eng" => "English",
    "fil" => "Filipino",
    "ap" => "Araling Panlipunan"
];
function map_subject($subject, $mapping) {
    return $mapping[$subject] ?? $subject; 
}

// Check professional verification
$already_verified = is_verified_as_professional($email, $conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <link rel="stylesheet" href="home.css">
</head>
<body>
<div class="header">
    <div class="logo">Scholarly Exchange</div>
    <div class="search-container" style="display: flex; align-items: center;">
    <form action="search.php" method="POST" class="search-bar">
        <input type="text" id="searchInput" name="searchInput" placeholder="Search Scholarly Exchange">
        <button type="submit" class="btn btn-primary" name="search">Search</button>
    </form>
</div>

    <div class="menu1">
        <a href="home.php"><button>Home</button></a>
        <a href="logout.php"><button>Logout</button></a>
        <?php if ($user_priv === 'Admin' || $user_priv === 'Moderator'): ?>
            <a href="reports.php"><button>Reports</button></a>
        <?php endif; ?>
        <?php if ($user_priv === 'Admin'): ?>
            <a href="verification.php"><button>Professional Verification</button></a>
        <?php endif; ?>
    </div>
</div>
<div class="main-content">
    <div class="post">
        <h2>User Profile</h2>
        <h3>
            <?php echo htmlspecialchars($user['profile_name']); ?> (<?php echo htmlspecialchars($user['user_name']); ?>)
            <?php if ($already_verified): ?>
                ✅
            <?php endif; ?>
        </h3>
        <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
        <p><strong>Bio:</strong> <?php echo htmlspecialchars($user['bio']); ?></p>
        <p><strong>Birth Date:</strong> <?php echo htmlspecialchars($user['bdate']); ?></p>
        <p><strong>Age:</strong> <?php echo htmlspecialchars($age); ?></p>
        <p><strong>User Type:</strong> <?php echo htmlspecialchars($user['user_sp']); ?></p>
        <?php if ($user['user_sp'] === "Professional"): ?>
            <p><strong>Profession:</strong> <?php echo htmlspecialchars($user['profession']); ?></p>
        <?php endif; ?>
        <p><strong>Specialized Subjects:</strong></p>
        <ul>
            <li><?php echo htmlspecialchars(map_subject($user['spec_sub1'], $subject_mapping)); ?></li>
            <li><?php echo htmlspecialchars(map_subject($user['spec_sub2'], $subject_mapping)); ?></li>
            <li><?php echo htmlspecialchars(map_subject($user['spec_sub3'], $subject_mapping)); ?></li>
        </ul>
        <p><strong>BigBrain Points:</strong> <?php echo htmlspecialchars($user['bigbrainp']); ?></p>
    </div>
    <div class="action-buttons">
        <?php if ($user['user_sp'] === "Professional"): ?>
            <?php if ($already_verified): ?>
                <p>Professional Verification Sent!</p>
            <?php else: ?>
                <button class="action-btn" onclick="verifyProfessionalism()">Verify Yourself as Professional!</button>
            <?php endif; ?>
        <?php endif; ?>
        <button class="action-btn" onclick="editAccount()">Edit Account</button>
        <form method="POST" action="delete_account.php" onsubmit="return confirmDeletion();" style="display:inline;">
            <button type="submit" name="delete_account" class="action-btn delete">Delete Account</button>
        </form>
    </div>
    <div id="searchResults"></div>
</div>
<script>
    function verifyProfessionalism() {
        window.location.href = "professional_verification.php";
    }

    function editAccount() {
        window.location.href = "edit_account.php";
    }

    function confirmDeletion() {
        return confirm("Are you sure you want to delete your account? This action cannot be undone.");
    }

    function searchQuestions() {
        const searchQuery = document.getElementById("searchInput").value;
        const xhr = new XMLHttpRequest();
        xhr.open("GET", "search.php?q=" + encodeURIComponent(searchQuery), true);
        xhr.onload = function() {
            if (xhr.status === 200) {
                document.getElementById("searchResults").innerHTML = xhr.responseText;
            }
        };
        xhr.send();
    }
</script>
</body>
</html>
