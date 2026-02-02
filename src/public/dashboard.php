<?php
require_once __DIR__ . "/../helpers/auth.php";
requireLogin();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Dashboard</title>
</head>

<body>
    <h2>Welcome, <?= htmlspecialchars($_SESSION['user']['fullname']) ?></h2>

    <p>Role: <?= htmlspecialchars($_SESSION['user']['role']) ?></p>

    <ul>
        <li><a href="/leave/list.php">My Leave Requests</a></li>

        <?php if ($_SESSION['user']['role'] === 'admin'): ?>
            <li><a href="/admin/requests.php">Manage Leave Requests</a></li>
        <?php endif; ?>

        <li><a href="/logout.php">Logout</a></li>
    </ul>
</body>

</html>
