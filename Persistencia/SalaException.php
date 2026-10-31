<?php

class SalaException extends Exception {

    private string $codigoError;

    public function __construct(string $codigoError) {
        parent::__construct($codigoError);
        $this->codigoError = $codigoError;
    }

    public function getCodigoError(): string {
        return $this->codigoError;
    }
}
