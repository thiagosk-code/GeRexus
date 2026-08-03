<?php

class JugadasDTO {
    // Atributos privados basados en tus otros DTOs y datos de turno
    private UsuarioDTO $usuario;
    private RecintoDTO $recinto;
    private AdeptoDTO $adepto;
    private int $turno;
    private int $ronda;

    // Constructor tradicional que inicializa todas las propiedades
    public function __construct(UsuarioDTO $usuario, RecintoDTO $recinto, AdeptoDTO $adepto, int $turno, int $ronda) {
        $this->usuario = $usuario;
        $this->recinto = $recinto;
        $this->adepto = $adepto;
        $this->turno = $turno;
        $this->ronda = $ronda;
    }

    // usuario
    public function setUsuario(UsuarioDTO $usuario): void {
        $this->usuario = $usuario;
    }

    public function getUsuario(): UsuarioDTO {
        return $this->usuario;
    }

    // recinto
    public function setRecinto(RecintoDTO $recinto): void {
        $this->recinto = $recinto;
    }

    public function getRecinto(): RecintoDTO {
        return $this->recinto;
    }

    // adepto
    public function setAdepto(AdeptoDTO $adepto): void {
        $this->adepto = $adepto;
    }

    public function getAdepto(): AdeptoDTO {
        return $this->adepto;
    }

    // turno
    public function setTurno(int $turno): void {
        $this->turno = $turno;
    }

    public function getTurno(): int {
        return $this->turno;
    }

    // ronda
    public function setRonda(int $ronda): void {
        $this->ronda = $ronda;
    }

    public function getRonda(): int {
        return $this->ronda;
    }
}
