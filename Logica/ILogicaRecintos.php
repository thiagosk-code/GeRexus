<?php

interface ILogicaRecintos {
    public function validarReglasAsistente(int $numRecinto, array $nuevoAdepto, array $recintoActual): array;
    public function calcularPuntajeAsistente(int $id, array $adeptosRecinto, array $escuelaCompleta): int;
    public function validarColocacionDigital(string $codigoRecinto, array $nuevoAdepto, array $tablero, ?string $restriccion, bool $ignoraRestriccion): array;
    public function calcularPuntajesDigital(array $tableros): array;
}
