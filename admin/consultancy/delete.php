<?php
require_once __DIR__ . '/../../includes/auth.php';
require_auth();
require_once __DIR__ . '/../../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/consultancy/index.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
if ($id <= 0) {
    header('Location: /admin/consultancy/index.php?error=invalid');
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM consultancy_videos WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: /admin/consultancy/index.php?status=deleted');
    exit;
} catch (Throwable $e) {
    header('Location: /admin/consultancy/index.php?error=failed');
    exit;
}
