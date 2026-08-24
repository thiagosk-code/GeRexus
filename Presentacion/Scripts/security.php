<?php
$params = session_get_cookie_params();
session_set_cookie_params([
    'lifetime' => 43200,
    'path'     => $params['path'],
    'domain'   => $params['domain'],
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("X-Frame-Options: DENY");
header("X-Content-Type-Options: nosniff");
header("Referrer-Policy: strict-origin-when-cross-origin");
header("X-XSS-Protection: 1; mode=block");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}