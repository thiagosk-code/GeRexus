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

    private function esAdminEjecutor(int $idAdminEjecutor): bool {
        if ($idAdminEjecutor <= 0) {
            return false;
        }

        $fachadaPersistencia = new FachadaPersistencia();
        $persistencia = $fachadaPersistencia->retornoIPersistenciaUsuario();
        $admin = $persistencia->buscarUsuario($idAdminEjecutor);

        return ($admin !== null && $admin->getEsAdmin() === true);
    }

    public function altaUsuarioL(UsuarioDTO $usuario, ?string $captchaToken = null, int $idAdminEjecutor = 0): array {

        if ($idAdminEjecutor > 0 && $this->esAdminEjecutor($idAdminEjecutor) === false) {
            return ['exito' => false, 'mensaje_key' => 'err_acceso_denegado', 'mensaje' => 'Acceso denegado. Se requieren permisos de administrador.'];
        }

        if ($usuario === null) {
            return ['exito' => false, 'mensaje_key' => 'err_datos_nulos', 'mensaje' => 'Datos de usuario nulos.'];
        }

        if ($captchaToken !== null) {
            $ip = $_SERVER['REMOTE_ADDR'] ?? null;
            if ($this->captchaService->verificar($captchaToken, $ip) === false) {
                return ['exito' => false, 'mensaje_key' => 'err_recaptcha', 'mensaje' => 'La verificacion de reCAPTCHA ha fallado.'];
            }
        }

        $nom = trim(htmlspecialchars($usuario->getNombre(), ENT_QUOTES, 'UTF-8'));
        $email = filter_var(trim($usuario->getEmail()), FILTER_SANITIZE_EMAIL);
        $contraPlana = $usuario->getPassword();

        if ($nom === '' || $email === '' || $contraPlana === '') {
            return ['exito' => false, 'mensaje_key' => 'err_campos_obligatorios', 'mensaje' => 'Todos los campos son obligatorios.'];
        }

        if (mb_strlen($nom) > 16) {
            return ['exito' => false, 'mensaje_key' => 'err_nombre_largo', 'mensaje' => 'El nombre no puede superar los 16 caracteres.'];
        }

        if (filter_var($email, FILTER_VALIDATE_EMAIL) === false || strlen($email) > 100) {
            return ['exito' => false, 'mensaje_key' => 'err_email_invalido', 'mensaje' => 'El formato del correo electronico no es valido.'];
        }

        if (strlen($contraPlana) < 8 || strlen($contraPlana) > 64) {
            return ['exito' => false, 'mensaje_key' => 'err_password_largo', 'mensaje' => 'La contrasenia debe tener entre 8 y 64 caracteres.'];
        }

        $fachadaPersistencia = new FachadaPersistencia();
        $persistencia = $fachadaPersistencia->retornoIPersistenciaUsuario();

        if ($persistencia->existeEmail($email) === true) {
            return ['exito' => false, 'mensaje_key' => 'err_email_registrado', 'mensaje' => 'El correo electronico ya esta registrado.'];
        }

        $contraConPimienta = $contraPlana . PEPPER_SECRET;
        $opciones = [
            'memory_cost' => 65536,
            'time_cost'   => 3,
            'threads'     => 4
        ];

        $hashSeguro = password_hash($contraConPimienta, PASSWORD_ARGON2ID, $opciones);
        if ($hashSeguro === false) {
            return ['exito' => false, 'mensaje_key' => 'err_cifrado', 'mensaje' => 'Error al procesar el cifrado de la contrasenia.'];
        }

        $nuevoDTO = new UsuarioDTO(0, $nom, $email, $hashSeguro, 0, 0, false);
        $idNuevo = $persistencia->altaUsuario($nuevoDTO);

        if ($idNuevo > 0) {
            return [
                'exito' => true,
                'mensaje_key' => 'msg_alta_exito',
                'mensaje' => 'Usuario registrado con exito.',
                'idUsuario' => $idNuevo
            ];
        }

        return ['exito' => false, 'mensaje_key' => 'err_alta_db', 'mensaje' => 'No se pudo completar el registro en la base de datos.'];
    }

    public function bajaUsuarioL(int $idElim, int $idAdminEjecutor = 0): array {
        if ($this->esAdminEjecutor($idAdminEjecutor) === false) {
            return ['exito' => false, 'mensaje_key' => 'err_acceso_denegado', 'mensaje' => 'Acceso denegado. Se requieren permisos de administrador.'];
        }

        if ($idElim <= 0) {
            return ['exito' => false, 'mensaje_key' => 'err_id_invalido', 'mensaje' => 'Debe ingresar un ID valido.'];
        }

        $fachadaPersistencia = new FachadaPersistencia();
        $persistencia = $fachadaPersistencia->retornoIPersistenciaUsuario();

        $target = $persistencia->buscarUsuario($idElim);
        if ($target === null) {
            return ['exito' => false, 'mensaje_key' => 'err_usuario_no_existe', 'mensaje' => 'El usuario no existe.'];
        }

        if ($target->getEsAdmin() === true) {
            return ['exito' => false, 'mensaje_key' => 'err_admin_no_eliminar', 'mensaje' => 'No se puede eliminar una cuenta de administrador.'];
        }

        $ok = $persistencia->bajaUsuario($idElim);
        if ($ok === true) {
            return ['exito' => true, 'mensaje_key' => 'msg_baja_exito', 'mensaje' => 'Usuario eliminado con exito.'];
        }

        return ['exito' => false, 'mensaje_key' => 'err_baja_db', 'mensaje' => 'Error al eliminar usuario.'];
    }

    public function modificarUsuarioL(int $idMod, string $nomMod, string $emailMod, string $contraMod, ?int $dracmasMod, int $idAdminEjecutor = 0): array {
        if ($this->esAdminEjecutor($idAdminEjecutor) === false) {
            return ['exito' => false, 'mensaje_key' => 'err_acceso_denegado', 'mensaje' => 'Acceso denegado. Se requieren permisos de administrador.'];
        }

        if ($idMod <= 0) {
            return ['exito' => false, 'mensaje_key' => 'err_id_invalido', 'mensaje' => 'Debe ingresar un ID valido.'];
        }

        $fachadaPersistencia = new FachadaPersistencia();
        $persistencia = $fachadaPersistencia->retornoIPersistenciaUsuario();

        $target = $persistencia->buscarUsuario($idMod);
        if ($target === null) {
            return ['exito' => false, 'mensaje_key' => 'err_usuario_no_existe', 'mensaje' => 'El usuario no existe.'];
        }

        if ($target->getEsAdmin() === true) {
            return ['exito' => false, 'mensaje_key' => 'err_admin_no_modificar', 'mensaje' => 'No se puede modificar una cuenta de administrador.'];
        }

        $nomFinal = $nomMod !== '' ? trim(htmlspecialchars($nomMod, ENT_QUOTES, 'UTF-8')) : $target->getNombre();
        $emailFinal = $emailMod !== '' ? filter_var(trim($emailMod), FILTER_SANITIZE_EMAIL) : $target->getEmail();
        $dracmasFinal = $dracmasMod !== null ? $dracmasMod : $target->getMonedas();

        if ($contraMod !== '') {
            $contraConPimienta = $contraMod . PEPPER_SECRET;
            $opciones = [
                'memory_cost' => 65536,
                'time_cost'   => 3,
                'threads'     => 4
            ];
            $hashSeguro = password_hash($contraConPimienta, PASSWORD_ARGON2ID, $opciones);
            if ($hashSeguro === false) {
                return ['exito' => false, 'mensaje_key' => 'err_cifrado', 'mensaje' => 'Error al procesar el cifrado de la contrasenia.'];
            }
        } else {
            $hashSeguro = $target->getPassword();
        }

        $dtoMod = new UsuarioDTO($idMod, $nomFinal, $emailFinal, $hashSeguro, $target->getPartidasGanadas(), $dracmasFinal, false);
        $ok = $persistencia->modificarUsuario($dtoMod);

        if ($ok === true) {
            return ['exito' => true, 'mensaje_key' => 'msg_mod_exito', 'mensaje' => 'Usuario modificado con exito.'];
        }

        return ['exito' => false, 'mensaje_key' => 'err_mod_db', 'mensaje' => 'Error al modificar usuario.'];
    }

    public function buscarUsuarioL(UsuarioDTO $usuario): ?UsuarioDTO {
        $res = null;

        if ($usuario !== null) {
            $fachadaPersistencia = new FachadaPersistencia();
            $res = $fachadaPersistencia->retornoIPersistenciaUsuario()->buscarUsuario($usuario->getIdUsuario());
        }

        return $res;
    }
}
?>