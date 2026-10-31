<?php

class TurnoPartidaDTO {
    public function __construct(
        private int $turnoGlobal,
        private int $ronda,
        private int $turnoRonda,
        private int $idJugadorActivo,
        private string $fase,
        private ?string $restriccion = null,
        private ?string $limite = null
    ) {}

    public function getTurnoGlobal(): int { return $this->turnoGlobal; }
    public function getRonda(): int { return $this->ronda; }
    public function getTurnoRonda(): int { return $this->turnoRonda; }
    public function getIdJugadorActivo(): int { return $this->idJugadorActivo; }
    public function getFase(): string { return $this->fase; }
    public function getRestriccion(): ?string { return $this->restriccion; }
    public function getLimite(): ?string { return $this->limite; }
}
