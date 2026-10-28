<?php
require_once __DIR__ . '/../Scripts/auth_check.php';
require_once __DIR__ . '/../Scripts/lang.php';
require_once __DIR__ . '/../../Logica/FachadaLogica.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (!isset($_SESSION['escuela'])) {
    $_SESSION['escuela'] = [
        1 => [], 2 => [], 3 => [], 4 => [], 5 => [], 6 => [], 'pasillo' => []
    ];
}

$mensajeError = "";
$facha = new FachadaLogica();
$logicaRecintos = $facha->retornoILogicaRecinto();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $mensajeError = "Token de seguridad inválido.";
    } else {
        if ($_POST['accion'] === 'reiniciar_tablero') {
            unset($_SESSION['escuela']);
            header("Location: asistente.php");
            exit();
        }

        if ($_POST['accion'] === 'eliminar_adepto') {
            $numRecinto = $_POST['recinto'];
            $indice = intval($_POST['indice']);
            $key = ($numRecinto === 'pasillo' || $numRecinto === '7' || $numRecinto === 7) ? 'pasillo' : intval($numRecinto);
            
            if ($key === 'pasillo') {
                if (isset($_SESSION['escuela']['pasillo'][$indice])) {
                    unset($_SESSION['escuela']['pasillo'][$indice]);
                    $_SESSION['escuela']['pasillo'] = array_values($_SESSION['escuela']['pasillo']);
                }
            } else {
                if (isset($_SESSION['escuela'][$key][$indice])) {
                    unset($_SESSION['escuela'][$key][$indice]);
                    $_SESSION['escuela'][$key] = array_values($_SESSION['escuela'][$key]);
                }
            }
            header("Location: asistente.php");
            exit();
        }

        if ($_POST['accion'] === 'actualizar_bonus_rey') {
            $nuevoBonus = isset($_POST['bonus_rey']) ? true : false;
            if (isset($_SESSION['escuela'][4][0])) {
                $_SESSION['escuela'][4][0]['bonusRey'] = $nuevoBonus;
            }
            header("Location: asistente.php");
            exit();
        }

        if ($_POST['accion'] === 'agregar') {
            $numRecinto = intval($_POST['recinto']);
            $corrienteSeleccionada = $_POST['corriente'] ?? '';
            $especieSeleccionada = $_POST['especie'] ?? '';
            $esSocraticoAuto = ($corrienteSeleccionada === 'socratica');

            $nuevoAdepto = [
                'especie' => $especieSeleccionada,
                'corriente' => $corrienteSeleccionada,
                'esSocratico' => $esSocraticoAuto,
                'bonusRey' => isset($_POST['bonus_rey'])
            ];

            $recintoKey = ($numRecinto === 7) ? 'pasillo' : $numRecinto;
            $recintoActual = $_SESSION['escuela'][$recintoKey] ?? [];

            $res = $logicaRecintos->validarReglasAsistente($numRecinto, $nuevoAdepto, $recintoActual);

            if ($res['valido']) {
                if ($numRecinto === 4 || $numRecinto === 6) {
                    $_SESSION['escuela'][$numRecinto] = [$nuevoAdepto];
                } elseif ($numRecinto === 7) {
                    $_SESSION['escuela']['pasillo'][] = $nuevoAdepto;
                } else {
                    $_SESSION['escuela'][$numRecinto][] = $nuevoAdepto;
                }
                
                header("Location: asistente.php");
                exit();
            } else {
                $mensajeError = $res['mensaje'];
            }
        }
    }
}

$recintosDef = [
    1 => ['id' => 1, 'nombre_key' => 'recinto_congreso',     'nombre_default' => 'El Congreso',          'x' => 517, 'y' => 464,  'w' => 400, 'h' => 400,  'rey' => false],
    2 => ['id' => 2, 'nombre_key' => 'recinto_profesorado',  'nombre_default' => 'Profesorado',          'x' => 515, 'y' => 775,  'w' => 350, 'h' => 350,  'rey' => false],
    3 => ['id' => 3, 'nombre_key' => 'recinto_pradera_amor', 'nombre_default' => 'La Pradera del Amor',  'x' => 512, 'y' => 1041, 'w' => 380, 'h' => 380,  'rey' => false],
    4 => ['id' => 4, 'nombre_key' => 'recinto_rey_filosofo', 'nombre_default' => 'Rey Filósofo',        'x' => 1110,'y' => 522,  'w' => 360, 'h' => 360,  'rey' => true],
    5 => ['id' => 5, 'nombre_key' => 'recinto_agora',        'nombre_default' => 'El Ágora',             'x' => 1102,'y' => 780,  'w' => 370, 'h' => 370,  'rey' => false],
    6 => ['id' => 6, 'nombre_key' => 'recinto_director',     'nombre_default' => 'Director',             'x' => 1112,'y' => 1053, 'w' => 310, 'h' => 310,  'rey' => false],
    7 => ['id' => 7, 'nombre_key' => 'recinto_pasillo',      'nombre_default' => 'El Pasillo',           'x' => 810, 'y' => 760,  'w' => 250, 'h' => 1080, 'rey' => false]
];

