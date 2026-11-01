<?php

require_once __DIR__ . '/../Conexion/ConexionBD.php';
require_once __DIR__ . '/IPersistenciaGacha.php';

class PersistenciaGacha implements IPersistenciaGacha {
    private ?PDO $conn = null;
    private static ?PersistenciaGacha $instancia = null;

    public static function getInstancia(): PersistenciaGacha {
        if (self::$instancia === null) {
            self::$instancia = new self();
        }
        return self::$instancia;
    }

    private function __construct() {
        try {
            $this->conn = (new ConexionBD())->connect();
        } catch (Throwable $e) {
            error_log('Error de conexión en PersistenciaGacha: ' . $e->getMessage());
        }
    }

    private function __clone() {}

    public function __wakeup() {}

    public function obtenerEstado(int $idUsuario): array {
        if ($this->conn === null || $idUsuario <= 0) {
            return ['exito' => false, 'mensaje_key' => 'err_gacha_estado'];
        }

        try {
            $stmt = $this->conn->prepare('CALL sp_gacha_q_estado(?);');
            $stmt->execute([$idUsuario]);
            $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if ($filas === []) {
                return ['exito' => false, 'mensaje_key' => 'err_gacha_usuario'];
            }

            $adeptos = [];
            foreach ($filas as $fila) {
                $adeptos[] = [
                    'id_adepto' => (int)$fila['idAdepto'],
                    'especie' => (string)$fila['Especie'],
                    'ruta' => (string)($fila['Ruta'] ?? ''),
                    'desbloqueado' => (bool)$fila['Desbloqueado']
                ];
            }

            return [
                'exito' => true,
                'dracmas' => (int)$filas[0]['Monedas'],
                'adeptos' => $adeptos
            ];
        } catch (Throwable $e) {
            error_log('Error al obtener el estado del gacha: ' . $e->getMessage());
            return ['exito' => false, 'mensaje_key' => 'err_gacha_estado'];
        }
    }

    public function desbloquear(int $idUsuario, int $idAdepto, int $costo): array {
        if ($this->conn === null || $idUsuario <= 0 || $idAdepto <= 0 || $costo <= 0) {
            return ['exito' => false, 'mensaje_key' => 'err_gacha_tirada'];
        }

        try {
            $stmt = $this->conn->prepare('CALL sp_gacha_tx_desbloquear(?, ?, ?);');
            $stmt->execute([$idUsuario, $idAdepto, $costo]);
            $fila = $stmt->fetch(PDO::FETCH_ASSOC);
            $stmt->closeCursor();

            if ($fila === false) {
                return ['exito' => false, 'mensaje_key' => 'err_gacha_tirada'];
            }

            $codigo = (string)($fila['Codigo'] ?? 'ERR_GACHA_TIRADA');
            $mensajes = [
                'ERR_GACHA_USUARIO' => 'err_gacha_usuario',
                'ERR_GACHA_COMPLETO' => 'err_gacha_completo',
                'ERR_GACHA_DRACMAS' => 'err_gacha_dracmas',
                'ERR_GACHA_ADEPTO' => 'err_gacha_tirada',
                'ERR_CONCURRENCIA' => 'err_gacha_tirada'
            ];

            if ($codigo !== 'OK') {
                return ['exito' => false, 'mensaje_key' => $mensajes[$codigo] ?? 'err_gacha_tirada'];
            }

            return [
                'exito' => true,
                'mensaje_key' => 'msg_gacha_desbloqueado',
                'dracmas' => (int)$fila['Monedas'],
                'adepto' => [
                    'id_adepto' => (int)$fila['idAdepto'],
                    'especie' => (string)$fila['Especie'],
                    'ruta' => (string)($fila['Ruta'] ?? '')
                ]
            ];
        } catch (Throwable $e) {
            error_log('Error al comprar una tirada del gacha: ' . $e->getMessage());
            return ['exito' => false, 'mensaje_key' => 'err_gacha_tirada'];
        }
    }
}
