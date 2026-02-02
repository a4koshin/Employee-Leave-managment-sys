<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../helpers/auth.php";

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $fullname = trim($_POST['fullname']);
  $email = trim($_POST['email']);
  $password = $_POST['password'];

  if (!$fullname || !$email || !$password) {
    $error = "All fields are required.";
  } else {
    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);

    if ($stmt->fetch()) {
      $error = "Email already registered.";
    } else {
      $hash = password_hash($password, PASSWORD_DEFAULT);

      $stmt = $pdo->prepare(
        "INSERT INTO users (fullname, email, password, role)
         VALUES (?, ?, ?, 'employee')"
      );
      $stmt->execute([$fullname, $email, $hash]);

      $success = "Registration successful. You can now login.";
    }
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

  <title>Register</title>
</head>

<body class="bg-light">

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-5 col-lg-4">

      <div class="card shadow">
        <div class="card-body p-4">

          <h3 class="text-center mb-4">Create Account</h3>

          <?php if ($error): ?>
            <div class="alert alert-danger text-center">
              <?= htmlspecialchars($error) ?>
            </div>
          <?php endif; ?>

          <?php if ($success): ?>
            <div class="alert alert-success text-center">
              <?= htmlspecialchars($success) ?>
            </div>
          <?php endif; ?>

          <form method="POST">

            <div class="mb-3">
              <label class="form-label">Full Name</label>
              <input type="text" name="fullname" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Email</label>
              <input type="email" name="email" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" required>
            </div>

            <button class="btn btn-primary w-100">Register</button>
          </form>

          <p class="text-center mt-3">
            Already have an account?
            <a href="/login.php">Login</a>
          </p>

        </div>
      </div>

    </div>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
