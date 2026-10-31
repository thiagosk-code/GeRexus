<?php
require_once __DIR__ . '/../Scripts/auth_check.php';
require_once __DIR__ . '/../Scripts/lang.php';
require_once __DIR__ . '/../Scripts/sala_render.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$idUsuario = (int)$usuarioActual->getIdUsuario();
$logicaSala = $fachadaLogica->retornoILogicaSala();

$codigo = strtoupper(trim((string)($_GET['codigo'] ?? '')));
if ($codigo === '') {
    $codigo = (string)$logicaSala->obtenerSalaActivaL($idUsuario);
}

if ($codigo === '') {
    header('Location: digital.php');
    exit();
}

$res = $logicaSala->obtenerEstadoSalaL($idUsuario, $codigo, 0);

if ($res['exito'] === false || $res['existe'] === false) {
    header('Location: digital.php');
    exit();
}

$sala = $res['sala'];

if ($sala->getEstado() === 'en_juego' && $sala->getMiEstado() === 'activo') {
    header('Location: partida.php?id=' . (int)$sala->getIdPartida());
    exit();
}

if ($sala->getEstado() !== 'espera' || $sala->getMiEstado() !== 'activo') {
    header('Location: digital.php');
    exit();
}

$esAnfitrion = $res['esAnfitrion'];
$jugadores = $sala->getJugadores();
$codigoUrl = urlencode($sala->getCodigo());

