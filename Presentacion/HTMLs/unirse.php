<?php
require_once __DIR__ . '/../Scripts/auth_check.php';
require_once __DIR__ . '/../Scripts/lang.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$idUsuario = (int)$usuarioActual->getIdUsuario();
$logicaSala = $fachadaLogica->retornoILogicaSala();

$salaActiva = $logicaSala->obtenerSalaActivaL($idUsuario);
if ($salaActiva !== null) {
    header('Location: sala.php?codigo=' . urlencode($salaActiva));
    exit();
}

$mensajeError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenEnviado = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $tokenEnviado)) {
        $mensajeError = $txt['err_csrf'];
    } else {
        $res = $logicaSala->unirseSalaL($idUsuario, (string)($_POST['codigo_invitacion'] ?? ''));

        if ($res['exito'] === true) {
            header('Location: sala.php?codigo=' . urlencode($res['codigo']));
            exit();
        }

        $mensajeError = $txt[$res['mensaje_key']] ?? $res['mensaje'];
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang ?? 'es', ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeRexus - <?php echo htmlspecialchars($txt['unirse_partida'] ?? 'Unirse Partida', ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="icon" type="image/png" href="../../Assets/Socrates.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Michroma&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <link rel="stylesheet" href="../CSSs/style.css">
    <link rel="stylesheet" href="../CSSs/unirse.css">
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

    <main class="unirse-content">
        <h1 class="main-title"><?php echo htmlspecialchars($txt['unirse_partida'] ?? 'Unirse Partida', ENT_QUOTES, 'UTF-8'); ?></h1>

        <form class="glass-box unirse-form" action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <label for="codigo-invitacion" class="card-title"><?php echo htmlspecialchars($txt['codigo_invitacion'] ?? 'Ingrese Código de invitación', ENT_QUOTES, 'UTF-8'); ?></label>
            <input type="text" id="codigo-invitacion" name="codigo_invitacion" class="input-pill" placeholder="<?php echo htmlspecialchars($txt['placeholder_codigo_invitacion'] ?? 'Ejemplo123', ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off" maxlength="6" autocapitalize="characters" spellcheck="false" required>
            <?php if ($mensajeError !== ''): ?>
                <p class="msg-error"><?php echo htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
            <button type="submit" class="btn-unirse"><?php echo htmlspecialchars($txt['unirse'] ?? 'Unirse', ENT_QUOTES, 'UTF-8'); ?></button>
        </form>

        <div class="volver-container">
            <a href="base.php" class="btn-nav"><?php echo htmlspecialchars($txt['volver'] ?? 'Volver', ENT_QUOTES, 'UTF-8'); ?></a>
        </div>
    </main>

    <script src="../Scripts/lang.js"></script>
    <script src="../Scripts/Temas.js"></script>
    <script src="../Scripts/header.js" defer></script>
</body>
</html>
