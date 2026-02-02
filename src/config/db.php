<?php
$host = "db";           // Docker service name
$dbname = "leave_system";
$user = "employee";     // Matches docker-compose MYSQL_USER
$pass = "password";     // Matches docker-compose MYSQL_PASSWORD

$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
];

$attempts = 0;
$maxAttempts = 8;
$pdo = null;

while ($attempts < $maxAttempts) {
    try {
        $pdo = new PDO($dsn, $user, $pass, $options);
        break;
    } catch (PDOException $e) {
        $attempts++;
        if ($attempts >= $maxAttempts) {
            throw $e;
        }
        usleep(250000);
    }
}
