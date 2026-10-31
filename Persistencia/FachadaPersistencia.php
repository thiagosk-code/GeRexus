<?php

require_once (__DIR__ . '/../DTO/UsuarioDTO.php'); 
require_once (__DIR__ . '/../DTO/HistorialDTO.php');

require_once (__DIR__ . '/../Persistencia/PersistenciaUsuario.php');
require_once (__DIR__ . '/../Persistencia/IPersistenciaUsuario.php');
require_once (__DIR__ . '/../Persistencia/PersistenciaHistorial.php');
require_once (__DIR__ . '/../Persistencia/IPersistenciaHistorial.php');
require_once (__DIR__ . '/../Persistencia/PersistenciaJugada.php');
require_once (__DIR__ . '/../Persistencia/IPersistenciaJugada.php');
require_once (__DIR__ . '/../Persistencia/PersistenciaSala.php');
require_once (__DIR__ . '/../Persistencia/IPersistenciaSala.php');
require_once (__DIR__ . '/../Persistencia/PersistenciaAutorizacion.php');
require_once (__DIR__ . '/../Persistencia/IPersistenciaAutorizacion.php');
require_once (__DIR__ . '/../Persistencia/PersistenciaPartida.php');
require_once (__DIR__ . '/../Persistencia/IPersistenciaPartida.php');

class FachadaPersistencia {

    public function retornoIPersistenciaUsuario() : IPersistenciaUsuario{
        return PersistenciaUsuario::getInstancia();
    }

    public function retornoIPersistenciaHistorial() : IPersistenciaHistorial {
        return PersistenciaHistorial::getInstancia();
    }

    public function retornoIPersistenciaJugada() : IPersistenciaJugada {
        return PersistenciaJugada::getInstancia();
    }

    public function retornoIPersistenciaSala() : IPersistenciaSala {
        return PersistenciaSala::getInstancia();
    }

    public function retornoIPersistenciaAutorizacion() : IPersistenciaAutorizacion {
        return PersistenciaAutorizacion::getInstancia();
    }

    public function retornoIPersistenciaPartida() : IPersistenciaPartida {
        return PersistenciaPartida::getInstancia();
    }
   
}
