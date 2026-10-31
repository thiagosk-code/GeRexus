<?php

require_once __DIR__ . '/../../DTO/UsuarioDTO.php';
require_once __DIR__ . '/../../DTO/PartidaDTO.php';
require_once __DIR__ . '/../../DTO/HistorialDTO.php';
require_once __DIR__ . '/../../DTO/JugadaDTO.php';

function renderPartidaCard(HistorialDTO $historial, array $txt): string {
    $esc = function ($valor): string {
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    };

    $gano = $historial->getEsGanador();
    $clase = $gano ? 'status-victory' : 'status-defeat';
    $estado = $gano ? ($txt['victoria'] ?? 'Victoria') : ($txt['derrota'] ?? 'Derrota');

    return '<div class="game-card">'
        . '<span class="status-txt ' . $clase . '">' . $esc($estado) . '</span>'
        . '<div class="game-details">'
        . '<span>' . $esc($txt['puesto'] ?? 'Puesto:') . ' ' . (int)$historial->getPuesto() . '</span>'
        . '<span>' . $esc($txt['puntos'] ?? 'Puntos:') . ' ' . (int)$historial->getPuntosHistoricos() . '</span>'
        . '</div>'
        . '<hr class="card-divider">'
        . '<button type="button" class="btn-sub-pill btn-moves" data-partida="' . (int)$historial->getIdPartida()->getIdPartida() . '">' . $esc($txt['ver_jugadas'] ?? 'Ver Jugadas') . '</button>'
        . '</div>';
}

function renderJugadaLinea(JugadaDTO $jugada, array $txt): string {
    $plantilla = $txt['jugada_texto'] ?? 'Jugador {jugador} asigna {adepto} a sala {recinto}';
    $texto = strtr($plantilla, [
        '{jugador}' => $jugada->getUsuario()->getNombre(),
        '{adepto}' => $jugada->getAdepto()->getEspecie(),
        '{recinto}' => $jugada->getRecinto()->getNombre()
    ]);

    return '<p>' . htmlspecialchars($texto, ENT_QUOTES, 'UTF-8') . '</p>';
}
