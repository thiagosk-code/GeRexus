<?php
require_once __DIR__ . '/../Scripts/auth_check.php';
require_once __DIR__ . '/../Scripts/lang.php';
require_once __DIR__ . '/../../Logica/ConfiguracionRecintos.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$recintosDef = [];
foreach (ConfiguracionRecintos::todas() as $definicion) {
    $idRegla = (int)$definicion['id_regla'];
    $recintosDef[$idRegla] = [
        'id' => $idRegla,
        'nombre_key' => $definicion['nombre_key'],
        'nombre_default' => $definicion['nombre_default'],
        'x' => $definicion['x'], 'y' => $definicion['y'],
        'w' => $definicion['w'], 'h' => $definicion['h'],
        'rey' => $definicion['codigo'] === 'rey_filosofo'
    ];
}

$adeptosDisponibles = [
    'DiogenesPerro.png' => ['nombre' => $txt['diogenes'] ?? 'Diógenes', 'especie' => 'perro', 'corriente' => 'cinica'],
    'SocratesHyrax.png' => ['nombre' => $txt['socrates'] ?? 'Sócrates', 'especie' => 'hyrax', 'corriente' => 'socratica'],
    'HypatiaAbeja.png' => ['nombre' => $txt['hypatia'] ?? 'Hypatia', 'especie' => 'abeja', 'corriente' => 'neoplatonica'],
    'AristotelesTortuga.png' => ['nombre' => $txt['aristoteles'] ?? 'Aristóteles', 'especie' => 'tortuga', 'corriente' => 'peripatetica'],
    'PlatonPato.png' => ['nombre' => $txt['platon'] ?? 'Platón', 'especie' => 'pato', 'corriente' => 'platonica'],
    'EpicuroKoala.png' => ['nombre' => $txt['epicuro'] ?? 'Epicuro', 'especie' => 'koala', 'corriente' => 'epicurea']
];

if (!isset($_SESSION['escuela']) || !is_array($_SESSION['escuela'])) {
    $_SESSION['escuela'] = [1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 'pasillo' => []];
}

$mensajeError = '';
$logicaRecintos = $fachadaLogica->retornoILogicaRecinto();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenEnviado = (string)($_POST['csrf_token'] ?? '');

    if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $tokenEnviado)) {
        $mensajeError = $txt['err_csrf'] ?? 'Token de seguridad inválido.';
    } else {
        $accion = (string)($_POST['accion'] ?? '');

        if ($accion === 'reiniciar_tablero') {
            unset($_SESSION['escuela']);
            header('Location: asistente.php');
            exit;
        }

        if ($accion === 'eliminar_adepto') {
            $recintoEntrada = (string)($_POST['recinto'] ?? '');
            $indice = filter_var($_POST['indice'] ?? null, FILTER_VALIDATE_INT);
            $recintoKey = $recintoEntrada === '7' ? 'pasillo' : (int)$recintoEntrada;

            if (($recintoKey === 'pasillo' || isset($recintosDef[$recintoKey])) && $indice !== false && $indice >= 0 && isset($_SESSION['escuela'][$recintoKey][$indice])) {
                unset($_SESSION['escuela'][$recintoKey][$indice]);
                $_SESSION['escuela'][$recintoKey] = array_values($_SESSION['escuela'][$recintoKey]);
                header('Location: asistente.php');
                exit;
            }

            $mensajeError = $txt['err_asistente_solicitud_invalida'] ?? 'La solicitud no es válida.';
        } elseif ($accion === 'actualizar_bonus_rey') {
            if (isset($_SESSION['escuela'][4][0])) {
                $_SESSION['escuela'][4][0]['bonusRey'] = isset($_POST['bonus_rey']);
                header('Location: asistente.php');
                exit;
            }
            $mensajeError = $txt['err_asistente_solicitud_invalida'] ?? 'La solicitud no es válida.';
        } elseif ($accion === 'agregar') {
            $numRecinto = filter_var($_POST['recinto'] ?? null, FILTER_VALIDATE_INT);
            $archivoAdepto = basename((string)($_POST['adepto'] ?? ''));

            if ($numRecinto === false || !isset($recintosDef[$numRecinto]) || !isset($adeptosDisponibles[$archivoAdepto])) {
                $mensajeError = $txt['err_asistente_solicitud_invalida'] ?? 'La solicitud no es válida.';
            } else {
                $datosAdepto = $adeptosDisponibles[$archivoAdepto];
                $nuevoAdepto = [
                    'archivo' => $archivoAdepto,
                    'especie' => $datosAdepto['especie'],
                    'corriente' => $datosAdepto['corriente'],
                    'esSocratico' => $datosAdepto['corriente'] === 'socratica',
                    'bonusRey' => false
                ];
                $recintoKey = $numRecinto === 7 ? 'pasillo' : $numRecinto;
                $resultado = $logicaRecintos->validarReglasAsistente($numRecinto, $nuevoAdepto, $_SESSION['escuela'][$recintoKey] ?? []);

                if ($resultado['valido'] === true) {
                    $_SESSION['escuela'][$recintoKey][] = $nuevoAdepto;
                    header('Location: asistente.php');
                    exit;
                }

                $mensajeKey = (string)($resultado['mensaje_key'] ?? 'err_asistente_solicitud_invalida');
                $mensajeError = $txt[$mensajeKey] ?? 'No se puede agregar el adepto a ese recinto.';
            }
        } elseif ($accion !== 'eliminar_adepto') {
            $mensajeError = $txt['err_asistente_solicitud_invalida'] ?? 'La solicitud no es válida.';
        }
    }
}

