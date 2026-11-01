<?php

require_once __DIR__ . '/ILogicaGacha.php';
require_once __DIR__ . '/ConfiguracionJuego.php';
require_once __DIR__ . '/../Persistencia/FachadaPersistencia.php';

class LogicaGacha implements ILogicaGacha {
    private function persistencia(): IPersistenciaGacha {
        return (new FachadaPersistencia())->retornoIPersistenciaGacha();
    }

    public function obtenerEstadoGachaL(int $idUsuario): array {
        if ($idUsuario <= 0) {
            return ['exito' => false, 'mensaje_key' => 'err_gacha_usuario'];
        }

        $estado = $this->persistencia()->obtenerEstado($idUsuario);
        $estado['costo'] = ConfiguracionJuego::COSTO_GACHA;
        return $estado;
    }

    public function comprarTiradaGachaL(int $idUsuario): array {
        if ($idUsuario <= 0) {
            return ['exito' => false, 'mensaje_key' => 'err_gacha_usuario'];
        }

        $persistencia = $this->persistencia();
        for ($intento = 0; $intento < 3; $intento++) {
            $estado = $persistencia->obtenerEstado($idUsuario);
            if (empty($estado['exito'])) {
                return $estado;
            }

            $bloqueados = array_values(array_filter(
                $estado['adeptos'],
                static fn(array $adepto): bool => empty($adepto['desbloqueado'])
            ));
            if ($bloqueados === []) {
                return ['exito' => false, 'mensaje_key' => 'err_gacha_completo'];
            }
            if ((int)$estado['dracmas'] < ConfiguracionJuego::COSTO_GACHA) {
                return ['exito' => false, 'mensaje_key' => 'err_gacha_dracmas'];
            }

            $elegido = $bloqueados[random_int(0, count($bloqueados) - 1)];
            $resultado = $persistencia->desbloquear(
                $idUsuario,
                (int)$elegido['id_adepto'],
                ConfiguracionJuego::COSTO_GACHA
            );
            if (($resultado['mensaje_key'] ?? '') !== 'err_gacha_tirada') {
                return $resultado;
            }
        }
        return ['exito' => false, 'mensaje_key' => 'err_gacha_tirada'];
    }
}
