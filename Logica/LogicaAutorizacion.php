<?php

require_once (__DIR__ . '/../DTO/UsuarioDTO.php');
require_once (__DIR__ . '/ILogicaAutorizacion.php');
require_once (__DIR__ . '/../Persistencia/FachadaPersistencia.php');

class LogicaAutorizacion implements ILogicaAutorizacion {

    private const ROL_ADMIN_ID = 2;

    public function tienePermiso(?UsuarioDTO $usuario, string $permiso): bool {
        if ($usuario === null) {
            return false;
        }

        $fachadaPersistencia = new FachadaPersistencia();
        $persistencia = $fachadaPersistencia->retornoIPersistenciaAutorizacion();
        $permisos = $persistencia->obtenerPermisosDeRol($usuario->getIdRol());

        return in_array($permiso, $permisos, true);
    }

    public function esRolProtegido(int $idRol): bool {
        return $idRol === self::ROL_ADMIN_ID;
    }
}