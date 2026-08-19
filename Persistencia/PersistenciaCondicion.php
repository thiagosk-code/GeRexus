<?php

require_once (__DIR__ . '/../DTO/CondicionDTO.php');
require_once ('IPersistenciaCondicion.php');
require_once '../Conexion/ConexionBD.php';

class PersistenciaCondicion implements IPersistenciaCondicion {

    private $conn;
    private $res;

    private static ?PersistenciaCondicion $instancia = null;
    public static function getInstancia(): PersistenciaCondicion {
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

            echo "Error de conexión en PersistenciaCondicion: " . $e->getMessage();
        }
    }
        public function altaCondicion(CondicionDTO $CondicionDTO): bool {
    $res = false;

    if ($this->conn != null) {
        if ($CondicionDTO != null) {
            $sql = "INSERT INTO Condiciones (idCondicion, puntos, nombre, descripcion, Baja_logica) VALUES (?, ?, ?, ?, ?);";
            //revisar nombre entidad y atributos
            $idCondicion = $CondicionDTO->getIdCondicion();
            $puntos = $CondicionDTO->getPuntos();
            $nombre = $CondicionDTO->getNombre();
            $descripcion = $CondicionDTO->getDescripcion();
            $bajaLogica = 0;

            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$nombre, $idCondicion, $puntos, $nombre, $descripcion, $bajaLogica]);
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
    public function modificarCondicion(CondicionDTO $Condicion): bool {

        if ($this->conn != null) {

            if ($Condicion != null) {                
                //revisar nombre de la entidad, revisar si la id debe estar y atributos
                $sql = "UPDATE Condiciones set puntos = ?, nombre = ?, descripcion = ? where idCondicion = ?;";
                $idCondicion = $Condicion->getIdCondicion();
                $puntos = $Condicion->getPuntos();
                $nombre = $Condicion->getNombre();
                $descripcion = $Condicion->getDescripcion();
                try {
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute([$puntos, $nombre, $descripcion, $idCondicion]);
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


    public function bajaCondicion(int $idCondicion): bool {
    $res = false;
    if ($this->conn != null) {
        //revisar nombre de entidad y atributos
        $sql = "UPDATE Condiciones SET Baja_logica = 1 WHERE idCondicion = ?;";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$idCondicion]);
            $stmt->closeCursor();
            $res = true;
        } catch (\PDOException $e) {

            print "Error al actualizar en la base de datos: " . $e->getMessage();
            $res = false;
        }
    }
        return $res;
    }

    public function buscarCondicion(int $idCondicion): CondicionDTO {

    if ($this->conn != null) {
        //revisar nombre de entidad y atributos
        $sql = "SELECT * FROM Condiciones WHERE idCondicion = ?;";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$idCondicion]);

            $reader = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($reader) {
                $idCondicion1 = (int)$reader['idCondicion'];
                $puntos = $reader['puntos'];
                $nombre = $reader['nombre'];
                $descripcion = $reader['descripcion'];

                $Condicion = new CondicionDTO($idCondicion1, $puntos, $nombre, $descripcion);
            }

            $stmt->closeCursor();
            return $Condicion;
        } catch (\PDOException $e) {
            print "Error al recuperar datos en la base de datos: " . $e->getMessage();
            $Condicion = null;
        }
    }
        return $Condicion;
    }
}

?>