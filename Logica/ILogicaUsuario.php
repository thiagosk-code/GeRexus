<?php
    interface ILogicaUsuario {

        public function altaUsuarioL(UsuarioDTO $usuario): bool;
        public function bajaUsuarioL(UsuarioDTO $usuario): bool;
        public function modificarUsuarioL(UsuarioDTO $usuario): bool;
        public function buscarUsuarioL(UsuarioDTO $usuario): ?UsuarioDTO;

    }
?>