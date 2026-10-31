<?php

interface ILogicaPartida {
    public function obtenerEstadoPartidaL(int $idUsuario, int $idPartida, int $versionCliente = 0): array;
    public function tirarDadoL(int $idUsuario, int $idPartida): array;
    public function colocarAdeptoL(int $idUsuario, int $idPartida, int $idFicha, string $codigoRecinto): array;
    public function establecerVotoFinalizacionL(int $idUsuario, int $idPartida, bool $voto): array;
}
