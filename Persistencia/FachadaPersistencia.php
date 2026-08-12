<?php

require_once (__DIR__ . '/../DTO/UsuarioDTO.php'); 
require_once (__DIR__ . '/../Persistencia/PersistenciaUsuario.php');
require_once (__DIR__ . '/../Persistencia/IPersistenciaUsuario.php');
require_once (_DIR_ . '/../DTO/AdeptoDTO.php'); 
require_once (_DIR_ . '/../Persistencia/PersistenciaAdepto.php');
require_once (_DIR_ . '/../Persistencia/IPersistenciaAdepto.php');
require_once (_DIR_ . '/../DTO/RecintoDTO.php'); 
require_once (_DIR_ . '/../Persistencia/PersistenciaRecinto.php');
require_once (_DIR_ . '/../Persistencia/IPersistenciaRecinto.php');
require_once (_DIR_ . '/../DTO/JugadaDTO.php'); 
require_once (_DIR_ . '/../Persistencia/PersistenciaJugada.php');
require_once (_DIR_ . '/../Persistencia/IPersistenciaJugada.php');

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