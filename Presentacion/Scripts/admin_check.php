<?php
require_once __DIR__ . '/auth_check.php';

$logicaAutorizacion = $fachadaLogica->retornoILogicaAutorizacion();

if ($logicaAutorizacion->tienePermiso($usuarioActual, 'ver_panel_admin') === false) {
    header('Location: base.php');
    exit();
}

$idAdminLogueado = (int)$usuarioActual->getIdUsuario();
?>