<?php

interface IPersistenciaRecinto {

    public function altaRecinto(RecintoDTO $recinto): bool;
    public function bajaRecinto(string $idRecinto): bool;
    public function buscarRecinto(string $idRecinto): RecintoDTO;
    public function modificarRecinto(RecintoDTO $recinto): bool;
}

?>