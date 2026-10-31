<?php

class PartidaDTO {

    private int $idPartida;
    private string $fecha;
    private int $jugadoresTotales;
    private bool $bajaLogica;
    private int $idTablero;
    private string $estado;
    private int $version;

    public function __construct(
        int $idPartida = 0,
        string $fecha = "",
        int $jugadoresTotales = 0,
        bool $bajaLogica = false,
        int $idTablero = 0,
        string $estado = '',
        int $version = 0
    ) {
        $this->idPartida = $idPartida;
        $this->fecha = $fecha;
        $this->jugadoresTotales = $jugadoresTotales;
        $this->bajaLogica = $bajaLogica;
        $this->idTablero = $idTablero;
        $this->estado = $estado;
        $this->version = $version;
    }

    public function getIdPartida(): int {
        return $this->idPartida;
    }
    public function setIdPartida(int $idPartida): void {
        $this->idPartida = $idPartida;
    }

    public function getFecha(): string {
        return $this->fecha;
    }
    public function setFecha(string $fecha): void {
        $this->fecha = $fecha;
    }

    public function getJugadoresTotales(): int {
        return $this->jugadoresTotales;
    }
    public function setJugadoresTotales(int $jugadoresTotales): void {
        $this->jugadoresTotales = $jugadoresTotales;
    }

    public function getBajaLogica(): bool {
        return $this->bajaLogica;
    }
    public function setBajaLogica(bool $bajaLogica): void {
        $this->bajaLogica = $bajaLogica;
    }

    public function getIdTablero(): int { return $this->idTablero; }
    public function setIdTablero(int $idTablero): void { $this->idTablero = $idTablero; }
    public function getEstado(): string { return $this->estado; }
    public function setEstado(string $estado): void { $this->estado = $estado; }
    public function getVersion(): int { return $this->version; }
    public function setVersion(int $version): void { $this->version = $version; }
}
