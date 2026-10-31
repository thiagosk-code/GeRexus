<?php

interface ILogicaJugada {
    public function obtenerJugadasPartidaL(int $idUsuario, int $idPartida): array;
}
