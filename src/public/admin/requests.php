<?php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../helpers/auth.php";
requireLogin();

$user = $_SESSION['user'];
if ($user['role'] !== 'admin') {
  header("Location: /leave/list.php");
  exit;
}

// Fetch all leave requests + employee + leave type
$stmt = $pdo->query("
  SELECT
    lr.id,
    lr.start_date,
    lr.end_date,
    lr.reason,
    lr.status,
    lr.admin_comment,
    lr.created_at,
    u.fullname AS employee_name,
    u.email AS employee_email,
    lt.name AS leave_type
  FROM leave_requests lr
  JOIN users u ON u.id = lr.user_id
  JOIN leave_types lt ON lt.id = lr.leave_type_id
  ORDER BY lr.created_at DESC
");
$requests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Flash messages
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';

function badgeClass($status) {
  if ($status === 'pending') return 'warning';
  if ($status === 'approved') return 'success';
  if ($status === 'rejected') return 'danger';
  return 'secondary';
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>Admin - Manage Requests</title>

  <style>
    .sidebar { width: 260px; min-height: 100vh; position: sticky; top: 0; }
  </style>
</head>

<body class="bg-light">
<div class="d-flex">

  <!-- Sidebar -->
  <aside class="sidebar bg-white border-end p-3">
    <div class="mb-4">
      <div class="fw-bold fs-5">Employee Leave System</div>
      <div class="text-muted small">Admin Panel</div>
    </div>

    <div class="mb-3 p-3 bg-light rounded">
      <div class="fw-semibold"><?= htmlspecialchars($user['fullname']) ?></div>
      <div class="text-muted small"><?= htmlspecialchars($user['email']) ?></div>
      <span class="badge text-bg-warning mt-2">admin</span>
    </div>

    <nav class="nav flex-column nav-pills gap-1">
      <a class="nav-link active" href="/admin/requests.php">Manage Requests</a>
      <hr class="my-3">
      <a class="nav-link text-danger" href="/logout.php">Logout</a>
    </nav>
  </aside>

  <!-- Main -->
  <main class="flex-grow-1 p-4">
    <div class="container-fluid">

      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Manage Leave Requests</h3>
      </div>

      <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
      <?php endif; ?>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
      <?php endif; ?>

      <div class="card shadow-sm">
        <div class="card-body">

          <div class="table-responsive">
            <table class="table table-hover align-middle">
              <thead>
                <tr>
                  <th>#</th>
                  <th>Employee</th>
                  <th>Type</th>
                  <th>Dates</th>
                  <th>Status</th>
                  <th>Reason</th>
                  <th>Admin Comment</th>
                  <th style="width: 240px;">Action</th>
                </tr>
              </thead>

              <tbody>
              <?php if (empty($requests)): ?>
                <tr>
                  <td colspan="8" class="text-center text-muted py-4">No leave requests found.</td>
                </tr>
              <?php else: ?>
                <?php foreach ($requests as $i => $r): ?>
                  <tr>
                    <td><?= $i + 1 ?></td>

                    <td>
                      <div class="fw-semibold"><?= htmlspecialchars($r['employee_name']) ?></div>
                      <div class="text-muted small"><?= htmlspecialchars($r['employee_email']) ?></div>
                    </td>

                    <td><?= htmlspecialchars($r['leave_type']) ?></td>

                    <td>
                      <div><span class="text-muted">From:</span> <?= htmlspecialchars($r['start_date']) ?></div>
                      <div><span class="text-muted">To:</span> <?= htmlspecialchars($r['end_date']) ?></div>
                    </td>

                    <td>
                      <span class="badge text-bg-<?= badgeClass($r['status']) ?>">
                        <?= htmlspecialchars($r['status']) ?>
                      </span>
                    </td>

                    <td style="max-width: 220px;">
                      <div class="text-truncate" title="<?= htmlspecialchars($r['reason'] ?? '') ?>">
                        <?= htmlspecialchars($r['reason'] ?? '') ?>
                      </div>
                    </td>

                    <td style="max-width: 220px;">
                      <div class="text-truncate" title="<?= htmlspecialchars($r['admin_comment'] ?? '') ?>">
                        <?= htmlspecialchars($r['admin_comment'] ?? '') ?>
                      </div>
                    </td>

                    <td>
                      <!-- Inline action form -->
                      <form method="POST" action="/admin/update_status.php" class="d-flex flex-column gap-2">
                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">

                        <input
                          type="text"
                          name="admin_comment"
                          class="form-control form-control-sm"
                          placeholder="Add comment (optional)"
                          value="<?= htmlspecialchars($r['admin_comment'] ?? '') ?>"
                        >

                        <div class="d-flex gap-2">
                          <button
                            type="submit"
                            name="action"
                            value="approved"
                            class="btn btn-sm btn-success w-50"
                            <?= ($r['status'] === 'approved') ? 'disabled' : '' ?>
                          >
                            Approve
                          </button>

                          <button
                            type="submit"
                            name="action"
                            value="rejected"
                            class="btn btn-sm btn-danger w-50"
                            <?= ($r['status'] === 'rejected') ? 'disabled' : '' ?>
                          >
                            Reject
                          </button>
                        </div>
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

    </div>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
