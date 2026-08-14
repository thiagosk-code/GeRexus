<?php

class JugadaDTO {
    
    private int $idJugada;
    private UsuarioDTO $usuario;
    private RecintoDTO $recinto;
    private AdeptoDTO $adepto;
    private PartidaDTO $partida;
    private int $turno;
    private int $ronda;

    // Constructor tradicional que inicializa todas las propiedades
    public function __construct(int $idJugada, UsuarioDTO $usuario, RecintoDTO $recinto, AdeptoDTO $adepto, int $turno, int $ronda) {
        $this->idJugada = $idJugada;
        $this->usuario = $usuario;
        $this->recinto = $recinto;
        $this->adepto = $adepto;
    }

    // jugada
    public function setIdJugada(int $idJugada): void{
        $this->idJugada = $idJugada;
    }

    public function getIdJugada(): int{
        $this->idJugada = $idJugada;
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
    
}