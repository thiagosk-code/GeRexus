<?php
require_once __DIR__ . '/../Scripts/auth_check.php';
require_once __DIR__ . '/../Scripts/lang.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$codigoInvitacion = '9PQ12Z';
$jugadores = [
    ['id' => 1, 'nombre' => 'Pepe', 'host' => true],
    ['id' => 2, 'nombre' => 'Pepito', 'host' => false],
    ['id' => 3, 'nombre' => 'Pepardo', 'host' => false],
    ['id' => 4, 'nombre' => 'Pepi', 'host' => false]
];
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

    <main class="sala-content">
        <h1 class="main-title"><?php echo htmlspecialchars($txt['sala_espera'] ?? 'Sala de Espera', ENT_QUOTES, 'UTF-8'); ?></h1>

        <section class="glass-box sala-card">
            <h2 class="sala-label"><?php echo htmlspecialchars($txt['codigo_invitacion_label'] ?? 'Código de invitación', ENT_QUOTES, 'UTF-8'); ?></h2>
            <p class="sala-codigo"><?php echo htmlspecialchars($codigoInvitacion, ENT_QUOTES, 'UTF-8'); ?></p>

            <div class="card-divider"></div>

            <h2 class="sala-label"><?php echo htmlspecialchars($txt['jugadores'] ?? 'Jugadores', ENT_QUOTES, 'UTF-8'); ?></h2>
            <ul class="jugadores-lista">
                <?php foreach ($jugadores as $jugador): ?>
                    <li class="jugador-fila">
                        <span class="jugador-nombre"><?php echo htmlspecialchars($jugador['nombre'], ENT_QUOTES, 'UTF-8'); ?></span>
                        <?php if ($jugador['host']): ?>
                            <span class="icono-host" role="img" aria-label="<?php echo htmlspecialchars($txt['anfitrion'] ?? 'Anfitrión', ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo htmlspecialchars($txt['anfitrion'] ?? 'Anfitrión', ENT_QUOTES, 'UTF-8'); ?>">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M3 18 L2 7 L8 12 L12 5 L16 12 L22 7 L21 18 Z" fill="#F2C230" stroke="#8a6a00" stroke-width="1" stroke-linejoin="round"/>
                                    <rect x="3" y="19" width="18" height="2.5" rx="1" fill="#F2C230" stroke="#8a6a00" stroke-width="1"/>
                                </svg>
                            </span>
                        <?php else: ?>
                            <button type="button" class="btn-echar" data-open="modal-echar" data-jugador="<?php echo (int)$jugador['id']; ?>" aria-label="<?php echo htmlspecialchars($txt['echar_usuario'] ?? 'Echar Usuario', ENT_QUOTES, 'UTF-8'); ?>" title="<?php echo htmlspecialchars($txt['echar_usuario'] ?? 'Echar Usuario', ENT_QUOTES, 'UTF-8'); ?>">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M5 3 H14 V21 H5 Z" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                    <path d="M14 5 L19 7 V19 L14 21 Z" fill="currentColor"/>
                                    <circle cx="11.5" cy="12" r="1.1" fill="currentColor"/>
                                </svg>
                            </button>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </section>

        <div class="acciones-sala">
            <button type="button" class="btn-nav" data-open="modal-cancelar"><?php echo htmlspecialchars($txt['cancelar_partida'] ?? 'Cancelar Partida', ENT_QUOTES, 'UTF-8'); ?></button>
            <button type="button" class="btn-nav" data-open="modal-comenzar"><?php echo htmlspecialchars($txt['comenzar_partida'] ?? 'Comenzar Partida', ENT_QUOTES, 'UTF-8'); ?></button>
        </div>
    </main>

    <div class="modal-overlay" id="modal-echar">
        <div class="modal-window">
            <button type="button" class="modal-close-btn" data-close="modal-echar" aria-label="<?php echo htmlspecialchars($txt['cerrar'] ?? 'Cerrar', ENT_QUOTES, 'UTF-8'); ?>">&times;</button>
            <h2 class="modal-header-title"><?php echo htmlspecialchars($txt['echar_usuario'] ?? 'Echar Usuario', ENT_QUOTES, 'UTF-8'); ?></h2>
            <p class="modal-body-txt"><?php echo htmlspecialchars($txt['confirmar_echar_usuario'] ?? '¿Seguro que desea remover al usuario de la partida?', ENT_QUOTES, 'UTF-8'); ?></p>
            <form action="" method="POST" class="modal-actions">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="accion" value="echar_usuario">
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
            <form action="" method="POST" class="modal-actions">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="accion" value="cancelar_partida">
                <button type="submit" class="btn-modal btn-danger"><?php echo htmlspecialchars($txt['confirmar'] ?? 'Confirmar', ENT_QUOTES, 'UTF-8'); ?></button>
            </form>
        </div>
    </div>

    <div class="modal-overlay" id="modal-comenzar">
        <div class="modal-window">
            <button type="button" class="modal-close-btn" data-close="modal-comenzar" aria-label="<?php echo htmlspecialchars($txt['cerrar'] ?? 'Cerrar', ENT_QUOTES, 'UTF-8'); ?>">&times;</button>
            <h2 class="modal-header-title"><?php echo htmlspecialchars($txt['comenzar_partida'] ?? 'Comenzar Partida', ENT_QUOTES, 'UTF-8'); ?></h2>
            <p class="modal-body-txt"><?php echo htmlspecialchars($txt['confirmar_comenzar_partida'] ?? '¿Quieres comenzar la partida con los jugadores actuales?', ENT_QUOTES, 'UTF-8'); ?></p>
            <form action="" method="POST" class="modal-actions">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="accion" value="comenzar_partida">
                <button type="submit" class="btn-modal btn-ok"><?php echo htmlspecialchars($txt['si'] ?? 'Sí', ENT_QUOTES, 'UTF-8'); ?></button>
                <button type="button" class="btn-modal btn-danger" data-close="modal-comenzar"><?php echo htmlspecialchars($txt['no'] ?? 'No', ENT_QUOTES, 'UTF-8'); ?></button>
            </form>
        </div>
    </div>

    <script src="../Scripts/lang.js"></script>
    <script src="../Scripts/Temas.js"></script>
    <script src="../Scripts/header.js" defer></script>
    <script src="../Scripts/sala.js" defer></script>
</body>
</html>
