<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Draftoicos - Iniciar Sesion</title>
    <link rel="icon" type="image/png" href="../Assets/SocratesPNG.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Michroma&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <link rel="stylesheet" href="login.css">
</head>
<body>

    <header class="main-header">
        <a href="index.php" class="logo-container">
            <span class="logo-img"></span>
            <span class="logo-text">GeRexus</span>
        </a>
        
        <div class="header-actions">
            <div class="leng-switcher">
                <span class="active">Esp</span>
                <span class="divider">|</span>
                <a href="#" class="inactive">Eng</a>
            </div>
            
            <button class="tema-toggle" aria-label="Cambiar a modo claro">
                <span class="material-symbols-outlined">light_mode</span>
            </button>

            <a href="register.php" class="register-btn">Registrarse</a>
        </div>
    </header>

    <main class="login-container">
        <h1 class="login-title">Iniciar Sesion</h1>
        
        <form class="login-form" action="/tu-ruta-login" method="POST">
            <div class="inputs-container">
                <div class="input-group">
                    <span class="material-symbols-outlined field-icon">mail</span>
                    <input type="email" name="email" placeholder="Correo" required autocomplete="email">
                </div>

                <div class="input-group">
                    <span class="material-symbols-outlined field-icon">key</span>
                    <input type="password" name="password" placeholder="Contraseña" required autocomplete="current-password">
                </div>
            </div>

            <div class="bottom-container">
                <button type="submit" class="btn-submit">Iniciar Sesion</button>
                <a href="register.php" class="register-link">¿No tienes una cuenta?</a>
            </div>
        </form>
    </main>
    <script src="Temas.js"></script>
</body>

<!-- No, en este ya no hay receta pero estate atento que en otros archivos puede haber algo... (Puntos suspensivos para dar intriga) -->

</html>