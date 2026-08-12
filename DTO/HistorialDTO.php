<?php

class HistorialDTO {
    // Atributos
    private UsuarioDTO $idUsuario;
    private PartidaDTO $idPartida;
    private int $puesto;
    private int $puntosHistoricos;
    private bool $esGanador;

    // Constructor
    public function __construct(UsuarioDTO $idUsuario, PartidaDTO $idPartida, int $puesto, int $puntosHistoricos, bool $esGanador) {
        $this->idUsuario = $idUsuario;
        $this->idPartida = $idPartida;
        $this->puesto = $puesto;
        $this->puntosHistoricos = $puntosHistoricos;
        $this->esGanador = $esGanador;
    }

    // idUsuario
    public function getIdUsuario(): UsuarioDTO {
        return $this->idUsuario; 
    }
    public function setIdUsuario(UsuarioDTO $idUsuario): void {
        $this->idUsuario = $idUsuario; 
    }

    // idPartida
    public function getIdPartida(): PartidaDTO {
        return $this->idPartida;
    }
    public function setIdPartida(PartidaDTO $idPartida): void {
        $this->idPartida = $idPartida;
    }

    // puesto
    public function getPuesto(): int {
        return $this->puesto;
    }
    public function setPuesto(int $puesto): void {
        $this->puesto = $puesto;
    }

    // puntosHistoricos
    public function getPuntosHistoricos(): int {
        return $this->puntosHistoricos;
    }
    public function setPuntosHistoricos(int $puntosHistoricos): void {
        $this->puntosHistoricos = $puntosHistoricos;
    }

    // esGanador
    public function getEsGanador(): bool {
        return $this->esGanador;
    }
    public function setEsGanador(bool $esGanador): void {
        $this->esGanador = $esGanador;
    }
}