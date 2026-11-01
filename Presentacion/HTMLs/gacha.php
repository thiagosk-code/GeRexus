<?php
require_once __DIR__ . '/../Scripts/auth_check.php';
require_once __DIR__ . '/../Scripts/lang.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$idUsuario = (int)$usuarioActual->getIdUsuario();
$logicaGacha = $fachadaLogica->retornoILogicaGacha();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $token = (string)($_POST['csrf_token'] ?? '');
    if (!hash_equals($_SESSION['csrf_token'], $token)) {
        $resultado = ['exito' => false, 'mensaje_key' => 'err_csrf'];
    } elseif (($_POST['accion'] ?? '') !== 'comprar_tirada') {
        $resultado = ['exito' => false, 'mensaje_key' => 'err_gacha_tirada'];
    } else {
        $resultado = $logicaGacha->comprarTiradaGachaL($idUsuario);
    }

    $_SESSION['flash_gacha'] = [
        'exito' => (bool)($resultado['exito'] ?? false),
        'mensaje_key' => (string)($resultado['mensaje_key'] ?? 'err_gacha_tirada'),
        'especie' => (string)($resultado['adepto']['especie'] ?? '')
    ];
    header('Location: gacha.php');
    exit;
}

$flashGacha = $_SESSION['flash_gacha'] ?? null;
unset($_SESSION['flash_gacha']);

$estadoGacha = $logicaGacha->obtenerEstadoGachaL($idUsuario);
$dracmas = (int)($estadoGacha['dracmas'] ?? $usuarioActual->getMonedas());
$adeptos = $estadoGacha['adeptos'] ?? [];
$adeptosDesbloqueados = array_values(array_filter($adeptos, static fn(array $adepto): bool => $adepto['desbloqueado'] === true));
$adeptosBloqueados = array_values(array_filter($adeptos, static fn(array $adepto): bool => $adepto['desbloqueado'] === false));
$puedeTirar = ($estadoGacha['exito'] ?? false) === true && $adeptosBloqueados !== [];
$mensajeGacha = '';
$mensajeExito = false;

if (is_array($flashGacha)) {
    $mensajeKey = (string)($flashGacha['mensaje_key'] ?? 'err_gacha_tirada');
    $mensajeGacha = $txt[$mensajeKey] ?? $txt['err_gacha_tirada'];
    $claveAdepto = strtolower((string)($flashGacha['especie'] ?? ''));
    $nombreAdepto = $txt[$claveAdepto] ?? (string)($flashGacha['especie'] ?? '');
    $mensajeGacha = str_replace('{adepto}', $nombreAdepto, $mensajeGacha);
    $mensajeExito = (bool)($flashGacha['exito'] ?? false);
} elseif (($estadoGacha['exito'] ?? false) !== true) {
    $mensajeKey = (string)($estadoGacha['mensaje_key'] ?? 'err_gacha_estado');
    $mensajeGacha = $txt[$mensajeKey] ?? $txt['err_gacha_estado'];
}

$prepararAdepto = static function (array $adepto) use ($txt): array {
    $archivo = basename(str_replace('\\', '/', (string)($adepto['ruta'] ?? '')));
    if ($archivo === '' || preg_match('/^[a-zA-Z0-9._-]+$/', $archivo) !== 1) {
        $archivo = 'sinAdepto.png';
    }
    $especie = (string)($adepto['especie'] ?? '');
    $nombre = $txt[strtolower($especie)] ?? $especie;
    return ['archivo' => $archivo, 'nombre' => $nombre];
};
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
                    <?php foreach ($adeptosDesbloqueados as $adepto): ?>
                        <?php $vistaAdepto = $prepararAdepto($adepto); ?>
                        <span class="adepto-item"><img src="../../Assets/<?php echo htmlspecialchars($vistaAdepto['archivo'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($vistaAdepto['nombre'], ENT_QUOTES, 'UTF-8'); ?>" class="adepto-icon"></span>
                    <?php endforeach; ?>
                    <?php if ($adeptosDesbloqueados === []): ?>
                        <p class="lista-vacia"><?php echo htmlspecialchars($txt['gacha_sin_desbloqueados'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                </div>
            </section>

            <section class="gacha-machine">
                <img src="../../Assets/Gachapon.svg" alt="<?php echo htmlspecialchars($txt['maquina_gachapon'] ?? 'Gacha Machine', ENT_QUOTES, 'UTF-8'); ?>" class="maquina-img" draggable="false">
                <form action="gacha.php" method="POST" id="form-tirada-gacha">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="accion" value="comprar_tirada">
                </form>
                <button type="submit" form="form-tirada-gacha" class="btn-tirada"<?php echo $puedeTirar ? '' : ' disabled'; ?>><?php echo htmlspecialchars($txt['comprar_tirada'] ?? 'Comprar Tirada', ENT_QUOTES, 'UTF-8'); ?></button>
            </section>

            <section class="adeptos-col col-der">
                <h2 class="col-titulo"><?php echo htmlspecialchars($txt['adeptos_por_desbloquear'] ?? 'Adeptos Por Desbloquear', ENT_QUOTES, 'UTF-8'); ?></h2>
                <div class="adeptos-list">
                    <?php foreach ($adeptosBloqueados as $adepto): ?>
                        <?php $vistaAdepto = $prepararAdepto($adepto); ?>
                        <span class="adepto-item bloqueado"><img src="../../Assets/<?php echo htmlspecialchars($vistaAdepto['archivo'], ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($vistaAdepto['nombre'], ENT_QUOTES, 'UTF-8'); ?>" class="adepto-icon"></span>
                    <?php endforeach; ?>
                    <?php if ($adeptosBloqueados === []): ?>
                        <p class="lista-vacia"><?php echo htmlspecialchars($txt['gacha_todos_desbloqueados'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <?php endif; ?>
                </div>
            </section>
        </div>

        <?php if ($mensajeGacha !== ''): ?>
            <p class="gacha-feedback <?php echo $mensajeExito ? 'exito' : 'error'; ?>"><?php echo htmlspecialchars($mensajeGacha, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

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
