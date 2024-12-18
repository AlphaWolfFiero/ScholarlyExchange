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
        <h1 class="logo">Scholarly Exchange</h1>
        <p>Reset your password</p>

        <form action="reset_password.php" method="POST" class="login-form">
            <label for="email">Email</label>
            <input type="email" id="email" name="email" placeholder="email@mail.com" required>

            <button type="submit" class="login-button">Reset Password</button>
        </form>
    </div>
</div>

</body>
</html>
