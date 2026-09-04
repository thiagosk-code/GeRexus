<?php
require_once __DIR__ . '/../../Logica/FachadaLogica.php';

$fachadaLogica = new FachadaLogica();
$logicaUsuario = $fachadaLogica->retornoILogicaUsuario();
$logicaUsuario->procesarCierreSesion();

header('Location: login.php');
exit;
?>