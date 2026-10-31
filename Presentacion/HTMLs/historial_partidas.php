<?php
require_once __DIR__ . '/../Scripts/auth_check.php';
require_once __DIR__ . '/../Scripts/lang.php';
require_once __DIR__ . '/../Scripts/historial_render.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');

$desplazamiento = max(0, (int)($_GET['offset'] ?? 0));

$logicaHistorial = $fachadaLogica->retornoILogicaHistorial();
$res = $logicaHistorial->obtenerHistorialUsuarioL((int)$usuarioActual->getIdUsuario(), 20, $desplazamiento);

$html = '';
foreach ($res['historiales'] as $historial) {
    $html .= renderPartidaCard($historial, $txt);
}

echo json_encode([
    'html' => $html,
    'hayMas' => $res['hayMas'],
    'siguiente' => $desplazamiento + count($res['historiales'])
], JSON_UNESCAPED_UNICODE);
