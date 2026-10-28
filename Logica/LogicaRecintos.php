<?php

require_once (__DIR__ . '/../DTO/RecintoDTO.php');
require_once (__DIR__ . '/ILogicaUsuario.php');

class LogicaRecintos implements ILogicaRecintos {

    public function validarReglasAsistente($numRecinto, $nuevoAdepto, $recintoActual): array {
        $corrienteNueva = $nuevoAdepto['corriente'] ?? '';
        
        switch ($numRecinto) {
            case 1:
                if (!empty($recintoActual)) {
                    $corrienteBase = $recintoActual[0]['corriente'] ?? '';
                    if ($corrienteNueva !== $corrienteBase) {
                        return ['valido' => false, 'mensaje' => 'El Congreso solo acepta adeptos de la misma corriente.'];
                    }
                }
                if (count($recintoActual) >= 6) {
                    return ['valido' => false, 'mensaje' => 'El Congreso está lleno (máximo 6).'];
                }
                break;

            case 2:
                if (count($recintoActual) >= 3) {
                    return ['valido' => false, 'mensaje' => 'El Profesorado admite un máximo de 3 adeptos.'];
                }
                break;

            case 3:
                if (count($recintoActual) >= 6) {
                    return ['valido' => false, 'mensaje' => 'La Pradera del Amor está llena.'];
                }
                break;

            case 4:
                if (count($recintoActual) >= 1) {
                    return ['valido' => false, 'mensaje' => 'El Rey Filósofo solo admite 1 adepto.'];
                }
                break;

            case 5:
                foreach ($recintoActual as $ad) {
                    if (($ad['corriente'] ?? '') === $corrienteNueva) {
                        return ['valido' => false, 'mensaje' => 'El Ágora no permite corrientes repetidas.'];
                    }
                }
                if (count($recintoActual) >= 6) {
                    return ['valido' => false, 'mensaje' => 'El Ágora está lleno (máximo 6 adeptos).'];
                }
                break;

            case 6:
                if (count($recintoActual) >= 1) {
                    return ['valido' => false, 'mensaje' => 'El Director solo admite 1 adepto.'];
                }
                break;
        }

        return ['valido' => true, 'mensaje' => ''];
    }

    public function calcularPuntajeAsistente($id, $adeptosRecinto, $parqueCompleto): int {
        if (empty($adeptosRecinto)) return 0;
        $puntos = 0;

        switch ($id) {
            case 1:
                $cant = count($adeptosRecinto);
                $tabla = [1 => 2, 2 => 4, 3 => 8, 4 => 12, 5 => 18, 6 => 24];
                $puntos = $tabla[$cant] ?? (min($cant, 6) * 4);
                break;

            case 2:
                if (count($adeptosRecinto) === 3) $puntos = 7;
                break;

            case 3:
                $disponibles = $adeptosRecinto;
                $parejas = 0;
                $n = count($disponibles);
                $usados = array_fill(0, $n, false);
                for ($i = 0; $i < $n; $i++) {
                    if ($usados[$i]) continue;
                    for ($j = $i + 1; $j < $n; $j++) {
                        if ($usados[$j]) continue;
                        $a = $disponibles[$i];
                        $b = $disponibles[$j];
                        if (($a['especie'] === $b['especie']) || ($a['corriente'] === $b['corriente'])) {
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
                if (count($adeptosRecinto) === 1) {
                    if (!empty($adeptosRecinto[0]['bonusRey'])) {
                        $puntos = 7;
                    }
                }
                break;

            case 5:
                $corrientesUnicas = [];
                foreach ($adeptosRecinto as $a) {
                    if (!empty($a['corriente'])) $corrientesUnicas[$a['corriente']] = true;
                }
                $cant = count($corrientesUnicas);
                $tablaAgora = [1 => 1, 2 => 3, 3 => 6, 4 => 10, 5 => 15, 6 => 21];
                $puntos = $tablaAgora[$cant] ?? 0;
                break;

            case 6:
                if (count($adeptosRecinto) === 1) {
                    $especieObj = $adeptosRecinto[0]['especie'];
                    $totalTablero = 0;
                    for ($r = 1; $r <= 6; $r++) {
                        if (isset($parqueCompleto[$r])) {
                            foreach ($parqueCompleto[$r] as $ad) {
                                if (($ad['especie'] ?? '') === $especieObj) {
                                    $totalTablero++;
                                }
                            }
                        }
                    }
                    if (isset($parqueCompleto['pasillo'])) {
                        foreach ($parqueCompleto['pasillo'] as $ad) {
                            if (($ad['especie'] ?? '') === $especieObj) {
                                $totalTablero++;
                            }
                        }
                    }
                    if ($totalTablero === 1) {
                        $puntos = 7;
                    }
                }
                break;

            case 7:
                $puntos = count($adeptosRecinto);
                break;
        }

        if ($id !== 7) {
            $tieneSocratico = false;
            foreach ($adeptosRecinto as $a) {
                if (!empty($a['esSocratico']) || ($a['corriente'] ?? '') === 'socratica') {
                    $tieneSocratico = true;
                    break;
                }
            }
            if ($tieneSocratico) {
                if ($id === 4 || $id === 6 || $puntos > 0) {
                    $puntos += 1;
                }
            }
        }

        return $puntos;
    }
}