$recintosNombres = [];
$puntajes = [];
$totalGeneral = 0;

foreach ($recintosDef as $id => $recinto) {
    $recintosNombres[$id] = $txt[$recinto['nombre_key']] ?? $recinto['nombre_default'];
    $recintoKey = $id === 7 ? 'pasillo' : $id;
    $puntajes[$id] = $logicaRecintos->calcularPuntajeAsistente($id, $_SESSION['escuela'][$recintoKey] ?? [], $_SESSION['escuela']);
    $totalGeneral += $puntajes[$id];
}

$etiquetaRecinto = $txt['recinto'] ?? 'Recinto';
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang ?? 'es', ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeRexus - <?php echo htmlspecialchars($txt['asistente_titulo'] ?? 'Asistente', ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="icon" type="image/png" href="../../Assets/Socrates.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Michroma&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <link rel="stylesheet" href="../CSSs/style.css">
    <link rel="stylesheet" href="../CSSs/asistente.css">
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

    <main class="main-content asistente-content">
        <section class="hero-section">
            <h1 class="main-title" data-i18n="asistente_titulo"><?php echo htmlspecialchars($txt['asistente_titulo'] ?? 'Asistente', ENT_QUOTES, 'UTF-8'); ?></h1>
        </section>

        <div class="asistente-grid">
            <div class="mapa-recintos" id="mapa-recintos">
                <img src="../../Assets/MapaAsistente.png" alt="" class="mapa-img" draggable="false">
                <?php foreach ($recintosDef as $recinto): ?>
                    <?php $nombreRecinto = $recintosNombres[$recinto['id']]; ?>
                    <button type="button" class="recinto-hotspot" data-recinto="<?php echo (int)$recinto['id']; ?>"<?php echo $recinto['rey'] ? ' data-rey="1"' : ''; ?> aria-label="<?php echo htmlspecialchars($nombreRecinto, ENT_QUOTES, 'UTF-8'); ?>" style="left: <?php echo round($recinto['x'] / 16, 2); ?>%; top: <?php echo round($recinto['y'] / 16, 2); ?>%; width: <?php echo round($recinto['w'] / 16, 2); ?>%; height: <?php echo round($recinto['h'] / 16, 2); ?>%;"><span class="recinto-fichas" aria-hidden="true"></span></button>
                <?php endforeach; ?>
            </div>

            <div class="glass-box puntaje-box">
                <h2 class="section-subtitle center" data-i18n="puntos"><?php echo htmlspecialchars($txt['puntos'] ?? 'Puntos:', ENT_QUOTES, 'UTF-8'); ?></h2>

                <ul class="puntaje-list">
                    <?php foreach ($puntajes as $idRecinto => $puntaje): ?>
                        <li><span><?php echo htmlspecialchars($recintosNombres[$idRecinto], ENT_QUOTES, 'UTF-8'); ?>:</span><span><?php echo (int)$puntaje; ?></span></li>
                    <?php endforeach; ?>
                </ul>

                <div class="puntaje-divider"></div>

                <div class="puntaje-total">
                    <span data-i18n="total"><?php echo htmlspecialchars($txt['total'] ?? 'Total:', ENT_QUOTES, 'UTF-8'); ?></span>
                    <span><?php echo (int)$totalGeneral; ?></span>
                </div>
            </div>
        </div>

        <div class="volver-container">
            <?php if ($mensajeError !== ''): ?>
                <p class="msg-error" role="alert"><?php echo htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8'); ?></p>
            <?php endif; ?>
            <div class="asistente-acciones-botones">
                <a href="base.php" class="btn-nav btn-volver-asistente" data-i18n="volver"><?php echo htmlspecialchars($txt['volver'] ?? 'Volver', ENT_QUOTES, 'UTF-8'); ?></a>
                <button type="button" class="btn-nav" data-open="modal-reiniciar" data-i18n="reiniciar_tablero"><?php echo htmlspecialchars($txt['reiniciar_tablero'] ?? 'Reiniciar Tablero', ENT_QUOTES, 'UTF-8'); ?></button>
            </div>
        </div>
    </main>

    <div class="modal-overlay" id="modal-recinto" data-recinto-label="<?php echo htmlspecialchars($etiquetaRecinto, ENT_QUOTES, 'UTF-8'); ?>">
        <div class="modal-window modal-wide">
            <button type="button" class="modal-close-btn" data-close="modal-recinto" aria-label="<?php echo htmlspecialchars($txt['cerrar'] ?? 'Cerrar', ENT_QUOTES, 'UTF-8'); ?>">&times;</button>
            <h2 class="modal-header-title" id="recinto-modal-title"><?php echo htmlspecialchars($etiquetaRecinto, ENT_QUOTES, 'UTF-8'); ?> 1</h2>

            <div class="recinto-panel-section">
                <h3 class="panel-subtitle"><?php echo htmlspecialchars($txt['colocados'] ?? 'Colocados:', ENT_QUOTES, 'UTF-8'); ?></h3>
                <div class="adeptos-row" id="recinto-colocados"></div>
            </div>

            <div class="recinto-panel-section">
                <h3 class="panel-subtitle" data-i18n="seleccionar"><?php echo htmlspecialchars($txt['seleccionar'] ?? 'Seleccionar', ENT_QUOTES, 'UTF-8'); ?>:</h3>
                <form action="asistente.php" method="POST" id="form-agregar-adepto">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                    <input type="hidden" name="accion" value="agregar">
                    <input type="hidden" name="recinto" id="input-recinto-id" value="1">
                    <input type="hidden" name="adepto" id="input-adepto" value="">
                    <div class="adeptos-row">
                        <?php foreach ($adeptosDisponibles as $archivo => $datos): ?>
                            <button type="button" class="adepto-select-btn" data-adepto="<?php echo htmlspecialchars($archivo, ENT_QUOTES, 'UTF-8'); ?>">
                                <img src="../../Assets/<?php echo htmlspecialchars($archivo, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($datos['nombre'], ENT_QUOTES, 'UTF-8'); ?>" class="adepto-icon">
                            </button>
                        <?php endforeach; ?>
                    </div>
                </form>
            </div>

            <label class="bonus-row" id="bonus-row">
                <input type="checkbox" id="bonus-rey" class="bonus-check">
                <span data-i18n="bonus_rey_filosofo"><?php echo htmlspecialchars($txt['bonus_rey_filosofo'] ?? 'Bonus del Rey Filósofo', ENT_QUOTES, 'UTF-8'); ?></span>
            </label>

            <div class="modal-footer-left">
                <button type="button" class="btn-sub-pill" data-close="modal-recinto" data-i18n="cerrar_panel"><?php echo htmlspecialchars($txt['cerrar_panel'] ?? 'Cerrar Panel', ENT_QUOTES, 'UTF-8'); ?></button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modal-reiniciar">
        <div class="modal-window">
            <button type="button" class="modal-close-btn" data-close="modal-reiniciar" aria-label="<?php echo htmlspecialchars($txt['cerrar'] ?? 'Cerrar', ENT_QUOTES, 'UTF-8'); ?>">&times;</button>
            <h2 class="modal-header-title" data-i18n="reiniciar_tablero"><?php echo htmlspecialchars($txt['reiniciar_tablero'] ?? 'Reiniciar Tablero', ENT_QUOTES, 'UTF-8'); ?></h2>
            <p class="modal-body-txt" data-i18n="confirmar_reiniciar_tablero"><?php echo htmlspecialchars($txt['confirmar_reiniciar_tablero'] ?? '¿Seguro que desea reiniciar el tablero?', ENT_QUOTES, 'UTF-8'); ?></p>
            <form action="" method="POST" class="modal-actions">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">
                <input type="hidden" name="accion" value="reiniciar_tablero">
                <button type="submit" class="btn-modal btn-danger"><?php echo htmlspecialchars($txt['confirmar'] ?? 'Confirmar', ENT_QUOTES, 'UTF-8'); ?></button>
            </form>
        </div>
    </div>

    <script>
        window.asistenteData = <?php echo json_encode([
            'escuela' => $_SESSION['escuela'],
            'nombresRecintos' => $recintosNombres,
            'csrfToken' => $_SESSION['csrf_token'],
            'eliminarLabel' => $txt['eliminar_adepto'] ?? 'Eliminar adepto'
        ], JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
    </script>
    <script src="../Scripts/lang.js"></script>
    <script src="../Scripts/Temas.js"></script>
    <script src="../Scripts/header.js" defer></script>
    <script src="../Scripts/asistente.js" defer></script>
</body>
</html>
