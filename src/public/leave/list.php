<?php
require_once __DIR__ . "/../../config/db.php";
require_once __DIR__ . "/../../helpers/auth.php";
requireLogin();

$user = $_SESSION['user'];
if ($user['role'] !== 'employee') {
    header("Location: /dashboard.php");
    exit;
}

// Fetch leave types (for modal dropdown)
$typesStmt = $pdo->query("SELECT id, name FROM leave_types ORDER BY name ASC");
$leaveTypes = $typesStmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch this employee's leave requests
$stmt = $pdo->prepare("
  SELECT lr.*, lt.name AS leave_type
  FROM leave_requests lr
  JOIN leave_types lt ON lt.id = lr.leave_type_id
  WHERE lr.user_id = ?
  ORDER BY lr.created_at DESC
");
$stmt->execute([$user['id']]);
$leaves = $stmt->fetchAll(PDO::FETCH_ASSOC);

// flash messages (optional)
$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>My Leave Requests</title>

    <style>
        .sidebar {
            width: 260px;
            min-height: 100vh;
            position: sticky;
            top: 0;
        }
    </style>
</head>

<body class="bg-light">
    <div class="d-flex">

        <!-- Sidebar (employee) -->
        <aside class="sidebar bg-white border-end p-3">
            <div class="mb-4">
                <div class="fw-bold fs-5">Employee Leave System</div>
                <div class="text-muted small">Employee</div>
            </div>

            <div class="mb-3 p-3 bg-light rounded">
                <div class="fw-semibold"><?= htmlspecialchars($user['fullname']) ?></div>
                <div class="text-muted small"><?= htmlspecialchars($user['email']) ?></div>
                <span class="badge text-bg-primary mt-2"><?= htmlspecialchars($user['role']) ?></span>
            </div>

            <nav class="nav flex-column nav-pills gap-1">
                <a class="nav-link" href="/dashboard.php">Home</a>
                <a class="nav-link active" href="/leave/list.php">My Leave Requests</a>
                <hr class="my-3">
                <a class="nav-link text-danger" href="/logout.php">Logout</a>
            </nav>
        </aside>

        <!-- Main -->
        <main class="flex-grow-1 p-4">
            <div class="container-fluid">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h3 class="mb-0">My Leave Requests</h3>

                    <!-- Button opens modal -->
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createLeaveModal">
                        + Create Leave
                    </button>
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
                                        <th>Type</th>
                                        <th>Start</th>
                                        <th>End</th>
                                        <th>Status</th>
                                        <th>Admin Comment</th>
                                        <th>Created</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($leaves)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted py-4">
                                                No leave requests yet. Click <b>Create Leave</b>.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($leaves as $i => $leave): ?>
                                            <tr>
                                                <td><?= $i + 1 ?></td>
                                                <td><?= htmlspecialchars($leave['leave_type']) ?></td>
                                                <td><?= htmlspecialchars($leave['start_date']) ?></td>
                                                <td><?= htmlspecialchars($leave['end_date']) ?></td>
                                                <td>
                                                    <?php
                                                    $status = $leave['status'];
                                                    $badge = 'secondary';
                                                    if ($status === 'pending')
                                                        $badge = 'warning';
                                                    if ($status === 'approved')
                                                        $badge = 'success';
                                                    if ($status === 'rejected')
                                                        $badge = 'danger';
                                                    ?>
                                                    <span
                                                        class="badge text-bg-<?= $badge ?>"><?= htmlspecialchars($status) ?></span>
                                                </td>
                                                <td class="text-muted"><?= htmlspecialchars($leave['admin_comment'] ?? '') ?>
                                                </td>
                                                <td class="text-muted"><?= htmlspecialchars($leave['created_at']) ?></td>
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

    <!-- Create Leave Modal -->
    <div class="modal fade" id="createLeaveModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form class="modal-content" method="POST" action="/leave/create.php">
                <div class="modal-header">
                    <h5 class="modal-title">Create Leave Request</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div class="mb-3">
                        <label class="form-label">Leave Type</label>
                        <select name="leave_type_id" class="form-select" required>
                            <option value="">Select type...</option>
                            <?php foreach ($leaveTypes as $t): ?>
                                <option value="<?= (int) $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">Start Date</label>
                            <input type="date" name="start_date" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Date</label>
                            <input type="date" name="end_date" class="form-control" required>
                        </div>
                    </div>

                    <div class="mt-3">
                        <label class="form-label">Reason</label>
                        <textarea name="reason" class="form-control" rows="3" placeholder="Optional"></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>