<?php

require_once (__DIR__ . '/../DTO/JugadaDTO.php');
require_once ('IPersistenciaJugada.php');
require_once '../Conexion/ConexionBD.php';

class PersistenciaJugada implements IPersistenciaJugada {

    private $conn;
    private $res;

    private static ?PersistenciaJugada $instancia = null;
    public static function getInstancia(): PersistenciaJugada {
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
            echo "Error de conexión en PersistenciaJugada: " . $e->getMessage();
        }
    }

    public function modificarJugada(JugadaDTO $jugada): bool {

        if ($this->conn != null) {

            if ($jugada != null) {

                $sql = "UPDATE Jugadas SET idUsuario = ?, idPartida = ?, idRecinto = ?, idAdepto = ? WHERE idJugada = ?;";
                $idJugada = $jugada->getIdJugada();
                $idUsuario = $jugada->getUsuario()->getIdUsuario();
                $idPartida = $jugada->getPartida()->getIdPartida();
                $idRecinto = $jugada->getRecinto()->getIdRecinto();
                $idAdepto = $jugada->getAdepto()->getIdAdepto();
                try {
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute([$idUsuario, $idPartida, $idRecinto, $idAdepto, $idJugada]);
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

    public function altaJugada(JugadaDTO $jugada): bool {

        if ($this->conn != null) {

            if ($jugada != null) {
                $sql = "INSERT INTO Jugadas (idUsuario, idPartida, idRecinto, idAdepto) VALUES (?, ?, ?, ?);";
                $idUsuario = $jugada->getUsuario()->getIdUsuario();
                $idPartida = $jugada->getPartida()->getIdPartida();
                $idRecinto = $jugada->getRecinto()->getIdRecinto();
                $idAdepto = $jugada->getAdepto()->getIdAdepto();
                try {
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute([$idUsuario, $idPartida, $idRecinto, $idAdepto]);
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

    public function bajaJugada(int $idJugada): bool {
        $res = false;
        if ($this->conn != null) {

            $sql = "DELETE FROM Jugadas WHERE idJugada = ?;";

            try {
                
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$idJugada]);
                $stmt->closeCursor();
                $res = true;
            } catch (\PDOException $e) {

                print "Error al actualizar la base de datos: " . $e->getMessage();
                $res = false;
            }
        }
        return $res;
    }

    public function buscarJugada(int $idJugada): ?JugadaDTO {

        $jugada = new JugadaDTO();
        if ($this->conn != null) {

            $sql = "SELECT * FROM Jugadas WHERE idJugada = ?;";

            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$idJugada]);
                $reader = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($reader) {
                    $idJugada = $reader['idJugada'];
                    $idUsuario = $reader['idUsuario'];
                    $idPartida = $reader['idPartida'];
                    $idRecinto = $reader['idRecinto'];
                    $idAdepto = $reader['idAdepto'];
                    $jugada = new JugadaDTO($idJugada, $idUsuario, $idPartida, $idRecinto, $idAdepto);
                }
                $stmt->closeCursor();
                return $jugada;
            } catch (\PDOException $e) {
                print "Error al recuperar datos en la base de datos: " . $e->getMessage();
                $jugada = null;
            }
        }
        return $jugada;
    }
}

?>