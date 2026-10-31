<?php

require_once (__DIR__ . '/ILogicaRecintos.php');
require_once (__DIR__ . '/ConfiguracionRecintos.php');

class LogicaRecintos implements ILogicaRecintos {

    public function validarReglasAsistente(int $numRecinto, array $nuevoAdepto, array $recintoActual): array {
        $corrienteNueva = (string)($nuevoAdepto['corriente'] ?? '');

        switch ($numRecinto) {
            case 1:
                if (!empty($recintoActual)) {
                    $corrienteBase = (string)($recintoActual[0]['corriente'] ?? '');
                    if ($corrienteNueva !== $corrienteBase) {
                        return ['valido' => false, 'mensaje_key' => 'err_asistente_misma_corriente'];
                    }
                }
                if (count($recintoActual) >= 6) {
                    return ['valido' => false, 'mensaje_key' => 'err_asistente_congreso_lleno'];
                }
                break;

            case 2:
                if (count($recintoActual) >= 3) {
                    return ['valido' => false, 'mensaje_key' => 'err_asistente_profesorado_lleno'];
                }
                break;

            case 3:
                if (count($recintoActual) >= 6) {
                    return ['valido' => false, 'mensaje_key' => 'err_asistente_pradera_llena'];
                }
                break;

            case 4:
                if (count($recintoActual) >= 1) {
                    return ['valido' => false, 'mensaje_key' => 'err_asistente_rey_lleno'];
                }
                break;

            case 5:
                foreach ($recintoActual as $adepto) {
                    if (($adepto['corriente'] ?? '') === $corrienteNueva) {
                        return ['valido' => false, 'mensaje_key' => 'err_asistente_agora_repetida'];
                    }
                }
                if (count($recintoActual) >= 6) {
                    return ['valido' => false, 'mensaje_key' => 'err_asistente_agora_llena'];
                }
                break;

            case 6:
                if (count($recintoActual) >= 1) {
                    return ['valido' => false, 'mensaje_key' => 'err_asistente_director_lleno'];
                }
                break;

            case 7:
                break;

            default:
                return ['valido' => false, 'mensaje_key' => 'err_asistente_recinto_invalido'];
        }

        return ['valido' => true, 'mensaje_key' => ''];
    }

    public function calcularPuntajeAsistente(int $id, array $adeptosRecinto, array $escuelaCompleta): int {
        if (empty($adeptosRecinto)) {
            return 0;
        }

        $puntos = 0;

        switch ($id) {
            case 1:
                $tabla = [1 => 2, 2 => 4, 3 => 8, 4 => 12, 5 => 18, 6 => 24];
                $puntos = $tabla[count($adeptosRecinto)] ?? 24;
                break;

            case 2:
                $puntos = count($adeptosRecinto) === 3 ? 7 : 0;
                break;

            case 3:
                $usados = array_fill(0, count($adeptosRecinto), false);
                $parejas = 0;
                $cantidad = count($adeptosRecinto);

                for ($i = 0; $i < $cantidad; $i++) {
                    if ($usados[$i]) {
                        continue;
                    }
                    for ($j = $i + 1; $j < $cantidad; $j++) {
                        if ($usados[$j]) {
                            continue;
                        }
                        $a = $adeptosRecinto[$i];
                        $b = $adeptosRecinto[$j];
                        if (($a['especie'] ?? '') === ($b['especie'] ?? '') || ($a['corriente'] ?? '') === ($b['corriente'] ?? '')) {
                            $parejas++;
                            $usados[$i] = true;
                            $usados[$j] = true;
                            break;
                        }
                    }
                }
                $puntos = $parejas * 5;
                break;

            case 4:
                $puntos = count($adeptosRecinto) === 1 && !empty($adeptosRecinto[0]['bonusRey']) ? 7 : 0;
                break;

            case 5:
                $corrientes = [];
                foreach ($adeptosRecinto as $adepto) {
                    $corriente = (string)($adepto['corriente'] ?? '');
                    if ($corriente !== '') {
                        $corrientes[$corriente] = true;
                    }
                }
                $tabla = [1 => 1, 2 => 3, 3 => 6, 4 => 10, 5 => 15, 6 => 21];
                $puntos = $tabla[count($corrientes)] ?? 0;
                break;

            case 6:
                if (count($adeptosRecinto) === 1) {
                    $especieObjetivo = (string)($adeptosRecinto[0]['especie'] ?? '');
                    $total = 0;
                    foreach ($escuelaCompleta as $adeptos) {
                        foreach ((array)$adeptos as $adepto) {
                            if (($adepto['especie'] ?? '') === $especieObjetivo) {
                                $total++;
                            }
                        }
                    }
                    $puntos = $total === 1 ? 7 : 0;
                }
                break;

            case 7:
                return count($adeptosRecinto);
        }

        foreach ($adeptosRecinto as $adepto) {
            if (!empty($adepto['esSocratico']) || !empty($adepto['es_socratico']) || ($adepto['corriente'] ?? '') === 'socratica') {
                if ($id === 4 || $id === 6 || $puntos > 0) {
                    $puntos++;
                }
                break;
            }
        }

        return $puntos;
    }

