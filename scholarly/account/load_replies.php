<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "scholarlyexchangedb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) die("Connection failed: " . $conn->connect_error);

function fetchReplies($conn, $parentId, $replyType) {
    $sql = "SELECT * FROM answer WHERE parent_post = ? AND reply_type = ? ORDER BY reply_date, reply_time";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $parentId, $replyType);
    $stmt->execute();
    $result = $stmt->get_result();

    $replies = [];
    while ($row = $result->fetch_assoc()) {
        $row['replies'] = fetchReplies($conn, $row['reply_id'], "To reply"); // Fetch nested replies
        $replies[] = $row;
    }

    return $replies;
}

$postId = $_GET['post_id'] ?? '';
$replies = fetchReplies($conn, $postId, "To post");
echo json_encode($replies);
?>
