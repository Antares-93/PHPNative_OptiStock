<?php
// config/database.php

$host = '127.0.0.1';
$db   = 'inventory_app';
$user = 'root';
$pass = '';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);

    // Otomatis generate akun default jika belum ada user
    $cekUser = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    if ($cekUser == 0) {
        $adminHash = password_hash('admin123', PASSWORD_DEFAULT);
        $staffHash = password_hash('staff123', PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (username, password_hash, role) VALUES (?, ?, ?)");
        $stmt->execute(['admin', $adminHash, 'admin']);
        $stmt->execute(['staff', $staffHash, 'staff']);
    }
} catch (PDOException $e) {
    die("Koneksi database gagal: " . $e->getMessage());
}