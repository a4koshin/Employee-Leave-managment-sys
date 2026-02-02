<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/../src/helpers/auth.php";

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
  <title>Register</title>
</head>
<body>
  <h2>Register</h2>

  <?php if ($error): ?>
    <p style="color:red;"><?= htmlspecialchars($error) ?></p>
  <?php endif; ?>

  <?php if ($success): ?>
    <p style="color:green;"><?= htmlspecialchars($success) ?></p>
  <?php endif; ?>

  <form method="POST">
    <input type="text" name="fullname" placeholder="Full Name" required><br><br>
    <input type="email" name="email" placeholder="Email" required><br><br>
    <input type="password" name="password" placeholder="Password" required><br><br>
    <button type="submit">Register</button>
  </form>

  <p>Already have an account? <a href="/login.php">Login</a></p>
</body>
</html>
