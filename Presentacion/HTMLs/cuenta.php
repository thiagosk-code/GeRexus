<?php
require_once __DIR__ . '/../Scripts/auth_check.php';
require_once __DIR__ . '/../Scripts/lang.php';
require_once __DIR__ . '/../../Logica/FachadaLogica.php';
require_once __DIR__ . '/../../DTO/UsuarioDTO.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$msgCuenta = '';
$esErrorCuenta = false;

$fachadaLogica = new FachadaLogica();
$logicaUsuario = $fachadaLogica->retornoILogicaUsuario();

$idUsuarioLogueado = 0;
if (isset($usuarioActual) && method_exists($usuarioActual, 'getIdUsuario')) {
    $idUsuarioLogueado = $usuarioActual->getIdUsuario();
} elseif (isset($_SESSION['usuario_id'])) {
    $idUsuarioLogueado = (int)$_SESSION['usuario_id'];
} elseif (isset($_SESSION['id'])) {
    $idUsuarioLogueado = (int)$_SESSION['id'];
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenEnviado = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $tokenEnviado)) {
        die('Solicitud rechazada: Token de seguridad no válido (CSRF).');
    }

    $accion = $_POST['accion'] ?? '';

    if ($accion === 'modificar_nombre') {
        $nuevoNombre = trim($_POST['nuevo_nombre'] ?? '');
        
        $resForm = $logicaUsuario->procesarModificacionNombrePropio($idUsuarioLogueado, $nuevoNombre);
        
        $key = $resForm['mensaje_key'] ?? '';
        $msgCuenta = isset($txt[$key]) ? $txt[$key] : ($resForm['mensaje'] ?? '');
        $esErrorCuenta = ($resForm['exito'] === false);

        if ($resForm['exito'] === true) {
            $_SESSION['usuario_nombre'] = $nuevoNombre;
            $nombreUsuarioLogueado = $nuevoNombre;
        }

    } elseif ($accion === 'eliminar_cuenta') {
        $resForm = $logicaUsuario->procesarBajaCuentaPropia($idUsuarioLogueado);

        if ($resForm['exito'] === true) {
            header('Location: login.php');
            exit;
        } else {
            $key = $resForm['mensaje_key'] ?? '';
            $msgCuenta = isset($txt[$key]) ? $txt[$key] : ($resForm['mensaje'] ?? '');
            $esErrorCuenta = true;
        }
    } elseif ($accion === 'cerrar_sesion') {
        $logicaUsuario->procesarCierreSesion();
        header('Location: login.php');
        exit;
    }
}

