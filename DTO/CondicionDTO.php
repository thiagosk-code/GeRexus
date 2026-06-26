<?php

class CondicionDTO {
    private string $idCondicion;
    private string $puntos;
    private string $nombre;
    private string $descripcion;

    public function __construct(string $idCondicion, string $puntos, string $nombre, string $descripcion) {
        $this->idCondicion = $idCondicion;
        $this->puntos = $puntos;
        $this->nombre = $nombre;
        $this->descripcion = $descripcion;
    }

    //idCondicion
    public function setIdCondicion(string $idCondicion): void {
        $this->idCondicion = $idCondicion;
    }

    public function getIdCondicion(): string {
        return $this->idCondicion;
    }

    //puntos
    public function setPuntos(string $puntos): void {
        $this->puntos = $puntos;
    }

    public function getPuntos(): string {
        return $this->puntos;
    }

    //nombre
    public function setNombre(string $nombre): void {
        $this->nombre = $nombre;
    }

    public function getNombre(): string {
        return $this->nombre;
    }

    //descripcion
    public function setDescripcion(string $descripcion): void {
        $this->descripcion = $descripcion;
    }

    public function getDescripcion(): string {
        return $this->descripcion;
    }

}