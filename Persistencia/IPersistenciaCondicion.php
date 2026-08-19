<?php

interface IPersistenciaCondicion {

    public function altaCondicion(CondicionDTO $Condicion): bool;
    public function bajaCondicion(int $idCondicion): bool;
    public function buscarCondicion(int $idCondicion): CondicionDTO;
    public function modificarCondicion(CondicionDTO $idCondicion): bool;
}

?>