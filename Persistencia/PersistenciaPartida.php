<?php

require_once (__DIR__ . '/../Conexion/ConexionBD.php');
require_once (__DIR__ . '/IPersistenciaPartida.php');
require_once (__DIR__ . '/PartidaException.php');

class PersistenciaPartida implements IPersistenciaPartida {
    private const RESTRICCIONES = ['izquierda', 'derecha', 'maestros', 'alumnos', 'vacio', 'sin_socrates'];

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

    private function conexion(): PDO {
        if ($this->conn === null) {
            throw new PartidaException('ERR_BD');
        }
        return $this->conn;
    }

    private function consultar(string $sql, array $params = []): array {
        $stmt = $this->conexion()->prepare($sql);
        $stmt->execute($params);
        $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return $filas;
    }

    private function consultarUna(string $sql, array $params = []): ?array {
        $filas = $this->consultar($sql, $params);
        return $filas[0] ?? null;
    }

    private function ejecutar(string $sql, array $params = []): int {
        $stmt = $this->conexion()->prepare($sql);
        $stmt->execute($params);
        $cantidad = $stmt->rowCount();
        $stmt->closeCursor();
        return $cantidad;
    }

    private function iniciar(): void {
        $conn = $this->conexion();
        if (!$conn->inTransaction()) {
            $conn->beginTransaction();
        }
    }

    private function bloquearPartida(int $idUsuario, int $idPartida): array {
        $partida = $this->consultarUna(
            "SELECT p.idPartida, p.Estado, p.Version, p.JugadoresTotales, p.idTablero
             FROM Partidas p
             INNER JOIN PartidaJugadores pj ON pj.idPartida = p.idPartida AND pj.idUsuario = ?
             WHERE p.idPartida = ? AND (p.Baja_logica IS NULL OR p.Baja_logica = FALSE)
             FOR UPDATE",
            [$idUsuario, $idPartida]
        );
        if ($partida === null) {
            throw new PartidaException('ERR_PARTIDA_NO_AUTORIZADA');
        }
        return $partida;
    }

    private function bloquearPartidaSistema(int $idPartida): array {
        $partida = $this->consultarUna(
            "SELECT idPartida, Estado, Version, JugadoresTotales, idTablero
             FROM Partidas WHERE idPartida = ? FOR UPDATE",
            [$idPartida]
        );
        if ($partida === null) {
            throw new PartidaException('ERR_PARTIDA_NO_EXISTE');
        }
        return $partida;
    }

    private function turnoActual(int $idPartida, bool $bloquear = false): ?array {
        $sufijo = $bloquear ? ' FOR UPDATE' : '';
        return $this->consultarUna(
            "SELECT pt.*,
                    (pt.LimiteTirada IS NOT NULL AND pt.LimiteTirada <= NOW()) AS VencioTirada,
                    (pt.LimiteColocacion IS NOT NULL AND pt.LimiteColocacion <= NOW()) AS VencioColocacion,
                    UNIX_TIMESTAMP(NOW()) AS ServidorEpoch,
                    UNIX_TIMESTAMP(pt.LimiteTirada) AS LimiteTiradaEpoch,
                    UNIX_TIMESTAMP(pt.LimiteColocacion) AS LimiteColocacionEpoch
             FROM PartidaTurnos pt WHERE pt.idPartida = ?
             ORDER BY pt.TurnoGlobal DESC LIMIT 1" . $sufijo,
            [$idPartida]
        );
    }

