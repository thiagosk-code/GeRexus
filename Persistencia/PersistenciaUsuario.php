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
            $sql = "CALL sp_ExisteEmailActivo(?);";
            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$email]);
                $conteo = (int) $stmt->fetchColumn();
                $stmt->closeCursor();

                $res = ($conteo > 0);
            } catch (\PDOException $e) {
                $res = false;
            }
        }
        return $res;
    }

    public function altaUsuario(UsuarioDTO $usuarioDTO): int {
        $idGenerado = 0;
        if ($this->conn !== null && $usuarioDTO !== null) {
            $sql = "CALL sp_InsertarUsuario(?, ?, ?, ?, ?, ?);";

            $nombre = $usuarioDTO->getNombre();
            $email = $usuarioDTO->getEmail();
            $contra = $usuarioDTO->getPassword();
            $monedas = $usuarioDTO->getMonedas();
            $esAdmin = $usuarioDTO->getEsAdmin() ? 1 : 0;
            $bajaLogica = false;

            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->bindValue(1, $nombre, \PDO::PARAM_STR);
                $stmt->bindValue(2, $email, \PDO::PARAM_STR);
                $stmt->bindValue(3, $contra, \PDO::PARAM_STR);
                $stmt->bindValue(4, $monedas, \PDO::PARAM_INT);
                $stmt->bindValue(5, $esAdmin, \PDO::PARAM_INT);
                $stmt->bindValue(6, $bajaLogica, \PDO::PARAM_BOOL);
                $stmt->execute();
                $stmt->closeCursor();

                $idGenerado = (int) $this->conn->query("SELECT LAST_INSERT_ID()")->fetchColumn();
            } catch (\PDOException $e) {
                $idGenerado = 0;
            }
        }
        return $idGenerado;
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
                $stmt->bindValue(1, $idUsuario, \PDO::PARAM_INT);
                $stmt->bindValue(2, $nombre, \PDO::PARAM_STR);
                $stmt->bindValue(3, $email, \PDO::PARAM_STR);
                $stmt->bindValue(4, $contra, \PDO::PARAM_STR);
                $stmt->bindValue(5, $monedas, \PDO::PARAM_INT);
                $stmt->bindValue(6, $bajaLogica, \PDO::PARAM_BOOL);
                $stmt->execute();
                $stmt->closeCursor();
                $res = true;
            } catch (\PDOException $e) {
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
                    $baja = isset($reader['Baja_logica']) ? (bool)$reader['Baja_logica'] : false;

                    $usuario = new UsuarioDTO($id, $nombre, $email, $contra, 0, $monedas, $esAdmin);
                    $usuario->setBajaLogica($baja);
                }
                $stmt->closeCursor();
            } catch (\PDOException $e) {
                $usuario = null;
            }
        }
        return $usuario;
    }

    public function obtenerTodosLosUsuarios(): array {
        $lista = [];

        if ($this->conn !== null) {
            $sql = "CALL sp_ObtenerTodosLosUsuarios()";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();
            $resultado = $stmt->fetchAll(\PDO::FETCH_ASSOC);

            foreach ($resultado as $fila) {
                $dto = new UsuarioDTO();
                $dto->setIdUsuario((int)$fila['idUsuario']);
                $dto->setNombre($fila['Nombre']);
                $dto->setEmail($fila['Email']);
                $dto->setPassword($fila['Contra']);
                $dto->setMonedas((int)$fila['Monedas']);
                $dto->setEsAdmin((bool)$fila['esAdmin']);
                $dto->setBajaLogica((bool)$fila['Baja_logica']);
                $dto->setPartidasGanadas((int)$fila['PartidasGanadas']);

                $lista[] = $dto;
            }
        }

        return $lista;
    }
}
?>