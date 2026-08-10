<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_GET['lang'])) {
    $_SESSION['lang'] = ($_GET['lang'] === 'en') ? 'en' : 'es';
}

$lang = (isset($_SESSION['lang']) && $_SESSION['lang'] === 'en') ? 'en' : 'es';

$rutaJson = __DIR__ . "/../Diccionarios/{$lang}.json";

if (!file_exists($rutaJson)) {
    $rutaJson = __DIR__ . "/../Diccionarios/es.json";
}

$jsonTxt = file_get_contents($rutaJson);
$txt = json_decode($jsonTxt, true);
?>