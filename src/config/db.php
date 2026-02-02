<?php
$host = "db";           // Docker service name
$dbname = "leave_system";
$user = "employee";     // Matches docker-compose MYSQL_USER
$pass = "password";     // Matches docker-compose MYSQL_PASSWORD

$pdo = new PDO(
    "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
    $user,
    $pass,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]
);
