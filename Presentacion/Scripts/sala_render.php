<?php

const SVG_CORONA = '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M3 18 L2 7 L8 12 L12 5 L16 12 L22 7 L21 18 Z" fill="#F2C230" stroke="#8a6a00" stroke-width="1" stroke-linejoin="round"/><rect x="3" y="19" width="18" height="2.5" rx="1" fill="#F2C230" stroke="#8a6a00" stroke-width="1"/></svg>';

const SVG_PUERTA = '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path d="M5 3 H14 V21 H5 Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/><path d="M14 5 L19 7 V19 L14 21 Z" fill="currentColor"/><circle cx="11.5" cy="12" r="1.1" fill="currentColor"/></svg>';

function renderJugadoresSala(array $jugadores, int $idAnfitrion, bool $esAnfitrion, array $txt): string {
    $esc = function ($valor): string {
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    };

    $etiquetaHost = $esc($txt['anfitrion'] ?? 'Anfitrión');
    $etiquetaEchar = $esc($txt['echar_usuario'] ?? 'Echar Usuario');
    $html = '';

    foreach ($jugadores as $jugador) {
        $id = (int)$jugador->getIdUsuario();
        $html .= '<li class="jugador-fila"><span class="jugador-nombre">' . $esc($jugador->getNombre()) . '</span>';

        if ($id === $idAnfitrion) {
            $html .= '<span class="icono-host" role="img" aria-label="' . $etiquetaHost . '" title="' . $etiquetaHost . '">' . SVG_CORONA . '</span>';
        } elseif ($esAnfitrion) {
            $html .= '<button type="button" class="btn-echar" data-open="modal-echar" data-jugador="' . $id . '" aria-label="' . $etiquetaEchar . '" title="' . $etiquetaEchar . '">' . SVG_PUERTA . '</button>';
        }

        $html .= '</li>';
    }

    return $html;
}
