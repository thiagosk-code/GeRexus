<?php

require_once (__DIR__ . '/../DTO/UsuarioDTO.php'); 
require_once (__DIR__ . '/../CapaPersistencia/PersistenciaUsuario.php');
require_once (__DIR__ . '/../CapaPersistencia/IPersistenciaUsuario.php');

class FachadaPersistencia {

    public function retornoIPersistenciaUsuario() : IPersistenciaUsuario{
        return PersistenciaUsuario::getInstancia();
    }
   
}