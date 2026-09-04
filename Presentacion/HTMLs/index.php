<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 1. Evalúa si viene por URL y actualiza la sesión
if (isset($_GET['lang'])) {
    $lang = ($_GET['lang'] === 'en') ? 'en' : 'es';
    $_SESSION['lang'] = $lang;
} else {
    $lang = $_SESSION['lang'] ?? 'es';
}

// 2. Diccionario de la página
$dic = [
    'es' => [
        'iniciar_sesion' => 'Iniciar sesion',
        'desc' => 'Un juego de mesa con mecanicas draft donde asignaras Adeptos de distintas corrientes filosoficas a distintas salas',
        'jugar' => 'Jugar'
    ],
    'en' => [
        'iniciar_sesion' => 'Log in',
        'desc' => 'A board game with draft mechanics where you will assign Adepts from different philosophical currents to different rooms',
        'jugar' => 'Play'
    ]
];

$txt = $dic[$lang];
?>
<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Draftoicos</title>
    <link rel="icon" type="image/png" href="../Assets/SocratesPNG.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Michroma&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <link rel="stylesheet" href="../CSSs/style.css">
    <link rel="stylesheet" href="../CSSs/index.css">
</head>
<body>

    <header class="main-header">
        <a href="index.php" class="logo-container">
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
            
            <button class="tema-toggle" aria-label="Cambiar a modo claro">
                <span class="material-symbols-outlined">light_mode</span>
            </button>
            
            <a href="login.php" class="login-btn"><?php echo $txt['iniciar_sesion']; ?></a>
        </div>
    </header>

    <main class="main-content">
        <h1 class="main-title">Draftoicos</h1>
        
        <p class="main-description">
            <?php echo $txt['desc']; ?>
        </p>
        
        <a href="register.php" class="btn-jugar">
            <?php echo $txt['jugar']; ?>
        </a>
    </main>

    <script src="lang.js"></script>
    <script src="Temas.js"></script>
</body>
</html>