<?php
    $servername = "localhost"; 
    $username = "root"; 
    $password = ""; 
    $dbname = "scholarlyexchangedb"; 

    $conn = new mysqli($servername, $username, $password, $dbname);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $code_in = $_POST['code'];

    $stmt = $conn->prepare("SELECT * FROM vercode");
    $stmt->execute();
    $result=$stmt->get_result();

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $code = $row["code"];
                $email = $row["email"];
            }
        }
        $stmt->close();
    


    if($code_in == $code) {
        header('Location: newpassword.php');
    } else {
        echo '<script>alert("Incorrect Code.")
        window.location.href = "rpassconf.php";</script>';
    }
}