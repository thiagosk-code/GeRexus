<?php

require_once (__DIR__ . '/ILogicaPartida.php');
require_once (__DIR__ . '/LogicaRecintos.php');
require_once (__DIR__ . '/ConfiguracionRecintos.php');
require_once (__DIR__ . '/ConfiguracionJuego.php');
require_once (__DIR__ . '/../Persistencia/FachadaPersistencia.php');
require_once (__DIR__ . '/../Persistencia/PartidaException.php');

class LogicaPartida implements ILogicaPartida {
    private const ERRORES = [
        'ERR_PARTIDA_NO_AUTORIZADA' => 'err_partida_no_autorizada',
        'ERR_PARTIDA_NO_EXISTE' => 'err_partida_no_existe',
        'ERR_PARTIDA_ESTADO' => 'err_partida_estado',
        'ERR_PARTIDA_FASE' => 'err_partida_fase',
        'ERR_PARTIDA_NO_ACTIVO' => 'err_partida_no_activo',
        'ERR_PARTIDA_TIEMPO' => 'err_partida_tiempo',
        'ERR_PARTIDA_YA_JUGO' => 'err_partida_ya_jugo',
        'ERR_PARTIDA_FICHA' => 'err_partida_ficha',
        'ERR_PARTIDA_RECINTO' => 'err_partida_recinto',
        'ERR_PARTIDA_VOTO' => 'err_partida_voto',
        'ERR_CONCURRENCIA' => 'err_partida_estado',
        'ERR_BD' => 'err_bd'
    ];

    private function persistencia(): IPersistenciaPartida {
        return (new FachadaPersistencia())->retornoIPersistenciaPartida();
    }

    private function error(string $codigo, ?string $mensajeKey = null): array {
        return [
            'exito' => false,
            'codigo_error' => $codigo,
            'mensaje_key' => $mensajeKey ?? (self::ERRORES[$codigo] ?? 'err_bd')
        ];
    }

    public function obtenerEstadoPartidaL(int $idUsuario, int $idPartida, int $versionCliente = 0): array {
        if ($idUsuario <= 0 || $idPartida <= 0) {
            return $this->error('ERR_PARTIDA_NO_EXISTE');
        }

        try {
            $persistencia = $this->persistencia();
            $this->procesarVencimientos($idUsuario, $idPartida);
            $raw = $persistencia->obtenerEstado($idUsuario, $idPartida);
            if ($raw === null) {
                return $this->error('ERR_PARTIDA_NO_AUTORIZADA');
            }

            if (($raw['partida']['Estado'] ?? '') === 'calculando') {
                $clasificacion = $this->crearClasificacion($raw);
                $persistencia->finalizar($idPartida, (int)$raw['partida']['Version'], $clasificacion);
                $raw = $persistencia->obtenerEstado($idUsuario, $idPartida);
            }

            $version = (int)$raw['partida']['Version'];
            $turno = $raw['turno'];
            $limite = $turno === null ? null : ($turno['Fase'] === 'esperando_tirada'
                ? ($turno['LimiteTirada'] ?? null)
                : ($turno['LimiteColocacion'] ?? null));
            $limiteEpoch = $turno === null ? null : ($turno['Fase'] === 'esperando_tirada'
                ? ($turno['LimiteTiradaEpoch'] ?? null)
                : ($turno['LimiteColocacionEpoch'] ?? null));
            $servidorEpoch = $turno === null ? time() : (int)($turno['ServidorEpoch'] ?? time());

            if ($versionCliente > 0 && $versionCliente === $version) {
                return [
                    'exito' => true, 'cambio' => false, 'version' => $version,
                    'servidor_epoch' => $servidorEpoch, 'limite' => $limite,
                    'limite_epoch' => $limiteEpoch === null ? null : (int)$limiteEpoch
                ];
            }

            return ['exito' => true, 'cambio' => true] + $this->formatearEstado($idUsuario, $raw);
        } catch (PartidaException $e) {
            return $this->error($e->getCodigoError());
        }
    }

