<?php
require_once __DIR__ . '/../Scripts/auth_check.php';
require_once __DIR__ . '/../Scripts/lang.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: digital.php');
    exit();
}

$tokenEnviado = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $tokenEnviado)) {
    die('Solicitud rechazada: Token de seguridad no válido (CSRF).');
}

$accion = $_POST['accion'] ?? '';
$codigo = strtoupper(trim((string)($_POST['codigo'] ?? '')));
$idUsuario = (int)$usuarioActual->getIdUsuario();
$logicaSala = $fachadaLogica->retornoILogicaSala();

$destinoExito = 'sala.php?codigo=' . urlencode($codigo);

switch ($accion) {
    case 'echar_usuario':
        $res = $logicaSala->echarJugadorSalaL($idUsuario, $codigo, (int)($_POST['id_jugador'] ?? 0));
        break;
    case 'cancelar_partida':
        $res = $logicaSala->cancelarSalaL($idUsuario, $codigo);
        $destinoExito = 'digital.php';
        break;
    case 'salir_sala':
        $res = $logicaSala->salirSalaL($idUsuario, $codigo);
        $destinoExito = 'digital.php';
        break;
    case 'comenzar_partida':
        $res = $logicaSala->iniciarSalaL($idUsuario, $codigo);
        if ($res['exito'] === true) {
            $destinoExito = 'partida.php?id=' . (int)$res['idPartida'];
        }
        break;
    default:
        header('Location: digital.php');
        exit();
}

if ($res['exito'] === true) {
    header('Location: ' . $destinoExito);
    exit();
}

if (in_array($res['codigo_error'], ['ERR_SALA_NO_EXISTE', 'ERR_NO_EN_SALA', 'ERR_CODIGO_INVALIDO'], true)) {
    header('Location: digital.php');
    exit();
}

$_SESSION['flash_sala'] = $res['mensaje_key'];
header('Location: sala.php?codigo=' . urlencode($codigo));
exit();
