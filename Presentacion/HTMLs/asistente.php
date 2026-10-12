<?php
require_once __DIR__ . '/../Scripts/auth_check.php';
require_once __DIR__ . '/../Scripts/lang.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$recintos = [
    ['id' => 1, 'x' => 517, 'y' => 464, 'w' => 400, 'h' => 400, 'rey' => false],
    ['id' => 2, 'x' => 515, 'y' => 775, 'w' => 350, 'h' => 350, 'rey' => false],
    ['id' => 3, 'x' => 512, 'y' => 1041, 'w' => 380, 'h' => 380, 'rey' => false],
    ['id' => 4, 'x' => 1110, 'y' => 522, 'w' => 360, 'h' => 360, 'rey' => true],
    ['id' => 5, 'x' => 1102, 'y' => 780, 'w' => 370, 'h' => 370, 'rey' => false],
    ['id' => 6, 'x' => 1112, 'y' => 1053, 'w' => 310, 'h' => 310, 'rey' => false],
    ['id' => 7, 'x' => 810, 'y' => 760, 'w' => 250, 'h' => 1080, 'rey' => false]
];
$puntajes = [12, 3, 13, 20, 1, 0, 0];
$adeptos = [
    'DiogenesPerro.png' => 'Diógenes',
    'SocratesHyrax.png' => 'Sócrates',
    'HypatiaAbeja.png' => 'Hypatia',
    'AristotelesTortuga.png' => 'Aristóteles',
    'PlatonPato.png' => 'Platón',
    'EpicuroKoala.png' => 'Epicuro'
];
$etiquetaRecinto = $txt['recinto'] ?? 'Recinto';
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang ?? 'es', ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeRexus - <?php echo htmlspecialchars($txt['asistente_titulo'] ?? 'Asistente', ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="icon" type="image/png" href="../../Assets/Socrates.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Michroma&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <link rel="stylesheet" href="../CSSs/style.css">
    <link rel="stylesheet" href="../CSSs/asistente.css">
</head>
<body>

    <header class="main-header">
        <a href="base.php" class="logo-container">
            <span class="logo-img"></span>
            <span class="logo-text">GeRexus</span>
        </a>

        <div class="header-actions">
            <div class="leng-switcher">
                <?php if (($lang ?? 'es') === 'en'): ?>
                    <a href="?lang=es" class="inactive">Esp</a>
                    <span class="divider">|</span>
                    <span class="active">Eng</span>
                <?php else: ?>
                    <span class="active">Esp</span>
                    <span class="divider">|</span>
                    <a href="?lang=en" class="inactive">Eng</a>
                <?php endif; ?>
            </div>

            <button class="tema-toggle" aria-label="Cambiar tema">
                <span class="material-symbols-outlined">light_mode</span>
            </button>

            <a href="cuenta.php" class="user-profile-link" style="text-decoration: none; color: inherit;">
                <div class="user-profile">
                    <span class="user-name"><?php echo htmlspecialchars($nombreUsuarioLogueado ?? 'User', ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="user-avatar-img" id="header-avatar-img"></span>
                </div>
            </a>
        </div>
    </header>

    <main class="main-content asistente-content">
        <section class="hero-section">
            <h1 class="main-title" data-i18n="asistente_titulo"><?php echo htmlspecialchars($txt['asistente_titulo'] ?? 'Asistente', ENT_QUOTES, 'UTF-8'); ?></h1>
        </section>

        <div class="asistente-grid">
            <div class="mapa-recintos" id="mapa-recintos">
                <img src="../../Assets/MapaAsistente.png" alt="" class="mapa-img" draggable="false">
                <?php foreach ($recintos as $recinto): ?>
                    <button type="button" class="recinto-hotspot" data-recinto="<?php echo (int)$recinto['id']; ?>"<?php echo $recinto['rey'] ? ' data-rey="1"' : ''; ?> aria-label="<?php echo htmlspecialchars($etiquetaRecinto, ENT_QUOTES, 'UTF-8'); ?> <?php echo (int)$recinto['id']; ?>" style="left: <?php echo round($recinto['x'] / 16, 2); ?>%; top: <?php echo round($recinto['y'] / 16, 2); ?>%; width: <?php echo round($recinto['w'] / 16, 2); ?>%; height: <?php echo round($recinto['h'] / 16, 2); ?>%;"></button>
                <?php endforeach; ?>
            </div>

            <div class="glass-box puntaje-box">
                <h2 class="section-subtitle center" data-i18n="puntos"><?php echo htmlspecialchars($txt['puntos'] ?? 'Puntos:', ENT_QUOTES, 'UTF-8'); ?></h2>

                <ul class="puntaje-list">
                    <?php foreach ($puntajes as $indice => $puntaje): ?>
                        <li><span><?php echo htmlspecialchars($etiquetaRecinto, ENT_QUOTES, 'UTF-8'); ?> <?php echo $indice + 1; ?>:</span><span><?php echo (int)$puntaje; ?></span></li>
                    <?php endforeach; ?>
                </ul>

                <div class="puntaje-divider"></div>

                <div class="puntaje-total">
                    <span data-i18n="total"><?php echo htmlspecialchars($txt['total'] ?? 'Total:', ENT_QUOTES, 'UTF-8'); ?></span>
                    <span><?php echo (int)array_sum($puntajes); ?></span>
                </div>
            </div>
        </div>

        <div class="volver-container">
            <a href="base.php" class="btn-nav btn-volver-asistente" data-i18n="volver"><?php echo htmlspecialchars($txt['volver'] ?? 'Volver', ENT_QUOTES, 'UTF-8'); ?></a>
            <button type="button" class="btn-nav" data-open="modal-reiniciar" data-i18n="reiniciar_tablero"><?php echo htmlspecialchars($txt['reiniciar_tablero'] ?? 'Reiniciar Tablero', ENT_QUOTES, 'UTF-8'); ?></button>
        </div>
    </main>

    <div class="modal-overlay" id="modal-recinto" data-recinto-label="<?php echo htmlspecialchars($etiquetaRecinto, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="modal-window modal-wide">
            <button type="button" class="modal-close-btn" data-close="modal-recinto">&times;</button>
            <h2 class="modal-header-title" id="recinto-modal-title"><?php echo htmlspecialchars($etiquetaRecinto, ENT_QUOTES, 'UTF-8'); ?> 1</h2>

            <div class="recinto-panel-section">
                <h3 class="panel-subtitle" data-i18n="adepto"><?php echo htmlspecialchars($txt['adepto'] ?? 'Adepto', ENT_QUOTES, 'UTF-8'); ?>s:</h3>
                <div class="adeptos-row" id="recinto-colocados"></div>
            </div>

            <div class="recinto-panel-section">
                <h3 class="panel-subtitle" data-i18n="seleccionar"><?php echo htmlspecialchars($txt['seleccionar'] ?? 'Seleccionar', ENT_QUOTES, 'UTF-8'); ?>:</h3>
                <div class="adeptos-row">
                    <?php foreach ($adeptos as $archivo => $nombre): ?>
                        <button type="button" class="adepto-select-btn">
                            <img src="../../Assets/<?php echo htmlspecialchars($archivo, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>" class="adepto-icon">
                        </button>
                    <?php endforeach; ?>
                </div>
            </div>

            <label class="bonus-row" id="bonus-row">
                <input type="checkbox" id="bonus-rey" class="bonus-check">
                <span data-i18n="bonus_rey_filosofo"><?php echo htmlspecialchars($txt['bonus_rey_filosofo'] ?? 'Bonus del Rey Filósofo', ENT_QUOTES, 'UTF-8'); ?></span>
            </label>

            <div class="modal-footer-left">
                <button type="button" class="btn-sub-pill" data-close="modal-recinto" data-i18n="cerrar_panel"><?php echo htmlspecialchars($txt['cerrar_panel'] ?? 'Cerrar Panel', ENT_QUOTES, 'UTF-8'); ?></button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modal-reiniciar">
        <div class="modal-window">
            <button type="button" class="modal-close-btn" data-close="modal-reiniciar" aria-label="<?php echo htmlspecialchars($txt['cerrar'] ?? 'Cerrar', ENT_QUOTES, 'UTF-8'); ?>">&times;</button>
            <h2 class="modal-header-title" data-i18n="reiniciar_tablero"><?php echo htmlspecialchars($txt['reiniciar_tablero'] ?? 'Reiniciar Tablero', ENT_QUOTES, 'UTF-8'); ?></h2>
            <p class="modal-body-txt" data-i18n="confirmar_reiniciar_tablero"><?php echo htmlspecialchars($txt['confirmar_reiniciar_tablero'] ?? '¿Seguro que desea reiniciar el tablero?', ENT_QUOTES, 'UTF-8'); ?></p>
            <form action="" method="POST" class="modal-actions">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="accion" value="reiniciar_tablero">
                <button type="submit" class="btn-modal btn-danger"><?php echo htmlspecialchars($txt['confirmar'] ?? 'Confirmar', ENT_QUOTES, 'UTF-8'); ?></button>
            </form>
        </div>
    </div>

    <script src="../Scripts/lang.js"></script>
    <script src="../Scripts/Temas.js"></script>
    <script src="../Scripts/header.js" defer></script>
    <script src="../Scripts/asistente.js" defer></script>
</body>
</html>
