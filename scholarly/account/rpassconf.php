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
        <p>Insert 6-Digit Reset Password Code from Email</p>

        <form action="rpassconf1.php" method="POST" class="login-form">
            <label for="code">6 Digit Code</label>
            <input type="text" id="code" name="code" placeholder="Insert code" required>

            <button type="submit" class="login-button">Reset Password</button>
        </form>
    </div>
</div>

</body>
</html>
