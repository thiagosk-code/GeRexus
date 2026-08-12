<?php

interface IPersistenciaAdepto {

    public function altaAdepto(AdeptoDTO $adepto): bool;
    public function bajaAdepto(string $idAdepto): bool;
    public function buscarAdepto(string $idAdepto): AdeptoDTO;
    public function modificarAdepto(AdeptoDTO $adepto): bool;
}

?>