    public function procesarVencimientos(int $idUsuario, int $idPartida): void {
        $conn = $this->conexion();
        try {
            $this->iniciar();
            $partida = $this->bloquearPartida($idUsuario, $idPartida);
            if ($partida['Estado'] !== 'en_juego') {
                $conn->commit();
                return;
            }

            $turno = $this->turnoActual($idPartida, true);
            if ($turno === null) {
                throw new PartidaException('ERR_PARTIDA_ESTADO');
            }

            if ($turno['Fase'] === 'esperando_tirada' && (int)$turno['VencioTirada'] === 1) {
                $restriccion = self::RESTRICCIONES[random_int(0, count(self::RESTRICCIONES) - 1)];
                $this->ejecutar(
                    "UPDATE PartidaTurnos
                     SET Restriccion = ?, Fase = 'colocacion', FechaTirada = NOW(),
                         LimiteColocacion = DATE_ADD(NOW(), INTERVAL 90 SECOND)
                     WHERE idPartida = ? AND TurnoGlobal = ? AND Fase = 'esperando_tirada'",
                    [$restriccion, $idPartida, (int)$turno['TurnoGlobal']]
                );
                $this->ejecutar("UPDATE Partidas SET Version = Version + 1 WHERE idPartida = ?", [$idPartida]);
            } elseif ($turno['Fase'] === 'colocacion' && (int)$turno['VencioColocacion'] === 1) {
                $this->colocarPendientesAutomaticamente($idPartida, $turno);
                $this->cerrarTurno($idPartida, $turno);
            }

            $conn->commit();
        } catch (PartidaException $e) {
            if ($conn->inTransaction()) { $conn->rollBack(); }
            throw $e;
        } catch (Throwable $e) {
            if ($conn->inTransaction()) { $conn->rollBack(); }
            error_log('Error al procesar vencimientos: ' . $e->getMessage());
            throw new PartidaException('ERR_BD');
        }
    }

    public function tirarDado(int $idUsuario, int $idPartida): void {
        $conn = $this->conexion();
        try {
            $this->iniciar();
            $partida = $this->bloquearPartida($idUsuario, $idPartida);
            $turno = $this->turnoActual($idPartida, true);
            if ($partida['Estado'] !== 'en_juego' || $turno === null || $turno['Fase'] !== 'esperando_tirada') {
                throw new PartidaException('ERR_PARTIDA_FASE');
            }
            if ((int)$turno['idJugadorActivo'] !== $idUsuario) {
                throw new PartidaException('ERR_PARTIDA_NO_ACTIVO');
            }

            $restriccion = self::RESTRICCIONES[random_int(0, count(self::RESTRICCIONES) - 1)];
            $cantidad = $this->ejecutar(
                "UPDATE PartidaTurnos
                 SET Restriccion = ?, Fase = 'colocacion', FechaTirada = NOW(),
                     LimiteColocacion = DATE_ADD(NOW(), INTERVAL 90 SECOND)
                 WHERE idPartida = ? AND TurnoGlobal = ? AND Fase = 'esperando_tirada'",
                [$restriccion, $idPartida, (int)$turno['TurnoGlobal']]
            );
            if ($cantidad !== 1) {
                throw new PartidaException('ERR_PARTIDA_FASE');
            }
            $this->ejecutar("UPDATE Partidas SET Version = Version + 1 WHERE idPartida = ?", [$idPartida]);
            $conn->commit();
        } catch (PartidaException $e) {
            if ($conn->inTransaction()) { $conn->rollBack(); }
            throw $e;
        } catch (Throwable $e) {
            if ($conn->inTransaction()) { $conn->rollBack(); }
            error_log('Error al tirar dado: ' . $e->getMessage());
            throw new PartidaException('ERR_BD');
        }
    }

