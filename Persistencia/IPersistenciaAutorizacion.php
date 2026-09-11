<?php

interface IPersistenciaAutorizacion {
    public function obtenerPermisosDeRol(int $idRol): array;
}