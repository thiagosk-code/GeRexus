<?php
require_once __DIR__ . '/../Scripts/auth_check.php';
require_once __DIR__ . '/../Scripts/lang.php';
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang ?? 'es', ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeRexus - DraftoWiki</title>
    <link rel="icon" type="image/png" href="../../Assets/Socrates.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Michroma&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <link rel="stylesheet" href="../CSSs/style.css">
    <link rel="stylesheet" href="../CSSs/draftowiki.css">
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

    <main class="main-content">
        <section class="hero-section">
            <h1 class="main-title">DraftoWiki</h1>
            <p class="main-description"><?php echo htmlspecialchars($txt['draftowiki_subtitulo'] ?? 'Una Wiki sobre Corrientes filosóficas y Filósofos', ENT_QUOTES, 'UTF-8'); ?></p>
        </section>

        <section class="modes-grid">
            <article class="mode-column">
                <span class="mode-icon icon-corrientes"></span>
                <h2 class="mode-title"><?php echo htmlspecialchars($txt['corrientes_titulo'] ?? 'Corrientes', ENT_QUOTES, 'UTF-8'); ?></h2>
                <p class="mode-text"></p>
                <a href="corrientes.php" class="btn-card"><?php echo htmlspecialchars($txt['seleccionar'] ?? 'Seleccionar', ENT_QUOTES, 'UTF-8'); ?></a>
            </article>

            <article class="mode-column">
                <span class="mode-icon icon-filosofos"></span>
                <h2 class="mode-title"><?php echo htmlspecialchars($txt['filosofos_titulo'] ?? 'Filósofos', ENT_QUOTES, 'UTF-8'); ?></h2>
                <p class="mode-text"></p>
                <a href="filosofos.php" class="btn-card"><?php echo htmlspecialchars($txt['seleccionar'] ?? 'Seleccionar', ENT_QUOTES, 'UTF-8'); ?></a>
            </article>
        </section>

        <div class="gacha-bar">
            <a href="base.php" class="btn-card btn-gacha"><?php echo htmlspecialchars($txt['draftoicos'] ?? 'Draftoicos', ENT_QUOTES, 'UTF-8'); ?></a>
        </div>
    </main>

    <script src="../Scripts/lang.js"></script>
    <script src="../Scripts/Temas.js"></script>
    <script src="../Scripts/header.js" defer></script>
</body>
</html>