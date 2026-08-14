<?php

require_once (__DIR__ . '/ILogicaUsuario.php');
require_once (__DIR__ . '/LogicaUsuario.php');
require_once (__DIR__ . '/ILogicaHistorial.php');
require_once (__DIR__ . '/LogicaHistorial.php');

class FachadaLogica {

    public function retornoILogicaUsuario() : ILogicaUsuario {
        $unILU = new LogicaUsuario();  
        return $unILU;
    }

    public function retornoILogicaHistorial() : ILogicaHistorial {
        $unILH = new LogicaHistorial();  
        return $unILH;
    }

}