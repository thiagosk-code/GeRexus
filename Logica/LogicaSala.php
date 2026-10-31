<?php

require_once (__DIR__ . '/../DTO/UsuarioDTO.php');
require_once (__DIR__ . '/../DTO/SalaDTO.php');
require_once (__DIR__ . '/../Persistencia/SalaException.php');
require_once (__DIR__ . '/../Persistencia/FachadaPersistencia.php');
require_once (__DIR__ . '/ILogicaSala.php');
require_once (__DIR__ . '/ILogicaAutorizacion.php');
require_once (__DIR__ . '/LogicaAutorizacion.php');

class LogicaSala implements ILogicaSala {

    private const ALFABETO_CODIGO = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
    private const LARGO_CODIGO = 6;
    private const REINTENTOS_CODIGO = 5;
    private const MAX_INTENTOS_FALLIDOS = 8;
    private const VENTANA_INTENTOS = 300;

    private const MENSAJES = [
        'ERR_SALA_NO_EXISTE' => ['err_sala_no_existe', 'La sala no existe.'],
        'ERR_SALA_LLENA' => ['err_sala_llena', 'La sala esta llena.'],
        'ERR_SALA_CERRADA' => ['err_sala_cerrada', 'La sala ya no esta disponible.'],
        'ERR_YA_EN_SALA' => ['err_ya_en_sala', 'Ya estas en una sala.'],
        'ERR_EXPULSADO' => ['err_expulsado', 'Fuiste expulsado de esta sala.'],
        'ERR_NO_ANFITRION' => ['err_no_anfitrion', 'Solo el anfitrion puede hacer esto.'],
        'ERR_JUGADORES_INSUFICIENTES' => ['err_jugadores_insuficientes', 'Se necesitan mas jugadores para comenzar.'],
        'ERR_ADEPTOS_INVALIDOS' => ['err_adeptos_invalidos', 'Los adeptos elegidos no son validos.'],
        'ERR_TABLERO_INVALIDO' => ['err_tablero_invalido', 'El tablero elegido no es valido.'],
        'ERR_NO_EN_SALA' => ['err_no_en_sala', 'No perteneces a esta sala.'],
        'ERR_NO_PUEDE_ECHAR_ANFITRION' => ['err_no_puede_echar_anfitrion', 'No se puede echar al anfitrion.'],
        'ERR_CODIGO_INVALIDO' => ['err_codigo_invalido', 'El codigo no es valido.'],
        'ERR_USUARIO_INVALIDO' => ['err_usuario_invalido', 'El usuario no es valido.'],
        'ERR_DEMASIADOS_INTENTOS' => ['err_demasiados_intentos', 'Demasiados intentos. Espera unos minutos.'],
        'ERR_ACCESO_DENEGADO' => ['err_acceso_denegado', 'Acceso denegado.'],
        'ERR_BD' => ['err_bd', 'Ocurrio un error. Intenta de nuevo.']
    ];

    private ILogicaAutorizacion $logicaAutorizacion;

    public function __construct(?ILogicaAutorizacion $logicaAutorizacion = null) {
        $this->logicaAutorizacion = $logicaAutorizacion ?? new LogicaAutorizacion();
    }

    private function persistencia(): IPersistenciaSala {
        $fachadaPersistencia = new FachadaPersistencia();
        return $fachadaPersistencia->retornoIPersistenciaSala();
    }

    private function error(string $codigoError): array {
        $mensaje = self::MENSAJES[$codigoError] ?? self::MENSAJES['ERR_BD'];
        return ['exito' => false, 'codigo_error' => $codigoError, 'mensaje_key' => $mensaje[0], 'mensaje' => $mensaje[1]];
    }

    private function normalizarCodigo(string $codigo): string {
        return strtoupper(trim($codigo));
    }

    private function codigoValido(string $codigo): bool {
        return preg_match('/^[A-HJ-NP-Z2-9]{' . self::LARGO_CODIGO . '}$/', $codigo) === 1;
    }

    private function generarCodigo(): string {
        $codigo = '';
        $maximo = strlen(self::ALFABETO_CODIGO) - 1;
        for ($i = 0; $i < self::LARGO_CODIGO; $i++) {
            $codigo .= self::ALFABETO_CODIGO[random_int(0, $maximo)];
        }
        return $codigo;
    }

    private function tienePermiso(int $idUsuario, string $permiso): bool {
        $fachadaPersistencia = new FachadaPersistencia();
        $usuario = $fachadaPersistencia->retornoIPersistenciaUsuario()->buscarUsuario($idUsuario);
        return $this->logicaAutorizacion->tienePermiso($usuario, $permiso);
    }