    public function tirarDadoL(int $idUsuario, int $idPartida): array {
        if ($idUsuario <= 0 || $idPartida <= 0) {
            return $this->error('ERR_PARTIDA_NO_EXISTE');
        }
        try {
            $persistencia = $this->persistencia();
            $this->procesarVencimientos($idUsuario, $idPartida);
            $raw = $persistencia->obtenerEstado($idUsuario, $idPartida);
            if ($raw === null) {
                return $this->error('ERR_PARTIDA_NO_AUTORIZADA');
            }
            $turno = $raw['turno'];
            if (($raw['partida']['Estado'] ?? '') !== 'en_juego' || $turno === null || $turno['Fase'] !== 'esperando_tirada') {
                return $this->error('ERR_PARTIDA_FASE');
            }
            if ((int)$turno['idJugadorActivo'] !== $idUsuario) {
                return $this->error('ERR_PARTIDA_NO_ACTIVO');
            }
            try {
                $persistencia->aplicarTirada(
                    $idUsuario,
                    $idPartida,
                    (int)$raw['partida']['Version'],
                    (int)$turno['TurnoGlobal'],
                    $this->elegirRestriccion(),
                    $this->fechaLimite($raw, ConfiguracionJuego::SEGUNDOS_COLOCACION)
                );
            } catch (PartidaException $e) {
                if ($e->getCodigoError() !== 'ERR_CONCURRENCIA') {
                    throw $e;
                }
                $actual = $persistencia->obtenerEstado($idUsuario, $idPartida);
                if ($actual === null || $actual['turno'] === null
                    || (int)$actual['turno']['TurnoGlobal'] !== (int)$turno['TurnoGlobal']
                    || $actual['turno']['Fase'] !== 'colocacion') {
                    throw $e;
                }
            }
            return ['exito' => true];
        } catch (PartidaException $e) {
            return $this->error($e->getCodigoError());
        }
    }

    public function colocarAdeptoL(int $idUsuario, int $idPartida, int $idFicha, string $codigoRecinto): array {
        $codigoRecinto = strtolower(trim($codigoRecinto));
        if ($idUsuario <= 0 || $idPartida <= 0 || $idFicha <= 0 || ConfiguracionRecintos::porCodigo($codigoRecinto) === null) {
            return $this->error('ERR_PARTIDA_RECINTO');
        }

        try {
            $persistencia = $this->persistencia();
            $this->procesarVencimientos($idUsuario, $idPartida);
            $contexto = $persistencia->obtenerContextoColocacion($idUsuario, $idPartida, $idFicha);
            if ($contexto === null) {
                return $this->error('ERR_PARTIDA_NO_AUTORIZADA');
            }
            if ($contexto['ficha'] === null) {
                return $this->error('ERR_PARTIDA_FICHA');
            }

            $turno = $contexto['turno'];
            if (($contexto['partida']['Estado'] ?? '') !== 'en_juego' || $turno === null || $turno['Fase'] !== 'colocacion') {
                return $this->error('ERR_PARTIDA_FASE');
            }
            foreach ($contexto['jugadas'] as $jugada) {
                if ((int)$jugada['idUsuario'] === $idUsuario && (int)$jugada['TurnoGlobal'] === (int)$turno['TurnoGlobal']) {
                    return $this->error('ERR_PARTIDA_YA_JUGO');
                }
            }

            $tableros = $this->construirTableros($contexto['jugadores'], $contexto['jugadas'], true);
            $tablero = $tableros[$idUsuario] ?? $this->tableroVacio();
            $ficha = $contexto['ficha'];
            $nuevoAdepto = [
                'id_adepto' => (int)$ficha['idAdepto'],
                'especie' => (string)$ficha['Especie'],
                'corriente' => (string)$ficha['Corriente'],
                'es_socratico' => (bool)$ficha['EsSocratico']
            ];
            $ignoraRestriccion = (int)$turno['idJugadorActivo'] === $idUsuario;
            $validacion = (new LogicaRecintos())->validarColocacionDigital(
                $codigoRecinto,
                $nuevoAdepto,
                $tablero,
                $turno['Restriccion'] ?? null,
                $ignoraRestriccion
            );
            if (!$validacion['valido']) {
                return $this->error('ERR_PARTIDA_REGLA', (string)$validacion['mensaje_key']);
            }

            for ($intento = 0; $intento < 3; $intento++) {
                try {
                    $persistencia->colocar(
                        $idUsuario,
                        $idPartida,
                        (int)$contexto['partida']['Version'],
                        (int)$turno['TurnoGlobal'],
                        $idFicha,
                        $codigoRecinto
                    );
                    break;
                } catch (PartidaException $e) {
                    if ($e->getCodigoError() !== 'ERR_CONCURRENCIA' || $intento === 2) {
                        throw $e;
                    }
                    $contexto = $persistencia->obtenerContextoColocacion($idUsuario, $idPartida, $idFicha);
                    if ($contexto === null || $contexto['ficha'] === null || $contexto['turno'] === null
                        || $contexto['turno']['Fase'] !== 'colocacion'
                        || (int)$contexto['turno']['TurnoGlobal'] !== (int)$turno['TurnoGlobal']) {
                        throw new PartidaException('ERR_PARTIDA_FASE');
                    }
                }
            }
            $this->cerrarTurnoSiCorresponde($idUsuario, $idPartida);
            return ['exito' => true];
        } catch (PartidaException $e) {
            return $this->error($e->getCodigoError());
        }
    }

