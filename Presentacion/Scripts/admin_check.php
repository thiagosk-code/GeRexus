<?php
require_once __DIR__ . '/auth_check.php';

if ($usuarioActual->getEsAdmin() === false) {
    header('Location: base.php');
    exit();
}

$idAdminLogueado = (int)$usuarioActual->getIdUsuario();
?>