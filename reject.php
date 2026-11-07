<?php
require_once 'helpers.php';
require_once 'db.php';
require_admin($conn);   // only administrators

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin.php');
}
csrf_verify();

$id = filter_var($_POST['id'] ?? '', FILTER_VALIDATE_INT);
if ($id === false || $id < 1) {
    flash_set('error', 'Invalid user ID.');
    redirect('admin.php');
}

// The record is kept (status "rejected") instead of deleted, so you can still see it
// and the username stays reserved.
$stmt = $conn->prepare("UPDATE users SET status = 'rejected' WHERE id = ? AND role = 'user'");
$stmt->bind_param("i", $id);
$stmt->execute();

flash_set('success', $stmt->affected_rows > 0 ? 'User rejected.' : 'No change was made.');
redirect('admin.php');
