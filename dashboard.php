<?php
require_once 'helpers.php';
require_once 'db.php';
require_login($conn);   // not logged in (or not approved)? sent back to the login page
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
</head>
<body>
    <h2>Welcome, <?php echo e($_SESSION['username']); ?>!</h2>
    <p>You have successfully logged in.</p>
    <?php if ($_SESSION['role'] === 'admin') { ?>
        <p><a href="admin.php">Go to the admin panel</a></p>
    <?php } ?>
    <form action="logout.php" method="POST">
        <?php echo csrf_field(); ?>
        <button type="submit">Logout</button>
    </form>
</body>
</html>
