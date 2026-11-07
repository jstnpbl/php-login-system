<?php
require_once 'helpers.php';
require_once 'db.php';

$errors = [];
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    csrf_verify();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm  = $_POST['confirm_password'] ?? '';

    $errors = validate_registration($username, $password, $confirm);

    if (!$errors) {
        // password_hash() makes a salted, one-way hash. There is no way to turn it back into the password.
        $hash = password_hash($password, PASSWORD_DEFAULT);

        try {
            $stmt = $conn->prepare("INSERT INTO users (username, password_hash) VALUES (?, ?)");
            $stmt->bind_param("ss", $username, $hash);
            $stmt->execute();

            flash_set('success', 'Registration successful! Your account is waiting for admin approval.');
            redirect('index.php');
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() === 1062) {   // 1062 = "duplicate entry" (the UNIQUE username rule)
                $errors[] = 'That username is already taken.';
            } else {
                error_log('Registration failed: ' . $e->getMessage());
                $errors[] = 'Something went wrong. Please try again.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register</title>
</head>
<body>
    <h2>Register</h2>
    <?php foreach ($errors as $error) { ?>
        <p><strong>Error: <?php echo e($error); ?></strong></p>
    <?php } ?>
    <form action="register.php" method="POST">
        <?php echo csrf_field(); ?>
        <input type="text" name="username" placeholder="Username" value="<?php echo e($username); ?>" required><br>
        <input type="password" name="password" placeholder="Password (8+ characters)" required><br>
        <input type="password" name="confirm_password" placeholder="Confirm password" required><br>
        <button type="submit">Register</button>
    </form>
    <p>Already have an account? <a href="index.php">Login here</a></p>
</body>
</html>
