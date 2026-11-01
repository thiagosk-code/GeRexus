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
            error_log("Error de conexion en PersistenciaUsuario: " . $e->getMessage());
        }
    }

    private function contarEmail(string $sql, string $email): int {
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$email]);
        $row = $stmt->fetch(\PDO::FETCH_ASSOC);
        $stmt->closeCursor();
        return ($row !== false && isset($row['total'])) ? (int)$row['total'] : 0;
    }

    public function existeEmail(string $email): bool {
        $res = false;
        if ($this->conn !== null) {
            try {
                $res = $this->contarEmail("CALL sp_usuario_q_contar_email(?);", $email) > 0;
            } catch (\PDOException $e) {
                $res = false;
            }
        }
        return $res;
    }

    public function esEmailBaja(string $email): bool {
        if ($this->conn === null) {
            return false;
        }

        try {
            $total = $this->contarEmail("CALL sp_usuario_q_contar_email(?);", $email);
            $bajas = $this->contarEmail("CALL sp_usuario_q_contar_email_baja(?);", $email);
            return $total > 0 && $total === $bajas;
        } catch (\PDOException $e) {
            return false;
        }
    }

    public function buscarUsuarioBaja(string $email): int {
        if ($this->conn === null) {
            return 0;
        }

        try {
            $stmt = $this->conn->prepare("CALL sp_usuario_q_baja_por_email(?);");
            $stmt->execute([$email]);
            $row = $stmt->fetch(\PDO::FETCH_ASSOC);
            $stmt->closeCursor();
            return ($row !== false && isset($row['idUsuario'])) ? (int)$row['idUsuario'] : 0;
        } catch (\PDOException $e) {
            return 0;
        }
    }

    public function reactivarUsuario(UsuarioDTO $usuario): bool {
        if ($this->conn === null || $usuario->getIdUsuario() <= 0) {
            return false;
        }

        try {
            $stmtUsuario = $this->conn->prepare("CALL sp_usuario_tx_reactivar(?, ?, ?, ?, ?);");
            $stmtUsuario->execute([
                $usuario->getIdUsuario(),
                $usuario->getNombre(),
                $usuario->getEmail(),
                $usuario->getPassword(),
                $usuario->getMonedas()
            ]);
            $stmtUsuario->closeCursor();
            return true;
        } catch (\Throwable $e) {
            error_log("Error al reactivar usuario: " . $e->getMessage());
            return false;
        }
    }

    public function altaUsuario(UsuarioDTO $usuarioDTO): int {
        $idGenerado = 0;
        if ($this->conn !== null && $usuarioDTO !== null) {
            $sql = "CALL sp_usuario_tx_insertar(?, ?, ?, ?, ?, ?);";

            $nom = $usuarioDTO->getNombre();
            $email = $usuarioDTO->getEmail();
            $contra = $usuarioDTO->getPassword();
            $monedas = $usuarioDTO->getMonedas();
            $idRol = $usuarioDTO->getIdRol();
            $bajaLogica = 0;

            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$nom, $email, $contra, $monedas, $idRol, $bajaLogica]);
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
            $sql = "CALL sp_usuario_tx_modificar(?, ?, ?, ?, ?, ?);";

            $idUsuario = $usuario->getIdUsuario();
            $nom = $usuario->getNombre();
            $email = $usuario->getEmail();
            $contra = $usuario->getPassword();
            $monedas = $usuario->getMonedas();
            $bajaLogica = $usuario->getBajaLogica() ? 1 : 0;

            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$idUsuario, $nom, $email, $contra, $monedas, $bajaLogica]);
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
            $sql = "CALL sp_usuario_tx_baja(?);";

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
            $sql = "CALL sp_usuario_q_por_id(?);";

            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$idUsuario]);

                $reader = $stmt->fetch(\PDO::FETCH_ASSOC);
                if ($reader !== false) {
                    $usuario = new UsuarioDTO();
                    $usuario->setIdUsuario((int)$reader['idUsuario']);
                    $usuario->setNombre($reader['Nombre']);
                    $usuario->setEmail($reader['Email']);
                    $usuario->setPassword($reader['Contra']);
                    $usuario->setMonedas((int)$reader['Monedas']);
                    $usuario->setIdRol((int)$reader['idRol']);
                    $usuario->setBajaLogica((bool)$reader['Baja_logica']);
                }
                $stmt->closeCursor();
            } catch (\PDOException $e) {
                $usuario = null;
            }
        }
        return $usuario;
    }

    public function buscarPartidasGanadas(int $idUsuario): int {
        $partidasGanadas = 0;
        if ($this->conn !== null) {
            try {
                $sql = "CALL sp_usuario_q_partidas_ganadas(?, @partidas);";
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$idUsuario]);
                $stmt->closeCursor();

                $stmtResult = $this->conn->query("SELECT @partidas AS partidas;");
                $row = $stmtResult->fetch(\PDO::FETCH_ASSOC);

                if ($row !== false) {
                    $partidasGanadas = (int)$row['partidas'];
                }
                $stmtResult->closeCursor();
            } catch (\PDOException $e) {
                $partidasGanadas = 0;
            }
        }

        return $partidasGanadas;
    }

    public function buscarEmail(string $email): ?UsuarioDTO {
        $usuario = null;
        if ($this->conn !== null) {
            $sql = "CALL sp_usuario_q_por_email(?);";

            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$email]);
                $reader = $stmt->fetch(\PDO::FETCH_ASSOC);

                if ($reader !== false) {
                    $usuario = new UsuarioDTO();
                    $usuario->setIdUsuario((int)$reader['idUsuario']);
                    $usuario->setNombre($reader['Nombre']);
                    $usuario->setEmail($reader['Email']);
                    $usuario->setPassword($reader['Contra']);
                    $usuario->setMonedas((int)$reader['Monedas']);
                    $usuario->setIdRol((int)$reader['idRol']);
                    $usuario->setBajaLogica((bool)$reader['Baja_logica']);
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
            $sql = "CALL sp_usuario_q_todos();";
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
                $dto->setIdRol((int)$fila['idRol']);
                $dto->setBajaLogica((bool)$fila['Baja_logica']);
                $dto->setPartidasGanadas((int)$fila['PartidasGanadas']);

                $lista[] = $dto;
            }
            $stmt->closeCursor();
        }

        return $lista;
    }
}
