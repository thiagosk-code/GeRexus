<?php

class RecintoDTO {
    // Atributos
    private int $idRecinto;
    private string $nombre;
    private string $sector;
    private int $capacidadMaxima;
    private int $ocupacionActual;

    // Constructor
    public function __construct(int $idRecinto, string $nombre, string $sector, int $capacidadMaxima) {
        $this->idRecinto = $idRecinto;
        $this->nombre = $nombre;
        $this->sector = $sector;
        $this->capacidadMaxima = $capacidadMaxima;
        $this->ocupacionActual = 0;
    }

    // idRecinto
    public function setIdRecinto(int $id): void {
        $this->idRecinto = $id;
    }

    public function getIdRecinto(): int {
        return $this->idRecinto;
    }

    // nombre
    public function setNombre(string $nombre): void {
        $this->nombre = $nombre;
    }

    public function getNombre(): string {
        return $this->nombre;
    }

    //sector
    public function setSector(string $sector): void {
        $this->sector = $sector;
    }

    public function getSector(): string {
        return $this->sector;
    }

    // capacidadMaxima
    public function setCapacidadMaxima(int $capacidad): void {
        $this->capacidadMaxima = $capacidad;
    }

    public function getCapacidadMaxima(): int {
        return $this->capacidadMaxima;
    }

    // ocupacionActual
    public function setOcupacionActual(int $cantidad): void {
        $this->ocupacionActual = $cantidad;
    }

    public function getOcupacionActual(): int {
        return $this->ocupacionActual;
    }
}