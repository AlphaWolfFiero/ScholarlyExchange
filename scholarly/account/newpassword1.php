<?php
$servername = "localhost"; 
$username = "root"; 
$password = ""; 
$dbname = "scholarlyexchangedb"; 

$conn = new mysqli($servername, $username, $password, $dbname);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $npass = $_POST['npassword'];
    $cnpass = $_POST['cnpassword'];

    if($npass === $cnpass){

        $stmt = $conn->prepare("SELECT user_name, email, pass_word FROM user_info");
        $stmt->execute();

        $result=$stmt->get_result();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $username = $row["user_name"];
            $email = $row["email"];
            $password = $row["pass_word"];
        }
        $stmt->close();
        
        $stmt = $conn->prepare("UPDATE users SET pass_word = ? WHERE user_name = ?");
        $stmt->bind_param("ss", $npass, $username);
        $stmt->execute();

        $stmt = $conn->prepare("DELETE FROM vercode WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        echo '<script>alert("Password Changed.")
        window.location.href = "scholarly.php";</script>';
    }else{
        echo '<script>alert("Passwords do not match.")
        window.location.href = "newpassword.php";</script>';
    }

}
}