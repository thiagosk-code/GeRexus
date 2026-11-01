<?php

require_once (__DIR__ . '/../Conexion/ConexionBD.php');
require_once (__DIR__ . '/../DTO/UsuarioDTO.php');
require_once (__DIR__ . '/../DTO/AdeptoDTO.php');
require_once (__DIR__ . '/../DTO/TableroDTO.php');
require_once (__DIR__ . '/../DTO/SalaDTO.php');
require_once (__DIR__ . '/SalaException.php');
require_once (__DIR__ . '/IPersistenciaSala.php');

class PersistenciaSala implements IPersistenciaSala {

    private $conn;
    private static ?PersistenciaSala $instancia = null;

    public static function getInstancia(): PersistenciaSala {
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
            error_log("Error de conexion en PersistenciaSala: " . $e->getMessage());
        }
    }

    private function llamar(string $sql, array $params = []): array {
        if ($this->conn === null) {
            throw new SalaException('ERR_BD');
        }

        try {
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $filas = $stmt->columnCount() > 0 ? $stmt->fetchAll(\PDO::FETCH_ASSOC) : [];
            $stmt->closeCursor();
            return $filas;
        } catch (\PDOException $e) {
            $detalle = (string)($e->errorInfo[2] ?? $e->getMessage());
            if (preg_match('/ERR_[A-Z_]+/', $detalle, $coincidencia) === 1) {
                throw new SalaException($coincidencia[0]);
            }
            error_log("Error en PersistenciaSala: " . $e->getMessage());
            throw new SalaException('ERR_BD');
        }
    }

    public function obtenerTableros(): array {
        $lista = [];
        foreach ($this->llamar("CALL sp_catalogo_q_tableros();") as $fila) {
            $lista[] = new TableroDTO((int)$fila['idTablero'], (string)$fila['Nombre'], (string)$fila['Imagen']);
        }
        return $lista;
    }

    public function obtenerAdeptosDisponibles(int $idUsuario): array {
        $lista = [];
        foreach ($this->llamar("CALL sp_catalogo_q_adeptos_disponibles(?);", [$idUsuario]) as $fila) {
            $lista[] = new AdeptoDTO(
                (int)$fila['idAdepto'],
                (string)$fila['Especie'],
                false,
                (string)$fila['Corriente'],
                (string)$fila['Descripcion'],
                (string)($fila['Ruta'] ?? '')
            );
        }
        return $lista;
    }

    public function crearSala(int $idUsuario, string $codigo, int $idTablero, array $idsAdeptos): string {
        $params = array_merge([$idUsuario, $codigo, $idTablero], array_map('intval', array_values($idsAdeptos)));
        $filas = $this->llamar("CALL sp_sala_tx_crear(?, ?, ?, ?, ?, ?, ?, ?, ?);", $params);

        if (count($filas) === 0) {
            throw new SalaException('ERR_BD');
        }
        return (string)$filas[0]['Codigo'];
    }

    public function unirseSala(int $idUsuario, string $codigo, int $versionEsperada, int $maxJugadores): void {
        $this->llamar("CALL sp_sala_tx_unir(?, ?, ?, ?);", [$idUsuario, $codigo, $versionEsperada, $maxJugadores]);
    }

    public function obtenerSalaActivaUsuario(int $idUsuario): ?string {
        $filas = $this->llamar("CALL sp_sala_q_activa_usuario(?);", [$idUsuario]);
        return count($filas) > 0 ? (string)$filas[0]['Codigo'] : null;
    }

    public function obtenerEstadoSala(int $idUsuario, string $codigo): ?SalaDTO {
        $filas = $this->llamar("CALL sp_sala_q_estado(?, ?);", [$idUsuario, $codigo]);
        if (count($filas) === 0) {
            return null;
        }

        $fila = $filas[0];
        $sala = new SalaDTO();
        $sala->setIdSala((int)$fila['idSala']);
        $sala->setCodigo((string)$fila['Codigo']);
        $sala->setIdAnfitrion((int)$fila['idAnfitrion']);
        $sala->setIdTablero((int)$fila['idTablero']);
        $sala->setMaxJugadores((int)($fila['MaxJugadores'] ?? 0));
        $sala->setEstado((string)$fila['Estado']);
        $sala->setIdPartida((int)($fila['idPartida'] ?? 0));
        $sala->setVersion((int)$fila['Version']);
        $sala->setMiEstado((string)$fila['MiEstado']);
        return $sala;
    }

    public function obtenerJugadoresSala(int $idUsuario, string $codigo): array {
        $lista = [];
        foreach ($this->llamar("CALL sp_sala_q_jugadores(?, ?);", [$idUsuario, $codigo]) as $fila) {
            $lista[] = new UsuarioDTO((int)$fila['idUsuario'], (string)$fila['Nombre']);
        }
        return $lista;
    }

    public function obtenerAdeptosSala(int $idUsuario, string $codigo): array {
        $lista = [];
        foreach ($this->llamar("CALL sp_sala_q_adeptos(?, ?);", [$idUsuario, $codigo]) as $fila) {
            $lista[] = new AdeptoDTO(
                (int)$fila['idAdepto'],
                (string)$fila['Especie'],
                false,
                '',
                '',
                (string)($fila['Ruta'] ?? '')
            );
        }
        return $lista;
    }

    public function registrarActividad(int $idUsuario, string $codigo): void {
        $this->llamar("CALL sp_sala_tx_registrar_actividad(?, ?);", [$idUsuario, $codigo]);
    }

    public function salirSala(int $idUsuario, string $codigo, int $versionEsperada): void {
        $this->llamar("CALL sp_sala_tx_salir(?, ?, ?);", [$idUsuario, $codigo, $versionEsperada]);
    }

    public function echarJugadorSala(int $idAnfitrion, string $codigo, int $idJugador, int $versionEsperada): void {
        $this->llamar("CALL sp_sala_tx_expulsar(?, ?, ?, ?);", [$idAnfitrion, $codigo, $idJugador, $versionEsperada]);
    }

    public function cancelarSala(int $idAnfitrion, string $codigo, int $versionEsperada): void {
        $this->llamar("CALL sp_sala_tx_cancelar(?, ?, ?);", [$idAnfitrion, $codigo, $versionEsperada]);
    }

    public function obtenerCandidatosInactivos(int $segundosInactividad, int $segundosVida): array {
        return $this->llamar("CALL sp_sala_q_candidatas_inactivas(?, ?);", [$segundosInactividad, $segundosVida]);
    }

    public function cancelarSalaInactiva(int $idSala, int $versionEsperada): void {
        $this->llamar("CALL sp_sala_tx_cancelar_inactiva(?, ?);", [$idSala, $versionEsperada]);
    }

    public function iniciarSala(int $idAnfitrion, string $codigo, int $versionEsperada, array $jugadores, array $fichas, int $primerActivo, int $segundosTirada): int {
        $filas = $this->llamar(
            "CALL sp_sala_tx_iniciar_partida(?, ?, ?, ?, ?, ?, ?);",
            [$idAnfitrion, $codigo, $versionEsperada, json_encode($jugadores, JSON_THROW_ON_ERROR), json_encode($fichas, JSON_THROW_ON_ERROR), $primerActivo, $segundosTirada]
        );

        if (count($filas) === 0) {
            throw new SalaException('ERR_BD');
        }
        return (int)$filas[0]['idPartida'];
    }
}
