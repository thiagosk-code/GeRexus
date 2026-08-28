<?php
require_once __DIR__ . '/../Scripts/admin_check.php';
require_once __DIR__ . '/../Scripts/lang.php';
require_once __DIR__ . '/../../Logica/FachadaLogica.php';
require_once __DIR__ . '/../../DTO/UsuarioDTO.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$msgAlta = '';
$msgMod = '';
$msgElim = '';

$esErrorAlta = false;
$esErrorMod = false;
$esErrorElim = false;

$fachadaLogica = new FachadaLogica();
$logicaUsuario = $fachadaLogica->retornoILogicaUsuario();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenEnviado = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $tokenEnviado)) {
        die('Solicitud rechazada: Token de seguridad no válido (CSRF).');
    }

    $resForm = $logicaUsuario->procesarFormularioAdmin($_POST, $idAdminLogueado);
    $key = $resForm['mensaje_key'] ?? '';
    $mensajeTexto = isset($txt[$key]) ? $txt[$key] : ($resForm['mensaje'] ?? '');
    
    if ($resForm['tipo'] === 'alta') {
        $msgAlta = $mensajeTexto;
        $esErrorAlta = $resForm['exito'] === false;
    } elseif ($resForm['tipo'] === 'modificar') {
        $msgMod = $mensajeTexto;
        $esErrorMod = $resForm['exito'] === false;
    } elseif ($resForm['tipo'] === 'eliminar') {
        $msgElim = $mensajeTexto;
        $esErrorElim = $resForm['exito'] === false;
    }
}

$usuariosLista = $logicaUsuario->obtenerTodosLosUsuariosL();
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang, ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeRexus - <?php echo $txt['panel_admin'] ?? 'Panel de Administración'; ?></title>
    <link rel="icon" type="image/png" href="../Assets/SocratesPNG.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Michroma&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <link rel="stylesheet" href="../CSSs/style.css">
    <link rel="stylesheet" href="../CSSs/panelAdmin.css">
