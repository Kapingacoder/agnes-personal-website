<?php
require_once __DIR__ . '/../includes/auth.php';
logout_user();
header('Location: /');
exit;
<?php
// Simple logout placeholder
header('Location: ../index.php');
exit;
