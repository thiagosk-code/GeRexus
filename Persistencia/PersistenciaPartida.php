<?php

require_once (__DIR__ . '/../Conexion/ConexionBD.php');
require_once (__DIR__ . '/IPersistenciaPartida.php');
require_once (__DIR__ . '/PartidaException.php');

class PersistenciaPartida implements IPersistenciaPartida {
    private ?PDO $conn = null;
    private static ?PersistenciaPartida $instancia = null;

    public static function getInstancia(): PersistenciaPartida {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    private function __construct() {
        try {
            $this->conn = (new ConexionBD())->connect();
            $this->conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (Throwable $e) {
            error_log('Error de conexion en PersistenciaPartida: ' . $e->getMessage());
        }
    }

    private function __clone() {}
    public function __wakeup() {}

    private function llamar(string $procedimiento, array $parametros = []): array {
        if ($this->conn === null) {
            throw new PartidaException('ERR_BD');
        }
        try {
            $marcadores = implode(', ', array_fill(0, count($parametros), '?'));
            $stmt = $this->conn->prepare('CALL ' . $procedimiento . '(' . $marcadores . ');');
            $stmt->execute($parametros);
            $filas = $stmt->columnCount() > 0 ? $stmt->fetchAll(PDO::FETCH_ASSOC) : [];
            $stmt->closeCursor();
            return $filas;
        } catch (PDOException $e) {
            $detalle = (string)($e->errorInfo[2] ?? $e->getMessage());
            if (preg_match('/ERR_[A-Z_]+/', $detalle, $coincidencia) === 1) {
                throw new PartidaException($coincidencia[0]);
            }
            error_log('Error en PersistenciaPartida: ' . $e->getMessage());
            throw new PartidaException('ERR_BD');
        }
    }

    public function obtenerEstado(int $idUsuario, int $idPartida): ?array {
        $partidas = $this->llamar('sp_partida_q_cabecera', [$idUsuario, $idPartida]);
        if ($partidas === []) {
            return null;
        }
        $turnosActuales = $this->llamar('sp_partida_q_turno_actual', [$idPartida]);
        $fichas = $this->llamar('sp_partida_q_fichas', [$idPartida]);
        $jugadasTodas = $this->llamar('sp_partida_q_jugadas', [$idPartida]);
        $mano = array_values(array_filter(
            $fichas,
            static fn(array $ficha): bool => $ficha['Estado'] === 'mano' && (int)$ficha['idUsuarioPoseedor'] === $idUsuario
        ));
        return [
            'partida' => $partidas[0],
            'turno' => $turnosActuales[0] ?? null,
            'jugadores' => $this->llamar('sp_partida_q_jugadores', [$idPartida]),
            'jugadas' => array_values(array_filter(
                $jugadasTodas,
                static fn(array $jugada): bool => $jugada['FaseTurno'] === 'cerrado' || (int)$jugada['idUsuario'] === $idUsuario
            )),
            'jugadas_todas' => $jugadasTodas,
            'mano' => $mano,
            'fichas' => $fichas,
            'turnos' => $this->llamar('sp_partida_q_turnos', [$idPartida])
        ];
    }

    public function obtenerContextoColocacion(int $idUsuario, int $idPartida, int $idFicha): ?array {
        $estado = $this->obtenerEstado($idUsuario, $idPartida);
        if ($estado === null) {
            return null;
        }
        $estado['ficha'] = null;
        foreach ($estado['mano'] as $ficha) {
            if ((int)$ficha['idFicha'] === $idFicha) {
                $estado['ficha'] = $ficha;
                break;
            }
        }
        return $estado;
    }

    public function aplicarTirada(int $idUsuario, int $idPartida, int $versionEsperada, int $turnoEsperado, string $restriccion, string $limiteColocacion): void {
        $this->llamar('sp_partida_tx_aplicar_tirada', [$idUsuario, $idPartida, $versionEsperada, $turnoEsperado, $restriccion, $limiteColocacion]);
    }

    public function colocar(int $idUsuario, int $idPartida, int $versionEsperada, int $turnoEsperado, int $idFicha, string $codigoRecinto): void {
        $this->llamar('sp_partida_tx_colocar', [$idUsuario, $idPartida, $versionEsperada, $turnoEsperado, $idFicha, $codigoRecinto]);
    }

    public function colocarAutomaticamente(int $idUsuario, int $idPartida, int $versionEsperada, int $turnoEsperado, int $idFicha): void {
        $this->llamar('sp_partida_tx_colocar_automatica', [$idUsuario, $idPartida, $versionEsperada, $turnoEsperado, $idFicha]);
    }

    public function establecerVotoFinalizacion(int $idUsuario, int $idPartida, int $versionEsperada, bool $voto): array {
        $filas = $this->llamar('sp_partida_tx_establecer_voto', [$idUsuario, $idPartida, $versionEsperada, $voto ? 1 : 0]);
        return $filas[0] ?? ['Cambio' => 0, 'Votos' => 0, 'Total' => 0, 'Version' => $versionEsperada, 'Terminada' => 1];
    }

    public function descartarTurno(int $idPartida, int $versionEsperada, int $turnoEsperado): void {
        $this->llamar('sp_partida_tx_descartar_turno', [$idPartida, $versionEsperada, $turnoEsperado]);
    }

    public function cerrarTurnoYPasarManos(int $idPartida, int $versionEsperada, int $turnoEsperado, array $planManos, array $nuevoTurno): void {
        $this->llamar('sp_partida_tx_cerrar_pasar_manos', [
            $idPartida, $versionEsperada, $turnoEsperado,
            json_encode($planManos, JSON_THROW_ON_ERROR),
            $nuevoTurno['global'], $nuevoTurno['ronda'], $nuevoTurno['numero'],
            $nuevoTurno['id_activo'], $nuevoTurno['limite_tirada']
        ]);
    }

    public function cerrarTurnoEIniciarRonda(int $idPartida, int $versionEsperada, int $turnoEsperado, array $planReparto, array $nuevoTurno): void {
        $this->llamar('sp_partida_tx_cerrar_iniciar_ronda', [
            $idPartida, $versionEsperada, $turnoEsperado,
            json_encode($planReparto, JSON_THROW_ON_ERROR),
            $nuevoTurno['global'], $nuevoTurno['ronda'], $nuevoTurno['numero'],
            $nuevoTurno['id_activo'], $nuevoTurno['limite_tirada']
        ]);
    }

    public function cerrarUltimoTurno(int $idPartida, int $versionEsperada, int $turnoEsperado): void {
        $this->llamar('sp_partida_tx_cerrar_final', [$idPartida, $versionEsperada, $turnoEsperado]);
    }

    public function finalizar(int $idPartida, int $versionEsperada, array $clasificacion): void {
        $this->llamar('sp_partida_tx_finalizar', [
            $idPartida, $versionEsperada, json_encode($clasificacion, JSON_THROW_ON_ERROR)
        ]);
    }
}
