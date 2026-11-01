<?php

interface ILogicaGacha {
    public function obtenerEstadoGachaL(int $idUsuario): array;
    public function comprarTiradaGachaL(int $idUsuario): array;
}
