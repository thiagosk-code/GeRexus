<?php

final class ConfiguracionJuego {
    public const MIN_JUGADORES = 2;
    public const MAX_JUGADORES = 5;
    public const ADEPTOS_POR_SALA = 6;
    public const COPIAS_POR_ADEPTO = 10;
    public const FICHAS_POR_RONDA = 6;
    public const RONDAS = 2;
    public const TURNOS_POR_RONDA = 6;
    public const SEGUNDOS_TIRADA = 30;
    public const SEGUNDOS_COLOCACION = 90;
    public const SEGUNDOS_INACTIVIDAD_SALA = 60;
    public const SEGUNDOS_VIDA_SALA = 900;
    public const COSTO_GACHA = 30;
    public const RESTRICCIONES_DADO = ['izquierda', 'derecha', 'maestros', 'alumnos', 'vacio', 'sin_socrates'];

    public static function totalTurnos(): int {
        return self::RONDAS * self::TURNOS_POR_RONDA;
    }

    public static function totalFichas(): int {
        return self::ADEPTOS_POR_SALA * self::COPIAS_POR_ADEPTO;
    }
}
