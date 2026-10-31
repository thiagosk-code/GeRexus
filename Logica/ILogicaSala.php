<?php

interface ILogicaSala {
    public function obtenerTablerosL(): array;
    public function obtenerAdeptosDisponiblesL(int $idUsuario): array;
    public function crearSalaL(int $idUsuario, int $idTablero, array $idsAdeptos): array;
    public function unirseSalaL(int $idUsuario, string $codigo): array;
    public function obtenerSalaActivaL(int $idUsuario): ?string;
    public function obtenerEstadoSalaL(int $idUsuario, string $codigo, int $versionCliente = 0): array;
    public function salirSalaL(int $idUsuario, string $codigo): array;
    public function echarJugadorSalaL(int $idAnfitrion, string $codigo, int $idJugador): array;
    public function cancelarSalaL(int $idAnfitrion, string $codigo): array;
    public function iniciarSalaL(int $idAnfitrion, string $codigo): array;
}