    private function iniciarSesionSiHaceFalta(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    private function intentosAgotados(): bool {
        $this->iniciarSesionSiHaceFalta();
        $registro = $_SESSION['intentos_codigo_sala'] ?? ['n' => 0, 't' => time()];

        if ((time() - (int)$registro['t']) > self::VENTANA_INTENTOS) {
            unset($_SESSION['intentos_codigo_sala']);
            return false;
        }
        return (int)$registro['n'] >= self::MAX_INTENTOS_FALLIDOS;
    }

    private function registrarIntentoFallido(): void {
        $this->iniciarSesionSiHaceFalta();
        $registro = $_SESSION['intentos_codigo_sala'] ?? ['n' => 0, 't' => time()];
        $registro['n'] = (int)$registro['n'] + 1;
        $_SESSION['intentos_codigo_sala'] = $registro;
    }

    private function accion(int $idUsuario, string $codigo, callable $ejecutar): array {
        $codigo = $this->normalizarCodigo($codigo);

        if ($idUsuario <= 0 || $this->codigoValido($codigo) === false) {
            return $this->error('ERR_CODIGO_INVALIDO');
        }

        try {
            return ['exito' => true] + $ejecutar($codigo);
        } catch (SalaException $e) {
            return $this->error($e->getCodigoError());
        }
    }

    public function obtenerTablerosL(): array {
        try {
            return $this->persistencia()->obtenerTableros();
        } catch (SalaException $e) {
            return [];
        }
    }

    public function obtenerAdeptosDisponiblesL(int $idUsuario): array {
        try {
            return $idUsuario > 0 ? $this->persistencia()->obtenerAdeptosDisponibles($idUsuario) : [];
        } catch (SalaException $e) {
            return [];
        }
    }

    public function crearSalaL(int $idUsuario, int $idTablero, array $idsAdeptos): array {
        $ids = array_map('intval', array_values($idsAdeptos));

        if (count($ids) !== 6 || count(array_unique($ids)) !== 6 || min($ids) <= 0) {
            return $this->error('ERR_ADEPTOS_INVALIDOS');
        }

        if ($idUsuario <= 0 || $idTablero <= 0) {
            return $this->error('ERR_USUARIO_INVALIDO');
        }

        if ($this->tienePermiso($idUsuario, 'crear_sala') === false) {
            return $this->error('ERR_ACCESO_DENEGADO');
        }

        for ($intento = 0; $intento < self::REINTENTOS_CODIGO; $intento++) {
            $codigo = $this->generarCodigo();

            try {
                $this->persistencia()->crearSala($idUsuario, $codigo, $idTablero, $ids);
                return ['exito' => true, 'codigo' => $codigo];
            } catch (SalaException $e) {
                if ($e->getCodigoError() !== 'ERR_CODIGO_DUPLICADO') {
                    return $this->error($e->getCodigoError());
                }
            }
        }

        return $this->error('ERR_BD');
    }

    public function unirseSalaL(int $idUsuario, string $codigo): array {
        $codigo = $this->normalizarCodigo($codigo);

        if ($this->intentosAgotados()) {
            return $this->error('ERR_DEMASIADOS_INTENTOS');
        }

        if ($idUsuario <= 0 || $this->codigoValido($codigo) === false) {
            $this->registrarIntentoFallido();
            return $this->error('ERR_CODIGO_INVALIDO');
        }

        if ($this->tienePermiso($idUsuario, 'unirse_sala') === false) {
            return $this->error('ERR_ACCESO_DENEGADO');
        }

        try {
            $this->persistencia()->unirseSala($idUsuario, $codigo);
            unset($_SESSION['intentos_codigo_sala']);
            return ['exito' => true, 'codigo' => $codigo];
        } catch (SalaException $e) {
            if ($e->getCodigoError() === 'ERR_SALA_NO_EXISTE') {
                $this->registrarIntentoFallido();
            }
            return $this->error($e->getCodigoError());
        }
    }

    public function obtenerSalaActivaL(int $idUsuario): ?string {
        try {
            return $idUsuario > 0 ? $this->persistencia()->obtenerSalaActivaUsuario($idUsuario) : null;
        } catch (SalaException $e) {
            return null;
        }
    }

    public function obtenerEstadoSalaL(int $idUsuario, string $codigo, int $versionCliente = 0): array {
        return $this->accion($idUsuario, $codigo, function (string $codigoSala) use ($idUsuario, $versionCliente): array {
            $persistencia = $this->persistencia();
            $sala = $persistencia->obtenerEstadoSala($idUsuario, $codigoSala);

            if ($sala === null) {
                return ['existe' => false, 'cambio' => true, 'sala' => null, 'esAnfitrion' => false];
            }

            $vigente = ($sala->getMiEstado() === 'activo' && $sala->getEstado() === 'espera');
            $cambio = ($vigente === false) || ($sala->getVersion() !== $versionCliente);

            if ($vigente && $cambio) {
                $sala->setJugadores($persistencia->obtenerJugadoresSala($idUsuario, $codigoSala));
                if ($versionCliente === 0) {
                    $sala->setAdeptos($persistencia->obtenerAdeptosSala($idUsuario, $codigoSala));
                }
            }

            return [
                'existe' => true,
                'cambio' => $cambio,
                'sala' => $sala,
                'esAnfitrion' => ($sala->getIdAnfitrion() === $idUsuario)
            ];
        });
    }

    public function salirSalaL(int $idUsuario, string $codigo): array {
        return $this->accion($idUsuario, $codigo, function (string $codigoSala) use ($idUsuario): array {
            $this->persistencia()->salirSala($idUsuario, $codigoSala);
            return [];
        });
    }

    public function echarJugadorSalaL(int $idAnfitrion, string $codigo, int $idJugador): array {
        return $this->accion($idAnfitrion, $codigo, function (string $codigoSala) use ($idAnfitrion, $idJugador): array {
            $this->persistencia()->echarJugadorSala($idAnfitrion, $codigoSala, $idJugador);
            return [];
        });
    }

    public function cancelarSalaL(int $idAnfitrion, string $codigo): array {
        return $this->accion($idAnfitrion, $codigo, function (string $codigoSala) use ($idAnfitrion): array {
            $this->persistencia()->cancelarSala($idAnfitrion, $codigoSala);
            return [];
        });
    }

    public function iniciarSalaL(int $idAnfitrion, string $codigo): array {
        return $this->accion($idAnfitrion, $codigo, function (string $codigoSala) use ($idAnfitrion): array {
            return ['idPartida' => $this->persistencia()->iniciarSala($idAnfitrion, $codigoSala)];
        });
    }
}
