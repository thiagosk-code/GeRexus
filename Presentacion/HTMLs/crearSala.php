<?php
require_once __DIR__ . '/../Scripts/auth_check.php';
require_once __DIR__ . '/../Scripts/lang.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$idUsuario = (int)$usuarioActual->getIdUsuario();
$logicaSala = $fachadaLogica->retornoILogicaSala();
$logicaAutorizacion = $fachadaLogica->retornoILogicaAutorizacion();

if ($logicaAutorizacion->tienePermiso($usuarioActual, 'crear_sala') === false) {
    header('Location: digital.php');
    exit();
}

$salaActiva = $logicaSala->obtenerSalaActivaL($idUsuario);
if ($salaActiva !== null) {
    header('Location: sala.php?codigo=' . urlencode($salaActiva));
    exit();
}

$tableros = $logicaSala->obtenerTablerosL();
$disponibles = $logicaSala->obtenerAdeptosDisponiblesL($idUsuario);
$seleccion = array_slice($disponibles, 0, 6);
$tableroElegido = count($tableros) > 0 ? $tableros[0]->getIdTablero() : 0;
$mensajeError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenEnviado = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $tokenEnviado)) {
        $mensajeError = 'Peticion no valida (Error CSRF).';
    } else {
        $tableroElegido = (int)($_POST['idTablero'] ?? 0);
        $idsAdeptos = array_map('intval', (array)($_POST['adeptos'] ?? []));
        $res = $logicaSala->crearSalaL($idUsuario, $tableroElegido, $idsAdeptos);

        if ($res['exito'] === true) {
            header('Location: sala.php?codigo=' . urlencode($res['codigo']));
            exit();
        }

        $mensajeError = $txt[$res['mensaje_key']] ?? $res['mensaje'];

        $mapa = [];
        foreach ($disponibles as $disponible) {
            $mapa[$disponible->getIdAdepto()] = $disponible;
        }
        $reordenados = [];
        foreach ($idsAdeptos as $idAdepto) {
            if (isset($mapa[$idAdepto])) {
                $reordenados[] = $mapa[$idAdepto];
            }
        }
        if (count($reordenados) === 6) {
            $seleccion = $reordenados;
        }
    }
}

