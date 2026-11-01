<?php

interface IPersistenciaSala {
    public function obtenerTableros(): array;
    public function obtenerAdeptosDisponibles(int $idUsuario): array;
    public function crearSala(int $idUsuario, string $codigo, int $idTablero, array $idsAdeptos): string;
    public function unirseSala(int $idUsuario, string $codigo, int $versionEsperada, int $maxJugadores): void;
    public function obtenerSalaActivaUsuario(int $idUsuario): ?string;
    public function obtenerEstadoSala(int $idUsuario, string $codigo): ?SalaDTO;
    public function obtenerJugadoresSala(int $idUsuario, string $codigo): array;
    public function obtenerAdeptosSala(int $idUsuario, string $codigo): array;
    public function registrarActividad(int $idUsuario, string $codigo): void;
    public function salirSala(int $idUsuario, string $codigo, int $versionEsperada): void;
    public function echarJugadorSala(int $idAnfitrion, string $codigo, int $idJugador, int $versionEsperada): void;
    public function cancelarSala(int $idAnfitrion, string $codigo, int $versionEsperada): void;
    public function obtenerCandidatosInactivos(int $segundosInactividad, int $segundosVida): array;
    public function cancelarSalaInactiva(int $idSala, int $versionEsperada): void;
    public function iniciarSala(int $idAnfitrion, string $codigo, int $versionEsperada, array $jugadores, array $fichas, int $primerActivo, int $segundosTirada): int;
}
