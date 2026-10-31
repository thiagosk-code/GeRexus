<?php

class FichaPartidaDTO {
    public function __construct(
        private int $idFicha,
        private AdeptoDTO $adepto,
        private string $estado = 'mano'
    ) {}

    public function getIdFicha(): int { return $this->idFicha; }
    public function getAdepto(): AdeptoDTO { return $this->adepto; }
    public function getEstado(): string { return $this->estado; }
}
