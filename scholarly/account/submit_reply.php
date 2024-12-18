<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "scholarlyexchangedb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

function generateRandomCode() {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomCode = '';
    for ($i = 0; $i < 15; $i++) {
        $randomCode .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomCode;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stmt = $conn->prepare("SELECT email FROM current");
    $stmt->execute();
    $result=$stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $email = $row["email"];
                }
            }
            $stmt->close();

    $stmt = $conn->prepare("SELECT user_name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result=$stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $username = $row["user_name"];
                }
            }


$replyId = generateRandomCode();
$postId = $_POST['post_id'];
$replyContent = $_POST['reply_cont'];
$replyType = $_POST['reply_type'];
$parentReplyId = $_POST['parent_reply'] ?? null;
$tag = $_POST['tag'];
$date = date("Y-m-d");
$time = date("H:i:s");

$sql = "INSERT INTO answer (reply_id, user_name, tags, reply_date, reply_time, reply_cont, parent_post, reply_type, parent_reply) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sssssssss", $replyId, $username, $tags, $date, $time, $replyContent, $postId, $replyType, $parentReplyId);

$response = [];
if ($stmt->execute()) {
    $response['success'] = true;
} else {
    $response['success'] = false;
    $response['error'] = $stmt->error;
}

$stmt->close();
}
echo json_encode($response);
?>
