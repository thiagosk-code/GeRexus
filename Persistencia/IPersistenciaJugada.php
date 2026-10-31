<?php

interface IPersistenciaJugada {
    public function obtenerJugadasDePartida(int $idUsuario, int $idPartida): array;
}
?>
