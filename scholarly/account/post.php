<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "scholarlyexchangedb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

function generateRandomCode() {
    return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'), 0, 15);
}

$postid = $_GET['postid'] ?? '';
if (empty($postid)) {
    die("Post ID is required.");
}

$stmt = $conn->prepare("SELECT post_title, post_cont, user_name FROM question WHERE post_id = ?");
$stmt->bind_param("s", $postid);
$stmt->execute();
$result = $stmt->get_result();
$post = $result->fetch_assoc();
$stmt->close();

if (!$post) {
    die("Post not found.");
}

$stmt = $conn->prepare("SELECT user_priv FROM current");
$stmt->execute();
$result = $stmt->get_result();
$user_priv = $result->fetch_assoc()['user_priv'];
$stmt->close();


function getCurrentUser($conn) {
    $stmt = $conn->prepare("SELECT email FROM current LIMIT 1");
    $stmt->execute();
    $result = $stmt->get_result();
    $email = $result->fetch_assoc()['email'] ?? null;
    $stmt->close();

    if (!$email) {
        return null;
    }

    $stmt = $conn->prepare("SELECT user_name FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $username = $result->fetch_assoc()['user_name'] ?? null;
    $stmt->close();

    return ['email' => $email, 'username' => $username];
}

$currentUser = getCurrentUser($conn);
if (!$currentUser) {
    die("User not logged in.");
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'answer') {
    $content = trim($_POST['reply_cont']);
    $tag = $_POST['tag'] ?? '';

    if (empty($content) || empty($tag)) {
        die("Answer content and tag are required.");
    }

    $replyid = generateRandomCode();
    $date = date("Y-m-d");
    $time = date("H:i:s");

    $stmt = $conn->prepare("INSERT INTO answer (reply_id, parent_post, reply_cont, user_name, reply_date, reply_time, tag) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $replyid, $postid, $content, $currentUser['username'], $date, $time, $tag);
    $stmt->execute();
    $stmt->close();

    header("Location: post.php?postid=$postid");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_answer') {
    if (!isset($_POST['answer_id']) || empty($_POST['answer_id'])) {
        die("Answer ID is required to delete.");
    }

    $answer_id = $_POST['answer_id'];


    $conn->begin_transaction();

    try {

        $stmt = $conn->prepare("DELETE FROM answer WHERE reply_id = ?");
        $stmt->bind_param("s", $answer_id); // Use "s" if answer_id is stored as a string
        $stmt->execute();
        $stmt->close();


        $stmt = $conn->prepare("DELETE FROM answer_replies WHERE answer_id = ?");
        $stmt->bind_param("s", $answer_id); // Use "s" if answer_id is stored as a string
        $stmt->execute();
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM answer_agrees WHERE answer_id = ?");
        $stmt->bind_param("s", $answer_id); // Use "s" if answer_id is stored as a string
        $stmt->execute();
        $stmt->close();

        
        $conn->commit();

 
        header("Location: post.php?postid=$postid");
        exit();
    } catch (Exception $e) {
 
        $conn->rollback();
        die("Error deleting answer: " . $e->getMessage());
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_reply') {
    $reply_id = $_POST['reply_id'];

    // Delete the reply from the database
    $stmt = $conn->prepare("DELETE FROM answer_replies WHERE reply_id = ?");
    $stmt->bind_param("s", $reply_id);
    $stmt->execute();
    $stmt->close();

    header("Location: post.php?postid=$postid");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rep_type'])) {


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

    $rep_type = $_POST['rep_type'];
    $rep_details = $_POST['rep_details'];
    $replyid = $_POST['postid'];

    $stmt = $conn->prepare("SELECT user_name FROM answer WHERE reply_id = ?");
    $stmt->bind_param("s", $replyid);
    $stmt->execute();
    $result = $stmt->get_result();
    $reported_user = $result->fetch_assoc()['user_name'];
    $stmt->close();
    $rep_id = generateRandomCode();

    $stmt = $conn->prepare("INSERT INTO report_ans (report_id, rep_subj, rep_sender, rep_type, rep_details, reply_id) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $rep_id, $reported_user, $username, $rep_type, $rep_details, $replyid);
    $stmt->execute();
    $stmt->close();

    
    header("Location: post.php?postid=$postid");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['replydetails'])) {
    // Get current user's email
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


    $replydeets = $_POST['replydetails'];
    $replytag = $_POST['replytag'];
    $replyid = $_POST['replyid'];
    $reply2repid = generateRandomCode();

    $date = date("Y-m-d");
    $time = date("H:i:s");

    $stmt = $conn->prepare("INSERT INTO answer_replies (reply_id, answer_id, reply_content, user_name, reply_date, reply_time, tag) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssss", $reply2repid, $replyid, $replydeets, $currentUser['username'], $date, $time, $replytag);
    $stmt->execute();
    $stmt->close();

    header("Location: post.php?postid=$postid");
    exit();
}


if ($_SERVER['REQUEST_METHOD'] === 'POST' && $_POST['action'] === 'agree') {
    $answer_id = $_POST['answer_id'];

    if (empty($answer_id)) {
        die("Answer ID is required.");
    }

    $stmt = $conn->prepare("SELECT * FROM answer_agrees WHERE answer_id = ? AND user_email = ?");
    $stmt->bind_param("ss", $answer_id, $currentUser['email']); // Ensure "ss" matches your data types
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {

        $stmt = $conn->prepare("DELETE FROM answer_agrees WHERE answer_id = ? AND user_email = ?");
        $stmt->bind_param("ss", $answer_id, $currentUser['email']); // Ensure "ss" matches your data types
        $stmt->execute();
        $stmt->close();
    } else {

        $stmt = $conn->prepare("INSERT INTO answer_agrees (answer_id, user_email) VALUES (?, ?)");
        $stmt->bind_param("ss", $answer_id, $currentUser['email']); // Ensure "ss" matches your data types
        $stmt->execute();
        $stmt->close();
    }


    header("Location: post.php?postid=$postid");
    exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post['post_title']); ?></title>
    <link rel="stylesheet" href="home.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

<div class="header">
    <div class="logo">Scholarly Exchange</div>


    <div class="search-container" style="display: flex; align-items: center;">
        <input type="text" id="searchInput" placeholder="Search Scholarly Exchange" onkeyup="searchQuestions()" style="margin-right: 10px;">
        <button type="button" class="btn btn-primary" onclick="searchQuestions()">Search</button>
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

<div class="main-content">
    <h2>Post Details</h2>
    <div class="post-detail">
    <h3><?php echo htmlspecialchars($post['post_title']); ?></h3>
    <p><?php echo htmlspecialchars($post['post_cont']); ?></p>
    <small>Posted by: <?php echo htmlspecialchars($post['user_name']); ?>
    <?php
    $stmt = $conn->prepare("SELECT email FROM users WHERE user_name = ?");
    $stmt->bind_param("s", $post['user_name']);
    $stmt->execute();
    $result = $stmt->get_result(); 
    $email = $result->fetch_assoc()['email'] ?? null;
    $stmt->close();

    $verified = false; // Default to not verified

    if ($email) {
        $stmt = $conn->prepare("SELECT status FROM prof_ver WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result(); 
        $stat = $result->fetch_assoc()['status'] ?? null;
        $stmt->close();

        if ($stat === 'Approved') {
            $verified = true;
        }
    }
?>
    <?php if ($verified): ?>
        ✅
    <?php endif; ?>
</small>
    <div class="action-buttons">
        <button onclick="window.location.href='home.php'" class="btn btn-secondary">Back</button>
    </div>
</div>


        <h3>Submit an Answer</h3>
<form method="POST" action="">
    <textarea class="form-control" name="reply_cont" rows="4" placeholder="Write your answer here" required></textarea>
    <br>
    <label for="tag">Choose a tag:</label>
    <select class="form-control" name="tag" required>
        <option value="Elementary School Level">Elementary School Level</option>
        <option value="High School Level">High School Level</option>
        <option value="College Level">College Level</option>
    </select>
    <br>
    <button type="submit" name="action" value="answer" class="btn btn-primary">Submit Answer</button>
</form>


<div class="modal fade" id="reportModal" tabindex="-1" aria-labelledby="reportModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="reportModalLabel">Report Post</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="reportForm" method="POST" action="">
                    <div class="mb-3">
                        <label for="rep_type" class="form-label">Report Type</label>
                        <select name="rep_type" id="rep_type" class="form-select" required>
                            <option value="">Select Report Type</option>
                            <option value="Hate">Hate</option>
                            <option value="Abuse or Harassment">Abuse or Harassment</option>
                            <option value="Violent Speech">Violent Speech</option>
                            <option value="Spam">Spam</option>
                            <option value="Privacy">Privacy</option>
                            <option value="Impersonation">Impersonation</option>
                            <option value="Non-Academic">Non-Academic</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="rep_details" class="form-label">Details</label>
                        <textarea class="form-control" id="rep_details" name="rep_details" rows="3" placeholder="Provide more details (optional)"></textarea>
                    </div>
                    <input type="hidden" id="report_postid" name="postid">
                    <button type="submit" class="btn btn-primary">Submit Report</button>
                </form>
            </div>
        </div>
    </div>
</div>


<div class="modal fade" id="replyModal" tabindex="-1" aria-labelledby="replyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <hclass="modal-title" id="replyModalLabel">Reply to Answer</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="replyForm" method="POST" action="">
                    <div class="mb-3">
                        <label for="replydetails" class="form-label">Details</label>
                        <textarea class="form-control" id="replydetails" name="replydetails" rows="3" placeholder="Your reply..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="replytag" class="form-label">Select Tag</label>
                        <select name="replytag" id="replytag" class="form-select" required>
                            <option value="">Select Tag</option>
                            <option value="Elementary School Level">Elementary School Level</option>
                            <option value="High School Level">High School Level</option>
                             <option value="College Level">College Level</option>
                        </select>
                    </div>
                    <input type="hidden" id="reply_postid" name="replyid">
                    <button type="submit" class="btn btn-primary">Post Reply</button>
                </form>
            </div>
        </div>
    </div>
</div>

        <h3>Answers</h3>
        <?php

$stmt = $conn->prepare("SELECT email FROM current");
$stmt->execute();
$result = $stmt->get_result();
$email = $result->fetch_assoc()['email'];
$stmt->close();

$stmt = $conn->prepare("SELECT user_name FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();
$username = $result->fetch_assoc()['user_name'];
$stmt->close();


$stmt = $conn->prepare("SELECT reply_id, reply_cont, user_name, reply_date, reply_time, tag FROM answer WHERE parent_post = ?");
$stmt->bind_param("s", $postid);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $answer_id = $row['reply_id'];
        $answer_id1 = $answer_id;

        $stmt2 = $conn->prepare("SELECT COUNT(*) as agree_count FROM answer_agrees WHERE answer_id = ?");
        $stmt2->bind_param("i", $answer_id);
        $stmt2->execute();
        $result2 = $stmt2->get_result();
        $agree_count = $result2->fetch_assoc()['agree_count'];
        $stmt2->close();

        $stmt3 = $conn->prepare("SELECT * FROM answer_agrees WHERE answer_id = ? AND user_email = ?");
        $stmt3->bind_param("is", $answer_id, $email);
        $stmt3->execute();
        $result3 = $stmt3->get_result();
        $has_agreed = $result3->num_rows > 0;
        $stmt3->close();

 
        echo '<div class="answer mb-4 p-3 border rounded">';
        echo '<p>' . htmlspecialchars($row['reply_cont']) . '</p>';
        echo '<small class="text-muted">Answered by: ' . htmlspecialchars($row['user_name']) . ' on ' . $row['reply_date'] . ' at ' . $row['reply_time'] . '</small>';
        echo '<br>';
        echo '<span class="badge bg-primary">Tag: ' . htmlspecialchars($row['tag']) . '</span>';
        echo '<div class="action-buttons d-flex justify-content-between mt-2">';


        echo '<button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#replyModal" data-replyid="' . htmlspecialchars($answer_id) . '">Reply</button>';


        echo '<form method="POST" action="" style="display:inline;">';
        echo '<button type="submit" name="action" value="agree" class="btn btn-sm ' . ($has_agreed ? 'btn-success' : 'btn-outline-success') . '">' .
            ($has_agreed ? 'Agreed' : 'Agree') . ' (' . $agree_count . ')</button>';
        echo '<input type="hidden" name="answer_id" value="' . $answer_id . '">';
        echo '</form>';


if ($row['user_name'] === $username) {
    echo '<form method="POST" action="" style="display:inline;">';
    echo '<button type="submit" name="action" value="delete_answer" class="btn btn-sm btn-danger">Delete</button>';
    echo '<input type="hidden" name="answer_id" value="' . $answer_id . '">';
    echo '</form>';
} else {

    echo '<button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#reportModal" data-postid="' . htmlspecialchars($answer_id) . '">Report</button>';
}

        echo '</div>';
        echo '</div>';

        // Fetch and display replies to the current answer
        $stmt4 = $conn->prepare("SELECT reply_id, answer_id, reply_content, user_name, reply_date, reply_time, tag FROM answer_replies WHERE answer_id = ?");
        $stmt4->bind_param("s", $answer_id1);
        $stmt4->execute();
        $result4 = $stmt4->get_result();
        if ($result4->num_rows > 0) {
            echo '<div class="replies ms-3">';
            while ($row4 = $result4->fetch_assoc()) {
                $reply_id = $row4['reply_id'];
                echo '<div class="reply p-2 border-start">';
                echo '<p>' . htmlspecialchars($row4['reply_content']) . '</p>';
                echo '<small class="text-muted">Replied by: ' . htmlspecialchars($row4['user_name']) . ' on ' . $row4['reply_date'] . ' at ' . $row4['reply_time'] . '</small>';
                echo '<br>';
                echo '<span class="badge bg-primary">Tag: ' . htmlspecialchars($row4['tag']) . '</span>';
                echo '<br>';
                echo '<br>';


                if ($row4['user_name'] === $username) {
                    echo '<form method="POST" action="" style="display:inline;">';
                    echo '<button type="submit" name="action" value="delete_reply" class="btn btn-sm btn-danger">Delete</button>';
                    echo '<input type="hidden" name="reply_id" value="' . $reply_id . '">';
                    echo '</form>';
                }

                echo '</div>'; 
            }
            echo '</div>';
        }
        $stmt4->close();
    
    }
} else {
    echo "<p>No answers yet. Be the first to answer!</p>";
}
?>

    </div>
</div>

<script>
    function showReplyForm(answer_id) {
        var form = document.getElementById("reply-form-" + answer_id);
        form.style.display = (form.style.display === "none") ? "block" : "none";
    }
</script>

<script>
    const reportModal = document.getElementById('reportModal');
    reportModal.addEventListener('show.bs.modal', function (event) {
        // Get the postid from the button that triggered the modal
        const button = event.relatedTarget; 
        const postid = button.getAttribute('data-postid');

        // Set the postid in the hidden field
        const modalBodyInput = reportModal.querySelector('#report_postid');
        modalBodyInput.value = postid;
    });
</script>

<script>
    const replyModal = document.getElementById('replyModal');
    replyModal.addEventListener('show.bs.modal', function (event) {

        const button = event.relatedTarget; 
        const replyid = button.getAttribute('data-replyid');


        const modalBodyInput = replyModal.querySelector('#reply_postid');
        modalBodyInput.value = replyid;
    });
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