    public function validarColocacionDigital(string $codigoRecinto, array $nuevoAdepto, array $tablero, ?string $restriccion, bool $ignoraRestriccion): array {
        $definicion = ConfiguracionRecintos::porCodigo($codigoRecinto);
        if ($definicion === null) {
            return ['valido' => false, 'mensaje_key' => 'err_asistente_recinto_invalido'];
        }

        if (!$ignoraRestriccion && $restriccion !== null
            && !ConfiguracionRecintos::cumpleRestriccion($restriccion, $codigoRecinto, $tablero)) {
            return ['valido' => false, 'mensaje_key' => 'err_partida_restriccion_dado'];
        }

        return $this->validarReglasAsistente(
            (int)$definicion['id_regla'],
            $nuevoAdepto,
            (array)($tablero[$codigoRecinto] ?? [])
        );
    }

    public function calcularPuntajesDigital(array $tableros): array {
        $resultado = [];

        foreach ($tableros as $idUsuario => $tablero) {
            $puntajes = [];
            foreach (ConfiguracionRecintos::todas() as $codigo => $definicion) {
                $adeptos = array_values((array)($tablero[$codigo] ?? []));

                if ($codigo === 'rey_filosofo' && count($adeptos) === 1) {
                    $especie = (string)($adeptos[0]['especie'] ?? '');
                    $cantidadPropia = $this->contarEspecie((array)$tablero, $especie);
                    $mayoriaEstricta = $especie !== '';

                    foreach ($tableros as $idRival => $tableroRival) {
                        if ((string)$idRival === (string)$idUsuario) {
                            continue;
                        }
                        if ($cantidadPropia <= $this->contarEspecie((array)$tableroRival, $especie)) {
                            $mayoriaEstricta = false;
                            break;
                        }
                    }
                    $adeptos[0]['bonusRey'] = $mayoriaEstricta;
                }

                $puntajes[$codigo] = $this->calcularPuntajeAsistente(
                    (int)$definicion['id_regla'],
                    $adeptos,
                    (array)$tablero
                );
            }
            $resultado[(int)$idUsuario] = [
                'recintos' => $puntajes,
                'total' => array_sum($puntajes),
                'pasillo' => count((array)($tablero['pasillo'] ?? []))
            ];
        }

        return $resultado;
    }

    private function contarEspecie(array $tablero, string $especie): int {
        if ($especie === '') {
            return 0;
        }

        $cantidad = 0;
        foreach ($tablero as $adeptos) {
            foreach ((array)$adeptos as $adepto) {
                if ((string)($adepto['especie'] ?? '') === $especie) {
                    $cantidad++;
                }
            }
        }
        return $cantidad;
    }
}
