<?php

class TableroDTO {

    private int $idTablero;
    private string $nombre;
    private string $imagen;

    public function __construct(
        int $idTablero = 0,
        string $nombre = "",
        string $imagen = ""
    ) {
        $this->idTablero = $idTablero;
        $this->nombre = $nombre;
        $this->imagen = $imagen;
    }

    public function getIdTablero(): int {
        return $this->idTablero;
    }
    public function setIdTablero(int $idTablero): void {
        $this->idTablero = $idTablero;
    }

    public function getNombre(): string {
        return $this->nombre;
    }
    public function setNombre(string $nombre): void {
        $this->nombre = $nombre;
    }

    public function getImagen(): string {
        return $this->imagen;
    }
    public function setImagen(string $imagen): void {
        $this->imagen = $imagen;
    }
}
