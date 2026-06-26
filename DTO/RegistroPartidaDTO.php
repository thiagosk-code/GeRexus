<?php

class RegistroPartidaDTO {
    // Atributos
    private int $idRegistro;
    private string $fecha;
    private int $idJugadorGanador;
    private array $jugadores;
    private int $puntajeTotal;

    // Constructor
    public function __construct(int $idRegistro, int $idPartida, string $fecha, int $idJugadorGanador, array $jugadores) {
        $this->idRegistro = $idRegistro;
        $this->idPartida = $idPartida;
        $this->fecha = $fecha;
        $this->idJugadorGanador = $idJugadorGanador;
        $this->jugadores = $jugadores;
        $this->puntajeTotal = 0;
    }

    // idRegistro
    public function setIdRegistro(int $id): void {
        $this->idRegistro = $id;
    }

    public function getIdRegistro(): int {
        return $this->idRegistro;
    }

    // idPartida
    public function setIdPartida(int $id): void {
        $this->idPartida = $id;
    }

    public function getIdPartida(): int {
        return $this->idPartida;
    }

    // fecha
    public function setFecha(string $fecha): void {
        $this->fecha = $fecha;
    }

    public function getFecha(): string {
        return $this->fecha;
    }

    // idJugadorGanador
    public function setIdJugadorGanador(int $id): void {
        $this->idJugadorGanador = $id;
    }

    public function getIdJugadorGanador(): int {
        return $this->idJugadorGanador;
    }

    // jugadores
    public function setJugadores(array $jugadores): void {
        $this->jugadores = $jugadores;
    }

    public function getJugadores(): array {
        return $this->jugadores;
    }

    // puntajeTotal
    public function setPuntajeTotal(int $puntaje): void {
        $this->puntajeTotal = $puntaje;
    }

    public function getPuntajeTotal(): int {
        return $this->puntajeTotal;
    }
}