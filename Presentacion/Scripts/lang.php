<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['lang'])) {
    $lang = ($_GET['lang'] === 'en') ? 'en' : 'es';
} elseif (isset($_COOKIE['lang'])) {
    $lang = ($_COOKIE['lang'] === 'en') ? 'en' : 'es';
} elseif (isset($_SESSION['lang'])) {
    $lang = ($_SESSION['lang'] === 'en') ? 'en' : 'es';
} else {
    $lang = 'es';
}

$_SESSION['lang'] = $lang;

function cargarDiccionarioIdioma(string $ruta): ?array {
    if (!is_file($ruta)) {
        return null;
    }

    $contenido = @file_get_contents($ruta);
    if ($contenido === false) {
        return null;
    }

    try {
        $diccionario = json_decode($contenido, true, 512, JSON_THROW_ON_ERROR);
    } catch (JsonException $e) {
        return null;
    }

    return is_array($diccionario) ? $diccionario : null;
}

$rutas = [__DIR__ . "/../Diccionarios/{$lang}.json"];
if ($lang !== 'es') {
    $rutas[] = __DIR__ . '/../Diccionarios/es.json';
}

$txt = null;
foreach ($rutas as $ruta) {
    $txt = cargarDiccionarioIdioma($ruta);
    if ($txt !== null) {
        break;
    }
}

if ($txt === null) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=UTF-8');
    echo 'No se pudo cargar la configuración de idioma.';
    exit;
}
?>
