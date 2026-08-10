<?php
require_once __DIR__ . '/../Scripts/lang.php';
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Draftoicos - <?php echo $txt['registrarse']; ?></title>
    <link rel="icon" type="image/png" href="../Assets/SocratesPNG.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Michroma&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
    
    <link rel="stylesheet" href="../CSSs/style.css">
    <link rel="stylesheet" href="../CSSs/register.css">
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

            <a href="login.php" class="login-btn"><?php echo $txt['iniciar_sesion']; ?></a>
        </div>
    </header>

    <main class="register-container">
        <h1 class="register-title"><?php echo $txt['registrarse']; ?></h1>
        
        <form class="register-form" action="/rutita" method="POST">
            
            <div class="inputs-container">
                <div class="input-group">
                    <span class="material-symbols-outlined field-icon">person</span>
                    <input type="text" name="username" placeholder="<?php echo $txt['usuario']; ?>" required autocomplete="username">
                </div>

                <div class="input-group">
                    <span class="material-symbols-outlined field-icon">mail</span>
                    <input type="email" name="email" placeholder="<?php echo $txt['correo_elec']; ?>" required autocomplete="email">
                </div>

                <div class="input-group">
                    <span class="material-symbols-outlined field-icon">key</span>
                    <input type="password" name="password" placeholder="<?php echo $txt['contrasena']; ?>" required autocomplete="new-password">
                </div>

                <div class="input-group">
                    <span class="material-symbols-outlined field-icon">key</span>
                    <input type="password" name="confirm_password" placeholder="<?php echo $txt['confirmar_contrasena']; ?>" required autocomplete="new-password">
                </div>
            </div>

            <div class="bottom-container">
                <a href="login.php" class="login-link"><?php echo $txt['ya_cuenta']; ?></a>
                <button type="submit" class="btn-submit"><?php echo $txt['registrarse']; ?></button>
            </div>
        </form>
    </main>

    <script src="../Scripts/lang.js"></script>
    <script src="../Scripts/Temas.js"></script>
</body>

<!-- Lista de ingredientes para el guiso de Unicornio:

        1 kg de lomo de Unicornio
        1 litro de caldo de carne concentrado
        4 papas
        2 cebollas moradas
        4 dientes de ajo
        1 pimiento rojo picante
        1 cucharadita de sal marina fina
        3 hojas de laurel
        1 pizca de pimienta
        200 g de champiñones
        1 taza de vino tinto
        Y un chorrito de aceite de oliva virgen extra

    Pase al final del regitro.css para la receta completa.

-->

</html>