    public function establecerVotoFinalizacionL(int $idUsuario, int $idPartida, bool $voto): array {
        if ($idUsuario <= 0 || $idPartida <= 0) {
            return $this->error('ERR_PARTIDA_NO_EXISTE');
        }

        try {
            $persistencia = $this->persistencia();
            $raw = $persistencia->obtenerEstado($idUsuario, $idPartida);
            if ($raw === null) {
                return $this->error('ERR_PARTIDA_NO_AUTORIZADA');
            }
            if (in_array($raw['partida']['Estado'], ['calculando', 'finalizada'], true)) {
                return ['exito' => true, 'finalizada' => true];
            }
            $resultado = [];
            for ($intento = 0; $intento < 3; $intento++) {
                try {
                    $resultado = $persistencia->establecerVotoFinalizacion(
                        $idUsuario,
                        $idPartida,
                        (int)$raw['partida']['Version'],
                        $voto
                    );
                    break;
                } catch (PartidaException $e) {
                    if ($e->getCodigoError() !== 'ERR_CONCURRENCIA' || $intento === 2) {
                        throw $e;
                    }
                    $raw = $persistencia->obtenerEstado($idUsuario, $idPartida);
                    if ($raw === null || ($raw['partida']['Estado'] ?? '') !== 'en_juego') {
                        return ['exito' => true, 'finalizada' => true];
                    }
                }
            }
            $votos = (int)($resultado['Votos'] ?? 0);
            $total = (int)($resultado['Total'] ?? 0);
            $debeFinalizar = $total > 0 && $votos >= intdiv($total, 2) + 1;
            if ($debeFinalizar) {
                for ($intento = 0; $intento < 3; $intento++) {
                    $actual = $persistencia->obtenerEstado($idUsuario, $idPartida);
                    if ($actual === null || ($actual['partida']['Estado'] ?? '') !== 'en_juego' || $actual['turno'] === null) {
                        break;
                    }
                    $votosActuales = count(array_filter(
                        $actual['jugadores'],
                        static fn(array $jugador): bool => (bool)$jugador['VotoFinalizacion']
                    ));
                    if ($votosActuales < intdiv(count($actual['jugadores']), 2) + 1) {
                        $debeFinalizar = false;
                        break;
                    }
                    $colocacionesActuales = count(array_filter(
                        $actual['jugadas_todas'],
                        static fn(array $jugada): bool => (int)$jugada['TurnoGlobal'] === (int)$actual['turno']['TurnoGlobal']
                    ));
                    if ($colocacionesActuales === count($actual['jugadores'])
                        && $actual['turno']['Fase'] === 'colocacion') {
                        $this->cerrarTurnoSiCorresponde($idUsuario, $idPartida);
                        continue;
                    }
                    try {
                        $persistencia->descartarTurno(
                            $idPartida,
                            (int)$actual['partida']['Version'],
                            (int)$actual['turno']['TurnoGlobal']
                        );
                        break;
                    } catch (PartidaException $e) {
                        if ($e->getCodigoError() !== 'ERR_CONCURRENCIA' || $intento === 2) {
                            throw $e;
                        }
                    }
                }
                $actual = $persistencia->obtenerEstado($idUsuario, $idPartida);
                if ($actual !== null && ($actual['partida']['Estado'] ?? '') === 'calculando') {
                    $persistencia->finalizar($idPartida, (int)$actual['partida']['Version'], $this->crearClasificacion($actual));
                }
            }

            return ['exito' => true, 'finalizada' => $debeFinalizar || !empty($resultado['Terminada'])];
        } catch (PartidaException $e) {
            return $this->error($e->getCodigoError());
        }
    }

