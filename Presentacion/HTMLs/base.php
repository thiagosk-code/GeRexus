<?php
require_once __DIR__ . '/../Scripts/auth_check.php';
require_once __DIR__ . '/../Scripts/lang.php';
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeRexus - Base</title>
    <link rel="icon" type="image/png" href="../Assets/SocratesPNG.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Michroma&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <link rel="stylesheet" href="../CSSs/style.css">
    <link rel="stylesheet" href="../CSSs/base.css">
</head>
<body>

    <header class="main-header">
        <a href="base.php" class="logo-container">
            <span class="logo-img"></span>
            <span class="logo-text">GeRexus</span>
        </a>
        
        <div class="header-actions">
            <div class="leng-switcher">
                <?php if ($lang === 'en'): ?>
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

            <div class="user-profile">
                <span class="user-name"><?php echo $nombreUsuarioLogueado; ?></span>
                <span class="user-avatar-img"></span>
            </div>
        </div>
    </header>

    <main class="main-content">
        <section class="hero-section">
            <div class="gacha-bar">
                <a href="gacha.php" class="btn-card btn-gacha"><?php echo $txt['gacha']; ?></a>
            </div>
            <h1 class="main-title">Draftoicos</h1>
            <p class="main-description"><?php echo $txt['subtitulo_modalidad']; ?></p>
        </section>

        <section class="modes-grid">
            <article class="mode-column">
                <span class="mode-icon icon-draftowiki"></span>
                <h2 class="mode-title">DraftoWiki</h2>
                <p class="mode-text"><?php echo $txt['draftowiki_desc']; ?></p>
                <a href="draftowiki.php" class="btn-card"><?php echo $txt['seleccionar']; ?></a>
            </article>

            <article class="mode-column card-gacha-mobile">
                <span class="mode-icon icon-gacha"></span>
                <h2 class="mode-title"><?php echo $txt['gacha']; ?></h2>
                <p class="mode-text"></p>
                <a href="gacha.php" class="btn-card"><?php echo $txt['seleccionar']; ?></a>
            </article>

            <article class="mode-column">
                <span class="mode-icon icon-asistente"></span>
                <h2 class="mode-title"><?php echo $txt['asistente_titulo']; ?></h2>
                <p class="mode-text"><?php echo $txt['asistente_desc']; ?></p>
                <a href="asistente.php" class="btn-card"><?php echo $txt['seleccionar']; ?></a>
            </article>

            <article class="mode-column">
                <span class="mode-icon icon-digital"></span>
                <h2 class="mode-title">Digital</h2>
                <p class="mode-text"><?php echo $txt['digital_desc']; ?></p>
                <a href="digital.php" class="btn-card"><?php echo $txt['seleccionar']; ?></a>
            </article>
        </section>
    </main>

    <script src="../Scripts/lang.js"></script>
    <script src="../Scripts/Temas.js"></script>
</body>
</html>