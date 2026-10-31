<?php

require_once (__DIR__ . '/ILogicaJugada.php');
require_once (__DIR__ . '/../Persistencia/FachadaPersistencia.php');

class LogicaJugada implements ILogicaJugada {

    public function obtenerJugadasPartidaL(int $idUsuario, int $idPartida): array {
        $res = [];

        if ($idUsuario > 0 && $idPartida > 0) {
            $fachadaPersistencia = new FachadaPersistencia();
            $res = $fachadaPersistencia->retornoIPersistenciaJugada()->obtenerJugadasDePartida($idUsuario, $idPartida);
        }

        return $res;
    }
}
