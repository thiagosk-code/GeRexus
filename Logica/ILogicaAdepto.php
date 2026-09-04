<?php
    interface ILogicaAdepto {
        public function asignarCorrienteDeAdepto(AdeptoDTO $adepto): void;
        public function asignarShinyAleatorio(AdeptoDTO $adepto): void;
    }
?>
