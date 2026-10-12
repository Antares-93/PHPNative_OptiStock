<?php
// helpers/auth.php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah user sudah login
function check_login() {
    if (!isset($_SESSION['user'])) {
        header('Location: /inventory-app/public/login.php');
        exit;
    }
}

// Batasi hak akses berdasarkan role (Otorisasi)
function require_role($allowed_roles = ['admin']) {
    check_login();
    if (!in_array($_SESSION['user']['role'], (array)$allowed_roles)) {
        http_response_code(403);
        echo "<div style='font-family: sans-serif; text-align: center; margin-top: 5rem;'>";
        echo "<h1>403 Forbidden</h1>";
        echo "<p>Anda tidak memiliki izin (role staff) untuk mengakses halaman ini.</p>";
        echo "<a href='/inventory-app/public/dashboard.php'>Kembali ke Dashboard</a>";
        echo "</div>";
        exit;
    }
}

// CSRF Helpers
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], (string)$token);
}