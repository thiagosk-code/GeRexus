<?php
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_start();
}

require_once __DIR__ . '/../../Logica/FachadaLogica.php';
require_once __DIR__ . '/../../DTO/UsuarioDTO.php';

if (!isset($_SESSION['idUsuario'])) {
    header('Location: login.php');
    exit();
}

$fachadaLogica = new FachadaLogica();
$logicaUsuario = $fachadaLogica->retornoILogicaUsuario();

$usuarioBusqueda = new UsuarioDTO((int)$_SESSION['idUsuario'], '', '', '', 0, 0, false);
$usuarioActual = $logicaUsuario->buscarUsuarioL($usuarioBusqueda);

if ($usuarioActual === null) {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}

$nombreUsuarioLogueado = htmlspecialchars($usuarioActual->getNombre(), ENT_QUOTES, 'UTF-8');
?>