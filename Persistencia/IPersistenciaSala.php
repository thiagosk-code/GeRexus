<?php

interface IPersistenciaSala {
    public function obtenerTableros(): array;
    public function obtenerAdeptosDisponibles(int $idUsuario): array;
    public function crearSala(int $idUsuario, string $codigo, int $idTablero, array $idsAdeptos): string;
    public function unirseSala(int $idUsuario, string $codigo): void;
    public function obtenerSalaActivaUsuario(int $idUsuario): ?string;
    public function obtenerEstadoSala(int $idUsuario, string $codigo): ?SalaDTO;
    public function obtenerJugadoresSala(int $idUsuario, string $codigo): array;
    public function obtenerAdeptosSala(int $idUsuario, string $codigo): array;
    public function salirSala(int $idUsuario, string $codigo): void;
    public function echarJugadorSala(int $idAnfitrion, string $codigo, int $idJugador): void;
    public function cancelarSala(int $idAnfitrion, string $codigo): void;
    public function iniciarSala(int $idAnfitrion, string $codigo): int;
}
