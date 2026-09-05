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

        if ($ipCliente !== null && trim($ipCliente) !== '' && $ipCliente !== '::1' && $ipCliente !== '127.0.0.1') {
            $datos['remoteip'] = $ipCliente;
        }

        if (function_exists('curl_init') === true) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($datos));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);

            $respuesta = curl_exec($ch);
            curl_close($ch);
        } else {
            $opciones = [
                'http' => [
                    'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method'  => 'POST',
                    'content' => http_build_query($datos),
                    'timeout' => 3
                ],
                'ssl' => [
                    'verify_peer'      => false,
                    'verify_peer_name' => false
                ]
            ];
            $contexto = stream_context_create($opciones);
            $respuesta = @file_get_contents($url, false, $contexto);
        }

        if ($respuesta === false || $respuesta === '') {
            return false;
        }

        $resultado = json_decode($respuesta, true);

        return is_array($resultado) && isset($resultado['success']) && $resultado['success'] === true;
    }
}