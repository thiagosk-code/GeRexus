<?php
require_once __DIR__ . '/../Scripts/lang.php';
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Draftoicos - <?php echo $txt['dev_title']; ?></title>
    <link rel="icon" type="image/png" href="../Assets/SocratesPNG.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Michroma&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <link rel="stylesheet" href="../CSSs/style.css">
    <link rel="stylesheet" href="../CSSs/desarrollo.css">
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

    <main class="main-content">
        <h1 class="main-title dev-title"><?php echo $txt['dev_title']; ?></h1>
        
        <div class="dev-container">
            <div aria-label="Orange and tan hamster running in a metal wheel" role="img" class="wheel-and-hamster">
                <div class="wheel"></div>
                <div class="hamster">
                    <div class="hamster__body">
                        <div class="hamster__head">
                            <div class="hamster__ear"></div>
                            <div class="hamster__eye"></div>
                            <div class="hamster__nose"></div>
                        </div>
                        <div class="hamster__limb hamster__limb--fr"></div>
                        <div class="hamster__limb hamster__limb--fl"></div>
                        <div class="hamster__limb hamster__limb--br"></div>
                        <div class="hamster__limb hamster__limb--bl"></div>
                        <div class="hamster__tail"></div>
                    </div>
                </div>
                <div class="spoke"></div>
            </div>
        </div>

        <a href="index.php" class="btn-volver"><?php echo $txt['volver']; ?></a>
    </main>

    <script src="../Scripts/lang.js"></script>
    <script src="../Scripts/Temas.js"></script>
</body>
</html>