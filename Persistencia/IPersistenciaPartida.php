<?php

interface IPersistenciaPartida {
    public function procesarVencimientos(int $idUsuario, int $idPartida): void;
    public function obtenerEstado(int $idUsuario, int $idPartida): ?array;
    public function obtenerContextoColocacion(int $idUsuario, int $idPartida, int $idFicha): ?array;
    public function tirarDado(int $idUsuario, int $idPartida): void;
    public function colocar(int $idUsuario, int $idPartida, int $idFicha, string $codigoRecinto): void;
    public function establecerVotoFinalizacion(int $idUsuario, int $idPartida, bool $voto): array;
    public function finalizar(int $idPartida, array $clasificacion): void;
}
