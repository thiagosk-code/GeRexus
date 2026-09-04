<?php
require_once (__DIR__ . '/ICaptchaService.php');
require_once (__DIR__ . '/../config.php');

class GoogleReCaptchaService implements ICaptchaService {
    private string $secretKey;

    public function __construct(?string $secretKey = null) {
        $this->secretKey = $secretKey ?? RECAPTCHA_SECRET_KEY;
    }

    public function verificar(?string $token, ?string $ipCliente = null): bool {
        if ($token === null || trim($token) === '') {
            return false;
        }

        $url = 'https://www.google.com/recaptcha/api/siteverify';
        $datos = [
            'secret'   => $this->secretKey,
            'response' => $token
        ];

        if ($ipCliente !== null && trim($ipCliente) !== '') {
            $datos['remoteip'] = $ipCliente;
        }

        $opciones = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($datos),
                'timeout' => 10
            ]
        ];

        $contexto = stream_context_create($opciones);
        $respuesta = @file_get_contents($url, false, $contexto);

        if ($respuesta === false) {
            return false;
        }

        $resultado = json_decode($respuesta, true);

        return is_array($resultado) && isset($resultado['success']) && $resultado['success'] === true;
    }
}