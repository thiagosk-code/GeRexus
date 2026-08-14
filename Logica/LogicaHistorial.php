<?php

require_once (__DIR__ . '/../DTO/HistorialDTO.php');
require_once (__DIR__ . '/ILogicaHistorial.php');
require_once (__DIR__ . '/../Persistencia/PersistenciaHistorial.php');
require_once (__DIR__ . '/../Persistencia/FachadaPersistencia.php');

class LogicaHistorial implements ILogicaHistorial {

    public function altaHistorialL(HistorialDTO $historial): bool {
        $res = false;
     
        if ($historial !== null) {
            $fachadaPersistencia = new FachadaPersistencia();
            $res = $fachadaPersistencia->retornoIPersistenciaHistorial()->altaHistorial($historial);
        }

        return $res;
    }

    public function bajaHistorialL(HistorialDTO $historial): bool {
        $res = false;
     
        if ($historial !== null) {
            $fachadaPersistencia = new FachadaPersistencia();
            
            $res = $fachadaPersistencia->retornoIPersistenciaHistorial()->bajaHistorial(
                $historial->getIdUsuario(), 
                $historial->getIdPartida()
            );
        }

        return $res;
    }

    public function modificarHistorialL(HistorialDTO $historial): bool {
        $res = false;
     
        if ($historial !== null) {
            $fachadaPersistencia = new FachadaPersistencia();
            $res = $fachadaPersistencia->retornoIPersistenciaHistorial()->modificarHistorial($historial);
        }

        return $res;
    }

    public function buscarHistorialL(HistorialDTO $historial): ?HistorialDTO {
        $res = null;
     
        if ($historial !== null) {
            $fachadaPersistencia = new FachadaPersistencia();
            $res = $fachadaPersistencia->retornoIPersistenciaHistorial()->buscarHistorial(
                $historial->getIdUsuario(), 
                $historial->getIdPartida()
            );
        }

        return $res;
    }

}