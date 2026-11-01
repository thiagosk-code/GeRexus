<?php

require_once (__DIR__ . '/ILogicaUsuario.php');
require_once (__DIR__ . '/LogicaUsuario.php');
require_once (__DIR__ . '/ILogicaHistorial.php');
require_once (__DIR__ . '/LogicaHistorial.php');
require_once (__DIR__ . '/ILogicaRecintos.php');
require_once (__DIR__ . '/LogicaRecintos.php');
require_once (__DIR__ . '/ILogicaJugada.php');
require_once (__DIR__ . '/LogicaJugada.php');
require_once (__DIR__ . '/ILogicaSala.php');
require_once (__DIR__ . '/LogicaSala.php');
require_once (__DIR__ . '/ILogicaAutorizacion.php');
require_once (__DIR__ . '/LogicaAutorizacion.php');
require_once (__DIR__ . '/ILogicaPartida.php');
require_once (__DIR__ . '/LogicaPartida.php');
require_once (__DIR__ . '/ILogicaGacha.php');
require_once (__DIR__ . '/LogicaGacha.php');

class FachadaLogica {

    public function retornoILogicaUsuario() : ILogicaUsuario {
        $unILU = new LogicaUsuario();  
        return $unILU;
    }

    public function retornoILogicaHistorial() : ILogicaHistorial {
        $unILH = new LogicaHistorial();  
        return $unILH;
    }

    public function retornoILogicaRecinto() : ILogicaRecintos {
        return new LogicaRecintos();
    }

    public function retornoILogicaJugada() : ILogicaJugada {
        $unILJ = new LogicaJugada();
        return $unILJ;
    }

    public function retornoILogicaSala() : ILogicaSala {
        $unILS = new LogicaSala();
        return $unILS;
    }

    public function retornoILogicaAutorizacion() : ILogicaAutorizacion {
        $unILA = new LogicaAutorizacion();
        return $unILA;
    }

    public function retornoILogicaPartida() : ILogicaPartida {
        return new LogicaPartida();
    }

    public function retornoILogicaGacha() : ILogicaGacha {
        return new LogicaGacha();
    }

}