    private function elegirRestriccion(): string {
        $restricciones = ConfiguracionJuego::RESTRICCIONES_DADO;
        return $restricciones[random_int(0, count($restricciones) - 1)];
    }

    private function fechaLimite(array $raw, int $segundos): string {
        $turno = $raw['turno'];
        $servidor = $turno === null ? time() : (int)($turno['ServidorEpoch'] ?? time());
        return date('Y-m-d H:i:s', $servidor + $segundos);
    }

    private function procesarVencimientos(int $idUsuario, int $idPartida): void {
        $persistencia = $this->persistencia();
        for ($intento = 0; $intento < 3; $intento++) {
            $raw = $persistencia->obtenerEstado($idUsuario, $idPartida);
            if ($raw === null || ($raw['partida']['Estado'] ?? '') !== 'en_juego' || $raw['turno'] === null) {
                return;
            }
            $turno = $raw['turno'];
            $servidor = (int)($turno['ServidorEpoch'] ?? time());
            try {
                if ($turno['Fase'] === 'esperando_tirada'
                    && $turno['LimiteTiradaEpoch'] !== null
                    && (int)$turno['LimiteTiradaEpoch'] <= $servidor) {
                    $persistencia->aplicarTirada(
                        (int)$turno['idJugadorActivo'],
                        $idPartida,
                        (int)$raw['partida']['Version'],
                        (int)$turno['TurnoGlobal'],
                        $this->elegirRestriccion(),
                        date('Y-m-d H:i:s', $servidor + ConfiguracionJuego::SEGUNDOS_COLOCACION)
                    );
                    continue;
                }

                if ($turno['Fase'] === 'colocacion'
                    && $turno['LimiteColocacionEpoch'] !== null
                    && (int)$turno['LimiteColocacionEpoch'] <= $servidor) {
                    $this->colocarPendientesAutomaticamente($idUsuario, $idPartida, $raw);
                    $this->cerrarTurnoSiCorresponde($idUsuario, $idPartida);
                }
                return;
            } catch (PartidaException $e) {
                if ($e->getCodigoError() !== 'ERR_CONCURRENCIA') {
                    throw $e;
                }
            }
        }
    }

    private function colocarPendientesAutomaticamente(int $idConsulta, int $idPartida, array $raw): void {
        $persistencia = $this->persistencia();
        $turnoGlobal = (int)$raw['turno']['TurnoGlobal'];
        $yaJugaron = [];
        foreach ($raw['jugadas_todas'] as $jugada) {
            if ((int)$jugada['TurnoGlobal'] === $turnoGlobal) {
                $yaJugaron[(int)$jugada['idUsuario']] = true;
            }
        }

        foreach ($raw['jugadores'] as $jugador) {
            $idUsuario = (int)$jugador['idUsuario'];
            if (isset($yaJugaron[$idUsuario])) {
                continue;
            }
            for ($intento = 0; $intento < 3; $intento++) {
                $actual = $persistencia->obtenerEstado($idConsulta, $idPartida);
                if ($actual === null || $actual['turno'] === null || (int)$actual['turno']['TurnoGlobal'] !== $turnoGlobal) {
                    return;
                }
                $fichas = array_values(array_filter(
                    $actual['fichas'],
                    static fn(array $ficha): bool => $ficha['Estado'] === 'mano' && (int)$ficha['idUsuarioPoseedor'] === $idUsuario
                ));
                if ($fichas === []) {
                    throw new PartidaException('ERR_PARTIDA_ESTADO');
                }
                $ficha = $fichas[random_int(0, count($fichas) - 1)];
                try {
                    $persistencia->colocarAutomaticamente(
                        $idUsuario,
                        $idPartida,
                        (int)$actual['partida']['Version'],
                        $turnoGlobal,
                        (int)$ficha['idFicha']
                    );
                    break;
                } catch (PartidaException $e) {
                    if ($e->getCodigoError() !== 'ERR_CONCURRENCIA') {
                        throw $e;
                    }
                }
            }
        }
    }

