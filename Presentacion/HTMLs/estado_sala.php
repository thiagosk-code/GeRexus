<?php
require_once __DIR__ . '/../Scripts/auth_check.php';
require_once __DIR__ . '/../Scripts/lang.php';
require_once __DIR__ . '/../Scripts/sala_render.php';

session_write_close();

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');

$codigo = (string)($_GET['codigo'] ?? '');
$version = (int)($_GET['version'] ?? 0);
$idUsuario = (int)$usuarioActual->getIdUsuario();

$res = $fachadaLogica->retornoILogicaSala()->obtenerEstadoSalaL($idUsuario, $codigo, $version);

$respuesta = ['accion' => 'ok', 'cambio' => false];

if ($res['exito'] === false || $res['existe'] === false) {
    $respuesta['accion'] = 'salio';
} else {
    $sala = $res['sala'];

    if ($sala->getMiEstado() === 'expulsado') {
        $respuesta['accion'] = 'expulsado';
    } elseif ($sala->getEstado() === 'cancelada') {
        $respuesta['accion'] = 'cancelada';
    } elseif ($sala->getEstado() === 'en_juego' && $sala->getMiEstado() === 'activo') {
        $respuesta['accion'] = 'iniciada';
        $respuesta['idPartida'] = $sala->getIdPartida();
    } elseif ($sala->getMiEstado() !== 'activo' || $sala->getEstado() !== 'espera') {
        $respuesta['accion'] = 'salio';
    } elseif ($res['cambio'] === true) {
        $respuesta['cambio'] = true;
        $respuesta['version'] = $sala->getVersion();
        $respuesta['cantidad'] = count($sala->getJugadores());
        $respuesta['max'] = $sala->getMaxJugadores();
        $respuesta['html'] = renderJugadoresSala($sala->getJugadores(), $sala->getIdAnfitrion(), $res['esAnfitrion'], $txt);
    }
}

echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