$nom = $nombreUsuarioLogueado ?? ($_SESSION['usuario_nombre'] ?? 'User');
$email = ($usuarioActual !== null && method_exists($usuarioActual, 'getEmail')) ? $usuarioActual->getEmail() : ($_SESSION['usuario_email'] ?? 'Ejemplo@gmail.com');
$admin = ($usuarioActual !== null && method_exists($usuarioActual, 'getEsAdmin')) ? ($usuarioActual->getEsAdmin() === true || $usuarioActual->getEsAdmin() === 1) : false;
$wins = ($usuarioActual !== null && method_exists($usuarioActual, 'getPartidasGanadas')) ? $usuarioActual->getPartidasGanadas() : 0;
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang ?? 'es', ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeRexus - <?php echo htmlspecialchars($nom, ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="icon" type="image/png" href="../../Assets/Socrates.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Michroma&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <link rel="stylesheet" href="../CSSs/style.css">
    <link rel="stylesheet" href="../CSSs/base.css">
    <link rel="stylesheet" href="../CSSs/cuenta.css">
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

            <a href="cuenta.php" class="user-profile-link">
                <div class="user-profile">
                    <span class="user-name"><?php echo htmlspecialchars($nom, ENT_QUOTES, 'UTF-8'); ?></span>
                    <span class="user-avatar-img" id="header-avatar-img"></span>
                </div>
            </a>
        </div>
    </header>

    <main class="main-content cuenta-content">
        <section class="hero-section">
            <h1 class="main-title"><?php echo htmlspecialchars($nom, ENT_QUOTES, 'UTF-8'); ?></h1>
        </section>

        <div class="cuenta-grid">
            <div class="col-left">
                <div class="glass-box">
                    <div class="avatar-section">
                        <h2 class="section-subtitle"><?php echo htmlspecialchars($txt['avatar'] ?? 'Avatar:', ENT_QUOTES, 'UTF-8'); ?></h2>
                        <div class="avatar-row">
                            <button type="button" class="avatar-opt active" data-src="../../Assets/DiogenesPerro.png">
                                <span class="avatar-thumb" style="background-image: url('../../Assets/DiogenesPerro.png');"></span>
                            </button>
                            <button type="button" class="avatar-opt" data-src="../../Assets/SocratesHyrax.png">
                                <span class="avatar-thumb" style="background-image: url('../../Assets/SocratesHyrax.png');"></span>
                            </button>
                            <button type="button" class="avatar-opt" data-src="../../Assets/HypatiaAbeja.png">
                                <span class="avatar-thumb" style="background-image: url('../../Assets/HypatiaAbeja.png');"></span>
                            </button>
                            <button type="button" class="avatar-opt" data-src="../../Assets/AristotelesTortuga.png">
                                <span class="avatar-thumb" style="background-image: url('../../Assets/AristotelesTortuga.png');"></span>
                            </button>
                            <button type="button" class="avatar-opt" data-src="../../Assets/PlatonPato.png">
                                <span class="avatar-thumb" style="background-image: url('../../Assets/PlatonPato.png');"></span>
                            </button>
                            <button type="button" class="avatar-opt btn-add-avatar" id="btn-open-avatars">+</button>
                        </div>
                    </div>

                    <div class="info-row">
                        <span class="info-label"><?php echo htmlspecialchars($txt['correo'] ?? 'Email:', ENT_QUOTES, 'UTF-8'); ?>:</span>
                        <span class="info-val"><?php echo htmlspecialchars($email, ENT_QUOTES, 'UTF-8'); ?></span>
                    </div>

                    <form action="" method="POST" class="form-username">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="accion" value="modificar_nombre">

                        <label for="nuevo_nombre" class="info-label"><?php echo htmlspecialchars($txt['nombre'] ?? 'Nombre', ENT_QUOTES, 'UTF-8'); ?>:</label>
                        <input type="text" id="nuevo_nombre" name="nuevo_nombre" class="input-pill" value="<?php echo htmlspecialchars($nom, ENT_QUOTES, 'UTF-8'); ?>" required>
                        <button type="submit" class="btn-sub-pill"><?php echo htmlspecialchars($txt['actualizar'] ?? 'Actualizar', ENT_QUOTES, 'UTF-8'); ?></button>
                    </form>

                    <?php if ($msgCuenta !== ''): ?>
                        <p class="msg-status-placeholder <?php echo $esErrorCuenta === true ? 'msg-error' : 'msg-exito'; ?>">
                            <?php echo htmlspecialchars($msgCuenta, ENT_QUOTES, 'UTF-8'); ?>
                        </p>
                    <?php endif; ?>

                    <div class="danger-actions">
                        <button type="button" class="btn-danger-pill" id="btn-open-logout"><?php echo htmlspecialchars($txt['cerrar_sesion'] ?? 'Cerrar Sesión', ENT_QUOTES, 'UTF-8'); ?></button>
                        <button type="button" class="btn-danger-pill" id="btn-open-delete"><?php echo htmlspecialchars($txt['eliminar_cuenta'] ?? 'Eliminar cuenta', ENT_QUOTES, 'UTF-8'); ?></button>
                    </div>
                </div>

                <div class="bottom-nav-row">
                    <a href="base.php" class="btn-nav"><?php echo htmlspecialchars($txt['menu_principal'] ?? 'Menú Principal', ENT_QUOTES, 'UTF-8'); ?></a>
                    <?php if ($admin === true): ?>
                        <a href="panelAdmin.php" class="btn-nav"><?php echo htmlspecialchars($txt['panel_admin'] ?? 'Panel de Administración', ENT_QUOTES, 'UTF-8'); ?></a>
                    <?php endif; ?>
                </div>
            </div>

            <div class="col-right">
                <div class="glass-box games-section">
                    <h2 class="section-subtitle center"><?php echo htmlspecialchars($txt['partidas_jugadas'] ?? 'Partidas Jugadas', ENT_QUOTES, 'UTF-8'); ?></h2>
                    <span class="wins-counter"><?php echo htmlspecialchars($txt['victorias'] ?? 'Victorias:', ENT_QUOTES, 'UTF-8'); ?> <?php echo $wins; ?></span>

                    <div class="games-scroll-container">
                        <div class="game-card">
                            <span class="status-txt status-victory"><?php echo htmlspecialchars($txt['victoria'] ?? 'Victoria', ENT_QUOTES, 'UTF-8'); ?></span>
                            <div class="game-details">
                                <span><?php echo htmlspecialchars($txt['puesto'] ?? 'Puesto:', ENT_QUOTES, 'UTF-8'); ?> 1</span>
                                <span><?php echo htmlspecialchars($txt['puntos'] ?? 'Puntos:', ENT_QUOTES, 'UTF-8'); ?> 256</span>
                            </div>
                            <hr class="card-divider">
                            <button type="button" class="btn-sub-pill btn-moves"><?php echo htmlspecialchars($txt['ver_jugadas'] ?? 'Ver Jugadas', ENT_QUOTES, 'UTF-8'); ?></button>
                        </div>

                        <div class="game-card">
                            <span class="status-txt status-defeat"><?php echo htmlspecialchars($txt['derrota'] ?? 'Derrota', ENT_QUOTES, 'UTF-8'); ?></span>
                            <div class="game-details">
                                <span><?php echo htmlspecialchars($txt['puesto'] ?? 'Puesto:', ENT_QUOTES, 'UTF-8'); ?> 4</span>
                                <span><?php echo htmlspecialchars($txt['puntos'] ?? 'Puntos:', ENT_QUOTES, 'UTF-8'); ?> 32</span>
                            </div>
                            <hr class="card-divider">
                            <button type="button" class="btn-sub-pill btn-moves"><?php echo htmlspecialchars($txt['ver_jugadas'] ?? 'Ver Jugadas', ENT_QUOTES, 'UTF-8'); ?></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="modal-overlay" id="modal-avatars">
        <div class="modal-window modal-wide">
            <h2 class="modal-header-title"><?php echo htmlspecialchars($txt['avatares'] ?? 'Avatares', ENT_QUOTES, 'UTF-8'); ?></h2>
            <div class="avatars-modal-grid">
                <button type="button" class="avatar-opt" data-src="../../Assets/Diogenes.png"><span class="avatar-thumb" style="background-image: url('../../Assets/Diogenes.png');"></span></button>
                <button type="button" class="avatar-opt" data-src="../../Assets/DiogenesPerro.png"><span class="avatar-thumb" style="background-image: url('../../Assets/DiogenesPerro.png');"></span></button>
                <button type="button" class="avatar-opt" data-src="../../Assets/Socrates.png"><span class="avatar-thumb" style="background-image: url('../../Assets/Socrates.png');"></span></button>
                <button type="button" class="avatar-opt" data-src="../../Assets/SocratesHyrax.png"><span class="avatar-thumb" style="background-image: url('../../Assets/SocratesHyrax.png');"></span></button>
                <button type="button" class="avatar-opt" data-src="../../Assets/Hypatia.png"><span class="avatar-thumb" style="background-image: url('../../Assets/Hypatia.png');"></span></button>
                <button type="button" class="avatar-opt" data-src="../../Assets/HypatiaAbeja.png"><span class="avatar-thumb" style="background-image: url('../../Assets/HypatiaAbeja.png');"></span></button>
                <button type="button" class="avatar-opt" data-src="../../Assets/Platon.png"><span class="avatar-thumb" style="background-image: url('../../Assets/Platon.png');"></span></button>
                <button type="button" class="avatar-opt" data-src="../../Assets/PlatonPato.png"><span class="avatar-thumb" style="background-image: url('../../Assets/PlatonPato.png');"></span></button>
                <button type="button" class="avatar-opt" data-src="../../Assets/Epicuro.png"><span class="avatar-thumb" style="background-image: url('../../Assets/Epicuro.png');"></span></button>
                <button type="button" class="avatar-opt" data-src="../../Assets/EpicuroKoala.png"><span class="avatar-thumb" style="background-image: url('../../Assets/EpicuroKoala.png');"></span></button>
                <button type="button" class="avatar-opt" data-src="../../Assets/Sartre.png"><span class="avatar-thumb" style="background-image: url('../../Assets/Sartre.png');"></span></button>
                <button type="button" class="avatar-opt" data-src="../../Assets/Camus.png"><span class="avatar-thumb" style="background-image: url('../../Assets/Camus.png');"></span></button>
                <button type="button" class="avatar-opt" data-src="../../Assets/Nietzsche.png"><span class="avatar-thumb" style="background-image: url('../../Assets/Nietzsche.png');"></span></button>
                <button type="button" class="avatar-opt" data-src="../../Assets/RexRojoPng.png"><span class="avatar-thumb" style="background-image: url('../../Assets/RexRojoPng.png');"></span></button>
                <button type="button" class="avatar-opt" data-src="../../Assets/RexVerdePng.png"><span class="avatar-thumb" style="background-image: url('../../Assets/RexVerdePng.png');"></span></button>
            </div>
            <div class="modal-footer-left">
                <button type="button" class="btn-sub-pill" data-close="modal-avatars"><?php echo htmlspecialchars($txt['cerrar_panel'] ?? 'Cerrar Panel', ENT_QUOTES, 'UTF-8'); ?></button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modal-logout">
        <div class="modal-window">
            <button type="button" class="modal-close-btn" data-close="modal-logout">&times;</button>
            <h2 class="modal-header-title text-danger"><?php echo htmlspecialchars($txt['cerrar_sesion'] ?? 'Cerrar Sesión', ENT_QUOTES, 'UTF-8'); ?></h2>
            <p class="modal-body-txt"><?php echo htmlspecialchars($txt['confirmar_cerrar_sesion'] ?? '¿Seguro que usted desea cerrar la sesión activa?', ENT_QUOTES, 'UTF-8'); ?></p>
            <form action="" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="accion" value="cerrar_sesion">
                <button type="submit" class="btn-sub-pill btn-full-width"><?php echo htmlspecialchars($txt['confirmar'] ?? 'Confirmar', ENT_QUOTES, 'UTF-8'); ?></button>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modal-delete">
        <div class="modal-window">
            <button type="button" class="modal-close-btn" data-close="modal-delete">&times;</button>
            <h2 class="modal-header-title text-danger"><?php echo htmlspecialchars($txt['eliminar_cuenta'] ?? 'Eliminar Cuenta', ENT_QUOTES, 'UTF-8'); ?></h2>
            <p class="modal-body-txt"><?php echo $txt['confirmar_eliminar_cuenta'] ?? '¿Seguro que usted desea eliminar su cuenta?<br>Este proceso es irreversible.'; ?></p>
            <form action="" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="accion" value="eliminar_cuenta">
                <button type="submit" class="btn-sub-pill btn-full-width"><?php echo htmlspecialchars($txt['confirmar'] ?? 'Confirmar', ENT_QUOTES, 'UTF-8'); ?></button>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modal-moves">
        <div class="modal-window modal-wide">
            <button type="button" class="modal-close-btn" data-close="modal-moves">&times;</button>
            <h2 class="modal-header-title"><?php echo htmlspecialchars($txt['historial_jugadas'] ?? 'Historial de Jugadas', ENT_QUOTES, 'UTF-8'); ?></h2>
            <div class="moves-scroll-box">
                <p>Jugador Pepe Asigna Koala a sala Rey Filósofo</p>
                <p>Jugador Pepe Asigna Koala a sala Rey Filósofo</p>
                <p>Jugador Pepe Asigna Koala a sala Rey Filósofo</p>
            </div>
            <button type="button" class="btn-sub-pill btn-full-width" data-close="modal-moves"><?php echo htmlspecialchars($txt['cerrar_panel'] ?? 'Cerrar Panel', ENT_QUOTES, 'UTF-8'); ?></button>
        </div>
    </div>

    <script src="../Scripts/lang.js"></script>
    <script src="../Scripts/Temas.js"></script>
    <script src="../Scripts/header.js" defer></script>
    <script src="../Scripts/cuenta.js" defer></script>
</body>
</html>