$claveFlash = $_SESSION['flash_sala'] ?? '';
unset($_SESSION['flash_sala']);
$mensajeFlash = $claveFlash !== '' ? ($txt[$claveFlash] ?? '') : '';
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang ?? 'es', ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeRexus - <?php echo htmlspecialchars($txt['sala_espera'] ?? 'Sala de Espera', ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="icon" type="image/png" href="../../Assets/Socrates.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Michroma&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <link rel="stylesheet" href="../CSSs/style.css">
    <link rel="stylesheet" href="../CSSs/sala.css">
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
                    <a href="?codigo=<?php echo $codigoUrl; ?>&lang=es" class="inactive">Esp</a>
                    <span class="divider">|</span>
                    <span class="active">Eng</span>
                <?php else: ?>
                    <span class="active">Esp</span>
                    <span class="divider">|</span>
                    <a href="?codigo=<?php echo $codigoUrl; ?>&lang=en" class="inactive">Eng</a>
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

    <main class="sala-content" id="sala-main" data-codigo="<?php echo htmlspecialchars($sala->getCodigo(), ENT_QUOTES, 'UTF-8'); ?>" data-version="<?php echo (int)$sala->getVersion(); ?>" data-estado-url="estado_sala.php">
        <h1 class="main-title"><?php echo htmlspecialchars($txt['sala_espera'] ?? 'Sala de Espera', ENT_QUOTES, 'UTF-8'); ?></h1>

        <section class="glass-box sala-card">
            <h2 class="sala-label"><?php echo htmlspecialchars($txt['codigo_invitacion_label'] ?? 'Código de invitación', ENT_QUOTES, 'UTF-8'); ?></h2>
            <p class="sala-codigo"><?php echo htmlspecialchars($sala->getCodigo(), ENT_QUOTES, 'UTF-8'); ?></p>

            <div class="card-divider"></div>

            <h2 class="sala-label"><?php echo htmlspecialchars($txt['jugadores'] ?? 'Jugadores', ENT_QUOTES, 'UTF-8'); ?> <span class="sala-contador" id="sala-contador"><?php echo count($jugadores); ?>/<?php echo (int)$sala->getMaxJugadores(); ?></span></h2>
            <ul class="jugadores-lista" id="jugadores-lista"><?php echo renderJugadoresSala($jugadores, $sala->getIdAnfitrion(), $esAnfitrion, $txt); ?></ul>
        </section>

        <?php if ($mensajeFlash !== ''): ?>
            <p class="msg-status-placeholder msg-error"><?php echo htmlspecialchars($mensajeFlash, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <div class="acciones-sala">
            <?php if ($esAnfitrion): ?>
                <button type="button" class="btn-nav" data-open="modal-cancelar"><?php echo htmlspecialchars($txt['cancelar_partida'] ?? 'Cancelar Partida', ENT_QUOTES, 'UTF-8'); ?></button>
                <button type="button" class="btn-nav" data-open="modal-comenzar"><?php echo htmlspecialchars($txt['comenzar_partida'] ?? 'Comenzar Partida', ENT_QUOTES, 'UTF-8'); ?></button>
            <?php else: ?>
                <button type="button" class="btn-nav" data-open="modal-salir"><?php echo htmlspecialchars($txt['salir_sala'] ?? 'Salir de la Sala', ENT_QUOTES, 'UTF-8'); ?></button>
            <?php endif; ?>
        </div>
    </main>

    <?php if ($esAnfitrion): ?>
    <div class="modal-overlay" id="modal-echar">
        <div class="modal-window">
            <button type="button" class="modal-close-btn" data-close="modal-echar" aria-label="<?php echo htmlspecialchars($txt['cerrar'] ?? 'Cerrar', ENT_QUOTES, 'UTF-8'); ?>">&times;</button>
            <h2 class="modal-header-title"><?php echo htmlspecialchars($txt['echar_usuario'] ?? 'Echar Usuario', ENT_QUOTES, 'UTF-8'); ?></h2>
            <p class="modal-body-txt"><?php echo htmlspecialchars($txt['confirmar_echar_usuario'] ?? '¿Seguro que desea remover al usuario de la partida?', ENT_QUOTES, 'UTF-8'); ?></p>
            <form action="acciones_sala.php" method="POST" class="modal-actions">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="accion" value="echar_usuario">
                <input type="hidden" name="codigo" value="<?php echo htmlspecialchars($sala->getCodigo(), ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="id_jugador" id="echar-id-jugador" value="">
                <button type="submit" class="btn-modal btn-danger"><?php echo htmlspecialchars($txt['confirmar'] ?? 'Confirmar', ENT_QUOTES, 'UTF-8'); ?></button>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modal-cancelar">
        <div class="modal-window">
            <button type="button" class="modal-close-btn" data-close="modal-cancelar" aria-label="<?php echo htmlspecialchars($txt['cerrar'] ?? 'Cerrar', ENT_QUOTES, 'UTF-8'); ?>">&times;</button>
            <h2 class="modal-header-title"><?php echo htmlspecialchars($txt['cancelar_partida'] ?? 'Cancelar Partida', ENT_QUOTES, 'UTF-8'); ?></h2>
            <p class="modal-body-txt"><?php echo htmlspecialchars($txt['confirmar_cancelar_partida'] ?? '¿Seguro que desea cancelar la partida?', ENT_QUOTES, 'UTF-8'); ?></p>
            <form action="acciones_sala.php" method="POST" class="modal-actions">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="accion" value="cancelar_partida">
                <input type="hidden" name="codigo" value="<?php echo htmlspecialchars($sala->getCodigo(), ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="btn-modal btn-danger"><?php echo htmlspecialchars($txt['confirmar'] ?? 'Confirmar', ENT_QUOTES, 'UTF-8'); ?></button>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modal-comenzar">
        <div class="modal-window">
            <button type="button" class="modal-close-btn" data-close="modal-comenzar" aria-label="<?php echo htmlspecialchars($txt['cerrar'] ?? 'Cerrar', ENT_QUOTES, 'UTF-8'); ?>">&times;</button>
            <h2 class="modal-header-title"><?php echo htmlspecialchars($txt['comenzar_partida'] ?? 'Comenzar Partida', ENT_QUOTES, 'UTF-8'); ?></h2>
            <p class="modal-body-txt"><?php echo htmlspecialchars($txt['confirmar_comenzar_partida'] ?? '¿Quieres comenzar la partida con los jugadores actuales?', ENT_QUOTES, 'UTF-8'); ?></p>
            <form action="acciones_sala.php" method="POST" class="modal-actions">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="accion" value="comenzar_partida">
                <input type="hidden" name="codigo" value="<?php echo htmlspecialchars($sala->getCodigo(), ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="btn-modal btn-ok"><?php echo htmlspecialchars($txt['si'] ?? 'Sí', ENT_QUOTES, 'UTF-8'); ?></button>
                <button type="button" class="btn-modal btn-danger" data-close="modal-comenzar"><?php echo htmlspecialchars($txt['no'] ?? 'No', ENT_QUOTES, 'UTF-8'); ?></button>
            </form>
        </div>
    </div>
    <?php else: ?>
    <div class="modal-overlay" id="modal-salir">
        <div class="modal-window">
            <button type="button" class="modal-close-btn" data-close="modal-salir" aria-label="<?php echo htmlspecialchars($txt['cerrar'] ?? 'Cerrar', ENT_QUOTES, 'UTF-8'); ?>">&times;</button>
            <h2 class="modal-header-title"><?php echo htmlspecialchars($txt['salir_sala'] ?? 'Salir de la Sala', ENT_QUOTES, 'UTF-8'); ?></h2>
            <p class="modal-body-txt"><?php echo htmlspecialchars($txt['confirmar_salir_sala'] ?? '¿Seguro que desea salir de la sala?', ENT_QUOTES, 'UTF-8'); ?></p>
            <form action="acciones_sala.php" method="POST" class="modal-actions">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="accion" value="salir_sala">
                <input type="hidden" name="codigo" value="<?php echo htmlspecialchars($sala->getCodigo(), ENT_QUOTES, 'UTF-8'); ?>">
                <button type="submit" class="btn-modal btn-danger"><?php echo htmlspecialchars($txt['confirmar'] ?? 'Confirmar', ENT_QUOTES, 'UTF-8'); ?></button>
            </form>
        </div>
    </div>
    <?php endif; ?>

    <script src="../Scripts/lang.js"></script>
    <script src="../Scripts/Temas.js"></script>
    <script src="../Scripts/header.js" defer></script>
    <script src="../Scripts/sala.js" defer></script>
</body>
</html>
