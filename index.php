<?php
require_once 'helpers.php';

// Already logged in? Skip the login form.
if (isset($_SESSION['user_id'])) {
    redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>
<body>
    <h2>Login</h2>
    <?php echo flash_html(); ?>
    <form action="login.php" method="POST">
        <?php echo csrf_field(); ?>
        <input type="text" name="username" placeholder="Username" required><br>
        <input type="password" name="password" placeholder="Password" required><br>
        <button type="submit">Login</button>
    </form>
    <p>Not registered yet? <a href="register.php">Register here</a></p>
</body>
</html>
