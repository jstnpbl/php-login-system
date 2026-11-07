<?php
// create_admin.php: creates the first administrator account.
// Run it from the COMMAND LINE, once, after importing database.sql:
//
//     C:\xampp\php\php.exe create_admin.php
//
// It refuses to run in a web browser, so nobody can use it to make themselves an admin.
// (The password is visible while you type it. Close the window afterwards.)

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    exit('This script can only be run from the command line.');
}

require_once 'helpers.php';
require_once 'db.php';

echo "Create an administrator account\n";
echo "Username (letters, numbers, underscores): ";
$username = trim(fgets(STDIN));
echo "Password (8 to 72 characters): ";
$password = rtrim(fgets(STDIN), "\r\n");

$errors = validate_registration($username, $password, $password);
if ($errors) {
    echo "\nCould not create the account:\n";
    foreach ($errors as $error) {
        echo " - $error\n";
    }
    exit(1);
}

$hash = password_hash($password, PASSWORD_DEFAULT);
try {
    $stmt = $conn->prepare("INSERT INTO users (username, password_hash, role, status) VALUES (?, ?, 'admin', 'approved')");
    $stmt->bind_param("ss", $username, $hash);
    $stmt->execute();
    echo "\nAdministrator '$username' created. You can now log in.\n";
} catch (mysqli_sql_exception $e) {
    if ($e->getCode() === 1062) {
        echo "\nThat username is already taken.\n";
    } else {
        echo "\nDatabase error: " . $e->getMessage() . "\n";
    }
    exit(1);
}
