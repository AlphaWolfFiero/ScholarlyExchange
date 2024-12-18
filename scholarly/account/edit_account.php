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


$stmt = $conn->prepare("SELECT email FROM current");
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $email = $row['email'];
} else {
    echo "User not logged in.";
    exit();
}
$stmt->close();


$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();
} else {
    echo "User not found.";
    exit();
}
$stmt->close();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pname = $_POST['pname'];
    $bdate = $_POST['bdate'];
    $pbio = $_POST['pbio'];
    $subj1 = $_POST['subj1'];
    $subj2 = $_POST['subj2'];
    $subj3 = $_POST['subj3'];
    $user_type = $_POST['user_type'];
    $profession = isset($_POST['profession']) ? $_POST['profession'] : ''; 

    
    $update_stmt = $conn->prepare("UPDATE users SET profile_name = ?, bdate = ?, bio = ?, spec_sub1 = ?, spec_sub2 = ?, spec_sub3 = ?, user_sp = ?, profession = ? WHERE email = ?");
    $update_stmt->bind_param("sssssssss", $pname, $bdate, $pbio, $subj1, $subj2, $subj3, $user_type, $profession, $email);

    if ($update_stmt->execute()) {
        echo "<script>alert('Profile updated successfully.');</script>";
        echo "<script>window.location.href = 'profile.php';</script>";
    } else {
        echo "<script>alert('Error updating profile.');</script>";
    }

    $update_stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Account - Scholarly Exchange</title>
    <link rel="stylesheet" href="scholarly.css">
    <script>
        
        function toggleProfessionField() {
            var userType = document.getElementById("user_type").value;
            var professionField = document.getElementById("professionField");

            if (userType === "Professional") {
                professionField.style.display = "block"; 
            } else {
                professionField.style.display = "none"; 
            }
        }

        
        window.onload = function() {
            toggleProfessionField(); 
        };
    </script>
</head>
<body>

<div class="login-container">
    <div class="login-box">
        <h1 class="logo">Account</h1>
        <p>Tell us about yourself!</p>

        <form action="edit_account.php" method="POST" class="login-form">
            <label for="pname">Profile Name</label>
            <input type="text" id="pname" name="pname" placeholder="Enter your Displayed Name" value="<?php echo htmlspecialchars($user['profile_name']); ?>" required>

            <label for="bdate">Birthdate</label>
            <input type="date" id="bdate" name="bdate" value="<?php echo htmlspecialchars($user['bdate']); ?>" required>

            <label for="user_type">User Type</label>
            <select id="user_type" name="user_type" required onchange="toggleProfessionField()">
                <option value="Student" <?php echo $user['user_sp'] == 'Student' ? 'selected' : ''; ?>>Student</option>
                <option value="Professional" <?php echo $user['user_sp'] == 'Professional' ? 'selected' : ''; ?>>Professional</option>
            </select>

            <!-- Profession field (hidden by default) -->
            <div id="professionField" style="display: <?php echo $user['user_sp'] == 'Professional' ? 'block' : 'none'; ?>;">
                <label for="profession">Profession</label>
                <input type="text" id="profession" name="profession" placeholder="Enter your profession" value="<?php echo htmlspecialchars($user['profession']); ?>">
            </div>

            <label for="pbio">Bio</label>
            <input type="text" id="pbio" name="pbio" placeholder="Your bio" value="<?php echo htmlspecialchars($user['bio']); ?>">

            <label for="subj1">Subject of Interest #1</label>
            <select id="subj1" name="subj1" required>
                <option value="" disabled>Select a Subject</option>
                <option value="math" <?php echo $user['spec_sub1'] == 'math' ? 'selected' : ''; ?>>Mathematics</option>
                <option value="sci" <?php echo $user['spec_sub1'] == 'sci' ? 'selected' : ''; ?>>Science</option>
                <option value="eng" <?php echo $user['spec_sub1'] == 'eng' ? 'selected' : ''; ?>>English</option>
                <option value="fil" <?php echo $user['spec_sub1'] == 'fil' ? 'selected' : ''; ?>>Filipino</option>
                <option value="ap" <?php echo $user['spec_sub1'] == 'ap' ? 'selected' : ''; ?>>Araling Panlipunan</option>
                <option value="his" <?php echo $user['spec_sub1'] == 'his' ? 'selected' : ''; ?>>History</option>
                <option value="tle" <?php echo $user['spec_sub1'] == 'tle' ? 'selected' : ''; ?>>Technology and Livelihood Education</option>
                <option value="pe" <?php echo $user['spec_sub1'] == 'pe' ? 'selected' : ''; ?>>Physical Education</option>
            </select>

            <label for="subj2">Subject of Interest #2</label>
            <select id="subj2" name="subj2" required>
                <option value="" disabled>Select a Subject</option>
                <option value="math" <?php echo $user['spec_sub2'] == 'math' ? 'selected' : ''; ?>>Mathematics</option>
                <option value="sci" <?php echo $user['spec_sub2'] == 'sci' ? 'selected' : ''; ?>>Science</option>
                <option value="eng" <?php echo $user['spec_sub2'] == 'eng' ? 'selected' : ''; ?>>English</option>
                <option value="fil" <?php echo $user['spec_sub2'] == 'fil' ? 'selected' : ''; ?>>Filipino</option>
                <option value="ap" <?php echo $user['spec_sub2'] == 'ap' ? 'selected' : ''; ?>>Araling Panlipunan</option>
                <option value="his" <?php echo $user['spec_sub2'] == 'his' ? 'selected' : ''; ?>>History</option>
                <option value="tle" <?php echo $user['spec_sub2'] == 'tle' ? 'selected' : ''; ?>>Technology and Livelihood Education</option>
                <option value="pe" <?php echo $user['spec_sub2'] == 'pe' ? 'selected' : ''; ?>>Physical Education</option>
            </select>

            <label for="subj3">Subject of Interest #3</label>
            <select id="subj3" name="subj3" required>
                <option value="" disabled>Select a Subject</option>
                <option value="math" <?php echo $user['spec_sub3'] == 'math' ? 'selected' : ''; ?>>Mathematics</option>
                <option value="sci" <?php echo $user['spec_sub3'] == 'sci' ? 'selected' : ''; ?>>Science</option>
                <option value="eng" <?php echo $user['spec_sub3'] == 'eng' ? 'selected' : ''; ?>>English</option>
                <option value="fil" <?php echo $user['spec_sub3'] == 'fil' ? 'selected' : ''; ?>>Filipino</option>
                <option value="ap" <?php echo $user['spec_sub3'] == 'ap' ? 'selected' : ''; ?>>Araling Panlipunan</option>
                <option value="his" <?php echo $user['spec_sub3'] == 'his' ? 'selected' : ''; ?>>History</option>
                <option value="tle" <?php echo $user['spec_sub3'] == 'tle' ? 'selected' : ''; ?>>Technology and Livelihood Education</option>
                <option value="pe" <?php echo $user['spec_sub3'] == 'pe' ? 'selected' : ''; ?>>Physical Education</option>
            </select>

            <button type="submit" class="login-button">Save Changes</button>
        </form>
    </div>
</div>

</body>
</html>
