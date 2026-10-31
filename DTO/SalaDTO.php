<?php

class SalaDTO {

    private int $idSala;
    private string $codigo;
    private int $idAnfitrion;
    private int $idTablero;
    private string $estado;
    private int $idPartida;
    private int $version;
    private string $miEstado;
    private int $maxJugadores;
    private array $jugadores;
    private array $adeptos;

    public function __construct(
        int $idSala = 0,
        string $codigo = "",
        int $idAnfitrion = 0,
        int $idTablero = 0,
        string $estado = "",
        int $idPartida = 0,
        int $version = 0,
        string $miEstado = "",
        int $maxJugadores = 0,
        array $jugadores = [],
        array $adeptos = []
    ) {
        $this->idSala = $idSala;
        $this->codigo = $codigo;
        $this->idAnfitrion = $idAnfitrion;
        $this->idTablero = $idTablero;
        $this->estado = $estado;
        $this->idPartida = $idPartida;
        $this->version = $version;
        $this->miEstado = $miEstado;
        $this->maxJugadores = $maxJugadores;
        $this->jugadores = $jugadores;
        $this->adeptos = $adeptos;
    }

    public function getIdSala(): int {
        return $this->idSala;
    }
    public function setIdSala(int $idSala): void {
        $this->idSala = $idSala;
    }

    public function getCodigo(): string {
        return $this->codigo;
    }
    public function setCodigo(string $codigo): void {
        $this->codigo = $codigo;
    }

    public function getIdAnfitrion(): int {
        return $this->idAnfitrion;
    }
    public function setIdAnfitrion(int $idAnfitrion): void {
        $this->idAnfitrion = $idAnfitrion;
    }

    public function getIdTablero(): int {
        return $this->idTablero;
    }
    public function setIdTablero(int $idTablero): void {
        $this->idTablero = $idTablero;
    }

    public function getEstado(): string {
        return $this->estado;
    }
    public function setEstado(string $estado): void {
        $this->estado = $estado;
    }

    public function getIdPartida(): int {
        return $this->idPartida;
    }
    public function setIdPartida(int $idPartida): void {
        $this->idPartida = $idPartida;
    }

    public function getVersion(): int {
        return $this->version;
    }
    public function setVersion(int $version): void {
        $this->version = $version;
    }

    public function getMiEstado(): string {
        return $this->miEstado;
    }
    public function setMiEstado(string $miEstado): void {
        $this->miEstado = $miEstado;
    }

    public function getMaxJugadores(): int {
        return $this->maxJugadores;
    }
    public function setMaxJugadores(int $maxJugadores): void {
        $this->maxJugadores = $maxJugadores;
    }

    public function getJugadores(): array {
        return $this->jugadores;
    }
    public function setJugadores(array $jugadores): void {
        $this->jugadores = $jugadores;
    }

    public function getAdeptos(): array {
        return $this->adeptos;
    }
    public function setAdeptos(array $adeptos): void {
        $this->adeptos = $adeptos;
    }
}
