<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Scholarly</title>
    <link rel="stylesheet" href="scholarly.css">
</head>
<body>

<div class="login-container">
    <div class="login-box">
        <h1 class="logo">Scholarly Exchange</h1>
        <p>Register</p>

        <form action="register.php" method="POST" class="login-form">
            <label for="email">Email</label>
            <input type="text" id="email" name="email" placeholder="Enter your email" required pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$" title="Please enter a valid email address">

            <label for="username">Username</label>
            <input type="text" id="username" name="username" placeholder="Create a username" required>

            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Create a password" required>

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>

            <button type="submit" class="login-button">Register</button>
        </form>

        <p>Already have an account? <a href="scholarly.php">Log In</a></p>
    </div>
</div>

</body>
</html>
