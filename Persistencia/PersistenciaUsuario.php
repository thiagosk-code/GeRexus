<?php

require_once (__DIR__ . '/../DTO/UsuarioDTO.php');
require_once ('IPersistenciaUsuario.php');
require_once '../Conexion/ConexionBD.php';

class PersistenciaUsuario implements IPersistenciaUsuario {

    private $conn;
    private $res;

    private static ?PersistenciaUsuario $instancia = null;
    public static function getInstancia(): PersistenciaUsuario {
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

            echo "Error de conexión en PersistenciaUsuario: " . $e->getMessage();
        }
    }
    
    public function modificarUsuario(UsuarioDTO $usuario): bool {

        if ($this->conn != null) {

            if ($usuario != null) {                

                $sql = "update Usuarios set Nombre = ?, Email = ?, Contra = ?, PartidasGanadas = ?, Monedas = ?, esAdmin = ? where idUsuario = ?;";
                $idUsuario = $usuario->getIdUsuario();
                $nombre = $usuario->getNombre();
                $email = $usuario->getEmail();
                $contra = $usuario->getPassword();
                $partidasGanadas = $usuario->getPartidasGanadas();
                $monedas = $usuario->getMonedas();
                $esAdmin = $usuario->getEsAdmin();
                try {
                    $stmt = $this->conn->prepare($sql);
                    $stmt->execute([$nombre, $email, $contra, $partidasGanadas, $monedas, $esAdmin, $idUsuario]);
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


    public function altaUsuario(UsuarioDTO $usuarioDTO): bool {
    $res = false;

    if ($this->conn != null) {
        if ($usuarioDTO != null) {
            $sql = "INSERT INTO Usuarios (Nombre, Email, Contra, PartidasGanadas, Monedas, esAdmin, Baja_logica) VALUES (?, ?, ?, ?, ?, ?, ?);";
            
            $nombre = $usuarioDTO->getNombre();
            $email = $usuarioDTO->getEmail();
            $contra = $usuarioDTO->getPassword();
            $partidasGanadas = $usuarioDTO->getPartidasGanadas();
            $monedas = $usuarioDTO->getMonedas();
            $esAdmin = $usuarioDTO->getEsAdmin();
            $bajaLogica = 0;

            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$nombre, $email, $contra, $monedas, $esAdmin, $bajaLogica]);
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

    public function bajaUsuario(int $idUsuario): bool {
    $res = false;
    if ($this->conn != null) {

        $sql = "UPDATE Usuarios SET Baja_logica = 1 WHERE idUsuario = ?;";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$idUsuario]);
            $stmt->closeCursor();
            $res = true;
        } catch (\PDOException $e) {

            print "Error al actualizar en la base de datos: " . $e->getMessage();
            $res = false;
        }
    }
        return $res;
    }

    public function buscarUsuario(int $idUsuario): ?UsuarioDTO {

    if ($this->conn != null) {
        $sql = "SELECT * FROM Usuarios WHERE idUsuario = ?;";

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$idUsuario]);

            $reader = $stmt->fetch(\PDO::FETCH_ASSOC);
            if ($reader) {
                $idUsuario1 = (int)$reader['idUsuario'];
                $nombre = $reader['Nombre'];
                $email = $reader['Email'];
                $contra = $reader['Contra'];
                $partidasGanadas = (int)$reader['PartidasGanadas'];
                $monedas = (int)$reader['Monedas'];
                $esAdmin = (bool)$reader['esAdmin'];

                $usuario = new UsuarioDTO($idUsuario1, $nombre, $email, $contra, $partidasGanadas, $monedas, $esAdmin);
            }

            $stmt->closeCursor();
            return $usuario;
        } catch (\PDOException $e) {
            print "Error al recuperar datos en la base de datos: " . $e->getMessage();
            $usuario = null;
        }
    }
        return $usuario;
    }
}

?>