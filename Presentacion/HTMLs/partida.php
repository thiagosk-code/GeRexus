<?php
require_once __DIR__ . '/../Scripts/auth_check.php';
require_once __DIR__ . '/../Scripts/lang.php';
require_once __DIR__ . '/../../Logica/ConfiguracionRecintos.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$idPartida = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if ($idPartida === false || $idPartida <= 0) {
    header('Location: digital.php');
    exit;
}

$idUsuario = (int)$usuarioActual->getIdUsuario();
$estadoInicial = $fachadaLogica->retornoILogicaPartida()->obtenerEstadoPartidaL($idUsuario, $idPartida, 0);
if ($estadoInicial['exito'] === false) {
    header('Location: digital.php');
    exit;
}

$textosPartida = [];
$claves = [
    'jugadores', 'historial_jugadas', 'tu_mano', 'dado_de', 'ronda', 'turno', 'tirar_dado',
    'esperando_tirada', 'esperando_jugadores', 'colocados', 'seleccionar', 'cerrar', 'puntos',
    'partida_finalizada', 'volver_modo_digital', 'ver_historial', 'puesto', 'victoria', 'derrota',
    'registro_activo', 'registro_dado', 'registro_colocacion', 'registro_automatica',
    'restriccion_izquierda', 'restriccion_derecha', 'restriccion_maestros', 'restriccion_alumnos',
    'restriccion_vacio', 'restriccion_sin_socrates', 'sin_restriccion', 'segundos_restantes',
    'decision_confirmada', 'tablero_ajeno', 'err_cargar', 'recinto_congreso', 'recinto_profesorado',
    'recinto_pradera_amor', 'recinto_rey_filosofo', 'recinto_agora', 'recinto_director', 'recinto_pasillo',
    'err_csrf', 'err_partida_accion', 'err_partida_no_autorizada', 'err_partida_no_existe',
    'err_partida_estado', 'err_partida_fase', 'err_partida_no_activo', 'err_partida_tiempo',
    'err_partida_ya_jugo', 'err_partida_ficha', 'err_partida_recinto', 'err_partida_restriccion_dado',
    'err_partida_voto', 'votar_finalizacion', 'retirar_voto_finalizacion', 'votos_finalizacion_estado',
    'voto_finalizacion_marcado',
    'err_asistente_misma_corriente', 'err_asistente_congreso_lleno', 'err_asistente_profesorado_lleno',
    'err_asistente_pradera_llena', 'err_asistente_rey_lleno', 'err_asistente_agora_repetida',
    'err_asistente_agora_llena', 'err_asistente_director_lleno', 'abrir_jugadores',
    'abrir_historial', 'cerrar_panel'
];
foreach (ConfiguracionRecintos::todas() as $recinto) {
    $claves[] = $recinto['regla_key'];
}
foreach ($claves as $clave) { $textosPartida[$clave] = $txt[$clave] ?? $clave; }
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang ?? 'es', ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeRexus - <?php echo htmlspecialchars($txt['partida_digital'] ?? 'Partida Digital', ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="icon" type="image/png" href="../../Assets/Socrates.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Michroma&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0">
    <link rel="stylesheet" href="../CSSs/style.css">
    <link rel="stylesheet" href="../CSSs/partida.css">
</head>
<body class="partida-page">
    <header class="main-header partida-header">
        <a href="base.php" class="logo-container"><span class="logo-img"></span><span class="logo-text">GeRexus</span></a>
        <div class="header-actions">
            <div class="leng-switcher">
                <?php if (($lang ?? 'es') === 'en'): ?>
                    <a href="?id=<?php echo (int)$idPartida; ?>&lang=es" class="inactive">Esp</a><span class="divider">|</span><span class="active">Eng</span>
                <?php else: ?>
                    <span class="active">Esp</span><span class="divider">|</span><a href="?id=<?php echo (int)$idPartida; ?>&lang=en" class="inactive">Eng</a>
                <?php endif; ?>
            </div>
            <button class="tema-toggle" aria-label="Cambiar tema"><span class="material-symbols-outlined">light_mode</span></button>
            <a href="cuenta.php" class="user-profile-link"><div class="user-profile"><span class="user-name"><?php echo htmlspecialchars($nombreUsuarioLogueado ?? 'User', ENT_QUOTES, 'UTF-8'); ?></span><span class="user-avatar-img" id="header-avatar-img"></span></div></a>
        </div>
    </header>

    <main class="partida-layout" id="partida-main">
        <nav class="partida-mobile-nav" aria-label="<?php echo htmlspecialchars($txt['partida_digital'] ?? 'Partida Digital', ENT_QUOTES, 'UTF-8'); ?>">
            <button type="button" class="btn-panel-movil" id="abrir-panel-jugadores" aria-controls="panel-jugadores-partida" aria-expanded="false"><?php echo htmlspecialchars($txt['abrir_jugadores'] ?? 'Jugadores', ENT_QUOTES, 'UTF-8'); ?></button>
            <button type="button" class="btn-panel-movil" id="abrir-panel-registro" aria-controls="panel-registro-partida" aria-expanded="false"><?php echo htmlspecialchars($txt['abrir_historial'] ?? 'Historial', ENT_QUOTES, 'UTF-8'); ?></button>
        </nav>

        <aside class="partida-panel jugadores-panel" id="panel-jugadores-partida">
            <button type="button" class="cerrar-panel-movil" data-close-game-panel aria-label="<?php echo htmlspecialchars($txt['cerrar_panel'] ?? 'Cerrar panel', ENT_QUOTES, 'UTF-8'); ?>">&times;</button>
            <h2><?php echo htmlspecialchars($txt['jugadores'] ?? 'Jugadores', ENT_QUOTES, 'UTF-8'); ?></h2>
            <ul id="partida-jugadores"></ul>
            <div class="votacion-finalizacion" id="votacion-finalizacion">
                <p id="votos-finalizacion-estado"></p>
                <button type="button" class="btn-partida" id="btn-votar-finalizacion"></button>
            </div>
        </aside>

        <section class="tablero-area">
            <div class="tablero-cabecera">
                <button type="button" class="flecha-tablero" id="tablero-anterior" aria-label="Anterior">&#10094;</button>
                <h1 id="tablero-propietario"></h1>
                <button type="button" class="flecha-tablero" id="tablero-siguiente" aria-label="Siguiente">&#10095;</button>
            </div>
            <div class="mapa-partida" id="mapa-partida">
                <img id="mapa-imagen" src="../../Assets/MapaAsistente.png" alt="" draggable="false">
                <?php foreach (ConfiguracionRecintos::todas() as $codigo => $recinto): ?>
                    <button type="button" class="recinto-partida" data-recinto="<?php echo htmlspecialchars($codigo, ENT_QUOTES, 'UTF-8'); ?>"
                        aria-label="<?php echo htmlspecialchars($txt[$recinto['nombre_key']] ?? $recinto['nombre_default'], ENT_QUOTES, 'UTF-8'); ?>"
                        style="left: <?php echo round($recinto['x'] / 16, 2); ?>%; top: <?php echo round($recinto['y'] / 16, 2); ?>%; width: <?php echo round($recinto['w'] / 16, 2); ?>%; height: <?php echo round($recinto['h'] / 16, 2); ?>%;">
                        <span class="recinto-fichas" aria-hidden="true"></span>
                    </button>
                <?php endforeach; ?>
            </div>
        </section>

        <aside class="partida-panel registro-panel" id="panel-registro-partida">
            <button type="button" class="cerrar-panel-movil" data-close-game-panel aria-label="<?php echo htmlspecialchars($txt['cerrar_panel'] ?? 'Cerrar panel', ENT_QUOTES, 'UTF-8'); ?>">&times;</button>
            <h2><?php echo htmlspecialchars($txt['historial_jugadas'] ?? 'Historial de Jugadas', ENT_QUOTES, 'UTF-8'); ?></h2>
            <ol id="partida-registro"></ol>
        </aside>

        <section class="estado-turno">
            <div class="dado-info">
                <span id="turno-activo"></span>
                <strong id="dado-restriccion"></strong>
                <span id="cuenta-regresiva"></span>
                <button type="button" class="btn-partida" id="btn-tirar-dado" hidden><?php echo htmlspecialchars($txt['tirar_dado'] ?? 'Tirar dado', ENT_QUOTES, 'UTF-8'); ?></button>
            </div>
            <div class="mano-area">
                <h2><?php echo htmlspecialchars($txt['tu_mano'] ?? 'Tu mano', ENT_QUOTES, 'UTF-8'); ?></h2>
                <div id="partida-mano" class="fichas-fila"></div>
                <p id="estado-decision"></p>
            </div>
            <div class="ronda-info"><span id="partida-ronda"></span><span id="partida-turno"></span></div>
        </section>
        <p id="partida-error" role="alert"></p>
        <button type="button" class="partida-panel-backdrop" id="partida-panel-backdrop" aria-label="<?php echo htmlspecialchars($txt['cerrar_panel'] ?? 'Cerrar panel', ENT_QUOTES, 'UTF-8'); ?>"></button>
    </main>

    <div class="modal-overlay" id="modal-recinto-partida">
        <div class="modal-window modal-partida">
            <button type="button" class="modal-close-btn" data-close-modal aria-label="<?php echo htmlspecialchars($txt['cerrar'] ?? 'Cerrar', ENT_QUOTES, 'UTF-8'); ?>">&times;</button>
            <h2 id="modal-recinto-titulo"></h2>
            <p id="modal-recinto-regla" class="modal-recinto-regla"></p>
            <h3><?php echo htmlspecialchars($txt['colocados'] ?? 'Colocados', ENT_QUOTES, 'UTF-8'); ?></h3>
            <div id="modal-recinto-colocados" class="fichas-fila"></div>
            <div id="modal-seleccion-mano">
                <h3><?php echo htmlspecialchars($txt['seleccionar'] ?? 'Seleccionar', ENT_QUOTES, 'UTF-8'); ?></h3>
                <div id="modal-recinto-mano" class="fichas-fila"></div>
            </div>
            <p id="modal-recinto-mensaje"></p>
        </div>
    </div>

    <div class="modal-overlay" id="modal-final-partida">
        <div class="modal-window modal-partida final-modal">
            <h2><?php echo htmlspecialchars($txt['partida_finalizada'] ?? 'Partida finalizada', ENT_QUOTES, 'UTF-8'); ?></h2>
            <ol id="clasificacion-final"></ol>
            <div class="final-acciones">
                <a class="btn-partida" href="digital.php"><?php echo htmlspecialchars($txt['volver_modo_digital'] ?? 'Volver al modo digital', ENT_QUOTES, 'UTF-8'); ?></a>
                <a class="btn-partida" href="cuenta.php"><?php echo htmlspecialchars($txt['ver_historial'] ?? 'Ver historial', ENT_QUOTES, 'UTF-8'); ?></a>
            </div>
        </div>
    </div>

    <script>
        window.partidaData = <?php echo json_encode([
            'idPartida' => (int)$idPartida,
            'idUsuario' => $idUsuario,
            'csrfToken' => $_SESSION['csrf_token'],
            'estadoInicial' => $estadoInicial,
            'textos' => $textosPartida,
            'estadoUrl' => 'estado_partida.php',
            'accionesUrl' => 'acciones_partida.php'
        ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    </script>
    <script src="../Scripts/Temas.js"></script>
    <script src="../Scripts/header.js" defer></script>
    <script src="../Scripts/partida.js" defer></script>
</body>
</html>
