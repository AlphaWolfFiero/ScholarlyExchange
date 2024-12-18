<?php

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
$email = $result->fetch_assoc()['email'];
$stmt->close();


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $imgurLink = isset($_POST['imgur_link']) ? trim($_POST['imgur_link']) : null;
    $uploadedFile = isset($_FILES['diploma_photo']) ? $_FILES['diploma_photo'] : null;
    $verificationStatus = 'Pending'; 

    
    if (empty($imgurLink) && empty($uploadedFile['name'])) {
        echo "Please provide either an Imgur link or upload a photo of your diploma.";
    } else {
        
        if ($imgurLink) {
            
            $stmt = $conn->prepare("INSERT INTO prof_ver (email, status, imgur, diploma) VALUES (?, ?, ?, ?)");
            $null = NULL; 
            $stmt->bind_param("ssss", $email, $verificationStatus, $imgurLink, $null);
            $stmt->execute();
            $message = "Imgur link is valid. Graduation photo verification in progress...";
        } elseif ($uploadedFile) {
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType = $uploadedFile['type'];

            if (in_array($fileType, $allowedTypes)) {
                
                $fileTmpName = $uploadedFile['tmp_name'];
                $fileData = file_get_contents($fileTmpName);

                
                $stmt = $conn->prepare("INSERT INTO prof_ver (email, status, imgur, diploma) VALUES (?, ?, ?, ?)");
                $null = NULL; 
                $stmt->bind_param("ssss", $email, $verificationStatus, $null, $fileData);
                $stmt->execute();
                $message = "Diploma photo uploaded successfully. Graduation verification in progress...";
            } else {
                echo "Invalid file type. Please upload a JPEG, PNG, or GIF image.";
            }
        }

        
        echo "<script>
                alert('$message');
                window.location.href = 'home.php';
              </script>";
    }
}
?>
