<?php

require_once (__DIR__ . '/../DTO/UsuarioDTO.php');

interface ILogicaAutorizacion {
    public function tienePermiso(?UsuarioDTO $usuario, string $permiso): bool;
    public function esRolProtegido(int $idRol): bool;
}