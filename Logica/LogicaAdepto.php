<?php

require_once (__DIR__ . '/../DTO/AdeptoDTO.php');
require_once (__DIR__ . '/../Logica/ILogicaAdepto.php');
require_once (__DIR__ . '/../Logica/LogicaAdepto.php');

class LogicaAdepto implements ILogicaAdepto {
    
    private const REGLAS_CORRIENTES = [
        'perro'    => 'naturalista',
        'cangrejo' => 'naturalista',
        'marmota'  => 'existencialista',
        'conejo'   => 'existencialista',
    ];

    public function asignarCorrienteDeAdepto(AdeptoDTO $adepto): void {
        $animal = $adepto->getEspecie();
        $corrienteCalculada = self::REGLAS_CORRIENTES[$animal] ?? 'desconocida';

        $adepto->setCorriente($corrienteCalculada);
    }

    public function asignarShinyAleatorio(AdeptoDTO $adepto): void {
        $probabilidad = rand(1, 1000);
        
        if ($probabilidad <= 11) {
            $adepto->setShiny(true);
        } else {
            $adepto->setShiny(false);
        }
    }

    public function altaAdeptoL(AdeptoDTO $Adepto): bool {
        $res = false;
     
        if ($Adepto !== null) {
            $persistenciaAdepto = new FachadaPersistencia();
            $res = $persistenciaAdepto->retornoIPersistenciaAdepto()->altaAdepto($Adepto);
        }

        return $res;
    }

    public function bajaAdeptoL(AdeptoDTO $Adepto): bool {
        $res = false;
     
        if ($Adepto !== null) {
            $persistenciaAdepto = new FachadaPersistencia();
            $res = $persistenciaAdepto->retornoIPersistenciaAdepto()->bajaAdepto($Adepto->getIdAdepto());
        }

        return $res;
    }

    public function modificarAdeptoL(AdeptoDTO $Adepto): bool {
        $res = false;
     
        if ($Adepto !== null) {
            $persistenciaAdepto = new FachadaPersistencia();
            $res = $persistenciaAdepto->retornoIPersistenciaAdepto()->modificarAdepto($Adepto);
        }

        return $res;
    }

    public function buscarAdeptoL(AdeptoDTO $Adepto): ?AdeptoDTO {
        $res = null;
     
        if ($Adepto !== null) {
            $persistenciaAdepto = new FachadaPersistencia();
            $res = $persistenciaAdepto->retornoIPersistenciaAdepto()->buscarAdepto($Adepto->getIdAdepto());
        }

        return $res;
    }

}