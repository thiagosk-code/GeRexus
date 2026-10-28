<?php
    interface ILogicaRecintos {
        public function validarReglasAsistente(int $numRecinto, array $nuevoFilosofo, array $recintoActual): array;
        public function calcularPuntajeAsistente($id, $adeptosRecinto, $parqueCompleto): int;
    }
?>