<?php

class PartidaException extends RuntimeException {
    public function __construct(private string $codigoError, string $mensaje = '') {
        parent::__construct($mensaje !== '' ? $mensaje : $codigoError);
    }

    public function getCodigoError(): string {
        return $this->codigoError;
    }
}
