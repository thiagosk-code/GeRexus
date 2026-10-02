<?php
require_once __DIR__ . '/../Scripts/auth_check.php';
require_once __DIR__ . '/../Scripts/lang.php';
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
            <div class="recintos-grid" id="recintos-grid">
                <?php for ($i = 1; $i <= 6; $i++): ?>
                    <button type="button" class="recinto-box" data-recinto="<?php echo $i; ?>" aria-label="Recinto <?php echo $i; ?>">
                        <svg class="recinto-icon" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                            <path d="M6 24 L32 8 L58 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                            <line x1="4" y1="24" x2="60" y2="24" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                            <line x1="10" y1="28" x2="10" y2="50" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                            <line x1="20" y1="28" x2="20" y2="50" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                            <line x1="32" y1="28" x2="32" y2="50" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                            <line x1="44" y1="28" x2="44" y2="50" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                            <line x1="54" y1="28" x2="54" y2="50" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                            <line x1="4" y1="54" x2="60" y2="54" stroke="currentColor" stroke-width="3" stroke-linecap="round"/>
                        </svg>
                    </button>
                <?php endfor; ?>
            </div>

            <div class="glass-box puntaje-box">
                <h2 class="section-subtitle center" data-i18n="puntos"><?php echo htmlspecialchars($txt['puntos'] ?? 'Puntos:', ENT_QUOTES, 'UTF-8'); ?></h2>

                <ul class="puntaje-list">
                    <li><span>Recinto 1:</span><span>12</span></li>
                    <li><span>Recinto 2:</span><span>3</span></li>
                    <li><span>Recinto 3:</span><span>13</span></li>
                    <li><span>Recinto 4:</span><span>20</span></li>
                    <li><span>Recinto 5:</span><span>1</span></li>
                    <li><span>Recinto 6:</span><span>0</span></li>
                </ul>

                <div class="puntaje-divider"></div>

                <div class="puntaje-total">
                    <span data-i18n="puntos"><?php echo htmlspecialchars($txt['puntos'] ?? 'Puntos:', ENT_QUOTES, 'UTF-8'); ?></span>
                    <span>36</span>
                </div>
            </div>
        </div>

        <div class="volver-container">
            <a href="base.php" class="btn-nav btn-volver-asistente" data-i18n="volver"><?php echo htmlspecialchars($txt['volver'] ?? 'Volver', ENT_QUOTES, 'UTF-8'); ?></a>
        </div>
    </main>

    <div class="modal-overlay" id="modal-recinto">
        <div class="modal-window modal-wide">
            <button type="button" class="modal-close-btn" data-close="modal-recinto">&times;</button>
            <h2 class="modal-header-title" id="recinto-modal-title">Recinto 1</h2>

            <div class="recinto-panel-section">
                <h3 class="panel-subtitle" data-i18n="adepto"><?php echo htmlspecialchars($txt['adepto'] ?? 'Adepto', ENT_QUOTES, 'UTF-8'); ?>s:</h3>
                <div class="adeptos-row" id="recinto-colocados"></div>
            </div>

            <div class="recinto-panel-section">
                <h3 class="panel-subtitle" data-i18n="seleccionar"><?php echo htmlspecialchars($txt['seleccionar'] ?? 'Seleccionar', ENT_QUOTES, 'UTF-8'); ?>:</h3>
                <div class="adeptos-row">
                    <button type="button" class="adepto-select-btn">
                        <img src="../../Assets/DiogenesPerro.png" alt="Diógenes" class="adepto-icon">
                    </button>
                    <button type="button" class="adepto-select-btn">
                        <img src="../../Assets/SocratesHyrax.png" alt="Sócrates" class="adepto-icon">
                    </button>
                    <button type="button" class="adepto-select-btn">
                        <img src="../../Assets/HypatiaAbeja.png" alt="Hypatia" class="adepto-icon">
                    </button>
                    <button type="button" class="adepto-select-btn">
                        <img src="../../Assets/AristotelesTortuga.png" alt="Aristóteles" class="adepto-icon">
                    </button>
                    <button type="button" class="adepto-select-btn">
                        <img src="../../Assets/PlatonPato.png" alt="Platón" class="adepto-icon">
                    </button>
                    <button type="button" class="adepto-select-btn">
                        <img src="../../Assets/EpicuroKoala.png" alt="Epicuro" class="adepto-icon">
                    </button>
                </div>
            </div>

            <div class="modal-footer-left">
                <button type="button" class="btn-sub-pill" data-close="modal-recinto" data-i18n="cerrar_panel"><?php echo htmlspecialchars($txt['cerrar_panel'] ?? 'Cerrar Panel', ENT_QUOTES, 'UTF-8'); ?></button>
            </div>
        </div>
    </div>

    <script src="../Scripts/lang.js"></script>
    <script src="../Scripts/Temas.js"></script>
    <script src="../Scripts/header.js" defer></script>
    <script src="../Scripts/asistente.js" defer></script>
</body>
</html>