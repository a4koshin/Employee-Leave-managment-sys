<?php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../helpers/auth.php";
requireLogin();

$user = $_SESSION['user'];
if ($user['role'] !== 'admin') {
    header("Location: /leave/list.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /admin/requests.php");
    exit;
}

$id = (int) ($_POST['id'] ?? 0);
$action = $_POST['action'] ?? '';
$comment = trim($_POST['admin_comment'] ?? '');

if ($id <= 0 || !in_array($action, ['approved', 'rejected'], true)) {
    header("Location: /admin/requests.php?error=" . urlencode("Invalid request."));
    exit;
}

// Update status + comment
$stmt = $pdo->prepare("UPDATE leave_requests SET status = ?, admin_comment = ? WHERE id = ?");
$stmt->execute([$action, $comment, $id]);

header("Location: /admin/requests.php?success=" . urlencode("Request updated successfully."));
exit;
