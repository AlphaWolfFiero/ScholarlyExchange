<!DOCTYPE html> 
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scholarly - A Place to Share Knowledge</title>
    <link rel="stylesheet" href="scholarly.css">
    <link href="https:
</head>
<body>

<div class="login-container">
    <div class="login-box">
        <h1 class="logo">Scholarly Exchange</h1>
        <p>"The pursuit of knowledge is not the accumulation of facts, 
            but the cultivation of wisdom through inquiry, reflection, and discovery."</p>

        <!-- Login Form -->
        <form action="login.php" method="POST" class="login-form">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Your username" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Your password" required>

            <button type="submit" class="login-button" name="login">Login</button>

            <a href="forgot_password.php" class="forgot-password">Forgot password?</a>
        </form>

        <p class="Register-text">New to Scholarly Exchange? <a href="signup.php">Register</a></p>
    </div>
</div>

<footer>
    <p>&copy; Scholarly Exchange, 2024. All Rights Reserved.</p>
</footer>

</body>
</html>

