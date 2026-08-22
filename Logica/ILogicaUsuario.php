<?php

interface ILogicaUsuario {

    public function altaUsuarioL(UsuarioDTO $usuario, ?string $captchaToken = null): array;
    public function bajaUsuarioL(UsuarioDTO $usuario): bool;
    public function modificarUsuarioL(UsuarioDTO $usuario): bool;
    public function buscarUsuarioL(UsuarioDTO $usuario): ?UsuarioDTO;

}

?>