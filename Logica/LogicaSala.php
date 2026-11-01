<?php

require_once (__DIR__ . '/../DTO/UsuarioDTO.php');
require_once (__DIR__ . '/../DTO/SalaDTO.php');
require_once (__DIR__ . '/../Persistencia/SalaException.php');
require_once (__DIR__ . '/../Persistencia/FachadaPersistencia.php');
require_once (__DIR__ . '/ILogicaSala.php');
require_once (__DIR__ . '/ILogicaAutorizacion.php');
require_once (__DIR__ . '/LogicaAutorizacion.php');
require_once (__DIR__ . '/ConfiguracionJuego.php');

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
        'ERR_CONCURRENCIA' => ['err_sala_cerrada', 'La sala cambio. Intenta nuevamente.'],
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

    private function limpiarSalasInactivas(): void {
        $persistencia = $this->persistencia();
        foreach ($persistencia->obtenerCandidatosInactivos(
            ConfiguracionJuego::SEGUNDOS_INACTIVIDAD_SALA,
            ConfiguracionJuego::SEGUNDOS_VIDA_SALA
        ) as $candidata) {
            try {
                $persistencia->cancelarSalaInactiva((int)$candidata['idSala'], (int)$candidata['Version']);
            } catch (SalaException $e) {
                if ($e->getCodigoError() !== 'ERR_CONCURRENCIA') {
                    throw $e;
                }
            }
        }
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

        if (count($ids) !== ConfiguracionJuego::ADEPTOS_POR_SALA || count(array_unique($ids)) !== ConfiguracionJuego::ADEPTOS_POR_SALA || min($ids) <= 0) {
            return $this->error('ERR_ADEPTOS_INVALIDOS');
        }

        if ($idUsuario <= 0 || $idTablero <= 0) {
            return $this->error('ERR_USUARIO_INVALIDO');
        }

        if ($this->tienePermiso($idUsuario, 'crear_sala') === false) {
            return $this->error('ERR_ACCESO_DENEGADO');
        }

        $tableros = array_map(static fn(TableroDTO $tablero): int => $tablero->getIdTablero(), $this->obtenerTablerosL());
        if (!in_array($idTablero, $tableros, true)) {
            return $this->error('ERR_TABLERO_INVALIDO');
        }
        $disponibles = array_map(static fn(AdeptoDTO $adepto): int => $adepto->getIdAdepto(), $this->obtenerAdeptosDisponiblesL($idUsuario));
        if (count(array_intersect($ids, $disponibles)) !== ConfiguracionJuego::ADEPTOS_POR_SALA) {
            return $this->error('ERR_ADEPTOS_INVALIDOS');
        }

        try {
            $this->limpiarSalasInactivas();
        } catch (SalaException $e) {
            return $this->error($e->getCodigoError());
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
            $this->limpiarSalasInactivas();
            $persistencia = $this->persistencia();
            $sala = $persistencia->obtenerEstadoSala($idUsuario, $codigo);
            if ($sala === null) {
                throw new SalaException('ERR_SALA_NO_EXISTE');
            }
            if ($sala->getEstado() !== 'espera') {
                throw new SalaException('ERR_SALA_CERRADA');
            }
            if ($sala->getMiEstado() === 'expulsado') {
                throw new SalaException('ERR_EXPULSADO');
            }
            if ($sala->getMiEstado() === 'activo') {
                unset($_SESSION['intentos_codigo_sala']);
                return ['exito' => true, 'codigo' => $codigo];
            }
            $jugadores = $persistencia->obtenerJugadoresSala($idUsuario, $codigo);
            if (count($jugadores) >= ConfiguracionJuego::MAX_JUGADORES) {
                throw new SalaException('ERR_SALA_LLENA');
            }
            $persistencia->unirseSala($idUsuario, $codigo, $sala->getVersion(), ConfiguracionJuego::MAX_JUGADORES);
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
            $persistencia->registrarActividad($idUsuario, $codigoSala);
            $sala = $persistencia->obtenerEstadoSala($idUsuario, $codigoSala);

            if ($sala === null) {
                return ['existe' => false, 'cambio' => true, 'sala' => null, 'esAnfitrion' => false];
            }
            $sala->setMaxJugadores(ConfiguracionJuego::MAX_JUGADORES);

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
            $persistencia = $this->persistencia();
            $sala = $persistencia->obtenerEstadoSala($idUsuario, $codigoSala);
            if ($sala === null) {
                throw new SalaException('ERR_SALA_NO_EXISTE');
            }
            if ($sala->getIdAnfitrion() === $idUsuario) {
                $persistencia->cancelarSala($idUsuario, $codigoSala, $sala->getVersion());
            } else {
                $persistencia->salirSala($idUsuario, $codigoSala, $sala->getVersion());
            }
            return [];
        });
    }

    public function echarJugadorSalaL(int $idAnfitrion, string $codigo, int $idJugador): array {
        return $this->accion($idAnfitrion, $codigo, function (string $codigoSala) use ($idAnfitrion, $idJugador): array {
            $persistencia = $this->persistencia();
            $sala = $persistencia->obtenerEstadoSala($idAnfitrion, $codigoSala);
            if ($sala === null || $sala->getIdAnfitrion() !== $idAnfitrion) {
                throw new SalaException('ERR_NO_ANFITRION');
            }
            if ($idJugador === $idAnfitrion) {
                throw new SalaException('ERR_NO_PUEDE_ECHAR_ANFITRION');
            }
            $persistencia->echarJugadorSala($idAnfitrion, $codigoSala, $idJugador, $sala->getVersion());
            return [];
        });
    }

    public function cancelarSalaL(int $idAnfitrion, string $codigo): array {
        return $this->accion($idAnfitrion, $codigo, function (string $codigoSala) use ($idAnfitrion): array {
            $persistencia = $this->persistencia();
            $sala = $persistencia->obtenerEstadoSala($idAnfitrion, $codigoSala);
            if ($sala === null || $sala->getIdAnfitrion() !== $idAnfitrion) {
                throw new SalaException('ERR_NO_ANFITRION');
            }
            $persistencia->cancelarSala($idAnfitrion, $codigoSala, $sala->getVersion());
            return [];
        });
    }

    public function iniciarSalaL(int $idAnfitrion, string $codigo): array {
        return $this->accion($idAnfitrion, $codigo, function (string $codigoSala) use ($idAnfitrion): array {
            $persistencia = $this->persistencia();
            $sala = $persistencia->obtenerEstadoSala($idAnfitrion, $codigoSala);
            if ($sala === null || $sala->getIdAnfitrion() !== $idAnfitrion) {
                throw new SalaException('ERR_NO_ANFITRION');
            }
            if ($sala->getEstado() === 'en_juego' && $sala->getIdPartida() > 0) {
                return ['idPartida' => $sala->getIdPartida()];
            }
            if ($sala->getEstado() !== 'espera') {
                throw new SalaException('ERR_SALA_CERRADA');
            }

            $usuarios = $persistencia->obtenerJugadoresSala($idAnfitrion, $codigoSala);
            $adeptos = $persistencia->obtenerAdeptosSala($idAnfitrion, $codigoSala);
            if (count($usuarios) < ConfiguracionJuego::MIN_JUGADORES) {
                throw new SalaException('ERR_JUGADORES_INSUFICIENTES');
            }
            if (count($usuarios) > ConfiguracionJuego::MAX_JUGADORES || count($adeptos) !== ConfiguracionJuego::ADEPTOS_POR_SALA) {
                throw new SalaException('ERR_ADEPTOS_INVALIDOS');
            }

            $jugadores = [];
            foreach ($usuarios as $indice => $usuario) {
                $jugadores[] = ['id_usuario' => $usuario->getIdUsuario(), 'orden' => $indice + 1];
            }
            $bolsa = [];
            foreach ($adeptos as $adepto) {
                for ($copia = 0; $copia < ConfiguracionJuego::COPIAS_POR_ADEPTO; $copia++) {
                    $bolsa[] = $adepto->getIdAdepto();
                }
            }
            for ($indice = count($bolsa) - 1; $indice > 0; $indice--) {
                $otro = random_int(0, $indice);
                [$bolsa[$indice], $bolsa[$otro]] = [$bolsa[$otro], $bolsa[$indice]];
            }
            $fichas = [];
            $repartidas = count($jugadores) * ConfiguracionJuego::FICHAS_POR_RONDA;
            foreach ($bolsa as $indice => $idAdepto) {
                $numero = $indice + 1;
                $idPoseedor = null;
                $ronda = null;
                if ($indice < $repartidas) {
                    $orden = intdiv($indice, ConfiguracionJuego::FICHAS_POR_RONDA);
                    $idPoseedor = $jugadores[$orden]['id_usuario'];
                    $ronda = 1;
                }
                $fichas[] = [
                    'numero' => $numero,
                    'id_adepto' => $idAdepto,
                    'estado' => $idPoseedor === null ? 'bolsa' : 'mano',
                    'id_poseedor' => $idPoseedor,
                    'ronda' => $ronda
                ];
            }
            $primerActivo = $jugadores[random_int(0, count($jugadores) - 1)]['id_usuario'];
            return ['idPartida' => $persistencia->iniciarSala(
                $idAnfitrion,
                $codigoSala,
                $sala->getVersion(),
                $jugadores,
                $fichas,
                $primerActivo,
                ConfiguracionJuego::SEGUNDOS_TIRADA
            )];
        });
    }
}
