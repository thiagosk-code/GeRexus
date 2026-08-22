<?php
interface ICaptchaService {
    public function verificar(?string $token, ?string $ipCliente = null): bool;
}

?>