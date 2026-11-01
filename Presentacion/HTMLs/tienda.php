<?php
require_once __DIR__ . '/../Scripts/auth_check.php';
require_once __DIR__ . '/../Scripts/lang.php';
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang ?? 'es', ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeRexus - <?php echo htmlspecialchars($txt['tienda_titulo'] ?? 'Tienda de dracmas', ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="icon" type="image/png" href="../../Assets/Socrates.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Michroma&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0">
    <link rel="stylesheet" href="../CSSs/style.css">
    <link rel="stylesheet" href="../CSSs/tienda.css">
</head>
<body class="tienda-page">
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

            <a href="cuenta.php" class="user-profile-link">
                <span class="user-profile">
                    <span class="user-name"><?php echo htmlspecialchars($nombreUsuarioLogueado ?? 'User', ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="user-avatar-img" id="header-avatar-img"></span>
                </span>
            </a>
        </div>
    </header>

    <main class="tienda-content">
        <div class="tienda-encabezado">
            <h1><?php echo htmlspecialchars($txt['tienda_titulo'] ?? 'Tienda de dracmas', ENT_QUOTES, 'UTF-8'); ?></h1>
            <p><?php echo htmlspecialchars($txt['tienda_descripcion'] ?? 'Elige un paquete de dracmas', ENT_QUOTES, 'UTF-8'); ?></p>
        </div>

        <section class="planes-grid" aria-label="<?php echo htmlspecialchars($txt['planes_dracmas'] ?? 'Planes de dracmas', ENT_QUOTES, 'UTF-8'); ?>">
            <?php
            $planes = [
                ['cantidad' => 90, 'precio' => '1.50', 'clave' => 'plan_dracmas_basico', 'clase' => ''],
                ['cantidad' => 270, 'precio' => '2.50', 'clave' => 'plan_dracmas_popular', 'clase' => 'destacado'],
                ['cantidad' => 540, 'precio' => '4.00', 'clave' => 'plan_dracmas_premium', 'clase' => '']
            ];
            ?>
            <?php foreach ($planes as $plan): ?>
                <button type="button" class="plan-card <?php echo $plan['clase']; ?>" data-plan-dracmas="<?php echo $plan['cantidad']; ?>">
                    <span class="plan-insignia <?php echo $plan['clase'] === 'destacado' ? '' : 'oculta'; ?>"<?php echo $plan['clase'] === 'destacado' ? '' : ' aria-hidden="true"'; ?>><?php echo htmlspecialchars($txt['mas_comprado'] ?? 'Más comprado', ENT_QUOTES, 'UTF-8'); ?></span>
                    <svg class="plan-dracma" viewBox="0 0 64 64" aria-hidden="true">
                        <circle cx="32" cy="32" r="28" fill="none" stroke="currentColor" stroke-width="4"></circle>
                        <path d="M16 28 L32 16 L48 28 Z" fill="currentColor"></path>
                        <rect x="20" y="31" width="4" height="14" fill="currentColor"></rect>
                        <rect x="30" y="31" width="4" height="14" fill="currentColor"></rect>
                        <rect x="40" y="31" width="4" height="14" fill="currentColor"></rect>
                        <rect x="16" y="47" width="32" height="4" fill="currentColor"></rect>
                    </svg>
                    <strong class="plan-cantidad"><?php echo $plan['cantidad']; ?></strong>
                    <span class="plan-unidad"><?php echo htmlspecialchars($txt['dracmas'] ?? 'Dracmas', ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="plan-nombre"><?php echo htmlspecialchars($txt[$plan['clave']] ?? '', ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="plan-precio"><?php echo htmlspecialchars($plan['precio'], ENT_QUOTES, 'UTF-8'); ?> USD</span>
                    <span class="plan-accion"><?php echo htmlspecialchars($txt['comprar_plan'] ?? 'Comprar', ENT_QUOTES, 'UTF-8'); ?></span>
                </button>
            <?php endforeach; ?>
        </section>

        <p id="tienda-feedback" class="tienda-feedback" role="status" aria-live="polite" tabindex="-1" hidden><?php echo htmlspecialchars($txt['item_no_disponible'] ?? 'Item no disponible en este momento', ENT_QUOTES, 'UTF-8'); ?></p>

        <a href="gacha.php" class="tienda-volver"><?php echo htmlspecialchars($txt['volver'] ?? 'Volver', ENT_QUOTES, 'UTF-8'); ?></a>
    </main>

    <script src="../Scripts/lang.js"></script>
    <script src="../Scripts/Temas.js"></script>
    <script src="../Scripts/header.js" defer></script>
    <script src="../Scripts/tienda.js" defer></script>
</body>
</html>
