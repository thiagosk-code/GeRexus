<?php

require_once (__DIR__ . '/ILogicaPartida.php');
require_once (__DIR__ . '/LogicaRecintos.php');
require_once (__DIR__ . '/ConfiguracionRecintos.php');
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
            $persistencia->procesarVencimientos($idUsuario, $idPartida);
            $raw = $persistencia->obtenerEstado($idUsuario, $idPartida);
            if ($raw === null) {
                return $this->error('ERR_PARTIDA_NO_AUTORIZADA');
            }

            if (($raw['partida']['Estado'] ?? '') === 'calculando') {
                $clasificacion = $this->crearClasificacion($raw);
                $persistencia->finalizar($idPartida, $clasificacion);
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
            $persistencia->procesarVencimientos($idUsuario, $idPartida);
            $persistencia->tirarDado($idUsuario, $idPartida);
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
            $persistencia->procesarVencimientos($idUsuario, $idPartida);
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

            $persistencia->colocar($idUsuario, $idPartida, $idFicha, $codigoRecinto);
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
            $resultado = $persistencia->establecerVotoFinalizacion($idUsuario, $idPartida, $voto);

            if (!empty($resultado['debe_finalizar'])) {
                $raw = $persistencia->obtenerEstado($idUsuario, $idPartida);
                if ($raw !== null && ($raw['partida']['Estado'] ?? '') === 'calculando') {
                    $persistencia->finalizar($idPartida, $this->crearClasificacion($raw));
                }
            }

            return ['exito' => true, 'finalizada' => !empty($resultado['terminada']) || !empty($resultado['debe_finalizar'])];
        } catch (PartidaException $e) {
            return $this->error($e->getCodigoError());
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
