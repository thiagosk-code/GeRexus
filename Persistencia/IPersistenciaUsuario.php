<?php
require_once (__DIR__ . '/../DTO/UsuarioDTO.php');

interface IPersistenciaUsuario {
    public function existeEmail(string $email): bool;
    public function altaUsuario(UsuarioDTO $usuarioDTO): int;
    public function modificarUsuario(UsuarioDTO $usuario): bool;
    public function bajaUsuario(int $idUsuario): bool;
    public function buscarUsuario(int $idUsuario): ?UsuarioDTO;
    public function obtenerTodosLosUsuarios(): array;
}
?>