$recintosNombresJs = [];
foreach ($recintosDef as $id => $recinto) {
    $recintosNombresJs[$id] = $txt[$recinto['nombre_key']] ?? $recinto['nombre_default'];
}

$recintosUiConfig = [
    ['id' => 'congreso',      'titulo_key' => 'recinto_congreso',     'desc_key' => 'desc_congreso'],
    ['id' => 'profesorado',   'titulo_key' => 'recinto_profesorado',  'desc_key' => 'desc_profesorado'],
    ['id' => 'pradera_amor',  'titulo_key' => 'recinto_pradera_amor', 'desc_key' => 'desc_pradera_amor'],
    ['id' => 'rey_filosofo',  'titulo_key' => 'recinto_rey_filosofo', 'desc_key' => 'desc_rey_filosofo'],
    ['id' => 'agora',         'titulo_key' => 'recinto_agora',        'desc_key' => 'desc_agora'],
    ['id' => 'director',      'titulo_key' => 'recinto_director',     'desc_key' => 'desc_director'],
    ['id' => 'pasillo',       'titulo_key' => 'recinto_pasillo',      'desc_key' => 'desc_pasillo']
];

$adeptosDisponibles = [
    'DiogenesPerro.png' => ['nombre' => $txt['diogenes'] ?? 'Diógenes', 'especie' => 'perro', 'corriente' => 'cinica'],
    'SocratesHyrax.png' => ['nombre' => $txt['socrates'] ?? 'Sócrates', 'especie' => 'hyrax', 'corriente' => 'socratica'],
    'HypatiaAbeja.png' => ['nombre' => $txt['hypatia'] ?? 'Hypatia', 'especie' => 'abeja', 'corriente' => 'neoplatonica'],
    'AristotelesTortuga.png' => ['nombre' => $txt['aristoteles'] ?? 'Aristóteles', 'especie' => 'tortuga', 'corriente' => 'peripatetica'],
    'PlatonPato.png' => ['nombre' => $txt['platon'] ?? 'Platón', 'especie' => 'pato', 'corriente' => 'platonica'],
    'EpicuroKoala.png' => ['nombre' => $txt['epicuro'] ?? 'Epicuro', 'especie' => 'koala', 'corriente' => 'epicurea']
];

$parqueCompleto = $_SESSION['escuela'];
$puntajes = [];
$totalGeneral = 0;

for ($i = 1; $i <= 6; $i++) {
    $adeptosEnRecinto = $parqueCompleto[$i] ?? [];
    $pts = $logicaRecintos->calcularPuntajeAsistente($i, $adeptosEnRecinto, $parqueCompleto);
    $puntajes[$i] = $pts;
    $totalGeneral += $pts;
}

$adeptosPasillo = $parqueCompleto['pasillo'] ?? [];
$ptsPasillo = $logicaRecintos->calcularPuntajeAsistente(7, $adeptosPasillo, $parqueCompleto);
$puntajes[7] = $ptsPasillo;
$totalGeneral += $ptsPasillo;
?>

