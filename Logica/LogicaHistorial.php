<?php

require_once (__DIR__ . '/../DTO/HistorialDTO.php');
require_once (__DIR__ . '/ILogicaHistorial.php');
require_once (__DIR__ . '/../Persistencia/PersistenciaHistorial.php');
require_once (__DIR__ . '/../Persistencia/FachadaPersistencia.php');

class LogicaHistorial implements ILogicaHistorial {

    public function altaHistorialL(HistorialDTO $historial): bool {
        $fachadaPersistencia = new FachadaPersistencia();
        return $fachadaPersistencia->retornoIPersistenciaHistorial()->altaHistorial($historial);
    }

    public function bajaHistorialL(HistorialDTO $historial): bool {
        $fachadaPersistencia = new FachadaPersistencia();
        return $fachadaPersistencia->retornoIPersistenciaHistorial()->bajaHistorial(
            $historial->getIdUsuario()->getIdUsuario(),
            $historial->getIdPartida()->getIdPartida()
        );
    }

    public function modificarHistorialL(HistorialDTO $historial): bool {
        $fachadaPersistencia = new FachadaPersistencia();
        return $fachadaPersistencia->retornoIPersistenciaHistorial()->modificarHistorial($historial);
    }

    public function buscarHistorialL(HistorialDTO $historial): ?HistorialDTO {
        $fachadaPersistencia = new FachadaPersistencia();
        return $fachadaPersistencia->retornoIPersistenciaHistorial()->buscarHistorial(
            $historial->getIdUsuario()->getIdUsuario(),
            $historial->getIdPartida()->getIdPartida()
        );
    }

    public function obtenerHistorialUsuarioL(int $idUsuario, int $limite = 20, int $desplazamiento = 0): array {
        $res = ['historiales' => [], 'hayMas' => false];

        if ($idUsuario > 0 && $limite > 0 && $limite <= 50 && $desplazamiento >= 0) {
            $fachadaPersistencia = new FachadaPersistencia();
            $lista = $fachadaPersistencia->retornoIPersistenciaHistorial()->obtenerHistorialDeUsuario($idUsuario, $limite + 1, $desplazamiento);

            $res['hayMas'] = count($lista) > $limite;
            $res['historiales'] = array_slice($lista, 0, $limite);
        }

        return $res;
    }
}
