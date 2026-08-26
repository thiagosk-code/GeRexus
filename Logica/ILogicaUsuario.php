<?php

require_once (__DIR__ . '/../DTO/UsuarioDTO.php');

interface ILogicaUsuario {
    public function altaUsuarioL(UsuarioDTO $usuario, ?string $captchaToken = null, int $idAdminEjecutor = 0): array;
    public function bajaUsuarioL(int $idElim, int $idAdminEjecutor = 0): array;
    public function modificarUsuarioL(int $idMod, string $nomMod, string $emailMod, string $contraMod, ?int $dracmasMod, int $idAdminEjecutor = 0): array;
    public function buscarUsuarioL(UsuarioDTO $usuario): ?UsuarioDTO;
}
?>