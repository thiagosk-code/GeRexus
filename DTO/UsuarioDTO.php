<?php

class UsuarioDTO {
    // Atributos
    private int $idUsuario;
    private string $nombre;
    private string $email;
    private string $password;
    private int $partidasGanadas;

    // Constructor
    public function __construct(int $idUsuario, string $nombre, string $email, string $password) {
        $this->idUsuario = $idUsuario;
        $this->nombre = $nombre;
        $this->email = $email;
        $this->password = $password;
        $this->partidasGanadas = 0;
        $this->esHost = false;
    }

    // idUsuario
    public function setIdUsuario(int $idUsuario): void {
        $this->idUsuario = $idUsuario;
    }

    public function getIdUsuario(): int {
        return $this->idUsuario;
    }

    // nombre
    public function setNombre(string $nombre): void {
        $this->nombre = $nombre;
    }

    public function getNombre(): string {
        return $this->nombre;
    }

    // email
    public function setEmail(string $email): void {
        $this->email = $email;
    }

    public function getEmail(): string {
        return $this->email;
    }

    // password
    public function setPassword(string $password): void {
        $this->password = $password;
    }

    public function getPassword(): string {
        return $this->password;
    }

    // partidasGanadas
    public function setPartidasGanadas(int $cantidad): void {
        $this->partidasGanadas = $cantidad;
    }

    public function getPartidasGanadas(): int {
        return $this->partidasGanadas;
    }

}