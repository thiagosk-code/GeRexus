<?php

require_once (__DIR__ . '/../Conexion/ConexionBD.php');
require_once (__DIR__ . '/IPersistenciaAutorizacion.php');

class PersistenciaAutorizacion implements IPersistenciaAutorizacion {

    private $conn;
    private static ?PersistenciaAutorizacion $instancia = null;

    public static function getInstancia(): PersistenciaAutorizacion {
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
            error_log("Error de conexion en PersistenciaAutorizacion: " . $e->getMessage());
        }
    }

    public function obtenerPermisosDeRol(int $idRol): array {
        $permisos = [];
        if ($this->conn !== null) {
            $sql = "CALL sp_ObtenerPermisosDeRol(?);";

            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$idRol]);
                $resultado = $stmt->fetchAll(\PDO::FETCH_ASSOC);

                foreach ($resultado as $fila) {
                    $permisos[] = $fila['Nombre'];
                }
                $stmt->closeCursor();
            } catch (\PDOException $e) {
                $permisos = [];
            }
        }
        return $permisos;
    }
}