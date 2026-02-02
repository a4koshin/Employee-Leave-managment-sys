<?php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../helpers/auth.php";
requireAdmin();

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fullname = trim($_POST['fullname'] ?? '');
  $email = trim($_POST['email'] ?? '');
  $password = $_POST['password'] ?? '';
  $department = trim($_POST['department'] ?? '');
  $jobTitle = trim($_POST['job_title'] ?? '');
  $phone = trim($_POST['phone'] ?? '');
  $address = trim($_POST['address'] ?? '');
  $startDate = $_POST['start_date'] ?? null;
  $status = $_POST['status'] ?? 'active';

  if (!$fullname || !$email || !$password || !$department || !$jobTitle) {
    $error = "Please fill all required fields.";
  } elseif (!in_array($status, ['active', 'inactive'], true)) {
    $error = "Invalid status.";
  } else {
    $exists = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $exists->execute([$email]);
    if ($exists->fetch()) {
      $error = "Email already exists.";
    } else {
      $pdo->beginTransaction();
      try {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $userStmt = $pdo->prepare("
          INSERT INTO users (fullname, email, password, role)
          VALUES (?, ?, ?, 'employee')
        ");
        $userStmt->execute([$fullname, $email, $hash]);
        $userId = (int) $pdo->lastInsertId();

        $empStmt = $pdo->prepare("
          INSERT INTO employees (user_id, employee_code, department, job_title, phone, address, start_date, status)
          VALUES (?, NULL, ?, ?, ?, ?, ?, ?)
        ");
        $empStmt->execute([
          $userId,
          $department,
          $jobTitle,
          $phone ?: null,
          $address ?: null,
          $startDate ?: null,
          $status
        ]);

        $pdo->commit();
        header("Location: /admin/employees.php?success=" . urlencode("Employee created successfully."));
        exit;
      } catch (Exception $e) {
        $pdo->rollBack();
        $error = "Failed to create employee.";
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
  <title>Add Employee</title>

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
      <h3 class="mb-0">Add Employee</h3>
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
              <input type="text" name="fullname" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Phone</label>
              <input type="text" name="phone" class="form-control">
            </div>
            <div class="col-12">
              <label class="form-label">Address</label>
              <input type="text" name="address" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Department</label>
              <input type="text" name="department" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Job Title</label>
              <input type="text" name="job_title" class="form-control" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Start Date</label>
              <input type="date" name="start_date" class="form-control">
            </div>
            <div class="col-md-6">
              <label class="form-label">Status</label>
              <select class="form-select" name="status">
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
              </select>
            </div>
          </div>

          <div class="mt-4 d-flex gap-2">
            <button class="btn btn-sidebar" type="submit">Create Employee</button>
            <a class="btn btn-outline-secondary" href="/admin/employees.php">Cancel</a>
          </div>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
