<?php
require_once __DIR__ . '/../Scripts/lang.php';
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Draftoicos - <?php echo $txt['iniciar_sesion']; ?></title>
    <link rel="icon" type="image/png" href="../../Assets/Socrates.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Michroma&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <link rel="stylesheet" href="../CSSs/style.css">
    <link rel="stylesheet" href="../CSSs/login.css">
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
        
        <form class="login-form" action="/tu-ruta-login" method="POST">
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

            <div class="bottom-container">
                <button type="submit" class="btn-submit"><?php echo $txt['iniciar_sesion']; ?></button>
                <a href="register.php" class="register-link"><?php echo $txt['no_cuenta']; ?></a>
            </div>
        </form>
    </main>
    
    <script src="../Scripts/lang.js"></script>
    <script src="../Scripts/Temas.js"></script>
</body>

<!-- No, en este ya no hay receta pero estate atento que en otros archivos puede haber algo... (Puntos suspensivos para dar intriga) -->

</html>