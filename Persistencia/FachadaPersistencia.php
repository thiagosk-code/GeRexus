<?php

require_once (__DIR__ . '/../DTO/UsuarioDTO.php'); 
require_once (__DIR__ . '/../DTO/HistorialDTO.php');

require_once (__DIR__ . '/../Persistencia/PersistenciaUsuario.php');
require_once (__DIR__ . '/../Persistencia/IPersistenciaUsuario.php');
require_once (__DIR__ . '/../Persistencia/PersistenciaHistorial.php');
require_once (__DIR__ . '/../Persistencia/IPersistenciaHistorial.php');

class FachadaPersistencia {

    // Usuario
    public function retornoIPersistenciaUsuario() : IPersistenciaUsuario{
        return PersistenciaUsuario::getInstancia();
    }

    // Historial
    public function retornoIPersistenciaHistorial() : IPersistenciaHistorial {
        return PersistenciaHistorial::getInstancia();
    }
   
}