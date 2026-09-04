<?php

interface ILogicaUsuario {
    public function altaUsuarioL(UsuarioDTO $usuario, ?string $captchaToken = null, int $idAdminEjecutor = 0): array;
    public function bajaUsuarioL(int $idElim, int $idAdminEjecutor = 0): array;
    public function modificarUsuarioL(int $idMod, string $nomMod, string $emailMod, string $contraMod, ?int $dracmasMod, int $idAdminEjecutor = 0): array;
    public function buscarUsuarioL(UsuarioDTO $usuario): ?UsuarioDTO;
    public function obtenerTodosLosUsuariosL(): array;
    public function procesarFormularioAdmin(array $postData, int $idAdminEjecutor): array;
    public function procesarModificacionNombrePropio(int $idUsuario, string $nuevoNombre): array;
    public function procesarBajaCuentaPropia(int $idUsuario): array;
    public function procesarCierreSesion(): void;
    public function buscarPartidasGanadasL(UsuarioDTO $usuario): int;
    public function IniciarSesionL(string $email, string $contra, string $captchaToken): array;   
}

?>