<?php

class UsuarioDTO {

    private int $idUsuario;
    private string $nombre;
    private string $email;
    private string $password;
    private int $partidasGanadas;
    private int $monedas;
    private int $idRol;
    private bool $bajaLogica;

    public function __construct(
        int $idUsuario = 0,
        string $nombre = "",
        string $email = "",
        string $password = "",
        int $partidasGanadas = 0,
        int $monedas = 0,
        int $idRol = 0,
        bool $bajaLogica = false
    ) {
        $this->idUsuario = $idUsuario;
        $this->nombre = $nombre;
        $this->email = $email;
        $this->password = $password;
        $this->partidasGanadas = $partidasGanadas;
        $this->monedas = $monedas;
        $this->idRol = $idRol;
        $this->bajaLogica = $bajaLogica;
    }

    public function getIdUsuario(): int {
        return $this->idUsuario;
    }
    public function setIdUsuario(int $idUsuario): void {
        $this->idUsuario = $idUsuario;
    }

    public function getNombre(): string {
        return $this->nombre;
    }
    public function setNombre(string $nombre): void {
        $this->nombre = $nombre;
    }

    public function getEmail(): string {
        return $this->email;
    }
    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function getPassword(): string {
        return $this->password;
    }
    public function setPassword(string $password): void {
        $this->password = $password;
    }

    public function getPartidasGanadas(): int {
        return $this->partidasGanadas;
    }
    public function setPartidasGanadas(int $partidasGanadas): void {
        $this->partidasGanadas = $partidasGanadas;
    }

    public function getMonedas(): int {
        return $this->monedas;
    }
    public function setMonedas(int $monedas): void {
        $this->monedas = $monedas;
    }

    public function getIdRol(): int {
        return $this->idRol;
    }
    public function setIdRol(int $idRol): void {
        $this->idRol = $idRol;
    }

    public function getBajaLogica(): bool {
        return $this->bajaLogica;
    }
    public function setBajaLogica(bool $bajaLogica): void {
        $this->bajaLogica = $bajaLogica;
    }
}
?>