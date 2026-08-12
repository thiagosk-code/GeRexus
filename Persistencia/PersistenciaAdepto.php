<?php

require_once (__DIR__ . '/../DTO/AdeptoDTO.php');
require_once ('IPersistenciaAdepto.php');
require_once '../Conexion/ConexionBD.php';

class PersistenciaAdepto implements IPersistenciaAdepto {

    private $conn;
    private $res;

    private static ?PersistenciaAdepto $instancia = null;
    public static function getInstancia(): PersistenciaAdepto {
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
            echo "Error de conexión en PersistenciaAdepto: " . $e->getMessage();
        }
    }
    
    public function modificarAdepto(AdeptoDTO $adepto): bool {

        if ($this->conn != null) {

            if ($adepto != null) {
               
                $sql = "UPDATE Adeptos SET Especie = ?, esShiny = ?, Corriente = ?, Descripcion = ? WHERE idAdepto = ?;";
                $idAdepto = $adepto->getIdAdepto();
                $especie = $adepto->getEspecie();
                $esShiny = $adepto->getEsShiny();
                $corriente = $adepto->getCorriente();
                $descripcion = $adepto->getDescripcion();
                try {
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute([$idAdepto, $especie, $esShiny, $corriente, $descripcion]);
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


    public function altaAdepto(AdeptoDTO $adepto): bool {

        if ($this->conn != null) {

            if ($adepto != null) {
                $sql = "INSERT INTO Adeptos (Especie, esShiny, Descripcion, Corriente, Baja_logica) VALUES (?, ?, ?, ?, ?);";
                $especie = $adepto->getEspecie();
                $esShiny = $adepto->getEsShiny();
                $corriente = $adepto->getCorriente();
                $descripcion = $adepto->getDescripcion();
                $bajaLogica = 0;
                try {
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute([$especie, $esShiny, $corriente, $descripcion, $bajaLogica]);
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

    public function bajaAdepto(string $idAdepto): bool {
        $res = false;
        if ($this->conn != null) {

            $sql = "UPDATE Adeptos SET Baja_logica = 1 WHERE idAdepto = ?;";

            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$idAdepto]);
                $stmt->closeCursor();
                $res = true;
            } catch (\PDOException $e) {

                print "Error al actualizar la base de datos: " . $e->getMessage();
                $res = false;
            }
        }
        return $res;
    }

    public function buscarAdepto(string $idAdepto): AdeptoDTO {

        $adepto = new AdeptoDTO();
        if ($this->conn != null) {

            $sql = "SELECT * FROM Adeptos WHERE idAdepto = ?;";

            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$idAdepto]);

                $reader = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($reader) {
                    $idAdepto = $reader['idAdepto'];
                    $especie = $reader['Especie'];
                    $esShiny = $reader['esShiny'];
                    $descripcion = $reader['Descripcion'];
                    $corriente = $reader['Corriente'];
                    //cada nombre dentro de los []
                    $adepto = new AdeptoDTO($idAdepto, $especie, $esShiny, $descripcion, $corriente);
                }
                $stmt->closeCursor();
                return $adepto;
            } catch (\PDOException $e) {

                print "Error al recuperar datos en la base de datos: " . $e->getMessage();
                $adepto = null;
            }
        }
        return $adepto;
    }
}

?>
