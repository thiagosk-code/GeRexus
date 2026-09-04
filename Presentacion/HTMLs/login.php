<?php

require_once (__DIR__ . '/../../Logica/FachadaLogica.php');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$mensajeError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tokenEnviado = $_POST['csrf_token'] ?? '';

    if (!hash_equals($_SESSION['csrf_token'], $tokenEnviado)) {
        $mensajeError = "Peticion no valida (Error CSRF).";
    }else {
        $email = $_POST['email'] ?? '';
        $contra = $_POST['password'] ?? '';
        $captchaToken = $_POST['g-recaptcha-response'] ?? '';

        if (!($_POST['email'] === '' || $_POST['password'] === '')){

            $facha = new FachadaLogica();
            $res = $facha->retornoIlogicaUsuario()->IniciarSesionL($email, $contra, $captchaToken);

            if ($res['exito'] === true) {
                session_regenerate_id(true);

                $_SESSION['idUsuario'] = $res['idUsuario'];
                $_SESSION['esAdmin'] = false;
                $_SESSION['login_time'] = time();

                header("Location: base.php");
                exit();
            }else{
                $mensajeError = $res['mensaje'];
            }
            
        }else{
            $mensajeError = "Hay campos sin completar";
        }
    }
    
}

if (isset($_GET['lang'])) {
    if ($_GET['lang'] === 'en') {
        $_SESSION['lang'] = 'en';
    } else {
        $_SESSION['lang'] = 'es';
    }
}

$lang = (isset($_SESSION['lang']) && $_SESSION['lang'] === 'en') ? 'en' : 'es';

$dic = [
    'es' => [
        'registrarse' => 'Registrarse',
        'iniciar_sesion' => 'Iniciar Sesion',
        'correo' => 'Correo',
        'contrasena' => 'Contraseña',
        'no_cuenta' => '¿No tienes una cuenta?'
    ],
    'en' => [
        'registrarse' => 'Sign up',
        'iniciar_sesion' => 'Log in',
        'correo' => 'Email',
        'contrasena' => 'Password',
        'no_cuenta' => "Don't have an account?"
    ]
];

$txt = $dic[$lang];
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Draftoicos - <?php echo $txt['iniciar_sesion']; ?></title>
    <link rel="icon" type="image/png" href="../Assets/SocratesPNG.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Michroma&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <link rel="stylesheet" href="../CSSs/style.css">
    <link rel="stylesheet" href="../CSSs/login.css">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>

    <header class="main-header">
        <a href="index.php" class="logo-container">
            <span class="logo-img"></span>
            <span class="logo-text">GeRexus</span>
        </a>
        
        <div class="header-actions">
            <div class="leng-switcher">
                <a href="?lang=es" class="<?php echo ($lang === 'es') ? 'active' : 'inactive'; ?>">Esp</a>
                <span class="divider">|</span>
                <a href="?lang=en" class="<?php echo ($lang === 'en') ? 'active' : 'inactive'; ?>">Eng</a>
            </div>
            
            <button class="tema-toggle" aria-label="Cambiar a modo claro">
                <span class="material-symbols-outlined">light_mode</span>
            </button>

            <a href="register.php" class="register-btn"><?php echo $txt['registrarse']; ?></a>
        </div>
    </header>

    <main class="login-container">
        <h1 class="login-title"><?php echo $txt['iniciar_sesion']; ?></h1>
        
        <form class="login-form" action="login.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="inputs-container">
                <div class="input-group">
                    <span class="material-symbols-outlined field-icon">mail</span>
                    <input type="email" name="email" placeholder="<?php echo $txt['correo']; ?>" required autocomplete="email">
                </div>

                <div class="input-group">
                    <span class="material-symbols-outlined field-icon">key</span>
                    <input type="password" name="password" placeholder="<?php echo $txt['contrasena']; ?>" required autocomplete="current-password">
                </div>
            </div>
            <div class="recaptcha-wrapper">
                <div class="g-recaptcha" data-sitekey="6Lc2npAtAAAA
                AHQhPwUsh3USUPIpKxiftXoNdcAg"></div>
            </div>
            <div class="bottom-container">
                <?php if ($mensajeError !== ''): ?>
                <p class="msg-status error"><?php echo $mensajeError; ?></p>
                <?php endif; ?>
                <a href="register.php" class="register-link"><?php echo $txt['no_cuenta']; ?></a>
                <input type="submit" name="btn_login" value="<?php echo $txt['iniciar_sesion']; ?>" class="btn-submit">
            </div>
        </form>
    </main>
    
    <script src="../Scripts/lang.js"></script>
    <script src="../Scripts/Temas.js"></script>
</body>

<!-- No, en este ya no hay receta pero estate atento que en otros archivos puede haber algo... (Puntos suspensivos para dar intriga) -->

</html>