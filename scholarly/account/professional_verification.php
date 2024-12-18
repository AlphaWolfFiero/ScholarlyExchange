<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify College Graduation</title>
    <link rel="stylesheet" href="scholarly.css">
    <script>
        
        function validateForm() {
            var imgurLink = document.getElementById("imgur_link").value;
            var fileInput = document.getElementById("diploma_photo").files.length;
            if (imgurLink === "" && fileInput === 0) {
                alert("Please provide either an Imgur link or upload a photo of your diploma.");
                return false;
            }
            return true;
        }
    </script>
</head>
<body>

<div class="login-container">
    <div class="login-box">
        <h1 class="logo">Verify Graduation</h1>
        <p>Please upload a photo of your diploma or provide an Imgur link to verify your college graduation.</p>

        <!-- Form to upload photo or provide Imgur link -->
        <form action="verify_graduation.php" method="POST" enctype="multipart/form-data" class="login-form" onsubmit="return validateForm()">
            
            <!-- Imgur Link Input -->
            <label for="imgur_link">Imgur Link (Optional)</label>
            <input type="url" id="imgur_link" name="imgur_link" placeholder="Enter your Imgur link" pattern="https?:
            
            <!-- OR -->
            <p style="text-align: center;">OR</p>
            
            <!-- File Upload Input -->
            <label for="diploma_photo">Upload Diploma Photo (Optional)</label>
            <input type="file" id="diploma_photo" name="diploma_photo" accept="image/*">

            <button type="submit" class="login-button">Submit</button>
        </form>
    </div>
</div>

</body>
</html>