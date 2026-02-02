<?php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../helpers/auth.php";
requireAdmin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: /admin/employees.php");
  exit;
}

$id = (int) ($_POST['id'] ?? 0);
if ($id <= 0) {
  header("Location: /admin/employees.php?error=" . urlencode("Invalid employee."));
  exit;
}

$stmt = $pdo->prepare("SELECT user_id FROM employees WHERE id = ?");
$stmt->execute([$id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
  header("Location: /admin/employees.php?error=" . urlencode("Employee not found."));
  exit;
}

$del = $pdo->prepare("DELETE FROM users WHERE id = ?");
$del->execute([$employee['user_id']]);

header("Location: /admin/employees.php?success=" . urlencode("Employee deleted successfully."));
exit;
