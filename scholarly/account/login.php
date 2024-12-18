<?php
$servername = "localhost";
$username = "root";
$password = ""; 
$dbname = "scholarlyexchangedb"; 


$conn = new mysqli($servername, $username, $password, $dbname);


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];


    $stmt = $conn->prepare("SELECT * FROM users WHERE user_name = ? OR email = ?");
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $dbpass = $user["pass_word"];
        $bannedStatus = $user['banned'];

       
        if ($bannedStatus === "Yes") {
            echo '<script>alert("Login Unsuccessful. Account is Banned.");
            window.location.href = "scholarly.php";</script>';
            exit();
        }

      
        if ($password === $dbpass) {
        
            session_start();
            $_SESSION["username"] = $user['user_name'];

         
            $stmt = $conn->prepare("SELECT email, user_priv FROM users WHERE user_name = ? or email = ?");
            $stmt->bind_param("ss", $username, $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $email = $row["email"];
                    $priv = $row["user_priv"];
                }
            }
            $stmt->close();

     
            $stmt = $conn->prepare("INSERT INTO current (email, user_priv) VALUES (?, ?)");
            $stmt->bind_param("ss", $email, $priv);
            $stmt->execute();
            $stmt->close();

            // Redirect to home.php
            header("Location: home.php");
            exit();
        } else {
            echo '<script>alert("Incorrect Password.");
            window.location.href = "scholarly.php";</script>';
        }
    } else {
        echo '<script>alert("Account Not Found.");
        window.location.href = "scholarly.php";</script>';
    }

    $stmt->close();
}

$conn->close();
?>
