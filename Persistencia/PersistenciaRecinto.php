<?php

require_once (__DIR__ . '/../Conexion/ConexionBD.php');
require_once (__DIR__ . '/../DTO/RecintoDTO.php');
require_once (__DIR__ . '/IPersistenciaRecinto.php');

class PersistenciaRecinto implements IPersistenciaRecinto {

    private $conn;
    private $res;

    private static ?PersistenciaRecinto $instancia = null;
    public static function getInstancia(): PersistenciaRecinto {
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
            echo "Error de conexión en PersistenciaRecinto: " . $e->getMessage();
        }
    }
    
    public function modificarRecinto(RecintoDTO $recinto): bool {

        if ($this->conn != null) {

            if ($recinto != null) {
               
                $sql = "UPDATE Recintos SET Nombre = ?, Seccion = ?, CapacidadMax = ? WHERE idRecinto = ?;";
                $idRecinto = $recinto->getIdRecinto();
                $nombre = $recinto->getNombre();
                $seccion = $recinto->getSeccion();
                $capacidadMax = $recinto->getCapacidadMax();
                try {
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute([$idRecinto, $nombre, $seccion, $capacidadMax]);
                    $stmt->closeCursor();
                    $res = true;
                } catch (\PDOException $e) {
                    // Manejo de errores de la base de datos
                    print "Error al guardar en la base de datos: " . $e->getMessage();
                    $res = false;
                }
            }
        }
        return $res;
    }


    public function altaRecinto(RecintoDTO $recinto): bool {

        if ($this->conn != null) {

            if ($recinto != null) {

                $sql = "INSERT INTO Recintos (Nombre, Seccion, CapacidadMax, Baja_logica) VALUES (?, ?, ?, ?, ?);";
                $nombre = $recinto->getNombre();
                $seccion = $recinto->getSeccion();
                $capacidadMax = $recinto->getCapacidadMax();
                $bajaLogica = 0;
                try {
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute([$nombre, $seccion, $capacidadMax, $bajaLogica]);
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

    public function bajaRecinto(string $idRecinto): bool {
        $res = false;
        if ($this->conn != null) {

            $sql = "UPDATE Recintos SET Baja_logica = 1 WHERE idRecinto = ?;";

            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$idRecinto]);
                $stmt->closeCursor();
                $res = true;
            } catch (\PDOException $e) {

                print "Error al actualizar la base de datos: " . $e->getMessage();
                $res = false;
            }
        }
        return $res;
    }

    public function buscarRecinto(string $idRecinto): RecintoDTO {

        $recinto = new RecintoDTO();
        if ($this->conn != null) {

            $sql = "SELECT * FROM Recintos WHERE idRecinto = ?;";

            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$idRecinto]);

                $reader = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($reader) {
                    $idRecinto = $reader['idRecinto'];
                    $nombre = $reader['Nombre'];
                    $seccion = $reader['Seccion'];
                    $capacidadMax = $reader['CapacidadMax'];
                    $recinto = new RecintoDTO($idRecinto, $nombre, $seccion, $capacidadMax);
                }
                $stmt->closeCursor();
                return $recinto;
            } catch (\PDOException $e) {

                print "Error al recuperar datos en la base de datos: " . $e->getMessage();
                $Recinto = null;
            }
        }
        return $recinto;
    }
}

?>
