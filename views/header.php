<?php
// views/header.php
require_once __DIR__ . '/../helpers/escape.php';
require_once __DIR__ . '/../helpers/validation.php';
require_once __DIR__ . '/../helpers/auth.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OptiStock — Inventory Management System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="/inventory_app/public/assets/style.css">
</head>
<body>
<header class="navbar">
    <div class="nav-wrapper">
        <a href="/inventory_app/public/index.php" class="brand">
            <span class="brand-badge"></span>
            <span class="brand-text">Opti<strong>Stock</strong></span>
        </a>

        <nav class="nav-actions">
            <?php if (isset($_SESSION['user'])): ?>
                <!-- Menu Khusus Petugas (Sudah Login) -->
                <a href="/inventory_app/public/dashboard.php">Dashboard</a>
                <a href="/inventory_app/public/products/index.php">Produk</a>
                <a href="/inventory_app/public/stock/in.php">+ Masuk</a>
                <a href="/inventory_app/public/stock/out.php">- Keluar</a>
                <a href="/inventory_app/public/stock/history.php">Histori</a>

                <?php if ($_SESSION['user']['role'] === 'admin'): ?>
                    <span class="nav-sep"></span>
                    <a href="/inventory_app/public/categories/index.php">Kategori</a>
                    <a href="/inventory_app/public/suppliers/index.php">Supplier</a>
                    <a href="/inventory_app/public/users/index.php">Kelola User</a>
                <?php endif; ?>

                <div class="user-pill">
                    <span><?= e($_SESSION['user']['username']) ?></span>
                    <span class="role-tag <?= $_SESSION['user']['role'] ?>"><?= strtoupper(e($_SESSION['user']['role'])) ?></span>
                </div>
                <a href="/inventory_app/public/logout.php" class="btn-logout">Logout</a>
            <?php else: ?>
                <!-- Menu Guest (Publik) -->
                <a href="/inventory_app/public/index.php" class="active">Katalog Publik</a>
                <a href="/inventory_app/public/login.php" class="btn btn-primary" style="padding: 0.5rem 1.1rem;">
                    Login Petugas →
                </a>
            <?php endif; ?>
        </nav>
    </div>
</header>

<main class="page-body">
    <div class="container">
        <?php if ($msg = get_flash('success')): ?>
            <div class="alert alert-success">✓ <?= e($msg) ?></div>
        <?php endif; ?>
        <?php if ($msg = get_flash('error')): ?>
            <div class="alert alert-error">⚠ <?= e($msg) ?></div>
        <?php endif; ?>