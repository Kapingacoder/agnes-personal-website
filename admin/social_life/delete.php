<?php
require_once __DIR__ . '/../../includes/auth.php';
require_auth();
require_once __DIR__ . '/../../includes/db.php';
require_once __DIR__ . '/../../includes/helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /admin/social_life/index.php');
    exit;
}

$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id <= 0) {
    header('Location: /admin/social_life/index.php?error=invalid');
    exit;
}

try {
    $stmt = $pdo->prepare('DELETE FROM social_life WHERE id = ?');
    $stmt->execute([$id]);
    header('Location: /admin/social_life/index.php?status=deleted');
} catch (Throwable $e) {
    header('Location: /admin/social_life/index.php?error=failed');
}
exit;
