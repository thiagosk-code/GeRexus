<?php

class JugadaDTO {

    private UsuarioDTO $usuario;
    private RecintoDTO $recinto;
    private AdeptoDTO $adepto;
    private int $turnoGlobal;
    private int $ronda;
    private int $turnoRonda;
    private bool $fueAutomatica;
    private string $fecha;

    public function __construct(UsuarioDTO $usuario, RecintoDTO $recinto, AdeptoDTO $adepto, int $turnoGlobal = 0, int $ronda = 0, int $turnoRonda = 0, bool $fueAutomatica = false, string $fecha = '') {
        $this->usuario = $usuario;
        $this->recinto = $recinto;
        $this->adepto = $adepto;
        $this->turnoGlobal = $turnoGlobal;
        $this->ronda = $ronda;
        $this->turnoRonda = $turnoRonda;
        $this->fueAutomatica = $fueAutomatica;
        $this->fecha = $fecha;
    }

    public function getUsuario(): UsuarioDTO {
        return $this->usuario;
    }
    public function setUsuario(UsuarioDTO $usuario): void {
        $this->usuario = $usuario;
    }

    public function getRecinto(): RecintoDTO {
        return $this->recinto;
    }
    public function setRecinto(RecintoDTO $recinto): void {
        $this->recinto = $recinto;
    }

    public function getAdepto(): AdeptoDTO {
        return $this->adepto;
    }
    public function setAdepto(AdeptoDTO $adepto): void {
        $this->adepto = $adepto;
    }

    public function getTurnoGlobal(): int { return $this->turnoGlobal; }
    public function getRonda(): int { return $this->ronda; }
    public function getTurnoRonda(): int { return $this->turnoRonda; }
    public function getFueAutomatica(): bool { return $this->fueAutomatica; }
    public function getFecha(): string { return $this->fecha; }
}
