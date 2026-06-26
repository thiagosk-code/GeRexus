<?php

class AdeptoDTO {
    // Atributos
    private int $idAdepto;
    private string $especie;
    private bool $shiny;
    private string $corriente;
    private string $descripcion;

    // Constructor
    public function __construct(int $idAdepto, string $especie, bool $shiny = false, string $corriente = '', string $descripcion = '') {
        $this->idAdepto = $idAdepto;
        $this->especie = $especie;
        $this->shiny = $shiny;
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

    // shiny
    public function setShiny(bool $shiny): void {
        $this->shiny = $shiny;
    }

    public function getShiny(): bool {
        return $this->shiny;
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