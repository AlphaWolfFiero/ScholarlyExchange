<?php
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "scholarlyexchangedb"; 


$conn = new mysqli($servername, $username, $password, $dbname);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $pname = $_POST['pname'];
    $bdate = $_POST['bdate'];
    $pbio = $_POST['pbio'];
    $subj1 = $_POST['subj1'];
    $subj2 = $_POST['subj2'];
    $subj3 = $_POST['subj3'];

    
    if ($subj1 === $subj2 || $subj2 === $subj3 || $subj3 === $subj1) {
        echo '<script>alert("Please Refrain from Having Duplicate Subject Input.")</script>';

        header("Location: accdeets1.php");
    }else{
        $stmt = $conn->prepare("SELECT user_name, email, pass_word FROM user_info");

        $stmt->execute();
        $result=$stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $username = $row["user_name"];
                $email = $row["email"];
                $password = $row["pass_word"];
            }
        }
        $stmt->close();

        
        $stmt = $conn->prepare("INSERT INTO users (user_name, email, pass_word, profile_name, bio, spec_sub1, spec_sub2, spec_sub3, bdate) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssss", $username, $email, $password, $pname, $pbio, $subj1, $subj2, $subj3, $bdate);
        
        $stmt->execute();

        $stmt = $conn->prepare("SELECT email, user_priv FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result=$stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $email = $row["email"];
                $priv = $row["user_priv"];
            }
        }
        $stmt->close();

        $stmt = $conn->prepare("DELETE FROM user_info");
        $stmt->execute();

        
        $stmt->close();

        $stmt = $conn->prepare("INSERT INTO current (email, user_priv) VALUES (?, ?)");
        $stmt->bind_param("ss", $email, $priv);
        $stmt->execute();
        $stmt->close();

        header("Location: home.php");
    }

}

$conn->close();