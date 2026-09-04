<?php

interface IPersistenciaJugada {

    public function altaJugada(JugadaDTO $Jugada): bool;
    public function bajaJugada(int $idJugada): bool;
    public function buscarJugada(int $idJugada): ?JugadaDTO;
    public function modificarJugada(JugadaDTO $Jugada): bool;
}

?>