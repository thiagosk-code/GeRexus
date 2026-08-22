<?php

require_once (__DIR__ . '/../DTO/UsuarioDTO.php');
require_once (__DIR__ . '/ILogicaUsuario.php');
require_once (__DIR__ . '/../Persistencia/PersistenciaUsuario.php');
require_once (__DIR__ . '/../Persistencia/FachadaPersistencia.php');
require_once (__DIR__ . '/../Servicios/ICaptchaService.php');
require_once (__DIR__ . '/../Servicios/GoogleReCaptchaService.php');
require_once (__DIR__ . '/../config.php');

class LogicaUsuario implements ILogicaUsuario {

    private ICaptchaService $captchaService;

    public function __construct(?ICaptchaService $captchaService = null) {
        $this->captchaService = $captchaService ?? new GoogleReCaptchaService();
    }

    public function altaUsuarioL(UsuarioDTO $usuario, ?string $captchaToken = null): array {
        if ($usuario === null) {
            return ['exito' => false, 'mensaje' => 'Datos de usuario nulos.'];
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? null;
        if ($this->captchaService->verificar($captchaToken, $ip) === false) {
            return ['exito' => false, 'mensaje' => 'La verificacion de reCAPTCHA ha fallado.'];
        }

        $nom = trim(htmlspecialchars($usuario->getNombre(), ENT_QUOTES, 'UTF-8'));
        $email = filter_var(trim($usuario->getEmail()), FILTER_SANITIZE_EMAIL);
        $contraPlana = $usuario->getPassword();

        if ($nom === '' || $email === '' || $contraPlana === '') {
            return ['exito' => false, 'mensaje' => 'Todos los campos son obligatorios.'];
        }

        if (mb_strlen($nom) > 16) {
            return ['exito' => false, 'mensaje' => 'El nombre no puede superar los 16 caracteres.'];
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || strlen($email) > 100) {
            return ['exito' => false, 'mensaje' => 'El formato del correo electronico no es valido.'];
        }

        if (strlen($contraPlana) < 8 || strlen($contraPlana) > 64) {
            return ['exito' => false, 'mensaje' => 'La contrasenia debe tener entre 8 y 64 caracteres.'];
        }

        $fachadaPersistencia = new FachadaPersistencia();
        $persistencia = $fachadaPersistencia->retornoIPersistenciaUsuario();

        if ($persistencia->existeEmail($email) === true) {
            return ['exito' => false, 'mensaje' => 'El correo electronico ya esta registrado.'];
        }

        $contraConPimienta = $contraPlana . PEPPER_SECRET;
        $opciones = [
            'memory_cost' => 65536,
            'time_cost'   => 3,
            'threads'     => 4
        ];

        $hashSeguro = password_hash($contraConPimienta, PASSWORD_ARGON2ID, $opciones);
        if ($hashSeguro === false) {
            return ['exito' => false, 'mensaje' => 'Error al procesar el cifrado de la contrasenia.'];
        }

        $nuevoDTO = new UsuarioDTO(0, $nom, $email, $hashSeguro, 0, 0, false);
        $res = $persistencia->altaUsuario($nuevoDTO);

        if ($res === true) {
            return ['exito' => true, 'mensaje' => 'Usuario registrado con exito.'];
        }

        return ['exito' => false, 'mensaje' => 'No se pudo completar el registro en la base de datos.'];
    }

    public function bajaUsuarioL(UsuarioDTO $usuario): bool {
        $res = false;
     
        if ($usuario !== null) {
            $persistenciaUsuario = new FachadaPersistencia();
            $res = $persistenciaUsuario->retornoIPersistenciaUsuario()->bajaUsuario($usuario->getIdUsuario());
        }

        return $res;
    }

    public function modificarUsuarioL(UsuarioDTO $usuario): bool {
        $res = false;
     
        if ($usuario !== null) {
            $persistenciaUsuario = new FachadaPersistencia();
            $res = $persistenciaUsuario->retornoIPersistenciaUsuario()->modificarUsuario($usuario);
        }

        return $res;
    }

    public function buscarUsuarioL(UsuarioDTO $usuario): ?UsuarioDTO {
        $res = null;
     
        if ($usuario !== null) {
            $persistenciaUsuario = new FachadaPersistencia();
            $res = $persistenciaUsuario->retornoIPersistenciaUsuario()->buscarUsuario($usuario->getIdUsuario());
        }

        return $res;
    }

}