    private function cerrarTurnoSiCorresponde(int $idUsuario, int $idPartida): void {
        $persistencia = $this->persistencia();
        for ($intento = 0; $intento < 3; $intento++) {
            $raw = $persistencia->obtenerEstado($idUsuario, $idPartida);
            if ($raw === null || ($raw['partida']['Estado'] ?? '') !== 'en_juego' || $raw['turno'] === null) {
                return;
            }
            $turno = $raw['turno'];
            if ($turno['Fase'] !== 'colocacion') {
                return;
            }
            $turnoGlobal = (int)$turno['TurnoGlobal'];
            $colocaciones = 0;
            foreach ($raw['jugadas_todas'] as $jugada) {
                if ((int)$jugada['TurnoGlobal'] === $turnoGlobal) {
                    $colocaciones++;
                }
            }
            if ($colocaciones !== count($raw['jugadores'])) {
                return;
            }

            try {
                $version = (int)$raw['partida']['Version'];
                if ($turnoGlobal >= ConfiguracionJuego::totalTurnos()) {
                    $persistencia->cerrarUltimoTurno($idPartida, $version, $turnoGlobal);
                    return;
                }

                $jugadores = $raw['jugadores'];
                usort($jugadores, static fn(array $a, array $b): int => (int)$a['OrdenJugador'] <=> (int)$b['OrdenJugador']);
                $ids = array_map(static fn(array $jugador): int => (int)$jugador['idUsuario'], $jugadores);
                $indiceActivo = array_search((int)$turno['idJugadorActivo'], $ids, true);
                if ($indiceActivo === false) {
                    throw new PartidaException('ERR_PARTIDA_ESTADO');
                }
                $nuevoGlobal = $turnoGlobal + 1;
                $nuevoTurno = [
                    'global' => $nuevoGlobal,
                    'ronda' => intdiv($nuevoGlobal - 1, ConfiguracionJuego::TURNOS_POR_RONDA) + 1,
                    'numero' => (($nuevoGlobal - 1) % ConfiguracionJuego::TURNOS_POR_RONDA) + 1,
                    'id_activo' => $ids[($indiceActivo + 1) % count($ids)],
                    'limite_tirada' => $this->fechaLimite($raw, ConfiguracionJuego::SEGUNDOS_TIRADA)
                ];

                if ((int)$turno['TurnoRonda'] < ConfiguracionJuego::TURNOS_POR_RONDA) {
                    $siguiente = [];
                    foreach ($ids as $indice => $id) {
                        $siguiente[$id] = $ids[($indice + 1) % count($ids)];
                    }
                    $plan = [];
                    foreach ($raw['fichas'] as $ficha) {
                        if ($ficha['Estado'] === 'mano') {
                            $propietario = (int)$ficha['idUsuarioPoseedor'];
                            $plan[] = ['id_ficha' => (int)$ficha['idFicha'], 'id_poseedor' => $siguiente[$propietario]];
                        }
                    }
                    $persistencia->cerrarTurnoYPasarManos($idPartida, $version, $turnoGlobal, $plan, $nuevoTurno);
                } else {
                    $bolsa = array_values(array_filter($raw['fichas'], static fn(array $ficha): bool => $ficha['Estado'] === 'bolsa'));
                    usort($bolsa, static fn(array $a, array $b): int => (int)$a['NumeroBolsa'] <=> (int)$b['NumeroBolsa']);
                    $necesarias = count($ids) * ConfiguracionJuego::FICHAS_POR_RONDA;
                    if (count($bolsa) < $necesarias) {
                        throw new PartidaException('ERR_PARTIDA_ESTADO');
                    }
                    $plan = [];
                    for ($indice = 0; $indice < $necesarias; $indice++) {
                        $orden = intdiv($indice, ConfiguracionJuego::FICHAS_POR_RONDA);
                        $plan[] = ['id_ficha' => (int)$bolsa[$indice]['idFicha'], 'id_poseedor' => $ids[$orden]];
                    }
                    $persistencia->cerrarTurnoEIniciarRonda($idPartida, $version, $turnoGlobal, $plan, $nuevoTurno);
                }
                return;
            } catch (PartidaException $e) {
                if ($e->getCodigoError() !== 'ERR_CONCURRENCIA') {
                    throw $e;
                }
            }
        }
    }

