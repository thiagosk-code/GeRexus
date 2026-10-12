<?php
require_once __DIR__ . '/../Scripts/auth_check.php';
require_once __DIR__ . '/../Scripts/lang.php';

$adeptos = [
    'DiogenesPerro.png' => 'Diógenes',
    'SocratesHyrax.png' => 'Sócrates',
    'HypatiaAbeja.png' => 'Hypatia',
    'AristotelesTortuga.png' => 'Aristóteles',
    'PlatonPato.png' => 'Platón',
    'EpicuroKoala.png' => 'Epicuro'
];
$dracmas = (int)$usuarioActual->getMonedas();
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang ?? 'es', ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeRexus - <?php echo htmlspecialchars($txt['gacha'] ?? 'Gacha', ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="icon" type="image/png" href="../../Assets/Socrates.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Michroma&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <link rel="stylesheet" href="../CSSs/style.css">
    <link rel="stylesheet" href="../CSSs/gacha.css">
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

    <main class="gacha-content">
        <section class="gacha-hero">
            <h1 class="main-title"><?php echo htmlspecialchars($txt['gacha'] ?? 'Gacha', ENT_QUOTES, 'UTF-8'); ?></h1>

            <div class="dracmas-box">
                <svg class="dracma-icon" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="32" cy="32" r="29" fill="none" stroke="currentColor" stroke-width="4"/>
                    <path d="M17 28 L32 17 L47 28 Z" fill="currentColor"/>
                    <rect x="20" y="31" width="4" height="14" fill="currentColor"/>
                    <rect x="30" y="31" width="4" height="14" fill="currentColor"/>
                    <rect x="40" y="31" width="4" height="14" fill="currentColor"/>
                    <rect x="17" y="47" width="30" height="3" fill="currentColor"/>
                </svg>
                <span><?php echo htmlspecialchars($txt['dracmas'] ?? 'Dracmas', ENT_QUOTES, 'UTF-8'); ?>: <?php echo $dracmas; ?></span>
            </div>
        </section>

        <div class="gacha-grid">
            <section class="adeptos-col col-izq">
                <h2 class="col-titulo"><?php echo htmlspecialchars($txt['adeptos_desbloqueados'] ?? 'Adeptos Desbloqueados', ENT_QUOTES, 'UTF-8'); ?></h2>
                <div class="adeptos-list">
                    <?php foreach ($adeptos as $archivo => $nombre): ?>
                        <span class="adepto-item"><img src="../../Assets/<?php echo htmlspecialchars($archivo, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>" class="adepto-icon"></span>
                    <?php endforeach; ?>
                </div>
            </section>

            <section class="gacha-machine">
                <img src="../../Assets/Gachapon.svg" alt="<?php echo htmlspecialchars($txt['maquina_gachapon'] ?? 'Gacha Machine', ENT_QUOTES, 'UTF-8'); ?>" class="maquina-img" draggable="false">
                <button type="button" class="btn-tirada"><?php echo htmlspecialchars($txt['comprar_tirada'] ?? 'Comprar Tirada', ENT_QUOTES, 'UTF-8'); ?></button>
            </section>

            <section class="adeptos-col col-der">
                <h2 class="col-titulo"><?php echo htmlspecialchars($txt['adeptos_por_desbloquear'] ?? 'Adeptos Por Desbloquear', ENT_QUOTES, 'UTF-8'); ?></h2>
                <div class="adeptos-list">
                    <?php foreach ($adeptos as $archivo => $nombre): ?>
                        <span class="adepto-item bloqueado"><img src="../../Assets/<?php echo htmlspecialchars($archivo, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>" class="adepto-icon"></span>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>

        <nav class="gacha-nav">
            <a href="base.php" class="btn-nav"><?php echo htmlspecialchars($txt['volver'] ?? 'Volver', ENT_QUOTES, 'UTF-8'); ?></a>
            <a href="tienda.php" class="btn-nav"><?php echo htmlspecialchars($txt['tienda'] ?? 'Tienda', ENT_QUOTES, 'UTF-8'); ?></a>
        </nav>
    </main>

    <script src="../Scripts/lang.js"></script>
    <script src="../Scripts/Temas.js"></script>
    <script src="../Scripts/header.js" defer></script>
</body>
</html>
