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

if (!isset($_SESSION['idUsuario']) || $_SESSION['idUsuario'] === null) {
    header('Location: register.php');
    exit();
}

$maxTiempo = 43200;
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time'] > $maxTiempo)) {
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    
    header('Location:register.php?error=expirado');
    exit();
}
?>