    public function colocar(int $idUsuario, int $idPartida, int $idFicha, string $codigoRecinto): void {
        $conn = $this->conexion();
        try {
            $this->iniciar();
            $partida = $this->bloquearPartida($idUsuario, $idPartida);
            $turno = $this->turnoActual($idPartida, true);
            if ($partida['Estado'] !== 'en_juego' || $turno === null || $turno['Fase'] !== 'colocacion') {
                throw new PartidaException('ERR_PARTIDA_FASE');
            }
            if ((int)$turno['VencioColocacion'] === 1) {
                throw new PartidaException('ERR_PARTIDA_TIEMPO');
            }

            $yaJugo = $this->consultarUna(
                "SELECT idJugada FROM Jugadas WHERE idPartida = ? AND TurnoGlobal = ? AND idUsuario = ? FOR UPDATE",
                [$idPartida, (int)$turno['TurnoGlobal'], $idUsuario]
            );
            if ($yaJugo !== null) {
                throw new PartidaException('ERR_PARTIDA_YA_JUGO');
            }

            $ficha = $this->consultarUna(
                "SELECT idFicha, idAdepto FROM PartidaFichas
                 WHERE idFicha = ? AND idPartida = ? AND idUsuarioPoseedor = ? AND Estado = 'mano'
                 FOR UPDATE",
                [$idFicha, $idPartida, $idUsuario]
            );
            if ($ficha === null) {
                throw new PartidaException('ERR_PARTIDA_FICHA');
            }

            $recinto = $this->consultarUna(
                "SELECT idRecinto FROM Recintos
                 WHERE Codigo = ? AND (Baja_logica IS NULL OR Baja_logica = FALSE)",
                [$codigoRecinto]
            );
            if ($recinto === null) {
                throw new PartidaException('ERR_PARTIDA_RECINTO');
            }

            $this->ejecutar(
                "INSERT INTO Jugadas
                    (idUsuario, idPartida, idRecinto, idAdepto, TurnoGlobal, Ronda, TurnoRonda, idFicha, FueAutomatica)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, FALSE)",
                [$idUsuario, $idPartida, (int)$recinto['idRecinto'], (int)$ficha['idAdepto'],
                 (int)$turno['TurnoGlobal'], (int)$turno['Ronda'], (int)$turno['TurnoRonda'], $idFicha]
            );
            $this->ejecutar(
                "UPDATE PartidaFichas SET Estado = 'colocada', idUsuarioPoseedor = NULL WHERE idFicha = ?",
                [$idFicha]
            );
            $colocaciones = $this->consultarUna(
                "SELECT COUNT(*) Cantidad FROM Jugadas WHERE idPartida = ? AND TurnoGlobal = ?",
                [$idPartida, (int)$turno['TurnoGlobal']]
            );
            if ((int)($colocaciones['Cantidad'] ?? 0) >= (int)$partida['JugadoresTotales']) {
                $this->cerrarTurno($idPartida, $turno);
            }
            $conn->commit();
        } catch (PartidaException $e) {
            if ($conn->inTransaction()) { $conn->rollBack(); }
            throw $e;
        } catch (PDOException $e) {
            if ($conn->inTransaction()) { $conn->rollBack(); }
            if ((string)$e->getCode() === '23000') {
                throw new PartidaException('ERR_PARTIDA_YA_JUGO');
            }
            error_log('Error al colocar ficha: ' . $e->getMessage());
            throw new PartidaException('ERR_BD');
        } catch (Throwable $e) {
            if ($conn->inTransaction()) { $conn->rollBack(); }
            error_log('Error al colocar ficha: ' . $e->getMessage());
            throw new PartidaException('ERR_BD');
        }
    }

    public function establecerVotoFinalizacion(int $idUsuario, int $idPartida, bool $voto): array {
        $conn = $this->conexion();
        try {
            $this->iniciar();
            $partida = $this->bloquearPartida($idUsuario, $idPartida);

            if (in_array($partida['Estado'], ['calculando', 'finalizada'], true)) {
                $conn->commit();
                return ['cambio' => false, 'debe_finalizar' => false, 'terminada' => true];
            }
            if ($partida['Estado'] !== 'en_juego') {
                throw new PartidaException('ERR_PARTIDA_FASE');
            }

            $jugador = $this->consultarUna(
                "SELECT VotoFinalizacion FROM PartidaJugadores
                 WHERE idPartida = ? AND idUsuario = ? FOR UPDATE",
                [$idPartida, $idUsuario]
            );
            if ($jugador === null) {
                throw new PartidaException('ERR_PARTIDA_NO_AUTORIZADA');
            }

            $valor = $voto ? 1 : 0;
            $cambio = (int)$jugador['VotoFinalizacion'] !== $valor;
            if ($cambio) {
                $this->ejecutar(
                    "UPDATE PartidaJugadores SET VotoFinalizacion = ? WHERE idPartida = ? AND idUsuario = ?",
                    [$valor, $idPartida, $idUsuario]
                );
            }

            $conteo = $this->consultarUna(
                "SELECT COUNT(*) AS Total, SUM(CASE WHEN VotoFinalizacion = TRUE THEN 1 ELSE 0 END) AS Votos
                 FROM PartidaJugadores WHERE idPartida = ?",
                [$idPartida]
            );
            $total = (int)($conteo['Total'] ?? 0);
            $votos = (int)($conteo['Votos'] ?? 0);
            $requeridos = intdiv($total, 2) + 1;
            $debeFinalizar = $total > 0 && $votos >= $requeridos;

            if ($debeFinalizar) {
                $turno = $this->turnoActual($idPartida, true);
                if ($turno === null) {
                    throw new PartidaException('ERR_PARTIDA_ESTADO');
                }

                if ($turno['Fase'] !== 'cerrado') {
                    $this->ejecutar(
                        "UPDATE PartidaFichas f
                         INNER JOIN Jugadas j ON j.idFicha = f.idFicha
                         SET f.Estado = 'mano', f.idUsuarioPoseedor = j.idUsuario
                         WHERE j.idPartida = ? AND j.TurnoGlobal = ?",
                        [$idPartida, (int)$turno['TurnoGlobal']]
                    );
                    $this->ejecutar(
                        "DELETE FROM Jugadas WHERE idPartida = ? AND TurnoGlobal = ?",
                        [$idPartida, (int)$turno['TurnoGlobal']]
                    );
                    $this->ejecutar(
                        "UPDATE PartidaTurnos
                         SET Fase = 'cerrado', FechaCierre = NOW(), LimiteTirada = NULL, LimiteColocacion = NULL
                         WHERE idPartida = ? AND TurnoGlobal = ? AND Fase <> 'cerrado'",
                        [$idPartida, (int)$turno['TurnoGlobal']]
                    );
                }

                $this->ejecutar(
                    "UPDATE Partidas SET Estado = 'calculando', Version = Version + 1 WHERE idPartida = ?",
                    [$idPartida]
                );
            } elseif ($cambio) {
                $this->ejecutar("UPDATE Partidas SET Version = Version + 1 WHERE idPartida = ?", [$idPartida]);
            }

            $conn->commit();
            return [
                'cambio' => $cambio,
                'debe_finalizar' => $debeFinalizar,
                'terminada' => false,
                'votos' => $votos,
                'requeridos' => $requeridos
            ];
        } catch (PartidaException $e) {
            if ($conn->inTransaction()) { $conn->rollBack(); }
            throw $e;
        } catch (Throwable $e) {
            if ($conn->inTransaction()) { $conn->rollBack(); }
            error_log('Error al establecer voto de finalizacion: ' . $e->getMessage());
            throw new PartidaException('ERR_BD');
        }
    }

    private function colocarPendientesAutomaticamente(int $idPartida, array $turno): void {
        $recinto = $this->consultarUna("SELECT idRecinto FROM Recintos WHERE Codigo = 'pasillo'");
        if ($recinto === null) {
            throw new PartidaException('ERR_PARTIDA_RECINTO');
        }

        $pendientes = $this->consultar(
            "SELECT pj.idUsuario FROM PartidaJugadores pj
             WHERE pj.idPartida = ? AND NOT EXISTS (
                 SELECT 1 FROM Jugadas j
                 WHERE j.idPartida = pj.idPartida AND j.TurnoGlobal = ? AND j.idUsuario = pj.idUsuario
             ) ORDER BY pj.OrdenJugador",
            [$idPartida, (int)$turno['TurnoGlobal']]
        );

        foreach ($pendientes as $pendiente) {
            $idUsuario = (int)$pendiente['idUsuario'];
            $ficha = $this->consultarUna(
                "SELECT idFicha, idAdepto FROM PartidaFichas
                 WHERE idPartida = ? AND idUsuarioPoseedor = ? AND Estado = 'mano'
                 ORDER BY RAND() LIMIT 1 FOR UPDATE",
                [$idPartida, $idUsuario]
            );
            if ($ficha === null) {
                throw new PartidaException('ERR_PARTIDA_ESTADO');
            }
            $this->ejecutar(
                "INSERT INTO Jugadas
                    (idUsuario, idPartida, idRecinto, idAdepto, TurnoGlobal, Ronda, TurnoRonda, idFicha, FueAutomatica)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, TRUE)",
                [$idUsuario, $idPartida, (int)$recinto['idRecinto'], (int)$ficha['idAdepto'],
                 (int)$turno['TurnoGlobal'], (int)$turno['Ronda'], (int)$turno['TurnoRonda'], (int)$ficha['idFicha']]
            );
            $this->ejecutar(
                "UPDATE PartidaFichas SET Estado = 'colocada', idUsuarioPoseedor = NULL WHERE idFicha = ?",
                [(int)$ficha['idFicha']]
            );
        }
    }

    private function cerrarTurno(int $idPartida, array $turno): void {
        $turnoGlobal = (int)$turno['TurnoGlobal'];
        $this->ejecutar(
            "UPDATE PartidaTurnos SET Fase = 'cerrado', FechaCierre = NOW()
             WHERE idPartida = ? AND TurnoGlobal = ? AND Fase = 'colocacion'",
            [$idPartida, $turnoGlobal]
        );

        if ($turnoGlobal >= 12) {
            $this->ejecutar(
                "UPDATE Partidas SET Estado = 'calculando', Version = Version + 1 WHERE idPartida = ?",
                [$idPartida]
            );
            return;
        }

        if ((int)$turno['TurnoRonda'] < 6) {
            $jugadores = $this->consultar(
                "SELECT idUsuario, OrdenJugador FROM PartidaJugadores WHERE idPartida = ? ORDER BY OrdenJugador",
                [$idPartida]
            );
            $siguiente = [];
            $cantidad = count($jugadores);
            foreach ($jugadores as $indice => $jugador) {
                $siguiente[(int)$jugador['idUsuario']] = (int)$jugadores[($indice + 1) % $cantidad]['idUsuario'];
            }
            $casos = [];
            $params = [];
            foreach ($siguiente as $actual => $nuevo) {
                $casos[] = 'WHEN ? THEN ?';
                $params[] = $actual;
                $params[] = $nuevo;
            }
            $marcadores = implode(',', array_fill(0, count($siguiente), '?'));
            $params[] = $idPartida;
            foreach (array_keys($siguiente) as $actual) { $params[] = $actual; }
            $this->ejecutar(
                "UPDATE PartidaFichas
                 SET idUsuarioPoseedor = CASE idUsuarioPoseedor " . implode(' ', $casos) . " END
                 WHERE idPartida = ? AND idUsuarioPoseedor IN ($marcadores) AND Estado = 'mano'",
                $params
            );
        } else {
            $cantidad = (int)($this->consultarUna(
                "SELECT COUNT(*) Cantidad FROM PartidaJugadores WHERE idPartida = ?",
                [$idPartida]
            )['Cantidad'] ?? 0);
            $this->ejecutar(
                "UPDATE PartidaFichas f
                 INNER JOIN PartidaJugadores pj
                    ON pj.idPartida = f.idPartida
                   AND f.NumeroBolsa BETWEEN (? + (pj.OrdenJugador - 1) * 6 + 1) AND (? + pj.OrdenJugador * 6)
                 SET f.Estado = 'mano', f.idUsuarioPoseedor = pj.idUsuario, f.RondaAsignada = 2
                 WHERE f.idPartida = ? AND f.Estado = 'bolsa'",
                [$cantidad * 6, $cantidad * 6, $idPartida]
            );
        }

        $activoSiguiente = $this->consultarUna(
            "SELECT siguiente.idUsuario
             FROM PartidaJugadores actual
             INNER JOIN PartidaJugadores siguiente
                ON siguiente.idPartida = actual.idPartida
               AND siguiente.OrdenJugador = CASE
                    WHEN actual.OrdenJugador = (SELECT MAX(x.OrdenJugador) FROM PartidaJugadores x WHERE x.idPartida = actual.idPartida)
                    THEN 1 ELSE actual.OrdenJugador + 1 END
             WHERE actual.idPartida = ? AND actual.idUsuario = ?",
            [$idPartida, (int)$turno['idJugadorActivo']]
        );
        if ($activoSiguiente === null) {
            throw new PartidaException('ERR_PARTIDA_ESTADO');
        }

        $nuevoGlobal = $turnoGlobal + 1;
        $nuevaRonda = $nuevoGlobal <= 6 ? 1 : 2;
        $nuevoTurnoRonda = (($nuevoGlobal - 1) % 6) + 1;
        $this->ejecutar(
            "INSERT INTO PartidaTurnos
                (idPartida, TurnoGlobal, Ronda, TurnoRonda, idJugadorActivo, Fase, LimiteTirada)
             VALUES (?, ?, ?, ?, ?, 'esperando_tirada', DATE_ADD(NOW(), INTERVAL 30 SECOND))",
            [$idPartida, $nuevoGlobal, $nuevaRonda, $nuevoTurnoRonda, (int)$activoSiguiente['idUsuario']]
        );
        $this->ejecutar("UPDATE Partidas SET Version = Version + 1 WHERE idPartida = ?", [$idPartida]);
    }

    public function obtenerEstado(int $idUsuario, int $idPartida): ?array {
        try {
            $partida = $this->consultarUna(
                "SELECT p.idPartida, p.Estado, p.Version, p.JugadoresTotales, p.idTablero,
                        t.Nombre AS NombreTablero, t.Imagen AS ImagenTablero
                 FROM Partidas p
                 INNER JOIN PartidaJugadores pj ON pj.idPartida = p.idPartida AND pj.idUsuario = ?
                 LEFT JOIN Tableros t ON t.idTablero = p.idTablero
                 WHERE p.idPartida = ? AND (p.Baja_logica IS NULL OR p.Baja_logica = FALSE)",
                [$idUsuario, $idPartida]
            );
            if ($partida === null) {
                return null;
            }

            $turno = $this->turnoActual($idPartida, false);
            $jugadores = $this->consultar(
                "SELECT pj.idUsuario, u.Nombre, pj.OrdenJugador, pj.PuntajeFinal, pj.Puesto, pj.EsGanador,
                        pj.VotoFinalizacion
                 FROM PartidaJugadores pj INNER JOIN Usuarios u ON u.idUsuario = pj.idUsuario
                 WHERE pj.idPartida = ? ORDER BY pj.OrdenJugador",
                [$idPartida]
            );
            $jugadas = $this->consultar(
                "SELECT j.idJugada, j.idUsuario, u.Nombre AS NombreUsuario, j.TurnoGlobal, j.Ronda, j.TurnoRonda,
                        j.idFicha, j.FueAutomatica, j.FechaJugada, r.Codigo AS CodigoRecinto,
                        r.Nombre AS NombreRecinto, a.idAdepto, a.Especie, a.Corriente, a.EsSocratico,
                        COALESCE((SELECT i.ruta FROM AdeptosImagenes ai
                                  INNER JOIN Imagenes i ON i.idImagen = ai.idImagen
                                  WHERE ai.idAdepto = a.idAdepto AND i.Tipo = 'adepto'
                                  ORDER BY i.idImagen LIMIT 1), '') AS Ruta,
                        pt.Fase AS FaseTurno
                 FROM Jugadas j
                 INNER JOIN Usuarios u ON u.idUsuario = j.idUsuario
                 INNER JOIN Recintos r ON r.idRecinto = j.idRecinto
                 INNER JOIN Adeptos a ON a.idAdepto = j.idAdepto
                 INNER JOIN PartidaTurnos pt ON pt.idPartida = j.idPartida AND pt.TurnoGlobal = j.TurnoGlobal
                 WHERE j.idPartida = ? AND (pt.Fase = 'cerrado' OR j.idUsuario = ?)
                 ORDER BY j.TurnoGlobal, j.idJugada",
                [$idPartida, $idUsuario]
            );
            $mano = $this->consultar(
                "SELECT f.idFicha, a.idAdepto, a.Especie, a.Corriente, a.EsSocratico,
                        COALESCE((SELECT i.ruta FROM AdeptosImagenes ai
                                  INNER JOIN Imagenes i ON i.idImagen = ai.idImagen
                                  WHERE ai.idAdepto = a.idAdepto AND i.Tipo = 'adepto'
                                  ORDER BY i.idImagen LIMIT 1), '') AS Ruta
                 FROM PartidaFichas f INNER JOIN Adeptos a ON a.idAdepto = f.idAdepto
                 WHERE f.idPartida = ? AND f.idUsuarioPoseedor = ? AND f.Estado = 'mano'
                 ORDER BY f.idFicha",
                [$idPartida, $idUsuario]
            );
            $turnos = $this->consultar(
                "SELECT pt.TurnoGlobal, pt.Ronda, pt.TurnoRonda, pt.idJugadorActivo, u.Nombre AS NombreActivo,
                        pt.Restriccion, pt.Fase, pt.FechaInicio, pt.FechaTirada, pt.LimiteTirada,
                        pt.LimiteColocacion, pt.FechaCierre
                 FROM PartidaTurnos pt INNER JOIN Usuarios u ON u.idUsuario = pt.idJugadorActivo
                 WHERE pt.idPartida = ? ORDER BY pt.TurnoGlobal",
                [$idPartida]
            );

            return ['partida' => $partida, 'turno' => $turno, 'jugadores' => $jugadores,
                    'jugadas' => $jugadas, 'mano' => $mano, 'turnos' => $turnos];
        } catch (Throwable $e) {
            error_log('Error al obtener estado de partida: ' . $e->getMessage());
            throw new PartidaException('ERR_BD');
        }
    }

    public function obtenerContextoColocacion(int $idUsuario, int $idPartida, int $idFicha): ?array {
        $estado = $this->obtenerEstado($idUsuario, $idPartida);
        if ($estado === null) {
            return null;
        }
        $ficha = null;
        foreach ($estado['mano'] as $candidata) {
            if ((int)$candidata['idFicha'] === $idFicha) {
                $ficha = $candidata;
                break;
            }
        }
        $estado['ficha'] = $ficha;
        return $estado;
    }

    public function finalizar(int $idPartida, array $clasificacion): void {
        $conn = $this->conexion();
        try {
            $this->iniciar();
            $partida = $this->bloquearPartidaSistema($idPartida);
            if ($partida['Estado'] === 'finalizada') {
                $conn->commit();
                return;
            }
            if ($partida['Estado'] !== 'calculando') {
                throw new PartidaException('ERR_PARTIDA_FASE');
            }

            foreach ($clasificacion as $fila) {
                $idUsuario = (int)$fila['id_usuario'];
                $puntos = (int)$fila['puntos'];
                $puesto = (int)$fila['puesto'];
                $ganador = !empty($fila['ganador']) ? 1 : 0;
                $this->ejecutar(
                    "UPDATE PartidaJugadores SET PuntajeFinal = ?, Puesto = ?, EsGanador = ?
                     WHERE idPartida = ? AND idUsuario = ?",
                    [$puntos, $puesto, $ganador, $idPartida, $idUsuario]
                );
                $this->ejecutar(
                    "INSERT INTO Historiales
                        (idUsuario, idPartida, Puesto, PuntosHistoricos, esGanador, Baja_logica)
                     VALUES (?, ?, ?, ?, ?, FALSE)
                     ON DUPLICATE KEY UPDATE Puesto = VALUES(Puesto), PuntosHistoricos = VALUES(PuntosHistoricos),
                                             esGanador = VALUES(esGanador), Baja_logica = FALSE",
                    [$idUsuario, $idPartida, $puesto, $puntos, $ganador]
                );
            }
            $this->ejecutar(
                "UPDATE Partidas SET Estado = 'finalizada', FechaFin = NOW(), Version = Version + 1 WHERE idPartida = ?",
                [$idPartida]
            );
            $this->ejecutar(
                "UPDATE Salas SET Estado = 'finalizada', Version = Version + 1 WHERE idPartida = ?",
                [$idPartida]
            );
            $this->ejecutar(
                "UPDATE SalaJugadores sj
                 INNER JOIN Salas s ON s.idSala = sj.idSala
                 SET sj.Estado = 'salio', sj.ActivoUsuario = NULL
                 WHERE s.idPartida = ? AND sj.Estado = 'activo'",
                [$idPartida]
            );
            $conn->commit();
        } catch (PartidaException $e) {
            if ($conn->inTransaction()) { $conn->rollBack(); }
            throw $e;
        } catch (Throwable $e) {
            if ($conn->inTransaction()) { $conn->rollBack(); }
            error_log('Error al finalizar partida: ' . $e->getMessage());
            throw new PartidaException('ERR_BD');
        }
    }
}
