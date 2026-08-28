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

$rutaJson = __DIR__ . "/../Diccionarios/{$lang}.json";

if (!file_exists($rutaJson)) {
    $rutaJson = __DIR__ . "/../Diccionarios/es.json";
}

$jsonTxt = file_get_contents($rutaJson);
$txt = json_decode($jsonTxt, true);
?>