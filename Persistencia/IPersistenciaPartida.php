<?php

interface IPersistenciaPartida {
    public function obtenerEstado(int $idUsuario, int $idPartida): ?array;
    public function obtenerContextoColocacion(int $idUsuario, int $idPartida, int $idFicha): ?array;
    public function aplicarTirada(int $idUsuario, int $idPartida, int $versionEsperada, int $turnoEsperado, string $restriccion, string $limiteColocacion): void;
    public function colocar(int $idUsuario, int $idPartida, int $versionEsperada, int $turnoEsperado, int $idFicha, string $codigoRecinto): void;
    public function colocarAutomaticamente(int $idUsuario, int $idPartida, int $versionEsperada, int $turnoEsperado, int $idFicha): void;
    public function establecerVotoFinalizacion(int $idUsuario, int $idPartida, int $versionEsperada, bool $voto): array;
    public function descartarTurno(int $idPartida, int $versionEsperada, int $turnoEsperado): void;
    public function cerrarTurnoYPasarManos(int $idPartida, int $versionEsperada, int $turnoEsperado, array $planManos, array $nuevoTurno): void;
    public function cerrarTurnoEIniciarRonda(int $idPartida, int $versionEsperada, int $turnoEsperado, array $planReparto, array $nuevoTurno): void;
    public function cerrarUltimoTurno(int $idPartida, int $versionEsperada, int $turnoEsperado): void;
    public function finalizar(int $idPartida, int $versionEsperada, array $clasificacion): void;
}
