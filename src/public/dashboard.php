<?php
require_once __DIR__ . '/../helpers/auth.php';
requireLogin();

$user = $_SESSION['user'];
$isAdmin = ($user['role'] === 'admin');
$isEmployee = ($user['role'] === 'employee');
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <title>Dashboard</title>

  <style>
    .sidebar {
      width: 260px;
      min-height: 100vh;
      position: sticky;
      top: 0;
    }
    .content {
      min-height: 100vh;
    }
    .nav-pills .nav-link.active {
      background-color: #0d6efd;
    }
  </style>
</head>

<body class="bg-light">

<div class="d-flex">

  <!-- Sidebar -->
  <aside class="sidebar bg-white border-end p-3">
    <div class="mb-4">
      <div class="fw-bold fs-5">Employee Leave System</div>
      <div class="text-muted small">Dashboard</div>
    </div>

    <div class="mb-3 p-3 bg-light rounded">
      <div class="fw-semibold"><?= htmlspecialchars($user['fullname']) ?></div>
      <div class="text-muted small"><?= htmlspecialchars($user['email']) ?></div>
      <span class="badge text-bg-primary mt-2"><?= htmlspecialchars($user['role']) ?></span>
    </div>

    <nav class="nav flex-column nav-pills gap-1">
      <a class="nav-link active" href="/dashboard.php">Home</a>

      <?php if ($isEmployee): ?>
        <a class="nav-link" href="/leave/list.php">My Leave Requests</a>
      <?php endif; ?>

      <?php if ($isAdmin): ?>
        <a class="nav-link" href="/admin/requests.php">Manage Requests</a>
      <?php endif; ?>

      <hr class="my-3">

      <a class="nav-link text-danger" href="/logout.php">Logout</a>
    </nav>
  </aside>

  <!-- Main Content -->
  <main class="content flex-grow-1 p-4">
    <div class="container-fluid">

      <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0">Welcome, <?= htmlspecialchars($user['fullname']) ?> 👋</h3>
      </div>

      <div class="row g-3">
        <?php if ($isEmployee): ?>
          <div class="col-md-6">
            <div class="card shadow-sm h-100">
              <div class="card-body">
                <h5 class="card-title">Request Leave</h5>
                <p class="text-muted mb-3">Create a new leave request from the “My Leave Requests” page.</p>
                <a href="/leave/list.php" class="btn btn-primary">Go to My Leaves</a>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card shadow-sm h-100">
              <div class="card-body">
                <h5 class="card-title">My Leave Requests</h5>
                <p class="text-muted mb-3">View your requests and their status.</p>
                <a href="/leave/list.php" class="btn btn-outline-primary">View Requests</a>
              </div>
            </div>
          </div>
        <?php endif; ?>

        <?php if ($isAdmin): ?>
          <div class="col-12">
            <div class="card shadow-sm border-start border-5 border-warning">
              <div class="card-body">
                <h5 class="card-title">Admin Panel</h5>
                <p class="text-muted mb-3">Approve or reject employee requests.</p>
                <a href="/admin/requests.php" class="btn btn-warning">Open Admin Panel</a>
              </div>
            </div>
          </div>
        <?php endif; ?>
      </div>

    </div>
  </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