</head>
<body>

    <header class="main-header">
        <a href="base.php" class="logo-container">
            <span class="logo-img"></span>
            <span class="logo-text">GeRexus</span>
        </a>
        
        <div class="header-actions">
            <div class="leng-switcher">
                <?php if ($lang === 'en'): ?>
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

            <div class="user-profile">
                <span class="user-name"><?php echo htmlspecialchars($nombreUsuarioLogueado, ENT_QUOTES, 'UTF-8'); ?></span>
                <span class="user-avatar-img"></span>
            </div>
        </div>
    </header>

    <div class="admin-wrapper">
        <aside class="admin-sidebar">
            <button class="nav-btn active" id="btn-tab-gu" title="<?php echo $txt['gestion_usuarios'] ?? 'Gestión de Usuarios'; ?>" data-i18n-title="gestion_usuarios">GU</button>
            <button class="nav-btn" id="btn-tab-ga" title="<?php echo $txt['gestion_adeptos'] ?? 'Gestión de Adeptos'; ?>" data-i18n-title="gestion_adeptos">GA</button>
            <button class="nav-btn" id="btn-tab-gc" title="<?php echo $txt['gestion_condiciones'] ?? 'Gestión de Condiciones'; ?>" data-i18n-title="gestion_condiciones">GC</button>
        </aside>

        <main class="admin-main">
            <div id="panel-gu" class="panel-section active">
                <h1 class="admin-title" data-i18n="gestion_usuarios"><?php echo $txt['gestion_usuarios'] ?? 'Gestión de Usuarios'; ?></h1>

                <section class="admin-card table-card">
                    <div class="table-header-toolbar">
                        <div class="search-container">
                            <label for="buscar-input" class="search-label" data-i18n="buscar"><?php echo $txt['buscar'] ?? 'Buscar:'; ?></label>
                            <input type="text" id="buscar-input" class="form-input search-input" placeholder="<?php echo $txt['buscar_placeholder'] ?? 'Filtrar por ID, Nombre o Email...'; ?>" data-i18n-placeholder="buscar_placeholder">
                        </div>

                        <?php if ($msgElim !== ''): ?>
                            <div class="msg-status-placeholder inline-msg <?php echo $esErrorElim === true ? 'msg-error' : 'msg-exito'; ?>">
                                <?php echo htmlspecialchars($msgElim, ENT_QUOTES, 'UTF-8'); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="table-responsive">
                        <table class="users-table" id="tabla-usuarios">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th data-i18n="nombre"><?php echo $txt['nombre'] ?? 'Nombre'; ?></th>
                                    <th data-i18n="correo"><?php echo $txt['correo'] ?? 'Email'; ?></th>
                                    <th data-i18n="dracmas"><?php echo $txt['dracmas'] ?? 'Dracmas'; ?></th>
                                    <th data-i18n="partidas_ganadas"><?php echo $txt['partidas_ganadas'] ?? 'Partidas Ganadas'; ?></th>
                                    <th data-i18n="baja_logica"><?php echo $txt['baja_logica'] ?? 'Baja Lógica'; ?></th>
                                    <th data-i18n="acciones"><?php echo $txt['acciones'] ?? 'Acciones'; ?></th>
                                </tr>
                            </thead>
                            <tbody id="tabla-body">
                                <?php if (is_array($usuariosLista) && count($usuariosLista) > 0): ?>
                                    <?php foreach ($usuariosLista as $usr): ?>
                                        <?php 
                                            $uId = (int)$usr->getIdUsuario();
                                            $uNombreRaw = $usr->getNombre();
                                            $uEmailRaw = $usr->getEmail();
                                            $uNombre = htmlspecialchars($uNombreRaw, ENT_QUOTES, 'UTF-8');
                                            $uEmail = htmlspecialchars($uEmailRaw, ENT_QUOTES, 'UTF-8');
                                            $uDracmas = (int)$usr->getMonedas();
                                            $uGanadas = (int)$usr->getPartidasGanadas();
                                            $uBaja = ($usr->getBajaLogica() === true || $usr->getBajaLogica() === 1) ? '1' : '0';
                                            $uEsAdmin = $usr->getEsAdmin() === true || $usr->getEsAdmin() === 1;
                                            
                                            $puedeEliminar = ($uEsAdmin === false);
                                        ?>
                                        <tr data-id="<?php echo $uId; ?>" data-nombre="<?php echo htmlspecialchars(strtolower($uNombreRaw), ENT_QUOTES, 'UTF-8'); ?>" data-email="<?php echo htmlspecialchars(strtolower($uEmailRaw), ENT_QUOTES, 'UTF-8'); ?>">
                                            <td><?php echo $uId; ?></td>
                                            <td><?php echo $uNombre; ?></td>
                                            <td><?php echo $uEmail; ?></td>
                                            <td><?php echo $uDracmas; ?></td>
                                            <td><?php echo $uGanadas; ?></td>
                                            <td><?php echo $uBaja; ?></td>
                                            <td class="actions-cell">
                                                <?php if ($puedeEliminar === true): ?>
                                                    <form action="" method="POST" class="inline-form form-eliminar">
                                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                        <input type="hidden" name="accion" value="eliminar">
                                                        <input type="hidden" name="id_eliminar" value="<?php echo $uId; ?>">
                                                        <button type="submit" class="action-icon btn-delete" title="Eliminar (Baja Lógica)">
                                                            <span class="material-symbols-outlined">delete</span>
                                                        </button>
                                                    </form>
                                                <?php else: ?>
                                                    <button class="action-icon btn-disabled" title="No se puede eliminar a un usuario administrador" disabled>
                                                        <span class="material-symbols-outlined">block</span>
                                                    </button>
                                                <?php endif; ?>

                                                <button type="button" 
                                                        class="action-icon btn-edit" 
                                                        title="Modificar"
                                                        data-id="<?php echo $uId; ?>"
                                                        data-nombre="<?php echo $uNombreRaw; ?>"
                                                        data-email="<?php echo $uEmailRaw; ?>"
                                                        data-dracmas="<?php echo $uDracmas; ?>">
                                                    <span class="material-symbols-outlined">edit</span>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="pagination-container" id="paginacion">
                        <button class="pag-btn" id="pag-prev">&lt;</button>
                        <div id="pag-numbers" class="pag-numbers"></div>
                        <button class="pag-btn" id="pag-next">&gt;</button>
                    </div>
                </section>

                <section class="admin-card">
                    <h2 class="card-title" data-i18n="alta_usuario"><?php echo $txt['alta_usuario'] ?? 'Alta Usuario'; ?></h2>
                    <div class="line-separator-title"></div>
                    <form class="admin-form" action="" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="accion" value="alta">

                        <div class="form-row">
                            <label for="alta-nombre" class="form-label" data-i18n="nombre"><?php echo $txt['nombre'] ?? 'Nombre'; ?>:</label>
                            <input type="text" id="alta-nombre" name="username" class="form-input" placeholder="<?php echo $txt['placeholder_nombre'] ?? 'Ejemplo123'; ?>" data-i18n-placeholder="placeholder_nombre" required>
                        </div>

                        <div class="form-row">
                            <label for="alta-email" class="form-label" data-i18n="correo"><?php echo $txt['correo'] ?? 'Email'; ?>:</label>
                            <input type="email" id="alta-email" name="email" class="form-input" placeholder="<?php echo $txt['placeholder_email'] ?? 'Ejemplo@gmail.com'; ?>" data-i18n-placeholder="placeholder_email" required>
                        </div>

                        <div class="form-row">
                            <label for="alta-password" class="form-label" data-i18n="contrasena"><?php echo $txt['contrasena'] ?? 'Contraseña'; ?>:</label>
                            <input type="password" id="alta-password" name="password" class="form-input" placeholder="*********" required>
                        </div>

                        <?php if ($msgAlta !== ''): ?>
                            <p class="msg-status-placeholder <?php echo $esErrorAlta === true ? 'msg-error' : 'msg-exito'; ?>">
                                <?php echo htmlspecialchars($msgAlta, ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                        <?php endif; ?>

                        <div class="btn-container">
                            <button type="submit" class="btn-admin btn-alta" data-i18n="alta_usuario"><?php echo $txt['alta_usuario'] ?? 'Alta Usuario'; ?></button>
                        </div>
                    </form>
                </section>

                <section class="admin-card" id="card-modificar">
                    <h2 class="card-title" data-i18n="modificar_usuario"><?php echo $txt['modificar_usuario'] ?? 'Modificar Usuario'; ?></h2>
                    <div class="line-separator-title"></div>
                    <form class="admin-form" action="" method="POST">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <input type="hidden" name="accion" value="modificar">

                        <div class="form-row">
                            <label for="mod-id" class="form-label" data-i18n="id_usuario_mod"><?php echo $txt['id_usuario_mod'] ?? 'ID Usuario:'; ?></label>
                            <input type="number" id="mod-id" name="id_usuario" class="form-input" placeholder="<?php echo $txt['placeholder_mod_id'] ?? 'Escribe un ID o usa el icono de edición'; ?>" data-i18n-placeholder="placeholder_mod_id" required>
                        </div>

                        <div class="line-separator-field"></div>

                        <div class="form-row">
                            <label for="mod-nombre" class="form-label" data-i18n="nuevo_nombre"><?php echo $txt['nuevo_nombre'] ?? 'Nuevo Nombre'; ?>:</label>
                            <input type="text" id="mod-nombre" name="nuevo_nombre" class="form-input" placeholder="<?php echo $txt['placeholder_nombre'] ?? 'Ejemplo123'; ?>" data-i18n-placeholder="placeholder_nombre">
                        </div>

                        <div class="form-row">
                            <label for="mod-email" class="form-label" data-i18n="nuevo_email"><?php echo $txt['nuevo_email'] ?? 'Nuevo Email'; ?>:</label>
                            <input type="email" id="mod-email" name="nuevo_email" class="form-input" placeholder="<?php echo $txt['placeholder_email'] ?? 'Ejemplo@gmail.com'; ?>" data-i18n-placeholder="placeholder_email">
                        </div>

                        <div class="form-row">
                            <label for="mod-password" class="form-label" data-i18n="nueva_contrasena"><?php echo $txt['nueva_contrasena'] ?? 'Nueva Contraseña'; ?>:</label>
                            <input type="password" id="mod-password" name="nueva_password" class="form-input" placeholder="Opcional (dejar en blanco para mantener la actual)">
                        </div>

                        <div class="form-row">
                            <label for="mod-dracmas" class="form-label" data-i18n="dracmas"><?php echo $txt['dracmas'] ?? 'Dracmas'; ?>:</label>
                            <input type="number" id="mod-dracmas" name="dracmas" class="form-input" placeholder="0">
                        </div>

                        <?php if ($msgMod !== ''): ?>
                            <p class="msg-status-placeholder <?php echo $esErrorMod === true ? 'msg-error' : 'msg-exito'; ?>">
                                <?php echo htmlspecialchars($msgMod, ENT_QUOTES, 'UTF-8'); ?>
                            </p>
                        <?php endif; ?>

                        <div class="btn-container">
                            <button type="submit" class="btn-admin btn-modificar" data-i18n="modificar_usuario"><?php echo $txt['modificar_usuario'] ?? 'Modificar Usuario'; ?></button>
                        </div>
                    </form>
                </section>

                <div class="volver-container">
                    <a href="base.php" class="btn-admin btn-volver" data-i18n="volver"><?php echo $txt['volver'] ?? 'Volver'; ?></a>
                </div>
            </div>

            <div id="panel-ga" class="panel-section">
                <h1 class="admin-title" data-i18n="gestion_adeptos"><?php echo $txt['gestion_adeptos'] ?? 'Gestión de Adeptos'; ?></h1>
                <section class="admin-card">
                    <p class="msg-status-placeholder" data-i18n="panel_desarrollo"><?php echo $txt['panel_desarrollo'] ?? 'Panel en desarrollo.'; ?></p>
                </section>
            </div>

            <div id="panel-gc" class="panel-section">
                <h1 class="admin-title" data-i18n="gestion_condiciones"><?php echo $txt['gestion_condiciones'] ?? 'Gestión de Condiciones'; ?></h1>
                <section class="admin-card">
                    <p class="msg-status-placeholder" data-i18n="panel_desarrollo"><?php echo $txt['panel_desarrollo'] ?? 'Panel en desarrollo.'; ?></p>
                </section>
            </div>
        </main>
    </div>

    <script src="../Scripts/lang.js"></script>
    <script src="../Scripts/Temas.js"></script>
    <script src="../Scripts/panelAdmin.js" defer></script>
</body>
</html>