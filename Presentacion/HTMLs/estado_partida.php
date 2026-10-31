<?php
require_once __DIR__ . '/../Scripts/auth_check.php';

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['exito' => false, 'mensaje_key' => 'err_partida_metodo']);
    exit;
}

$token = (string)($_POST['csrf_token'] ?? '');
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    http_response_code(403);
    echo json_encode(['exito' => false, 'mensaje_key' => 'err_csrf']);
    exit;
}

$idUsuario = (int)$usuarioActual->getIdUsuario();
$idPartida = filter_var($_POST['id_partida'] ?? null, FILTER_VALIDATE_INT);
$version = max(0, (int)($_POST['version'] ?? 0));

if ($idPartida === false || $idPartida <= 0) {
    http_response_code(400);
    echo json_encode(['exito' => false, 'mensaje_key' => 'err_partida_no_existe']);
    exit;
}

session_write_close();
$resultado = $fachadaLogica->retornoILogicaPartida()->obtenerEstadoPartidaL($idUsuario, $idPartida, $version);
if ($resultado['exito'] === false) {
    http_response_code(in_array($resultado['codigo_error'] ?? '', ['ERR_PARTIDA_NO_AUTORIZADA', 'ERR_PARTIDA_NO_EXISTE'], true) ? 403 : 409);
}

echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
