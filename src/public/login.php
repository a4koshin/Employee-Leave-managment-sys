<?php
require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . '/../helpers/auth.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = [
            'id' => $user['id'],
            'fullname' => $user['fullname'],
            'email' => $user['email'],
            'role' => $user['role']
        ];

        if ($user['role'] === 'admin') {
            header("Location: /admin/requests.php");
        } else {
            header("Location: /leave/list.php");
        }
        exit;
    } else {
        $error = "Invalid email or password.";
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

    <title>Login</title>
</head>

<body class="bg-light">

<div class="container mt-5">
    <div class="row justify-content-center items-center">
        <div class="col-md-5 col-lg-4">

            <div class="card shadow">
                <div class="card-body p-4">

                    <h3 class="text-center mb-4">Employee Login</h3>

                    <?php if ($error): ?>
                        <div class="alert alert-danger text-center">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST">

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>

                        <button class="btn btn-primary w-100">Login</button>
                    </form>

                    <p class="text-center mt-3">
                        No account?
                        <a href="/register.php">Register</a>
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
