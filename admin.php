<?php
require_once 'helpers.php';
require_once 'db.php';
require_admin($conn);   // only logged-in administrators may see this page

// Pending requests first, then everyone else.
$users = $conn->query(
    "SELECT id, username, role, status, created_at FROM users
     ORDER BY (status = 'pending') DESC, id"
);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
</head>
<body>
    <h2>Admin Panel</h2>
    <p>Logged in as <?php echo e($_SESSION['username']); ?>.</p>
    <?php echo flash_html(); ?>

    <h3>Users</h3>
    <table border="1" cellpadding="4">
        <tr>
            <th>ID</th>
            <th>Username</th>
            <th>Role</th>
            <th>Status</th>
            <th>Registered</th>
            <th>Actions</th>
        </tr>
        <?php while ($row = $users->fetch_assoc()) { ?>
        <tr>
            <td><?php echo e($row['id']); ?></td>
            <td><?php echo e($row['username']); ?></td>
            <td><?php echo e($row['role']); ?></td>
            <td><?php echo e($row['status']); ?></td>
            <td><?php echo e($row['created_at']); ?></td>
            <td>
                <?php if ($row['role'] === 'user') { ?>
                    <form method="POST" action="approve.php" style="display:inline">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="id" value="<?php echo e($row['id']); ?>">
                        <?php if ($row['status'] !== 'approved') { ?>
                            <button type="submit">Approve</button>
                        <?php } ?>
                    </form>
                    <form method="POST" action="reject.php" style="display:inline">
                        <?php echo csrf_field(); ?>
                        <input type="hidden" name="id" value="<?php echo e($row['id']); ?>">
                        <?php if ($row['status'] !== 'rejected') { ?>
                            <button type="submit">Reject</button>
                        <?php } ?>
                    </form>
                <?php } else { ?>
                    (administrator)
                <?php } ?>
            </td>
        </tr>
        <?php } ?>
    </table>

    <p>
        <a href="dashboard.php">Dashboard</a>
    </p>
    <form action="logout.php" method="POST">
        <?php echo csrf_field(); ?>
        <button type="submit">Logout</button>
    </form>
</body>
</html>
