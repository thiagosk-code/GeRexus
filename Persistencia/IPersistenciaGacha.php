<?php

interface IPersistenciaGacha {
    public function obtenerEstado(int $idUsuario): array;
    public function desbloquear(int $idUsuario, int $idAdepto, int $costo): array;
}
