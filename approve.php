<?php
require_once 'helpers.php';
require_once 'db.php';
require_admin($conn);   // only administrators

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin.php');
}
csrf_verify();

// The id must be a whole number. Anything else is refused before it gets near the database.
$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
if ($id === false || $id < 1) {
    flash_set('error', 'Invalid user ID.');
    redirect('admin.php');
}

// The "?" is filled in safely by bind_param. "role = 'user'" means admins can't be changed from here.
$stmt = $conn->prepare("UPDATE users SET status = 'approved' WHERE id = ? AND role = 'user'");
$stmt->bind_param("i", $id);
$stmt->execute();

flash_set('success', $stmt->affected_rows > 0 ? 'User approved.' : 'No change was made.');
redirect('admin.php');
