<?php

require_once (__DIR__ . '/../DTO/UsuarioDTO.php');
require_once (__DIR__ . '/ILogicaUsuario.php');
require_once (__DIR__ . '/../Persistencia/PersistenciaUsuario.php');
require_once (__DIR__ . '/../Persistencia/FachadaPersistencia.php');

class LogicaUsuario implements ILogicaUsuario {

    public function altaUsuarioL(UsuarioDTO $usuario): bool {
        $res = false;
     
        if ($usuario !== null) {
            $persistenciaUsuario = new FachadaPersistencia();
            $res = $persistenciaUsuario->retornoIPersistenciaUsuario()->altaUsuario($usuario);
        }

        return $res;
    }

    public function bajaUsuarioL(UsuarioDTO $usuario): bool {
        $res = false;
     
        if ($usuario !== null) {
            $persistenciaUsuario = new FachadaPersistencia();
            $res = $persistenciaUsuario->retornoIPersistenciaUsuario()->bajaUsuario($usuario->getIdUsuario());
        }

        return $res;
    }

    public function modificarUsuarioL(UsuarioDTO $usuario): bool {
        $res = false;
     
        if ($usuario !== null) {
            $persistenciaUsuario = new FachadaPersistencia();
            $res = $persistenciaUsuario->retornoIPersistenciaUsuario()->modificarUsuario($usuario);
        }

        return $res;
    }

    public function buscarUsuarioL(UsuarioDTO $usuario): ?UsuarioDTO {
        $res = null;
     
        if ($usuario !== null) {
            $persistenciaUsuario = new FachadaPersistencia();
            $res = $persistenciaUsuario->retornoIPersistenciaUsuario()->buscarUsuario($usuario->getIdUsuario());
        }

        return $res;
    }

}