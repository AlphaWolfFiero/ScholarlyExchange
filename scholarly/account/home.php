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

function insertNewQuestion($conn, $postid, $subject, $title, $content, $username, $tag, $post_date, $post_time) {
    $stmt = $conn->prepare("INSERT INTO question (post_id, subj, post_title, post_cont, user_name, tags, post_date, post_time) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssss", $postid, $subject, $title, $content, $username, $tag, $post_date, $post_time);
    
    return $stmt->execute();
}

$sections = [];
$sql = "SELECT post_id, subj, post_title, post_cont, user_name, tags, 
               (SELECT COUNT(*) FROM answer WHERE answer.parent_post = question.post_id) AS answer_count
        FROM question";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $postid = $row['post_id'];
        $section = strtolower($row['subj']);
        $title = $row['post_title'];
        $content = $row['post_cont'];
        $username = $row['user_name'];
        $tag = $row['tags'];
        $answers = intval($row['answer_count']);
        
        if (!isset($sections[$section])) {
            $sections[$section] = [];
        }

        $sections[$section][] = [
            'postid' => $postid,
            'title' => $title,
            'content' => $content,
            'username' => $username,
            'answers' => $answers,
            'tag' => $tag,
        ];
    }
}

$currentSection = isset($_GET['section']) ? strtolower($_GET['section']) : 'home';
$posts = $currentSection === 'home' ? array_merge(...array_values($sections)) : ($sections[$currentSection] ?? []);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $postidToDelete = $_POST['postid'];
    $stmt = $conn->prepare("DELETE FROM question WHERE post_id = ?");
    $stmt->bind_param("s", $postidToDelete);
    $stmt->execute();
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'submit') {
    session_start();
    $stmt = $conn->prepare("SELECT email FROM current");
    $stmt->execute();
    $result = $stmt->get_result();
    $email = $result->fetch_assoc()['email'];
    $stmt->close();
    $stmt = $conn->prepare("SELECT user_name FROM users WHERE email = ?");
    if (!$stmt) {
        die("Prepare failed: " . $conn->error);
    }
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
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "Error: Failed to insert question after {$maxRetries} attempts. Please try again later.";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['rep_type'])) {
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
    $rep_type = $_POST['rep_type'];
    $rep_details = $_POST['rep_details'];
    $postid = $_POST['postid'];
    $rep_id = generateRandomCode();
    $stmt = $conn->prepare("SELECT user_name FROM question WHERE post_id = ?");
    $stmt->bind_param("s", $postid);
    $stmt->execute();
    $result = $stmt->get_result();
    $reported_user = $result->fetch_assoc()['user_name'];
    $stmt->close();
    $stmt = $conn->prepare("INSERT INTO report (report_id, rep_subj, rep_sender, rep_type, rep_details, post_id) 
                            VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $rep_id, $reported_user, $username, $rep_type, $rep_details, $postid);
    $stmt->execute();
    $stmt->close();
    echo '<script type="text/javascript">'; 
    echo 'alert("Report Submitted Successfully.");';
    echo 'window.location.href = "home.php";';
    echo '</script>';
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Scholarly Exchange</title>
    <link rel="stylesheet" href="home.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        function searchQuestions() {
            const searchQuery = document.getElementById("searchInput").value;
            const xhr = new XMLHttpRequest();
            xhr.open("GET", "search.php?q=" + encodeURIComponent(searchQuery), true);
            xhr.onload = function() {
                if (xhr.status == 200) {
                    document.getElementById("searchResults").innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }
    </script>
</head>
<body>
<div class="header">
    <div class="logo">Scholarly Exchange</div>
    <div class="search-container" style="display: flex; align-items: center;">
        <form action="search.php" method="POST" class="search-bar">
            <input type="text" id="searchInput" name="searchInput" placeholder="Search Scholarly Exchange" style="width: 300px;">
            <button type="submit" class="btn btn-primary" name="search">Search</button>
        </form>
    </div>
    <div class="menu">
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

<div class="modal fade" id="askQuestionModal" tabindex="-1" aria-labelledby="askQuestionModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="askQuestionModalLabel">Ask a Question</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="askQuestionForm" method="POST" action="submit">
                    <div class="mb-3">
                        <label for="subject" class="form-label">Subject</label>
                        <select name="subject" id="subject" class="form-select" required>
                            <option value="">Select Subject</option>
                            <option value="Mathematics">Mathematics</option>
                            <option value="Science">Science</option>
                            <option value="English">English</option>
                            <option value="Filipino">Filipino</option>
                            <option value="Araling Panlipunan">Araling Panlipunan</option>
                            <option value="History">History</option>
                            <option value="TLE">Technology and Livelihood Education</option>
                            <option value="Physical Education">Physical Education</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="title" class="form-label">Title</label>
                        <input type="text" class="form-control" id="title" name="title" placeholder="Enter your question title" required>
                    </div>
                    <div class="mb-3">
                        <label for="content" class="form-label">Content</label>
                        <textarea class="form-control" id="content" name="content" rows="3" placeholder="Describe your question in detail" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="tag" class="form-label">Tag</label>
                        <select name="tag" id="tag" class="form-select" required>
                            <option value="">Select Tag</option>
                            <option value="Elementary Level">Elementary Level</option>
                            <option value="High School Level">High School Level</option>
                            <option value="College Level">College Level</option>
                        </select>
                    </div>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Post Question</button>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="sidebar">
    <div class="logo-container">
        <h2>Scholarly Exchange</h2>
    </div>
    <ul class="menu-items">
        <li>
            <a href="?section=home">
                <span class="icon">🏠</span> Home
            </a>
        </li>
        <li>
            <a href="?section=Mathematics">
                <span class="icon">📊</span> Mathematics
            </a>
        </li>
        <li>
            <a href="?section=Science">
                <span class="icon">🧪</span> Science
            </a>
        </li>
        <li>
            <a href="?section=English">
                <span class="icon">📝</span> English
            </a>
        </li>
        <li>
            <a href="?section=Filipino">
                <span class="icon">📖</span> Filipino
            </a>
        </li>
        <li>
            <a href="?section=Araling Panlipunan">
                <span class="icon">🌏</span> AP
            </a>
        </li>
        <li>
            <a href="?section=History">
                <span class="icon">📜</span> History
            </a>
        </li>
        <li>
            <a href="?section=TLE">
                <span class="icon">💻</span> TLE
            </a>
        </li>
        <li>
            <a href="?section=Physical Education">
                <span class="icon">🏃</span> Physical Education
            </a>
        </li>
    </ul>
</div>


<div class="main-content">
        <h2>
            <?php echo $currentSection === 'home' ? 'All Questions' : strtoupper($currentSection); ?>
        </h2>

        <?php if (!empty($posts)): ?>
            <?php foreach ($posts as $post): ?>

    <div class="post" style="padding: 20px; position: relative;">
        <a href="post.php?postid=<?php echo $post['postid']; ?>" style="color: inherit; text-decoration: none;">
                <?php $stmt = $conn->prepare("SELECT email FROM users WHERE user_name = ?");
                $stmt->bind_param("s", $post['username']);
                $stmt->execute();
                $result = $stmt->get_result(); 
                $email = $result->fetch_assoc()['email'];
                $stmt->close();
                $stmt = $conn->prepare("SELECT status FROM prof_ver WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result(); 
                $stat = $result->fetch_assoc();
                $stmt->close();
                ?>
            <h3><?php echo htmlspecialchars($post['title']); ?></h3>
            <p><?php echo htmlspecialchars($post['content']); ?></p>
            <?php
    $stmt = $conn->prepare("SELECT email FROM users WHERE user_name = ?");
    $stmt->bind_param("s", $post['username']);
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
<small>
    Posted by: <?php echo htmlspecialchars($post['username']); ?> 
    <?php if ($verified): ?>
        ✅
    <?php endif; ?>
</small>


            <br><small>Tag: <?php echo htmlspecialchars($post['tag']); ?></small>
            <br><small><?php echo $post['answers']; ?> Answers</small>
        </a>

        <!-- Only show the delete button if the logged-in user is the author of the post -->
        <?php if ($post['username'] === $current_user): ?>
            <form method="POST" action="" style="position: absolute; bottom: 10px; right: 10px;">
                <input type="hidden" name="postid" value="<?php echo $post['postid']; ?>">
                <input type="hidden" name="action" value="delete">
                <button type="submit" class="btn btn-danger btn-sm">Delete</button>
            </form>
        <?php else: ?>
            <!-- Report Button (For posts not authored by the current user) -->
            <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#reportModal" data-postid="<?php echo $post['postid']; ?>" style="position: absolute; bottom: 10px; right: 10px;">
                Report
            </button>
        <?php endif; ?>
    </div>
<?php endforeach; ?>

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
                            <option value="Privacy Violation">Privacy</option>
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


<?php else: ?>
    <p>No questions found for this section.</p>
<?php endif; ?>
    </div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>

<script>
    document.getElementById('askQuestionForm').addEventListener('submit', function(event) {
        event.preventDefault(); // Prevent the default form submission (which would refresh the page)

        // Create a FormData object to capture the form data
        var formData = new FormData(this);

        // Send the form data to the server using fetch (AJAX)
        fetch('submit.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())  // Get the response text from the server
        .then(data => {
            console.log(data);  // Optional: log the server response to the console for debugging

            // Optionally, handle the server response
            // If successful, you can do things like showing a success message or closing the modal
            alert('Question posted successfully!');
            location.reload(); // Optionally reload the page to reflect the new post
        })
        .catch(error => {
            console.error('Error:', error);
            alert('There was an error submitting your question. Please try again.');
        });
    });
</script>

<script>
    // Function to search questions based on input
    function searchQuestions() {
    const searchQuery = document.getElementById("searchInput").value;

    // Create a new XMLHttpRequest to perform AJAX
    const xhr = new XMLHttpRequest();
    xhr.open("GET", "search.php?q=" + encodeURIComponent(searchQuery), true);
    xhr.onload = function() {
        if (xhr.status == 200) {
            // Update the page with search results
            document.getElementById("searchResults").innerHTML = xhr.responseText;
        }
    };
    xhr.send();
}

</script>

<script>
    // Handle the report button click and pass the postid to the modal form
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



</body>
</html>
