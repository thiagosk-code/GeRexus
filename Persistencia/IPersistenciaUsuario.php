<?php

interface IPersistenciaUsuario {

    public function altaUsuario(UsuarioDTO $usuario): int;
    public function bajaUsuario(int $idUsuario): bool;
    public function buscarUsuario(int $idUsuario): ?UsuarioDTO;
    public function modificarUsuario(UsuarioDTO $usuario): bool;
    public function existeEmail(string $email): bool;
}

?>