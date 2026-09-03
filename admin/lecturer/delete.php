<?php
require_once __DIR__ . '/../../includes/auth.php';
require_auth();
require_once __DIR__ . '/../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/lecturer/index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: /admin/lecturer/index.php?error=invalid');
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM lecturers WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: /admin/lecturer/index.php?status=deleted');
    exit;
} catch (Throwable $e) {
    header('Location: /admin/lecturer/index.php?error=failed');
    exit;
}
