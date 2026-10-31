<?php

require_once (__DIR__ . '/../Conexion/ConexionBD.php');
require_once (__DIR__ . '/../DTO/UsuarioDTO.php');
require_once (__DIR__ . '/../DTO/PartidaDTO.php');
require_once (__DIR__ . '/../DTO/HistorialDTO.php');
require_once (__DIR__ . '/IPersistenciaHistorial.php');

class PersistenciaHistorial implements IPersistenciaHistorial {

    private $conn;
    private static ?PersistenciaHistorial $instancia = null;

    public static function getInstancia(): PersistenciaHistorial {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    private function __clone() {}
    public function __wakeup() {}

    private function __construct() {
        try {
            $conexionBD = new ConexionBD();
            $this->conn = $conexionBD->connect();
        } catch (Exception $e) {
            error_log("Error de conexion en PersistenciaHistorial: " . $e->getMessage());
        }
    }

    public function altaHistorial(HistorialDTO $historial): bool {
        $res = false;
        if ($this->conn !== null) {
            $sql = "CALL sp_InsertarHistorial(?, ?, ?, ?, ?, ?);";

            $idUsuario = $historial->getIdUsuario()->getIdUsuario();
            $idPartida = $historial->getIdPartida()->getIdPartida();
            $puesto = $historial->getPuesto();
            $puntosHistoricos = $historial->getPuntosHistoricos();
            $esGanador = $historial->getEsGanador() ? 1 : 0;
            $bajaLogica = 0;

            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$idUsuario, $idPartida, $puesto, $puntosHistoricos, $esGanador, $bajaLogica]);
                $stmt->closeCursor();
                $res = true;
            } catch (\PDOException $e) {
                error_log("Error en altaHistorial: " . $e->getMessage());
                $res = false;
            }
        }
        return $res;
    }

    public function modificarHistorial(HistorialDTO $historial): bool {
        $res = false;
        if ($this->conn !== null) {
            $sql = "CALL sp_ModificarHistorial(?, ?, ?, ?, ?);";

            $idUsuario = $historial->getIdUsuario()->getIdUsuario();
            $idPartida = $historial->getIdPartida()->getIdPartida();
            $puesto = $historial->getPuesto();
            $puntosHistoricos = $historial->getPuntosHistoricos();
            $esGanador = $historial->getEsGanador() ? 1 : 0;

            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$idUsuario, $idPartida, $puesto, $puntosHistoricos, $esGanador]);
                $stmt->closeCursor();
                $res = true;
            } catch (\PDOException $e) {
                error_log("Error en modificarHistorial: " . $e->getMessage());
                $res = false;
            }
        }
        return $res;
    }

    public function bajaHistorial(int $idUsuario, int $idPartida): bool {
        $res = false;
        if ($this->conn !== null) {
            $sql = "CALL sp_BajaHistorial(?, ?);";

            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$idUsuario, $idPartida]);
                $stmt->closeCursor();
                $res = true;
            } catch (\PDOException $e) {
                error_log("Error en bajaHistorial: " . $e->getMessage());
                $res = false;
            }
        }
        return $res;
    }

    public function buscarHistorial(int $idUsuario, int $idPartida): ?HistorialDTO {
        $historial = null;
        if ($this->conn !== null) {
            $sql = "CALL sp_BuscarHistorial(?, ?);";

            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$idUsuario, $idPartida]);
                $reader = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($reader !== false) {
                    $historial = new HistorialDTO(
                        new UsuarioDTO((int)$reader['idUsuario']),
                        new PartidaDTO((int)$reader['idPartida']),
                        (int)$reader['Puesto'],
                        (int)$reader['PuntosHistoricos'],
                        (bool)$reader['esGanador']
                    );
                }
                $stmt->closeCursor();
            } catch (\PDOException $e) {
                error_log("Error en buscarHistorial: " . $e->getMessage());
                $historial = null;
            }
        }
        return $historial;
    }

    public function obtenerHistorialDeUsuario(int $idUsuario, int $limite, int $desplazamiento): array {
        $lista = [];
        if ($this->conn !== null) {
            $sql = "CALL sp_ObtenerHistorialUsuario(?, ?, ?);";

            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$idUsuario, $limite, $desplazamiento]);
                $filas = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                $stmt->closeCursor();

                foreach ($filas as $fila) {
                    $lista[] = new HistorialDTO(
                        new UsuarioDTO($idUsuario),
                        new PartidaDTO((int)$fila['idPartida'], (string)$fila['Fecha'], (int)$fila['JugadoresTotales']),
                        (int)$fila['Puesto'],
                        (int)$fila['PuntosHistoricos'],
                        (bool)$fila['esGanador']
                    );
                }
            } catch (\PDOException $e) {
                error_log("Error en obtenerHistorialDeUsuario: " . $e->getMessage());
                $lista = [];
            }
        }
        return $lista;
    }
}
