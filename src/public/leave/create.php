<?php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../helpers/auth.php";
requireLogin();

$user = $_SESSION['user'];
if ($user['role'] !== 'employee') {
    header("Location: /admin/requests.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: /leave/list.php");
    exit;
}

$leaveTypeId = (int) ($_POST['leave_type_id'] ?? 0);
$startDate = $_POST['start_date'] ?? '';
$endDate = $_POST['end_date'] ?? '';
$reason = trim($_POST['reason'] ?? '');

if ($leaveTypeId <= 0 || !$startDate || !$endDate) {
    header("Location: /leave/list.php?error=" . urlencode("Please fill all required fields."));
    exit;
}

if ($endDate < $startDate) {
    header("Location: /leave/list.php?error=" . urlencode("End date cannot be before start date."));
    exit;
}

// Insert (Prepared Statement)
$stmt = $pdo->prepare("
  INSERT INTO leave_requests (user_id, leave_type_id, start_date, end_date, reason, status)
  VALUES (?, ?, ?, ?, ?, 'pending')
");
$stmt->execute([$user['id'], $leaveTypeId, $startDate, $endDate, $reason]);

header("Location: /leave/list.php?success=" . urlencode("Leave request submitted successfully."));
exit;
