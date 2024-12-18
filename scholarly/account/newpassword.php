<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholarly - Forgot Password</title>
    <link rel="stylesheet" href="scholarly.css">
</head>
<body>

<div class="login-container">
    <div class="login-box">
        <h1 class="logo">Scholarly</h1>
        <p>Enter Your New Password</p>

        <form action="newpassword1.php" method="POST" class="login-form">
            <label for="npassword">Enter New Password</label>
            <input type="password" id="npassword" name="npassword" placeholder="Enter new password." required>

            <label for="cnpassword">Confirm New Password</label>
            <input type="password" id="cnpassword" name="cnpassword" placeholder="Confirma new password." required>

            <button type="submit" class="login-button">Confirm New Password</button>
        </form>
    </div>
</div>

</body>
</html>
