<?php

class JugadorPartidaDTO {
    public function __construct(
        private int $idUsuario,
        private string $nombre,
        private int $orden,
        private int $puntaje = 0,
        private ?int $puesto = null,
        private bool $esGanador = false,
        private bool $votoFinalizacion = false
    ) {}

    public function getIdUsuario(): int { return $this->idUsuario; }
    public function getNombre(): string { return $this->nombre; }
    public function getOrden(): int { return $this->orden; }
    public function getPuntaje(): int { return $this->puntaje; }
    public function getPuesto(): ?int { return $this->puesto; }
    public function getEsGanador(): bool { return $this->esGanador; }
    public function getVotoFinalizacion(): bool { return $this->votoFinalizacion; }
}
