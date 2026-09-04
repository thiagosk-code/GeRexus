<?php

require_once (__DIR__ . '/../DTO/UsuarioDTO.php'); 
require_once (__DIR__ . '/../Persistencia/PersistenciaUsuario.php');
require_once (__DIR__ . '/../Persistencia/IPersistenciaUsuario.php');
require_once (__DIR__ . '/../DTO/AdeptoDTO.php'); 
require_once (__DIR__ . '/../Persistencia/PersistenciaAdepto.php');
require_once (__DIR__ . '/../Persistencia/IPersistenciaAdepto.php');
require_once (__DIR__ . '/../DTO/RecintoDTO.php'); 
require_once (__DIR__ . '/../Persistencia/PersistenciaRecinto.php');
require_once (__DIR__ . '/../Persistencia/IPersistenciaRecinto.php');
require_once (__DIR__ . '/../DTO/JugadaDTO.php'); 
require_once (__DIR__ . '/../Persistencia/PersistenciaJugada.php');
require_once (__DIR__ . '/../Persistencia/IPersistenciaJugada.php');

class FachadaPersistencia {

    public function retornoIPersistenciaUsuario() : IPersistenciaUsuario{
        return PersistenciaUsuario::getInstancia();
    }
   
    public function retornoIPersistenciaAdepto() : IPersistenciaAdepto{
        return PersistenciaAdepto::getInstancia();
    }

    public function retornoIPersistenciaRecinto() : IPersistenciaRecinto{
        return PersistenciaRecinto::getInstancia();
    }

    public function retornoIPersistenciaJugada() : IPersistenciaJugada{
        return PersistenciaJugada::getInstancia();
    }
    
}