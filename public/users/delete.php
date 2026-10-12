<?php
// public/users/delete.php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../helpers/auth.php';
require_once __DIR__ . '/../../helpers/validation.php';
require_role('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        die('Token CSRF tidak valid.');
    }

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if ($id) {
        // Jangan biarkan admin menghapus akunnya sendiri
        if ($id === (int)$_SESSION['user']['id']) {
            set_flash('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            set_flash('success', 'Pengguna berhasil dihapus.');
        }
    }
}

header('Location: index.php');
exit;