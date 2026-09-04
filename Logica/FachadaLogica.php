<?php

require_once (__DIR__ . '/ILogicaUsuario.php');
require_once (__DIR__ . '/LogicaUsuario.php');
require_once (__DIR__ . '/ILogicaAdepto.php'); 
require_once (__DIR__ . '/LogicaAdepto.php');

class FachadaLogica {

    public function retornoILogicaUsuario() : ILogicaUsuario {
        $unILU = new LogicaUsuario();  
        return $unILU;
    }

    public function retornoILogicaAdepto() : ILogicaAdepto{
        $unILA = new LogicaAdepto();
        return $unILA;
    }
}