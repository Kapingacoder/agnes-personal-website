<?php
require_once __DIR__ . '/../../includes/auth.php';
require_auth();
require_once __DIR__ . '/../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/projects/index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: /admin/projects/index.php?status=invalid');
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM projects WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: /admin/projects/index.php?status=deleted');
} catch (Throwable $e) {
    header('Location: /admin/projects/index.php?status=failed');
}
exit;
