<?php
require_once __DIR__ . '/../Scripts/auth_check.php';
require_once __DIR__ . '/../Scripts/lang.php';

$idCorriente = isset($_GET['id']) ? trim($_GET['id']) : '';
$jsonPath = __DIR__ . '/../WikiData/corrientes/' . $idCorriente . '.json';

if (!preg_match('/^[A-Za-z0-9_-]+$/', $idCorriente) || !file_exists($jsonPath)) {
    header('Location: corrientes.php');
    exit;
}

$corriente = json_decode(file_get_contents($jsonPath), true);

if (!$corriente) {
    header('Location: corrientes.php');
    exit;
}

$idiomaActual = $lang ?? 'es';

$i18nData = $corriente['i18n'][$idiomaActual] ?? ($corriente['i18n']['es'] ?? []);

$nombre = $i18nData['nombre'] ?? ($corriente['nombre'] ?? 'Sin Nombre');
$descripcion = $i18nData['descripcion'] ?? ($corriente['descripcion'] ?? '');
$exponentes = $corriente['exponentes'] ?? [];
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($idiomaActual, ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeRexus - <?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="icon" type="image/png" href="../../Assets/Socrates.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Michroma&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <link rel="stylesheet" href="../CSSs/style.css">
    <link rel="stylesheet" href="../CSSs/corriente_detalle.css">
</head>
<body>

    <header class="main-header">
        <a href="base.php" class="logo-container">
            <span class="logo-img"></span>
            <span class="logo-text">GeRexus</span>
        </a>
        
        <div class="header-actions">
            <div class="leng-switcher">
                <?php if ($idiomaActual === 'en'): ?>
                    <a href="?id=<?php echo urlencode($idCorriente); ?>&lang=es" class="inactive">Esp</a>
                    <span class="divider">|</span>
                    <span class="active">Eng</span>
                <?php else: ?>
                    <span class="active">Esp</span>
                    <span class="divider">|</span>
                    <a href="?id=<?php echo urlencode($idCorriente); ?>&lang=en" class="inactive">Eng</a>
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
        <div class="detalle-container">
            <div class="left-column">
                <section class="title-section">
                    <h1 class="corriente-title"><?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?></h1>
                </section>

                <article class="desc-box">
                    <div class="desc-scroll">
                        <?php echo nl2br(htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8')); ?>
                    </div>
                </article>

                <div class="action-buttons">
                    <a href="corrientes.php" class="btn-action"><?php echo htmlspecialchars($txt['volver'] ?? 'Volver', ENT_QUOTES, 'UTF-8'); ?></a>
                </div>
            </div>

            <aside class="right-column">
                <div class="exponentes-card">
                    <h3 class="exponentes-title"><?php echo htmlspecialchars($txt['principales_exponentes'] ?? 'Principales Exponentes', ENT_QUOTES, 'UTF-8'); ?></h3>
                    <div class="exponentes-lista">
                        <?php foreach ($exponentes as $exponente): ?>
                            <?php
                                $idExp = $exponente['id'] ?? '';
                                $nombreExp = $txt[$idExp] ?? ($exponente['nombre'] ?? $idExp);
                                $imgExp = $exponente['img_autor'] ?? '../../Assets/sinAdepto.png';
                            ?>
                            <a href="filosofo_detalle.php?id=<?php echo urlencode($idExp); ?>" class="exponente-item">
                                <img src="<?php echo htmlspecialchars($imgExp, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($nombreExp, ENT_QUOTES, 'UTF-8'); ?>" class="exponente-img">
                                <span class="exponente-nombre"><?php echo htmlspecialchars($nombreExp, ENT_QUOTES, 'UTF-8'); ?></span>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <script src="../Scripts/lang.js"></script>
    <script src="../Scripts/Temas.js"></script>
    <script src="../Scripts/header.js" defer></script>
</body>
</html>
