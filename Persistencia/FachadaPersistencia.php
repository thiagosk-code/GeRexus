<?php

require_once (__DIR__ . '/../DTO/UsuarioDTO.php'); 
require_once (__DIR__ . '/../DTO/HistorialDTO.php');

require_once (__DIR__ . '/../Persistencia/PersistenciaUsuario.php');
require_once (__DIR__ . '/../Persistencia/IPersistenciaUsuario.php');
require_once (__DIR__ . '/../Persistencia/PersistenciaHistorial.php');
require_once (__DIR__ . '/../Persistencia/IPersistenciaHistorial.php');
require_once (__DIR__ . '/../Persistencia/PersistenciaCondicion.php');
require_once (__DIR__ . '/../Persistencia/IPersistenciaCondicion.php');
class FachadaPersistencia {

    // Usuario
    public function retornoIPersistenciaUsuario() : IPersistenciaUsuario{
        return PersistenciaUsuario::getInstancia();
    }

    // Historial
    public function retornoIPersistenciaHistorial() : IPersistenciaHistorial {
        return PersistenciaHistorial::getInstancia();
    }

    //Condicion
        public function retornoIPersistenciaCondicion() : IPersistenciaCondicion {
        return PersistenciaCondicion::getInstancia();
    }
}