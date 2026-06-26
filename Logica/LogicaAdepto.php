<?php

require_once 'AdeptoDTO.php';

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
}