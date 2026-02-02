<?php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../helpers/auth.php";
requireAdmin();

$id = (int) ($_GET['id'] ?? 0);
if ($id <= 0) {
  header("Location: /admin/employees.php?error=" . urlencode("Employee not found."));
  exit;
}

$stmt = $pdo->prepare("
  SELECT e.*, u.fullname, u.email
  FROM employees e
  JOIN users u ON u.id = e.user_id
  WHERE e.id = ?
");
$stmt->execute([$id]);
$employee = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$employee) {
  header("Location: /admin/employees.php?error=" . urlencode("Employee not found."));
  exit;
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fullname = trim($_POST['fullname'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $newPassword = $_POST['password'] ?? '';
  $department = trim($_POST['department'] ?? '');
  $jobTitle = trim($_POST['job_title'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $address = trim($_POST['address'] ?? '');
  $startDate = $_POST['start_date'] ?? null;
  $status = $_POST['status'] ?? 'active';

  if (!$fullname || !$email || !$department || !$jobTitle) {
    $error = "Please fill all required fields.";
  } elseif (!in_array($status, ['active', 'inactive'], true)) {
    $error = "Invalid status.";
  } else {
    $emailExists = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id <> ?");
    $emailExists->execute([$email, $employee['user_id']]);
    if ($emailExists->fetch()) {
      $error = "Email already exists.";
    } else {
      $pdo->beginTransaction();
      try {
        $userSql = "UPDATE users SET fullname = ?, email = ? WHERE id = ?";
        $userParams = [$fullname, $email, $employee['user_id']];
        $userStmt = $pdo->prepare($userSql);
        $userStmt->execute($userParams);

        if ($newPassword) {
          $hash = password_hash($newPassword, PASSWORD_DEFAULT);
          $pwdStmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
          $pwdStmt->execute([$hash, $employee['user_id']]);
        }

        $update = $pdo->prepare("
          UPDATE employees
          SET department = ?, job_title = ?, phone = ?, address = ?, start_date = ?, status = ?
          WHERE id = ?
        ");
        $update->execute([
          $department,
          $jobTitle,
          $phone ?: null,
          $address ?: null,
          $startDate ?: null,
          $status,
          $id
        ]);

        $pdo->commit();
        header("Location: /admin/employees.php?success=" . urlencode("Employee updated successfully."));
        exit;
      } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Failed to update employee.";
      }
    }
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>Edit Employee</title>

  <style>
    .btn-sidebar {
      background: #0b1f2a;
      color: #fff;
      border: none;
    }

    .btn-sidebar:hover {
      background: #0a2a37;
      color: #fff;
    }
  </style>
</head>

<body class="bg-light">
  <div class="container py-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h3 class="mb-0">Edit Employee</h3>
      <a class="btn btn-outline-secondary" href="/admin/employees.php">Back</a>
    </div>

    <?php if ($error): ?>
      <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <div class="card shadow-sm">
      <div class="card-body">
        <form method="POST">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Full Name</label>
              <input type="text" name="fullname" class="form-control" required
                     value="<?= htmlspecialchars($employee['fullname']) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" required
                     value="<?= htmlspecialchars($employee['email']) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">New Password (optional)</label>
              <input type="password" name="password" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Department</label>
              <input type="text" name="department" class="form-control" required
                     value="<?= htmlspecialchars($employee['department']) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Job Title</label>
              <input type="text" name="job_title" class="form-control" required
                     value="<?= htmlspecialchars($employee['job_title']) ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone</label>
              <input type="text" name="phone" class="form-control"
                     value="<?= htmlspecialchars($employee['phone'] ?? '') ?>">
            </div>
            <div class="col-12">
              <label class="form-label">Address</label>
              <input type="text" name="address" class="form-control"
                     value="<?= htmlspecialchars($employee['address'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Start Date</label>
              <input type="date" name="start_date" class="form-control"
                     value="<?= htmlspecialchars($employee['start_date'] ?? '') ?>">
            </div>
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select class="form-select" name="status">
                <option value="active" <?= $employee['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                <option value="inactive" <?= $employee['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
              </select>
            </div>
          </div>

          <div class="mt-4 d-flex gap-2">
            <button class="btn btn-sidebar" type="submit">Save Changes</button>
            <a class="btn btn-outline-secondary" href="/admin/employees.php">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