$puedeCrear = (count($disponibles) >= 6 && count($tableros) > 0);
$etiquetaAdepto = $txt['adepto'] ?? 'Adepto';
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang ?? 'es', ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeRexus - <?php echo htmlspecialchars($txt['crear_partida'] ?? 'Crear Partida', ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="icon" type="image/png" href="../../Assets/Socrates.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Michroma&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <link rel="stylesheet" href="../CSSs/style.css">
    <link rel="stylesheet" href="../CSSs/crearSala.css">
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

    <main class="crear-content">
        <h1 class="main-title"><?php echo htmlspecialchars($txt['crear_partida'] ?? 'Crear Partida', ENT_QUOTES, 'UTF-8'); ?></h1>

        <form class="crear-form" action="" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">

            <section class="glass-box">
                <h2 class="seccion-titulo"><?php echo htmlspecialchars($txt['tablero'] ?? 'Tablero', ENT_QUOTES, 'UTF-8'); ?></h2>
                <div class="tableros-grid">
                    <?php foreach ($tableros as $tablero): ?>
                        <label class="tablero-opt">
                            <input type="radio" name="idTablero" value="<?php echo (int)$tablero->getIdTablero(); ?>" class="tablero-radio"<?php echo ($tablero->getIdTablero() === $tableroElegido) ? ' checked' : ''; ?>>
                            <span class="tablero-card">
                                <img src="../../Assets/<?php echo htmlspecialchars($tablero->getImagen(), ENT_QUOTES, 'UTF-8'); ?>" alt="" class="tablero-img">
                                <span class="tablero-nombre"><?php echo htmlspecialchars($tablero->getNombre(), ENT_QUOTES, 'UTF-8'); ?></span>
                            </span>
                        </label>
                    <?php endforeach; ?>
                </div>

                <h2 class="seccion-titulo"><?php echo htmlspecialchars($txt['adeptos'] ?? 'Adeptos', ENT_QUOTES, 'UTF-8'); ?></h2>
                <div class="adeptos-slots">
                    <?php foreach ($seleccion as $indice => $adepto): ?>
                        <div class="adepto-slot-wrap">
                            <span class="slot-numero"><?php echo $indice + 1; ?></span>
                            <button type="button" class="adepto-slot" aria-label="<?php echo htmlspecialchars($etiquetaAdepto, ENT_QUOTES, 'UTF-8'); ?> <?php echo $indice + 1; ?>">
                                <img src="../../Assets/<?php echo htmlspecialchars($adepto->getRuta(), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($adepto->getEspecie(), ENT_QUOTES, 'UTF-8'); ?>" class="adepto-icon">
                            </button>
                            <input type="hidden" name="adeptos[]" value="<?php echo (int)$adepto->getIdAdepto(); ?>">
                        </div>
                    <?php endforeach; ?>
                </div>

                <?php if ($puedeCrear === false): ?>
                    <p class="msg-error"><?php echo htmlspecialchars($txt['adeptos_insuficientes'] ?? 'Necesitas al menos 6 adeptos desbloqueados para crear una sala.', ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>

                <?php if ($mensajeError !== ''): ?>
                    <p class="msg-error"><?php echo htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>
            </section>

            <button type="submit" class="btn-nav btn-crear"<?php echo $puedeCrear ? '' : ' disabled'; ?>><?php echo htmlspecialchars($txt['crear_partida'] ?? 'Crear Partida', ENT_QUOTES, 'UTF-8'); ?></button>
        </form>

        <div class="volver-container">
            <a href="digital.php" class="btn-nav"><?php echo htmlspecialchars($txt['volver'] ?? 'Volver', ENT_QUOTES, 'UTF-8'); ?></a>
        </div>
    </main>

    <div class="modal-overlay" id="modal-adepto" data-etiqueta="<?php echo htmlspecialchars($etiquetaAdepto, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="modal-window modal-wide">
            <button type="button" class="modal-close-btn" data-close="modal-adepto" aria-label="<?php echo htmlspecialchars($txt['cerrar'] ?? 'Cerrar', ENT_QUOTES, 'UTF-8'); ?>">&times;</button>
            <h2 class="modal-header-title" id="modal-adepto-titulo"><?php echo htmlspecialchars($etiquetaAdepto, ENT_QUOTES, 'UTF-8'); ?> 1</h2>

            <div class="adepto-actual">
                <span class="adepto-select-btn"><img src="" alt="" id="modal-adepto-img" class="adepto-icon"></span>
            </div>

            <h3 class="panel-subtitle"><?php echo htmlspecialchars($txt['adeptos_disponibles'] ?? 'Adeptos Disponibles', ENT_QUOTES, 'UTF-8'); ?></h3>
            <div class="adeptos-row">
                <?php foreach ($disponibles as $disponible): ?>
                    <button type="button" class="adepto-select-btn" data-id="<?php echo (int)$disponible->getIdAdepto(); ?>" data-src="../../Assets/<?php echo htmlspecialchars($disponible->getRuta(), ENT_QUOTES, 'UTF-8'); ?>" data-nombre="<?php echo htmlspecialchars($disponible->getEspecie(), ENT_QUOTES, 'UTF-8'); ?>">
                        <img src="../../Assets/<?php echo htmlspecialchars($disponible->getRuta(), ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($disponible->getEspecie(), ENT_QUOTES, 'UTF-8'); ?>" class="adepto-icon">
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="modal-footer-left">
                <button type="button" class="btn-sub-pill" data-close="modal-adepto"><?php echo htmlspecialchars($txt['cerrar_panel'] ?? 'Cerrar Panel', ENT_QUOTES, 'UTF-8'); ?></button>
            </div>
        </div>
    </div>

    <script src="../Scripts/lang.js"></script>
    <script src="../Scripts/Temas.js"></script>
    <script src="../Scripts/header.js" defer></script>
    <script src="../Scripts/crearSala.js" defer></script>
</body>
</html>
