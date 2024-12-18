<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "scholarlyexchangedb";


$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}


function generateRandomCode() {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomCode = '';
    for ($i = 0; $i < 15; $i++) {
        $randomCode .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomCode;
}

function insertNewQuestion($conn, $postid, $subject, $title, $content, $username, $tag, $post_date, $post_time) {
    $stmt = $conn->prepare("INSERT INTO question (post_id, subj, post_title, post_cont, user_name, tags, post_date, post_time) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $postid, $subject, $title, $content, $username, $tag, $post_date, $post_time);
    
    return $stmt->execute();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    session_start();

    
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

    $username = $user['user_name'];
    $stmt->close();

    
    $subject = $_POST['subject'];
    $title = $_POST['title'];
    $content = $_POST['content'];
    $tag = $_POST['tag'];
    $post_date = date("Y-m-d");
    $post_time = date("H:i:s");

    
    $maxRetries = 3;
    $retryCount = 0;
    $success = false;

    while ($retryCount < $maxRetries && !$success) {
        $postid = generateRandomCode();
        $success = insertNewQuestion($conn, $postid, $subject, $title, $content, $username, $tag, $post_date, $post_time);
        $retryCount++;
    }

    if ($success) {
        header('Location: home.php');
    } else {
        echo "Error: Failed to insert question after {$maxRetries} attempts. Please try again later.";
    }
}
?>
