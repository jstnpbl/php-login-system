<?php
require_once 'helpers.php';
require_once 'db.php';

// A real hash of a throwaway password. When the username does not exist we still check
// against this, so a wrong username and a wrong password take about the same time.
const DUMMY_HASH = '$2y$10$h1HBGumw49ypvI.5z5SoXOEcKrlPdOGqdIfGLSnvTN2X6HSNQFUGO';

const MAX_FAILED_ATTEMPTS = 5;   // wrong passwords allowed in a row...
const LOCK_MINUTES = 5;          // ...before the account is locked for this long

// Only the login form may send data here.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}
csrf_verify();

$username = trim($_POST['username'] ?? '');
$password = $_POST['password'] ?? '';

// Look the user up with a normal query. (Usernames are plain text now and UNIQUE.)
$stmt = $conn->prepare(
    "SELECT id, username, password_hash, role, status, failed_attempts,
            (locked_until IS NOT NULL AND locked_until > NOW()) AS is_locked
     FROM users WHERE username = ?"
);
$stmt->bind_param("s", $username);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Same message for "no such user" and "wrong password", so it doesn't reveal which usernames exist.
$genericError = 'Invalid username or password.';

function fail_login($message) {
    flash_set('error', $message);
    redirect('index.php');
}

if (!$user) {
    password_verify($password, DUMMY_HASH);   // spend the same time as a real check
    fail_login($genericError);
}

if ($user['is_locked']) {
    fail_login('Too many failed attempts. Please try again in a few minutes.');
}

if (!password_verify($password, $user['password_hash'])) {
    $attempts = $user['failed_attempts'] + 1;
    if ($attempts >= MAX_FAILED_ATTEMPTS) {
        $lock = $conn->prepare("UPDATE users SET failed_attempts = 0, locked_until = DATE_ADD(NOW(), INTERVAL ? MINUTE) WHERE id = ?");
        $minutes = LOCK_MINUTES;
        $lock->bind_param("ii", $minutes, $user['id']);
        $lock->execute();
    } else {
        $count = $conn->prepare("UPDATE users SET failed_attempts = ? WHERE id = ?");
        $count->bind_param("ii", $attempts, $user['id']);
        $count->execute();
    }
    fail_login($genericError);
}

// Correct password. Clear the failed-attempt counter.
$reset = $conn->prepare("UPDATE users SET failed_attempts = 0, locked_until = NULL WHERE id = ?");
$reset->bind_param("i", $user['id']);
$reset->execute();

// Correct password, but the account may not be approved yet.
// (Only someone who knows the password sees these messages.)
if ($user['status'] === 'pending') {
    fail_login('Your account is still waiting for admin approval.');
}
if ($user['status'] === 'rejected') {
    fail_login('Your registration was not approved.');
}

// Success. Give the visitor a brand-new session ID, so no old ID can be reused (session fixation).
session_regenerate_id(true);
$_SESSION['user_id']  = $user['id'];
$_SESSION['username'] = $user['username'];
$_SESSION['role']     = $user['role'];

redirect($user['role'] === 'admin' ? 'admin.php' : 'dashboard.php');
