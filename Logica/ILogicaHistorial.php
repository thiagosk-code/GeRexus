<?php

interface ILogicaHistorial {
    public function altaHistorialL(HistorialDTO $historial): bool;
    public function bajaHistorialL(HistorialDTO $historial): bool;
    public function modificarHistorialL(HistorialDTO $historial): bool;
    public function buscarHistorialL(HistorialDTO $historial): ?HistorialDTO;
    public function obtenerHistorialUsuarioL(int $idUsuario, int $limite = 20, int $desplazamiento = 0): array;
}
