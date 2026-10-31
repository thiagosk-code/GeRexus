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
$accion = (string)($_POST['accion'] ?? '');

if ($idPartida === false || $idPartida <= 0) {
    http_response_code(400);
    echo json_encode(['exito' => false, 'mensaje_key' => 'err_partida_no_existe']);
    exit;
}

session_write_close();
$logica = $fachadaLogica->retornoILogicaPartida();
if ($accion === 'tirar_dado') {
    $resultado = $logica->tirarDadoL($idUsuario, $idPartida);
} elseif ($accion === 'colocar') {
    $idFicha = filter_var($_POST['id_ficha'] ?? null, FILTER_VALIDATE_INT);
    $recinto = (string)($_POST['recinto'] ?? '');
    $resultado = ($idFicha !== false && $idFicha > 0)
        ? $logica->colocarAdeptoL($idUsuario, $idPartida, $idFicha, $recinto)
        : ['exito' => false, 'codigo_error' => 'ERR_PARTIDA_FICHA', 'mensaje_key' => 'err_partida_ficha'];
} elseif ($accion === 'establecer_voto_finalizacion') {
    $voto = (string)($_POST['voto'] ?? '');
    $resultado = in_array($voto, ['0', '1'], true)
        ? $logica->establecerVotoFinalizacionL($idUsuario, $idPartida, $voto === '1')
        : ['exito' => false, 'codigo_error' => 'ERR_PARTIDA_VOTO', 'mensaje_key' => 'err_partida_voto'];
} else {
    $resultado = ['exito' => false, 'codigo_error' => 'ERR_PARTIDA_ACCION', 'mensaje_key' => 'err_partida_accion'];
}

if ($resultado['exito'] === false) {
    http_response_code(409);
}
echo json_encode($resultado, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT);
