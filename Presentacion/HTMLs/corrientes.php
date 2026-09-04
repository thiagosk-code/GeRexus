<?php
require_once __DIR__ . '/../Scripts/auth_check.php';
require_once __DIR__ . '/../Scripts/lang.php';

$corrientes = [
    [
        'id' => 'cinismo',
        'nombre' => $txt['cinismo'] ?? 'Cinismo',
        'img_simbolo' => '../../Assets/Cinismo.png'
    ],
    [
        'id' => 'estoicismo',
        'nombre' => $txt['estoicismo'] ?? 'Estoicismo',
        'img_simbolo' => '../../Assets/Estoicismo.png'
    ],
    [
        'id' => 'platonismo',
        'nombre' => $txt['platonismo'] ?? 'Platonismo',
        'img_simbolo' => '../../Assets/Platonismo.png'
    ],
    [
        'id' => 'aristotelismo',
        'nombre' => $txt['aristotelismo'] ?? 'Aristotelismo',
        'img_simbolo' => '../../Assets/Aristotelismo.png'
    ],
    [
        'id' => 'epicureismo',
        'nombre' => $txt['epicureismo'] ?? 'Epicureísmo',
        'img_simbolo' => '../../Assets/Epicureismo.png'
    ],
    [
        'id' => 'existencialismo',
        'nombre' => $txt['existencialismo'] ?? 'Existencialismo',
        'img_simbolo' => '../../Assets/Existencialismo.png'
    ]
];
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang ?? 'es', ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeRexus - <?php echo htmlspecialchars($txt['corrientes_titulo'] ?? 'Corrientes Filosóficas', ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="icon" type="image/png" href="../../Assets/Socrates.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Michroma&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <link rel="stylesheet" href="../CSSs/style.css">
    <link rel="stylesheet" href="../CSSs/corrientes.css">
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

    <main class="main-content">
        <section class="hero-section">
            <h1 class="main-title"><?php echo htmlspecialchars($txt['corrientes_titulo'] ?? 'Corrientes Filosóficas', ENT_QUOTES, 'UTF-8'); ?></h1>
        </section>

        <div class="search-container">
            <span class="material-symbols-outlined search-icon">search</span>
            <input type="text" id="corriente-search" class="search-input" placeholder="<?php echo htmlspecialchars($txt['buscar_corriente'] ?? 'Buscar corriente...', ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off">
        </div>

        <section class="corrientes-scroll-container">
            <div class="corrientes-grid" id="corrientes-grid">
                <?php foreach ($corrientes as $corriente): ?>
                    <a href="corriente_detalle.php?id=<?php echo urlencode($corriente['id']); ?>" class="corriente-card" data-nombre="<?php echo htmlspecialchars(mb_strtolower($corriente['nombre'], 'UTF-8')); ?>">
                        <div class="card-avatars">
                            <span class="avatar-img corriente-img" style="background-image: url('<?php echo htmlspecialchars($corriente['img_simbolo'], ENT_QUOTES, 'UTF-8'); ?>');"></span>
                        </div>
                        <div class="card-info">
                            <h2 class="corriente-nombre"><?php echo htmlspecialchars($corriente['nombre'], ENT_QUOTES, 'UTF-8'); ?></h2>
                        </div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <div class="gacha-bar">
            <a href="DraftoWiki.php" class="btn-card btn-gacha">DraftoWiki</a>
        </div>
    </main>

    <script src="../Scripts/lang.js"></script>
    <script src="../Scripts/Temas.js"></script>
    <script src="../Scripts/header.js" defer></script>
    <script>
        document.getElementById('corriente-search').addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.corriente-card');

            cards.forEach(card => {
                const nombre = card.getAttribute('data-nombre');
                if (nombre.includes(query)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>