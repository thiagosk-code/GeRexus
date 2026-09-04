<?php

interface IPersistenciaUsuario {

    public function altaUsuario(UsuarioDTO $usuario): bool;
    public function bajaUsuario(int $idUsuario): bool;
    public function buscarUsuario(int $idUsuario): ?UsuarioDTO;
    public function modificarUsuario(UsuarioDTO $usuario): bool;
    public function existeEmail(string $email): bool;
    public function obtenerTodosLosUsuarios(): array;
    public function buscarPartidasGanadas(int $usuario): int;
    public function buscarEmail(string $email): ?UsuarioDTO;
}

?>