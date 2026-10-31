<?php
require_once __DIR__ . '/../Scripts/auth_check.php';
require_once __DIR__ . '/../Scripts/lang.php';
require_once __DIR__ . '/../Scripts/historial_render.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');

$idPartida = (int)($_GET['id'] ?? 0);

$logicaJugada = $fachadaLogica->retornoILogicaJugada();
$jugadas = $logicaJugada->obtenerJugadasPartidaL((int)$usuarioActual->getIdUsuario(), $idPartida);

$html = '';
foreach ($jugadas as $jugada) {
    $html .= renderJugadaLinea($jugada, $txt);
}

echo json_encode([
    'html' => $html,
    'vacio' => count($jugadas) === 0
], JSON_UNESCAPED_UNICODE);
