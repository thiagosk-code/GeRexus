<?php

final class ConfiguracionRecintos {
    public const PASILLO = 'pasillo';

    private const DEFINICIONES = [
        'congreso' => [
            'id_regla' => 1, 'nombre_key' => 'recinto_congreso', 'nombre_default' => 'El Congreso',
            'regla_key' => 'regla_congreso_corta', 'regla_default' => 'Todos de la misma corriente',
            'x' => 517, 'y' => 464, 'w' => 400, 'h' => 400, 'zona' => 'izquierda', 'categoria' => 'maestros'
        ],
        'profesorado' => [
            'id_regla' => 2, 'nombre_key' => 'recinto_profesorado', 'nombre_default' => 'Profesorado',
            'regla_key' => 'regla_profesorado_corta', 'regla_default' => 'Exactamente 3 adeptos',
            'x' => 515, 'y' => 775, 'w' => 350, 'h' => 350, 'zona' => 'izquierda', 'categoria' => 'maestros'
        ],
        'pradera_amor' => [
            'id_regla' => 3, 'nombre_key' => 'recinto_pradera_amor', 'nombre_default' => 'La Pradera del Amor',
            'regla_key' => 'regla_pradera_amor_corta', 'regla_default' => 'Parejas iguales',
            'x' => 512, 'y' => 1041, 'w' => 380, 'h' => 380, 'zona' => 'izquierda', 'categoria' => 'alumnos'
        ],
        'rey_filosofo' => [
            'id_regla' => 4, 'nombre_key' => 'recinto_rey_filosofo', 'nombre_default' => 'Rey Filósofo',
            'regla_key' => 'regla_rey_filosofo_corta', 'regla_default' => 'Un adepto y mayoría estricta',
            'x' => 1110, 'y' => 522, 'w' => 360, 'h' => 360, 'zona' => 'derecha', 'categoria' => 'alumnos'
        ],
        'agora' => [
            'id_regla' => 5, 'nombre_key' => 'recinto_agora', 'nombre_default' => 'El Ágora',
            'regla_key' => 'regla_agora_corta', 'regla_default' => 'Todas las corrientes distintas',
            'x' => 1102, 'y' => 780, 'w' => 370, 'h' => 370, 'zona' => 'derecha', 'categoria' => 'alumnos'
        ],
        'director' => [
            'id_regla' => 6, 'nombre_key' => 'recinto_director', 'nombre_default' => 'Director',
            'regla_key' => 'regla_director_corta', 'regla_default' => 'Especie única en tu tablero',
            'x' => 1112, 'y' => 1053, 'w' => 310, 'h' => 310, 'zona' => 'derecha', 'categoria' => 'maestros'
        ],
        'pasillo' => [
            'id_regla' => 7, 'nombre_key' => 'recinto_pasillo', 'nombre_default' => 'El Pasillo',
            'regla_key' => 'regla_pasillo_corta', 'regla_default' => 'Un punto por adepto',
            'x' => 810, 'y' => 760, 'w' => 250, 'h' => 1080, 'zona' => 'neutral', 'categoria' => 'neutral'
        ]
    ];

    public static function todas(): array {
        $resultado = [];
        foreach (self::DEFINICIONES as $codigo => $definicion) {
            $resultado[$codigo] = ['codigo' => $codigo] + $definicion;
        }
        return $resultado;
    }

    public static function porCodigo(string $codigo): ?array {
        return isset(self::DEFINICIONES[$codigo]) ? ['codigo' => $codigo] + self::DEFINICIONES[$codigo] : null;
    }

    public static function porIdRegla(int $idRegla): ?array {
        foreach (self::todas() as $definicion) {
            if ($definicion['id_regla'] === $idRegla) {
                return $definicion;
            }
        }
        return null;
    }

    public static function cumpleRestriccion(string $restriccion, string $codigo, array $tablero): bool {
        if ($codigo === self::PASILLO) {
            return true;
        }

        $definicion = self::porCodigo($codigo);
        if ($definicion === null) {
            return false;
        }

        if ($restriccion === 'izquierda' || $restriccion === 'derecha') {
            return $definicion['zona'] === $restriccion;
        }
        if ($restriccion === 'maestros' || $restriccion === 'alumnos') {
            return $definicion['categoria'] === $restriccion;
        }

        $colocados = (array)($tablero[$codigo] ?? []);
        if ($restriccion === 'vacio') {
            return count($colocados) === 0;
        }
        if ($restriccion === 'sin_socrates') {
            foreach ($colocados as $adepto) {
                if (!empty($adepto['es_socratico'])) {
                    return false;
                }
            }
            return true;
        }

        return false;
    }
}
