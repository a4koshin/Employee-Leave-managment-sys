<?php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../helpers/auth.php";
requireAdmin();

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

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
        $userStmt = $pdo->prepare(
          "INSERT INTO users (fullname, email, password, role) VALUES (?, ?, ?, 'employee')"
        );
        $userStmt->execute([$fullname, $email, $hash]);
        $userId = (int) $pdo->lastInsertId();

        $empStmt = $pdo->prepare(
          "INSERT INTO employees (user_id, employee_code, department, job_title, phone, address, start_date, status)
           VALUES (?, NULL, ?, ?, ?, ?, ?, ?)"
        );
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

$stmt = $pdo->query("
  SELECT
    e.id,
    e.department,
    e.job_title,
    e.phone,
    e.address,
    e.start_date,
    e.status,
    u.fullname,
    u.email
  FROM employees e
  JOIN users u ON u.id = e.user_id
  ORDER BY e.created_at DESC
");
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>Admin - Employees</title>

  <style>
    :root {
      --ink: #0f172a;
      --muted: #64748b;
      --brand: #0f766e;
      --surface: #ffffff;
      --bg: #f4f6fb;
    }

    body {
      background: var(--bg);
      color: var(--ink);
      font-family: "Manrope", system-ui, -apple-system, Segoe UI, sans-serif;
    }

    .sidebar {
      width: 260px;
      min-height: 100vh;
      position: sticky;
      top: 0;
      background: #0b1f2a;
      color: #e2e8f0;
    }

    .sidebar .nav-link {
      color: #e2e8f0;
      border-radius: 10px;
      padding: 10px 12px;
    }

    .sidebar .nav-link.active,
    .sidebar .nav-link:hover {
      background: rgba(15, 118, 110, 0.35);
      color: #fff;
    }

    .card-glass {
      background: rgba(255, 255, 255, 0.92);
      border: 1px solid rgba(148, 163, 184, 0.2);
      box-shadow: 0 12px 30px rgba(15, 23, 42, 0.08);
      border-radius: 16px;
    }

    .badge-status {
      padding: 6px 10px;
      border-radius: 999px;
      font-size: 0.75rem;
    }

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

<body>
<div class="d-flex">
  <aside class="sidebar p-3">
    <div class="mb-3 p-3 rounded" style="background: rgba(255,255,255,0.08);">
      <div class="fw-bold fs-5 text-white">Employee Leave System</div>
      <div class="small text-white-50 mb-3">Admin Panel</div>
      <div class="fw-semibold text-white"><?= htmlspecialchars($_SESSION['user']['fullname']) ?></div>
      <div class="small text-white-50"><?= htmlspecialchars($_SESSION['user']['email']) ?></div>
      <span class="badge text-bg-warning mt-2">admin</span>
    </div>

    <nav class="nav flex-column nav-pills gap-1" style="height: calc(100vh - 220px);">
      <a class="nav-link" href="/admin/requests.php">Manage Requests</a>
      <a class="nav-link active" href="/admin/employees.php">Employees</a>
      <div class="mt-auto">
        <hr class="my-3 border-secondary">
        <a class="nav-link text-danger" href="/logout.php">Logout</a>
      </div>
    </nav>
  </aside>

  <main class="flex-grow-1 p-4">
    <div class="container-fluid">
      <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
          <h3 class="mb-1">Employees</h3>
          <div class="text-muted">Manage employee profiles and access.</div>
        </div>
        <button class="btn btn-sidebar" data-bs-toggle="modal" data-bs-target="#addEmployeeModal">+ Add Employee</button>
      </div>

      <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <div class="card-glass p-3">
        <div class="table-responsive">
          <table class="table table-hover align-middle mb-0">
            <thead>
              <tr>
                <th>#</th>
                <th>Employee</th>
                <th>Department</th>
                <th>Title</th>
                <th>Phone</th>
                <th>Status</th>
                <th style="width: 180px;">Action</th>
              </tr>
            </thead>
            <tbody>
            <?php if (empty($employees)): ?>
              <tr>
                <td colspan="8" class="text-center text-muted py-4">No employees found.</td>
              </tr>
            <?php else: ?>
              <?php foreach ($employees as $i => $emp): ?>
                <tr>
                  <td><?= $i + 1 ?></td>
                  <td>
                    <div class="fw-semibold"><?= htmlspecialchars($emp['fullname']) ?></div>
                    <div class="text-muted small"><?= htmlspecialchars($emp['email']) ?></div>
                  </td>
                  <td><?= htmlspecialchars($emp['department']) ?></td>
                  <td><?= htmlspecialchars($emp['job_title']) ?></td>
                  <td><?= htmlspecialchars($emp['phone'] ?? '-') ?></td>
                  <td>
                    <span class="badge badge-status text-bg-<?= $emp['status'] === 'active' ? 'success' : 'secondary' ?>">
                      <?= htmlspecialchars($emp['status']) ?>
                    </span>
                  </td>
                  <td>
                    <a class="btn btn-sm btn-outline-primary" href="/admin/employee_edit.php?id=<?= $emp['id'] ?>">Edit</a>
                    <form class="d-inline" method="POST" action="/admin/employee_delete.php" onsubmit="return confirm('Delete this employee?')">
                      <input type="hidden" name="id" value="<?= $emp['id'] ?>">
                      <button class="btn btn-sm btn-outline-danger" type="submit">Delete</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
</div>

<div class="modal fade" id="addEmployeeModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Add Employee</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <form method="POST">
        <div class="modal-body">
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
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
          <button class="btn btn-sidebar" type="submit">Create Employee</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
