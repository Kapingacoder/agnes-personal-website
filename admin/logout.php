<?php
require_once __DIR__ . '/../includes/auth.php';

// Clear any application-specific persistent cookies (e.g. remember-me)
if (!empty($_COOKIE['remember_token'])) {
	setcookie('remember_token', '', time() - 3600, '/');
}

// Perform session logout and destroy
logout_user();

// Prevent caching of authenticated pages
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');

// Redirect to public home
header('Location: /');
exit;

