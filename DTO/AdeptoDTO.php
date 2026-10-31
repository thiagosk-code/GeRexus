<?php

class AdeptoDTO {
    // Atributos
    private int $idAdepto;
    private string $especie;
    private bool $esShiny;
    private string $corriente;
    private string $descripcion;
    private string $ruta = '';
    private bool $esSocratico = false;

    // Constructor
    public function __construct(int $idAdepto, string $especie, bool $esShiny = false, string $corriente = '', string $descripcion = '', string $ruta = '', bool $esSocratico = false) {
        $this->ruta = $ruta;
        $this->idAdepto = $idAdepto;
        $this->especie = $especie;
        $this->esShiny = $esShiny;
        $this->corriente = $corriente;
        $this->descripcion = $descripcion;
        $this->esSocratico = $esSocratico;
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

    public function getRuta(): string {
        return $this->ruta;
    }

    public function setRuta(string $ruta): void {
        $this->ruta = $ruta;
    }

    public function getEsSocratico(): bool {
        return $this->esSocratico;
    }

    public function setEsSocratico(bool $esSocratico): void {
        $this->esSocratico = $esSocratico;
    }
}