<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang ?? 'es', ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeRexus - <?php echo $txt['asistente_titulo']; ?></title>
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
            <h1 class="main-title"><?php echo $txt['asistente_titulo']; ?></h1>
        </section>

        <?php if (!empty($mensajeError)): ?>
            <div class="error-banner" style="background: transparent; color: var(--color-main); text-shadow: var(--shadow-text); font-family: var(--font-main); text-align: center; margin-bottom: 15px; font-size: 1.1rem;">
                <?php echo htmlspecialchars($mensajeError, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <div class="asistente-grid">
            <div class="mapa-recintos" id="mapa-recintos">
                <img src="../../Assets/MapaAsistente.png" alt="" class="mapa-img" draggable="false">
                <?php foreach ($recintosDef as $recinto): 
                    $nombreRecinto = $txt[$recinto['nombre_key']] ?? $recinto['nombre_default'];
                ?>
                    <button type="button" 
                            class="recinto-hotspot" 
                            data-recinto="<?php echo (int)$recinto['id']; ?>"
                            data-clave-recinto="<?php echo htmlspecialchars($recinto['nombre_key'], ENT_QUOTES, 'UTF-8'); ?>"
                            <?php echo $recinto['rey'] ? ' data-rey="1"' : ''; ?>
                            aria-label="<?php echo $nombreRecinto; ?>" 
                            style="left: <?php echo round($recinto['x'] / 16, 2); ?>%; top: <?php echo round($recinto['y'] / 16, 2); ?>%; width: <?php echo round($recinto['w'] / 16, 2); ?>%; height: <?php echo round($recinto['h'] / 16, 2); ?>%;">
                    </button>
                <?php endforeach; ?>
            </div>

            <div class="glass-box puntaje-box">
                <h2 class="section-subtitle center"><?php echo $txt['puntaje'] ?? $txt['puntos']; ?></h2>

                <ul class="puntaje-list">
                    <?php foreach ($recintosDef as $id => $recinto): 
                        $nombreRecinto = $txt[$recinto['nombre_key']] ?? $recinto['nombre_default'];
                    ?>
                        <li>
                            <span><?php echo $nombreRecinto; ?>:</span>
                            <span><?php echo (int)$puntajes[$id]; ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="puntaje-divider"></div>

                <div class="puntaje-total">
                    <span><?php echo $txt['total']; ?></span>
                    <span><?php echo (int)$totalGeneral; ?></span>
                </div>
            </div>
        </div>

        <div class="volver-container">
            <a href="base.php" class="btn-nav btn-volver-asistente"><?php echo $txt['volver']; ?></a>
            <button type="button" class="btn-nav" data-open="modal-reiniciar"><?php echo $txt['reiniciar_tablero']; ?></button>
        </div>
    </main>

    <div class="modal-overlay" id="modal-recinto">
        <div class="modal-window modal-wide">
            <button type="button" class="modal-close-btn" data-close="modal-recinto">&times;</button>
            <h2 class="modal-header-title" id="recinto-modal-title"><?php echo $txt['recinto']; ?></h2>

            <div class="recinto-panel-section">
                <h3 class="panel-subtitle"><?php echo $txt['colocados']; ?></h3>
                <div class="adeptos-row" id="recinto-colocados"></div>
            </div>

            <div class="recinto-panel-section">
                <h3 class="panel-subtitle"><?php echo $txt['seleccionar']; ?></h3>
                
                <form action="asistente.php" method="POST" id="form-agregar-adepto">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <input type="hidden" name="accion" value="agregar">
                    <input type="hidden" name="recinto" id="input-recinto-id" value="1">
                    <input type="hidden" name="especie" id="input-especie" value="">
                    <input type="hidden" name="corriente" id="input-corriente" value="">

                    <label class="bonus-row" id="bonus-row">
                        <input type="checkbox" name="bonus_rey" id="bonus-rey" class="bonus-check">
                        <span><?php echo $txt['bonus_socratico']; ?></span>
                    </label>

                    <div class="adeptos-row">
                        <?php foreach ($adeptosDisponibles as $archivo => $datos): ?>
                            <button type="button" 
                                    class="adepto-select-btn" 
                                    data-especie="<?php echo htmlspecialchars($datos['especie'], ENT_QUOTES, 'UTF-8'); ?>" 
                                    data-corriente="<?php echo htmlspecialchars($datos['corriente'], ENT_QUOTES, 'UTF-8'); ?>">
                                <img src="../../Assets/<?php echo htmlspecialchars($archivo, ENT_QUOTES, 'UTF-8'); ?>" alt="<?php echo htmlspecialchars($datos['nombre'], ENT_QUOTES, 'UTF-8'); ?>" class="adepto-icon">
                            </button>
                        <?php endforeach; ?>
                    </div>
                </form>
            </div>

            <div class="modal-footer-left">
                <button type="button" class="btn-sub-pill" data-close="modal-recinto"><?php echo $txt['cerrar_panel']; ?></button>
            </div>
        </div>
    </div>

    <div class="modal-overlay" id="modal-reiniciar">
        <div class="modal-window">
            <button type="button" class="modal-close-btn" data-close="modal-reiniciar">&times;</button>
            <h2 class="modal-header-title"><?php echo $txt['reiniciar_tablero']; ?></h2>
            <p class="modal-body-txt"><?php echo $txt['confirmar_reiniciar_tablero']; ?></p>
            <form action="asistente.php" method="POST" class="modal-actions">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="accion" value="reiniciar_tablero">
                <button type="submit" class="btn-modal btn-danger"><?php echo $txt['confirmar']; ?></button>
            </form>
        </div>
    </div>

    <script>
        window.escuela = <?php echo json_encode($_SESSION['escuela']); ?>;
        window.translations = <?php echo json_encode($txt, JSON_UNESCAPED_UNICODE); ?>;
        window.currentLang = "<?php echo htmlspecialchars($lang ?? 'es', ENT_QUOTES, 'UTF-8'); ?>";
        window.recintosNombres = <?php echo json_encode($recintosNombresJs, JSON_UNESCAPED_UNICODE); ?>;
    </script>
    <script src="../Scripts/lang.js"></script>
    <script src="../Scripts/Temas.js"></script>
    <script src="../Scripts/header.js" defer></script>
    <script src="../Scripts/asistente.js" defer></script>
</body>
</html>