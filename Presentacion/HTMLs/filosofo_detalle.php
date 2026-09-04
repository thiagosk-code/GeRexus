<?php
require_once __DIR__ . '/../Scripts/auth_check.php';
require_once __DIR__ . '/../Scripts/lang.php';

$idFilosofo = isset($_GET['id']) ? trim($_GET['id']) : '';
$jsonPath = __DIR__ . '/../WikiData/filosofos/' . $idFilosofo . '.json';

if (empty($idFilosofo) || !file_exists($jsonPath)) {
    header('Location: filosofos.php');
    exit;
}

$jsonData = file_get_contents($jsonPath);
$filosofo = json_decode($jsonData, true);

if (!$filosofo) {
    header('Location: filosofos.php');
    exit;
}

$idiomaActual = $lang ?? 'es';

$i18nData = $filosofo['i18n'][$idiomaActual] ?? ($filosofo['i18n']['es'] ?? []);

$nombre = $i18nData['nombre'] ?? ($filosofo['nombre'] ?? 'Sin Nombre');
$corriente = $i18nData['corriente'] ?? ($filosofo['corriente'] ?? 'Desconocida');
$descripcion = $i18nData['descripcion'] ?? ($filosofo['descripcion'] ?? '');

$corrienteId = $filosofo['corriente_id'] ?? '';
$imgCuadro = $filosofo['img_cuadro'] ?? '../../Assets/Socrates.png';
$imgAdeptoNormal = $filosofo['img_adepto_normal'] ?? '../../Assets/sinAdepto.png';
$imgAdeptoShiny = $filosofo['img_adepto_shiny'] ?? '../../Assets/sinAdepto.png';
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
    <link rel="stylesheet" href="../CSSs/filosofo_detalle.css">
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
                    <a href="?id=<?php echo urlencode($idFilosofo); ?>&lang=es" class="inactive">Esp</a>
                    <span class="divider">|</span>
                    <span class="active">Eng</span>
                <?php else: ?>
                    <span class="active">Esp</span>
                    <span class="divider">|</span>
                    <a href="?id=<?php echo urlencode($idFilosofo); ?>&lang=en" class="inactive">Eng</a>
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
                    <h1 class="filosofo-title"><?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?></h1>
                    <h2 class="corriente-subtitle"><?php echo htmlspecialchars($txt['corriente_label'] ?? 'Corriente:', ENT_QUOTES, 'UTF-8'); ?> <?php echo htmlspecialchars($corriente, ENT_QUOTES, 'UTF-8'); ?></h2>
                </section>

                <article class="desc-box">
                    <div class="desc-scroll">
                        <?php echo nl2br(htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8')); ?>
                    </div>
                </article>

                <div class="action-buttons">
                    <a href="filosofos.php" class="btn-action"><?php echo htmlspecialchars($txt['volver'] ?? 'Volver', ENT_QUOTES, 'UTF-8'); ?></a>
                    <?php if (!empty($corrienteId)): ?>
                        <a href="corriente_detalle.php?id=<?php echo urlencode($corrienteId); ?>" class="btn-action"><?php echo htmlspecialchars($txt['ver_corriente'] ?? 'Ver Corriente', ENT_QUOTES, 'UTF-8'); ?></a>
                    <?php endif; ?>
                </div>
            </div>

            <aside class="right-column">
                <div class="cuadro-container">
                    <img src="<?php echo htmlspecialchars($imgCuadro, ENT_QUOTES, 'UTF-8'); ?>" alt="Cuadro de <?php echo htmlspecialchars($nombre, ENT_QUOTES, 'UTF-8'); ?>" class="cuadro-img">
                </div>

                <div class="adeptos-card">
                    <h3 class="adeptos-title"><?php echo htmlspecialchars($txt['adepto'] ?? 'Adepto', ENT_QUOTES, 'UTF-8'); ?></h3>
                    <div class="adeptos-wrapper">
                        <div class="adepto-item">
                            <span class="adepto-label"><?php echo htmlspecialchars($txt['normal'] ?? 'Normal', ENT_QUOTES, 'UTF-8'); ?></span>
                            <img src="<?php echo htmlspecialchars($imgAdeptoNormal, ENT_QUOTES, 'UTF-8'); ?>" alt="Adepto Normal" class="adepto-img">
                        </div>
                        <div class="adepto-item">
                            <span class="adepto-label"><?php echo htmlspecialchars($txt['shiny'] ?? 'Shiny', ENT_QUOTES, 'UTF-8'); ?></span>
                            <img src="<?php echo htmlspecialchars($imgAdeptoShiny, ENT_QUOTES, 'UTF-8'); ?>" alt="Adepto Shiny" class="adepto-img">
                        </div>
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