    private function formatearEstado(int $idUsuario, array $raw): array {
        $tablerosVisibles = $this->construirTableros($raw['jugadores'], $raw['jugadas'], true);
        $tablerosPuntaje = $this->construirTableros($raw['jugadores'], $raw['jugadas'], false);
        $puntajes = (new LogicaRecintos())->calcularPuntajesDigital($tablerosPuntaje);
        $turno = $raw['turno'];
        $estadoPartida = (string)$raw['partida']['Estado'];

        $yaJugo = false;
        if ($turno !== null) {
            foreach ($raw['jugadas'] as $jugada) {
                if ((int)$jugada['idUsuario'] === $idUsuario && (int)$jugada['TurnoGlobal'] === (int)$turno['TurnoGlobal']) {
                    $yaJugo = true;
                    break;
                }
            }
        }

        $jugadores = [];
        foreach ($raw['jugadores'] as $jugador) {
            $id = (int)$jugador['idUsuario'];
            $puntajeActual = $estadoPartida === 'finalizada' && $jugador['PuntajeFinal'] !== null
                ? (int)$jugador['PuntajeFinal']
                : (int)($puntajes[$id]['total'] ?? 0);
            $jugadores[] = [
                'id_usuario' => $id,
                'nombre' => (string)$jugador['Nombre'],
                'orden' => (int)$jugador['OrdenJugador'],
                'puntos' => $puntajeActual,
                'puesto' => $jugador['Puesto'] === null ? null : (int)$jugador['Puesto'],
                'ganador' => (bool)$jugador['EsGanador'],
                'voto_finalizacion' => (bool)$jugador['VotoFinalizacion']
            ];
        }

        $cantidadVotos = count(array_filter($jugadores, static fn(array $jugador): bool => $jugador['voto_finalizacion']));
        $votosRequeridos = intdiv(count($jugadores), 2) + 1;
        $miVoto = false;
        foreach ($jugadores as $jugador) {
            if ($jugador['id_usuario'] === $idUsuario) {
                $miVoto = $jugador['voto_finalizacion'];
                break;
            }
        }

        $mano = array_map(fn(array $ficha): array => [
            'id_ficha' => (int)$ficha['idFicha'],
            'id_adepto' => (int)$ficha['idAdepto'],
            'especie' => (string)$ficha['Especie'],
            'corriente' => (string)$ficha['Corriente'],
            'es_socratico' => (bool)$ficha['EsSocratico'],
            'ruta' => basename((string)$ficha['Ruta'])
        ], $raw['mano']);

        $turnoFormateado = null;
        $servidorEpoch = $turno === null ? time() : (int)($turno['ServidorEpoch'] ?? time());
        if ($turno !== null) {
            $limite = $turno['Fase'] === 'esperando_tirada' ? $turno['LimiteTirada'] : $turno['LimiteColocacion'];
            $limiteEpoch = $turno['Fase'] === 'esperando_tirada'
                ? ($turno['LimiteTiradaEpoch'] ?? null)
                : ($turno['LimiteColocacionEpoch'] ?? null);
            $turnoFormateado = [
                'global' => (int)$turno['TurnoGlobal'], 'ronda' => (int)$turno['Ronda'],
                'numero' => (int)$turno['TurnoRonda'], 'id_activo' => (int)$turno['idJugadorActivo'],
                'fase' => (string)$turno['Fase'], 'restriccion' => $turno['Restriccion'],
                'limite' => $limite,
                'limite_epoch' => $limiteEpoch === null ? null : (int)$limiteEpoch
            ];
        }

        return [
            'version' => (int)$raw['partida']['Version'],
            'servidor_epoch' => $servidorEpoch,
            'partida' => [
                'id' => (int)$raw['partida']['idPartida'], 'estado' => $estadoPartida,
                'id_tablero' => (int)$raw['partida']['idTablero'],
                'nombre_tablero' => (string)($raw['partida']['NombreTablero'] ?? ''),
                'imagen_tablero' => basename((string)($raw['partida']['ImagenTablero'] ?? 'MapaAsistente.png'))
            ],
            'turno' => $turnoFormateado,
            'jugadores' => $jugadores,
            'tableros' => $tablerosVisibles,
            'mano' => $mano,
            'registro' => $this->crearRegistro($raw['turnos'], $raw['jugadas']),
            'recintos' => array_values(ConfiguracionRecintos::todas()),
            'votacion_finalizacion' => [
                'votos' => $cantidadVotos,
                'requeridos' => $votosRequeridos,
                'mi_voto' => $miVoto,
                'habilitada' => $estadoPartida === 'en_juego'
            ],
            'puede_tirar' => $estadoPartida === 'en_juego' && $turno !== null
                && $turno['Fase'] === 'esperando_tirada' && (int)$turno['idJugadorActivo'] === $idUsuario,
            'puede_colocar' => $estadoPartida === 'en_juego' && $turno !== null
                && $turno['Fase'] === 'colocacion' && !$yaJugo
        ];
    }

