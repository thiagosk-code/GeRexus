<?php
require_once __DIR__ . '/../Scripts/admin_check.php';
require_once __DIR__ . '/../Scripts/lang.php';
require_once __DIR__ . '/../../Logica/FachadaLogica.php';
require_once __DIR__ . '/../../DTO/UsuarioDTO.php';

$msgAlta = '';
$msgMod = '';
$msgElim = '';

$esErrorAlta = false;
$esErrorMod = false;
$esErrorElim = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';
    $fachadaLogica = new FachadaLogica();
    $logicaUsuario = $fachadaLogica->retornoILogicaUsuario();

    if ($accion === 'alta') {
        $nom = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $contra = $_POST['password'] ?? '';

        $dtoAlta = new UsuarioDTO(0, $nom, $email, $contra, 0, 0, false);
        $res = $logicaUsuario->altaUsuarioL($dtoAlta, null, $idAdminLogueado);

        $key = $res['mensaje_key'] ?? '';
        $msgAlta = isset($txt[$key]) ? $txt[$key] : ($res['mensaje'] ?? '');
        $esErrorAlta = $res['exito'] === false;
    } elseif ($accion === 'modificar') {
        $idMod = (int)($_POST['id_usuario'] ?? 0);
        $nomMod = trim($_POST['nuevo_nombre'] ?? '');
        $emailMod = trim($_POST['nuevo_email'] ?? '');
        $contraMod = $_POST['nueva_password'] ?? '';
        $dracmasMod = isset($_POST['dracmas']) && $_POST['dracmas'] !== '' ? (int)$_POST['dracmas'] : null;

        $res = $logicaUsuario->modificarUsuarioL($idMod, $nomMod, $emailMod, $contraMod, $dracmasMod, $idAdminLogueado);

        $key = $res['mensaje_key'] ?? '';
        $msgMod = isset($txt[$key]) ? $txt[$key] : ($res['mensaje'] ?? '');
        $esErrorMod = $res['exito'] === false;
    } elseif ($accion === 'eliminar') {
        $idElim = (int)($_POST['id_eliminar'] ?? 0);

        $res = $logicaUsuario->bajaUsuarioL($idElim, $idAdminLogueado);

        $key = $res['mensaje_key'] ?? '';
        $msgElim = isset($txt[$key]) ? $txt[$key] : ($res['mensaje'] ?? '');
        $esErrorElim = $res['exito'] === false;
    }
}
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
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
                <span class="user-name"><?php echo $nombreUsuarioLogueado; ?></span>
                <span class="user-avatar-img"></span>
            </div>
        </div>
    </header>

    <main class="admin-main">
        <h1 class="admin-title" data-i18n="panel_admin"><?php echo $txt['panel_admin'] ?? 'Panel de Administración'; ?></h1>

        <section class="admin-card">
            <h2 class="card-title" data-i18n="alta_usuario"><?php echo $txt['alta_usuario'] ?? 'Alta Usuario'; ?></h2>
            <div class="line-separator-title"></div>
            <form class="admin-form" action="" method="POST">
                <input type="hidden" name="accion" value="alta">

                <div class="form-row">
                    <label for="alta-nombre" class="form-label" data-i18n="nombre"><?php echo $txt['nombre'] ?? 'Nombre'; ?>:</label>
                    <input type="text" id="alta-nombre" name="username" class="form-input" placeholder="Ejemplo123">
                </div>

                <div class="form-row">
                    <label for="alta-email" class="form-label" data-i18n="correo"><?php echo $txt['correo'] ?? 'Email'; ?>:</label>
                    <input type="email" id="alta-email" name="email" class="form-input" placeholder="Ejemplo@gmail.com">
                </div>

                <div class="form-row">
                    <label for="alta-password" class="form-label" data-i18n="contrasena"><?php echo $txt['contrasena'] ?? 'Contraseña'; ?>:</label>
                    <input type="password" id="alta-password" name="password" class="form-input" placeholder="*********">
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

        <section class="admin-card">
            <h2 class="card-title" data-i18n="modificar_usuario"><?php echo $txt['modificar_usuario'] ?? 'Modificar Usuario'; ?></h2>
            <div class="line-separator-title"></div>
            <form class="admin-form" action="" method="POST">
                <input type="hidden" name="accion" value="modificar">

                <div class="form-row">
                    <label for="mod-id" class="form-label" data-i18n="id_usuario_mod"><?php echo $txt['id_usuario_mod'] ?? 'ID del usuario a modificar'; ?>:</label>
                    <input type="number" id="mod-id" name="id_usuario" class="form-input" placeholder="0">
                </div>

                <div class="line-separator-field"></div>

                <div class="form-row">
                    <label for="mod-nombre" class="form-label" data-i18n="nuevo_nombre"><?php echo $txt['nuevo_nombre'] ?? 'Nuevo Nombre'; ?>:</label>
                    <input type="text" id="mod-nombre" name="nuevo_nombre" class="form-input" placeholder="Ejemplo123">
                </div>

                <div class="form-row">
                    <label for="mod-email" class="form-label" data-i18n="nuevo_email"><?php echo $txt['nuevo_email'] ?? 'Nuevo Email'; ?>:</label>
                    <input type="email" id="mod-email" name="nuevo_email" class="form-input" placeholder="Ejemplo@gmail.com">
                </div>

                <div class="form-row">
                    <label for="mod-password" class="form-label" data-i18n="nueva_contrasena"><?php echo $txt['nueva_contrasena'] ?? 'Nueva Contraseña'; ?>:</label>
                    <input type="password" id="mod-password" name="nueva_password" class="form-input" placeholder="*********">
                </div>

                <div class="form-row">
                    <label for="mod-dracmas" class="form-label">Dracmas:</label>
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

        <section class="admin-card">
            <h2 class="card-title" data-i18n="eliminar_usuario"><?php echo $txt['eliminar_usuario'] ?? 'Eliminar Usuario'; ?></h2>
            <div class="line-separator-title"></div>
            <form class="admin-form" action="" method="POST">
                <input type="hidden" name="accion" value="eliminar">

                <div class="form-row">
                    <label for="elim-id" class="form-label" data-i18n="id_usuario_elim"><?php echo $txt['id_usuario_elim'] ?? 'ID del usuario'; ?>:</label>
                    <input type="number" id="elim-id" name="id_eliminar" class="form-input" placeholder="0">
                </div>

                <?php if ($msgElim !== ''): ?>
                    <p class="msg-status-placeholder <?php echo $esErrorElim === true ? 'msg-error' : 'msg-exito'; ?>">
                        <?php echo htmlspecialchars($msgElim, ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                <?php endif; ?>

                <div class="btn-container">
                    <button type="submit" class="btn-admin btn-eliminar" data-i18n="eliminar_usuario"><?php echo $txt['eliminar_usuario'] ?? 'Eliminar Usuario'; ?></button>
                </div>
            </form>
        </section>

        <div class="volver-container">
            <a href="base.php" class="btn-admin btn-volver" data-i18n="volver"><?php echo $txt['volver'] ?? 'Volver'; ?></a>
        </div>
    </main>

    <script src="../Scripts/lang.js"></script>
    <script src="../Scripts/Temas.js"></script>
</body>
</html>