<?php

require_once (__DIR__ . '/../DTO/HistorialDTO.php');
require_once '../Conexion/ConexionBD.php';

class PersistenciaHistorial implements IPersistenciaHistorial {

    private $conn;
    private $res;

    private static ?PersistenciaHistorial $instancia = null;

    public static function getInstancia(): PersistenciaHistorial {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    private function __clone() {}
    private function __wakeup() {}

    private function __construct() {
        try {
            $conexionBD = new ConexionBD();
            $this->conn = $conexionBD->connect();
        } catch (Exception $e) {
            echo "Error de conexión en PersistenciaHistorial: " . $e->getMessage();
        }
    }

    public function altaHistorial(HistorialDTO $historialDTO): bool {
        $res = false;

        if ($this->conn != null) {
            if ($historialDTO != null) {
                $sql = "INSERT INTO Historiales (idUsuario, idPartida, Puesto, PuntosHistoricos, esGanador, Baja_logica) VALUES (?, ?, ?, ?, ?, ?);";

                $idUsuario = $historialDTO->getIdUsuario();
                $idPartida = $historialDTO->getIdPartida();
                $puesto = $historialDTO->getPuesto();
                $puntosHistoricos = $historialDTO->getPuntosHistoricos();
                $esGanador = $historialDTO->getEsGanador() ? 1 : 0;
                $bajaLogica = 0;

                try {
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute([$idUsuario, $idPartida, $puesto, $puntosHistoricos, $esGanador, $bajaLogica]);
                    $stmt->closeCursor();
                    $res = true;
                } catch (\PDOException $e) {
                    print "Error al guardar en la base de datos: " . $e->getMessage();
                    $res = false;
                }
            }
        }

        return $res;
    }

    public function modificarHistorial(HistorialDTO $historial): bool {
        $res = false;
        if ($this->conn != null) {
            if ($historial != null) {
                $sql = "UPDATE Historiales SET Puesto = ?, PuntosHistoricos = ?, esGanador = ? WHERE idUsuario = ? AND idPartida = ?;";

                $idUsuario = $historial->getIdUsuario();
                $idPartida = $historial->getIdPartida();
                $puesto = $historial->getPuesto();
                $puntosHistoricos = $historial->getPuntosHistoricos();
                $esGanador = $historial->getEsGanador() ? 1 : 0;

                try {
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute([$puesto, $puntosHistoricos, $esGanador, $idUsuario, $idPartida]);
                    $stmt->closeCursor();
                    $res = true;
                } catch (\PDOException $e) {
                    print "Error al guardar en la base de datos: " . $e->getMessage();
                    $res = false;
                }
            }
        }
        return $res;
    }

    public function bajaHistorial(int $idUsuario, int $idPartida): bool {
        $res = false;
        if ($this->conn != null) {
            $sql = "UPDATE Historiales SET Baja_logica = 1 WHERE idUsuario = ? AND idPartida = ?;";

            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$idUsuario, $idPartida]);
                $stmt->closeCursor();
                $res = true;
            } catch (\PDOException $e) {
                print "Error al actualizar en la base de datos: " . $e->getMessage();
                $res = false;
            }
        }
        return $res;
    }

    public function buscarHistorial(int $idUsuario, int $idPartida): ?HistorialDTO {
        $historial = null;

        if ($this->conn != null) {
            $sql = "SELECT * FROM Historiales WHERE idUsuario = ? AND idPartida = ?;";

            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$idUsuario, $idPartida]);

                $reader = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($reader) {
                    $idUsuario = (int)$reader['idUsuario'];
                    $idPartida = (int)$reader['idPartida'];
                    $puesto = (int)$reader['Puesto'];
                    $puntosHistoricos = (int)$reader['PuntosHistoricos'];
                    $esGanador = (bool)$reader['esGanador'];

                    $historial = new HistorialDTO($idUsuario, $idPartida, $puesto, $puntosHistoricos, $esGanador);
                }

                $stmt->closeCursor();
                return $historial;
            } catch (\PDOException $e) {
                print "Error al recuperar datos en la base de datos: " . $e->getMessage();
                $historial = null;
            }
        }
        return $historial;
    }
}
?>