<?php

require_once (__DIR__ . '/../Conexion/ConexionBD.php');
require_once (__DIR__ . '/../DTO/UsuarioDTO.php');
require_once (__DIR__ . '/../DTO/RecintoDTO.php');
require_once (__DIR__ . '/../DTO/AdeptoDTO.php');
require_once (__DIR__ . '/../DTO/JugadaDTO.php');
require_once (__DIR__ . '/IPersistenciaJugada.php');

class PersistenciaJugada implements IPersistenciaJugada {

    private $conn;
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
            error_log("Error de conexion en PersistenciaJugada: " . $e->getMessage());
        }
    }

    public function obtenerJugadasDePartida(int $idUsuario, int $idPartida): array {
        $lista = [];
        if ($this->conn !== null) {
            $sql = "CALL sp_historial_q_jugadas_partida(?, ?);";

            try {
                $stmt = $this->conn->prepare($sql);
                $stmt->execute([$idUsuario, $idPartida]);
                $filas = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                $stmt->closeCursor();

                foreach ($filas as $fila) {
                    $usuario = new UsuarioDTO((int)$fila['idUsuario'], (string)$fila['NombreUsuario']);
                    $recinto = new RecintoDTO((int)$fila['idRecinto'], (string)$fila['NombreRecinto'], (string)$fila['Seccion'], (int)$fila['CapacidadMax']);
                    $adepto = new AdeptoDTO(
                        (int)$fila['idAdepto'],
                        (string)$fila['Especie'],
                        false,
                        (string)$fila['Corriente'],
                        (string)$fila['Descripcion'],
                        '',
                        (bool)($fila['EsSocratico'] ?? false)
                    );

                    $lista[] = new JugadaDTO(
                        $usuario,
                        $recinto,
                        $adepto,
                        (int)($fila['TurnoGlobal'] ?? 0),
                        (int)($fila['Ronda'] ?? 0),
                        (int)($fila['TurnoRonda'] ?? 0),
                        (bool)($fila['FueAutomatica'] ?? false),
                        (string)($fila['FechaJugada'] ?? '')
                    );
                }
            } catch (\PDOException $e) {
                error_log("Error en obtenerJugadasDePartida: " . $e->getMessage());
                $lista = [];
            }
        }
        return $lista;
    }
}
