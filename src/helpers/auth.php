<?php
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

function isLoggedIn() {
  return isset($_SESSION['user']);
}

function requireLogin() {
  if (!isLoggedIn()) {
    header("Location: /login.php");
    exit;
  }
}

function requireAdmin() {
  requireLogin();
  if ($_SESSION['user']['role'] !== 'admin') {
    header("Location: /dashboard.php");
    exit;
  }
}
