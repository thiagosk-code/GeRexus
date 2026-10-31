<?php
require_once __DIR__ . '/../Scripts/auth_check.php';
require_once __DIR__ . '/../Scripts/lang.php';

$claveAviso = in_array($_GET['aviso'] ?? '', ['expulsado', 'cancelada'], true) ? 'aviso_' . $_GET['aviso'] : '';
$aviso = $claveAviso !== '' ? ($txt[$claveAviso] ?? '') : '';
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang ?? 'es', ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeRexus - Digital</title>
    <link rel="icon" type="image/png" href="../../Assets/Socrates.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Michroma&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <link rel="stylesheet" href="../CSSs/style.css">
    <link rel="stylesheet" href="../CSSs/digital.css">
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
            
            <button class="tema-toggle" aria-label="<?php echo htmlspecialchars($txt['cambiar_modo_claro'], ENT_QUOTES, 'UTF-8'); ?>" data-label-light="<?php echo htmlspecialchars($txt['cambiar_modo_claro'], ENT_QUOTES, 'UTF-8'); ?>" data-label-dark="<?php echo htmlspecialchars($txt['cambiar_modo_oscuro'], ENT_QUOTES, 'UTF-8'); ?>">
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

    <main class="main-content">
        <section class="hero-section">
            <h1 class="main-title">Digital</h1>
            <p class="main-description"><?php echo htmlspecialchars($txt['digital_desc'] ?? 'Juego digital completo con modo asistente incluido', ENT_QUOTES, 'UTF-8'); ?></p>
            <?php if ($aviso !== ''): ?>
                <p class="aviso-sala"><?php echo htmlspecialchars($aviso, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
        </section>

        <section class="modes-grid">
            <article class="mode-column">
                <span class="mode-icon">
                    <svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                        <path d="M36 10 H54 V54 H36" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M8 32 H38" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                        <path d="M26 20 L38 32 L26 44" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </span>
                <h2 class="mode-title"><?php echo htmlspecialchars($txt['unirse_partida'] ?? 'Unirse Partida', ENT_QUOTES, 'UTF-8'); ?></h2>
                <p class="mode-text"></p>
                <a href="unirse.php" class="btn-card"><?php echo htmlspecialchars($txt['seleccionar'] ?? 'Seleccionar', ENT_QUOTES, 'UTF-8'); ?></a>
            </article>

            <article class="mode-column">
                <span class="mode-icon">
                    <svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                        <rect x="8" y="8" width="48" height="48" rx="10" fill="none" stroke="currentColor" stroke-width="4"/>
                        <path d="M32 20 V44 M20 32 H44" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round"/>
                    </svg>
                </span>
                <h2 class="mode-title"><?php echo htmlspecialchars($txt['crear_partida'] ?? 'Crear Partida', ENT_QUOTES, 'UTF-8'); ?></h2>
                <p class="mode-text"></p>
                <a href="crearSala.php" class="btn-card"><?php echo htmlspecialchars($txt['seleccionar'] ?? 'Seleccionar', ENT_QUOTES, 'UTF-8'); ?></a>
            </article>
        </section>

        <div class="gacha-bar">
            <a href="base.php" class="btn-card btn-gacha"><?php echo htmlspecialchars($txt['volver'] ?? 'Volver', ENT_QUOTES, 'UTF-8'); ?></a>
        </div>
    </main>

    <script src="../Scripts/lang.js"></script>
    <script src="../Scripts/Temas.js"></script>
    <script src="../Scripts/header.js" defer></script>
</body>
</html>
