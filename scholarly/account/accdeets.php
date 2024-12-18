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
        <h1 class="logo">Account</h1>
        <p>Tell us about yourself!</p>

        <form action="accdeets1.php" method="POST" class="login-form">
            <label for="pname">Profile Name</label>
            <input type="text" id="pname" name="pname" placeholder="Enter your Displayed Name" required>

            <label for="bdate">Birthdate</label>
            <input type="date" id="bdate" name="bdate" required>

            <label for="pbio">Bio</label>
            <input type="text" id="pbio" name="pbio" placeholder="Your bio">

            <label for="subj1">Subject of Interest #1</label>
            <select id="subj1" name="subj1" required>
                <option value="" disabled selected>--Select a Subject--</option>
                <option value="math">Mathematics</option>
                <option value="sci">Science</option>
                <option value="eng">English</option>
                <option value="fil">Filipino</option>
                <option value="ap">Araling Panlipunan</option>
                <option value="his">History</option>
                <option value="tle">Technology and Livelihood Education</option>
                <option value="pe">Physical Education</option>
            </select>

            <label for="subj2">Subject of Interest #2</label>
            <select id="subj2" name="subj2" required>
                <option value="" disabled selected>--Select a Subject--</option>
                <option value="math">Mathematics</option>
                <option value="sci">Science</option>
                <option value="eng">English</option>
                <option value="fil">Filipino</option>
                <option value="ap">Araling Panlipunan</option>
                <option value="his">History</option>
                <option value="tle">Technology and Livelihood Education</option>
                <option value="pe">Physical Education</option>
            </select>

            <label for="subj3">Subject of Interest #3</label>
            <select id="subj3" name="subj3" required>
                <option value="" disabled selected>--Select a Subject--</option>
                <option value="math">Mathematics</option>
                <option value="sci">Science</option>
                <option value="eng">English</option>
                <option value="fil">Filipino</option>
                <option value="ap">Araling Panlipunan</option>
                <option value="his">History</option>
                <option value="tle">Technology and Livelihood Education</option>
                <option value="pe">Physical Education</option>
            </select>

            <button type="submit" class="login-button">Register</button>
        </form>
    </div>
</div>

</body>
</html>
