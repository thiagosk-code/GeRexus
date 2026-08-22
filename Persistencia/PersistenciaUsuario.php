<?php

require_once (__DIR__ . '/../Conexion/ConexionBD.php');
require_once (__DIR__ . '/../DTO/UsuarioDTO.php');
require_once (__DIR__ . '/IPersistenciaUsuario.php');

class PersistenciaUsuario implements IPersistenciaUsuario {

    private $conn;
    private static ?PersistenciaUsuario $instancia = null;

    public static function getInstancia(): PersistenciaUsuario {
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
            echo "Error de conexion en PersistenciaUsuario: " . $e->getMessage();
        }
    }

    public function existeEmail(string $email): bool {
        $res = false;
        if ($this->conn !== null) {
            $sql = "SELECT COUNT(*) FROM Usuarios WHERE Email = ? AND (Baja_logica IS NULL OR Baja_logica = false);";
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$email]);
                $conteo = (int) $stmt->fetchColumn();
                $stmt->closeCursor();
                $res = $conteo > 0;
            } catch (\PDOException $e) {
                $res = false;
            }
        }
        return $res;
    }

    public function altaUsuario(UsuarioDTO $usuarioDTO): bool {
        $res = false;
        if ($this->conn !== null && $usuarioDTO !== null) {
            $sql = "CALL sp_InsertarUsuario(?, ?, ?, ?, ?, ?);";

            $nombre = $usuarioDTO->getNombre();
            $email = $usuarioDTO->getEmail();
            $contra = $usuarioDTO->getPassword();
            $monedas = $usuarioDTO->getMonedas();
            $esAdmin = $usuarioDTO->getEsAdmin() ? 1 : 0;
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
        return $res;
    }

    public function modificarUsuario(UsuarioDTO $usuario): bool {
        $res = false;
        if ($this->conn !== null && $usuario !== null) {
            $sql = "CALL sp_ModificarUsuario(?, ?, ?, ?, ?, ?);";

            $idUsuario = $usuario->getIdUsuario();
            $nombre = $usuario->getNombre();
            $email = $usuario->getEmail();
            $contra = $usuario->getPassword();
            $monedas = $usuario->getMonedas();
            $bajaLogica = $usuario->getBajaLogica();

            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$idUsuario, $nombre, $email, $contra, $monedas, $bajaLogica]);
                $stmt->closeCursor();
                $res = true;
            } catch (\PDOException $e) {
                print "Error al actualizar en la base de datos: " . $e->getMessage();
                $res = false;
            }
        }
        return $res;
    }

    public function bajaUsuario(int $idUsuario): bool {
        $res = false;
        if ($this->conn !== null) {
            $sql = "CALL sp_BajaUsuario(?);";

            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$idUsuario]);
                $stmt->closeCursor();
                $res = true;
            } catch (\PDOException $e) {
                print "Error al dar de baja en la base de datos: " . $e->getMessage();
                $res = false;
            }
        }
        return $res;
    }

    public function buscarUsuario(int $idUsuario): ?UsuarioDTO {
        $usuario = null;
        if ($this->conn !== null) {
            $sql = "CALL sp_BuscarUsuario(?);";

            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$idUsuario]);

                $reader = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($reader !== false) {
                    $id = (int)$reader['idUsuario'];
                    $nombre = $reader['Nombre'];
                    $email = $reader['Email'];
                    $contra = $reader['Contra'];
                    $monedas = (int)$reader['Monedas'];
                    $esAdmin = (bool)$reader['esAdmin'];

                    $usuario = new UsuarioDTO($id, $nombre, $email, $contra, 0, $monedas, $esAdmin);
                }
                $stmt->closeCursor();
            } catch (\PDOException $e) {
                print "Error al recuperar datos en la base de datos: " . $e->getMessage();
                $usuario = null;
            }
        }
        return $usuario;
    }
}
?>