    private function tableroVacio(): array {
        $tablero = [];
        foreach (ConfiguracionRecintos::todas() as $codigo => $_) { $tablero[$codigo] = []; }
        return $tablero;
    }

    private function construirTableros(array $jugadores, array $jugadas, bool $incluirPendientesPropios): array {
        $tableros = [];
        foreach ($jugadores as $jugador) {
            $tableros[(int)$jugador['idUsuario']] = $this->tableroVacio();
        }
        foreach ($jugadas as $jugada) {
            $revelada = $jugada['FaseTurno'] === 'cerrado';
            if (!$revelada && !$incluirPendientesPropios) { continue; }
            $idUsuario = (int)$jugada['idUsuario'];
            $codigo = (string)$jugada['CodigoRecinto'];
            if (!isset($tableros[$idUsuario][$codigo])) { continue; }
            $tableros[$idUsuario][$codigo][] = [
                'id_jugada' => (int)$jugada['idJugada'], 'id_ficha' => (int)$jugada['idFicha'],
                'id_adepto' => (int)$jugada['idAdepto'], 'especie' => (string)$jugada['Especie'],
                'corriente' => (string)$jugada['Corriente'], 'es_socratico' => (bool)$jugada['EsSocratico'],
                'ruta' => basename((string)$jugada['Ruta']), 'pendiente' => !$revelada,
                'automatica' => (bool)$jugada['FueAutomatica']
            ];
        }
        return $tableros;
    }

    private function crearRegistro(array $turnos, array $jugadas): array {
        $porTurno = [];
        foreach ($jugadas as $jugada) {
            if ($jugada['FaseTurno'] === 'cerrado') {
                $porTurno[(int)$jugada['TurnoGlobal']][] = $jugada;
            }
        }
        $registro = [];
        foreach ($turnos as $turno) {
            $global = (int)$turno['TurnoGlobal'];
            $registro[] = [
                'tipo' => 'activo', 'ronda' => (int)$turno['Ronda'], 'turno' => (int)$turno['TurnoRonda'],
                'nombre' => (string)$turno['NombreActivo']
            ];
            if ($turno['Restriccion'] !== null) {
                $registro[] = ['tipo' => 'dado', 'ronda' => (int)$turno['Ronda'], 'turno' => (int)$turno['TurnoRonda'],
                               'restriccion' => (string)$turno['Restriccion']];
            }
            foreach ($porTurno[$global] ?? [] as $jugada) {
                $registro[] = [
                    'tipo' => 'colocacion', 'ronda' => (int)$jugada['Ronda'], 'turno' => (int)$jugada['TurnoRonda'],
                    'nombre' => (string)$jugada['NombreUsuario'], 'adepto' => (string)$jugada['Especie'],
                    'recinto_codigo' => (string)$jugada['CodigoRecinto'], 'automatica' => (bool)$jugada['FueAutomatica']
                ];
            }
        }
        return $registro;
    }

    private function crearClasificacion(array $raw): array {
        $tableros = $this->construirTableros($raw['jugadores'], $raw['jugadas'], false);
        $puntajes = (new LogicaRecintos())->calcularPuntajesDigital($tableros);
        $filas = [];
        foreach ($raw['jugadores'] as $jugador) {
            $id = (int)$jugador['idUsuario'];
            $filas[] = ['id_usuario' => $id, 'puntos' => (int)$puntajes[$id]['total'],
                        'pasillo' => (int)$puntajes[$id]['pasillo']];
        }
        usort($filas, static function (array $a, array $b): int {
            if ($a['puntos'] !== $b['puntos']) {
                return $b['puntos'] <=> $a['puntos'];
            }
            if ($a['pasillo'] !== $b['pasillo']) {
                return $a['pasillo'] <=> $b['pasillo'];
            }
            return $a['id_usuario'] <=> $b['id_usuario'];
        });

        $puestoAnterior = 0;
        $criterioAnterior = null;
        foreach ($filas as $indice => &$fila) {
            $criterio = $fila['puntos'] . ':' . $fila['pasillo'];
            if ($criterio !== $criterioAnterior) {
                $puestoAnterior = $indice + 1;
                $criterioAnterior = $criterio;
            }
            $fila['puesto'] = $puestoAnterior;
            $fila['ganador'] = $puestoAnterior === 1;
        }
        unset($fila);
        return $filas;
    }
}
