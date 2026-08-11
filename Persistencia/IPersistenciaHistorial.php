<?php

interface IPersistenciaHistorial {

    public function altaHistorial(HistorialDTO $historial): bool;
    public function bajaHistorial(int $idUsuario, int $idPartida): bool;
    public function buscarHistorial(int $idUsuario, int $idPartida): ?HistorialDTO;
    public function modificarHistorial(HistorialDTO $historial): bool;
}

?>