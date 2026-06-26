<?php
    interface ILogicaAdepto {
        public function asignarCorrienteDeAdepto(AdeptoDTO $adepto): void;
        public function asignarRarezaAleatoria(AdeptoDTO $adepto): void;
    }
?>
