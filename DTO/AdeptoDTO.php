<?php

class AdeptoDTO {
    // Atributos
    private int $idAdepto;
    private string $especie;
    private bool $esShiny;
    private string $corriente;
    private string $descripcion;

    // Constructor
    public function __construct(int $idAdepto, string $especie, bool $esShiny = false, string $corriente = '', string $descripcion = '') {
        $this->idAdepto = $idAdepto;
        $this->especie = $especie;
        $this->esShiny = $esShiny;
        $this->corriente = $corriente;
        $this->descripcion = $descripcion;
    }

    // idAdepto
    public function setIdAdepto(int $idAdepto): void {
        $this->idAdepto = $idAdepto;
    }

    public function getIdAdepto(): int {
        return $this->idAdepto;
    }

    // especie
    public function setEspecie(string $especie): void {
        $this->especie = $especie;
    }

    public function getEspecie(): string {
        return $this->especie;
    }

    // esShiny
    public function setEsShiny(bool $esShiny): void {
        $this->esShiny = $esShiny;
    }

    public function getEsShiny(): bool {
        return $this->esShiny;
    }

    // corriente 
    public function setCorriente(string $corriente): void {
        $this->corriente = $corriente;
    }

    public function getCorriente(): string {
        return $this->corriente;
    }

    // descripcion
    public function setDescripcion(string $descripcion): void {
        $this->descripcion = $descripcion;
    }

    public function getDescripcion(): string {
        return $this->descripcion;
    }
}