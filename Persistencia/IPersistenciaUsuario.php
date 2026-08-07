<?php

interface IPersistenciaUsuario {

    public function altaUsuario(Usuario $usuario): bool;
    public function bajaUsuario(string $idUsuario): bool;
    public function buscarUsuario(string $idUsuario): Usuario;
    public function modificarUsuario(UsuarioDTO $usuario): bool;
}

?>