<?php
require_once __DIR__ . '/../Scripts/auth_check.php';
require_once __DIR__ . '/../Scripts/lang.php';

$filosofos = [
    [
        'id' => 'diogenes',
        'nombre' => $txt['diogenes'] ?? 'Diógenes',
        'img_autor' => '../../Assets/Diogenes.png',
        'img_adepto' => '../../Assets/DiogenesPerro.png'
    ],
    [
        'id' => 'socrates',
        'nombre' => $txt['socrates'] ?? 'Sócrates',
        'img_autor' => '../../Assets/Socrates.png',
        'img_adepto' => '../../Assets/SocratesHyrax.png'
    ],
    [
        'id' => 'hypatia',
        'nombre' => $txt['hypatia'] ?? 'Hypatia',
        'img_autor' => '../../Assets/Hypatia.png',
        'img_adepto' => '../../Assets/HypatiaAbeja.png'
    ],
    [
        'id' => 'aristoteles',
        'nombre' => $txt['aristoteles'] ?? 'Aristóteles',
        'img_autor' => '../../Assets/Aristoteles.png',
        'img_adepto' => '../../Assets/AristotelesTortuga.png'
    ],
    [
        'id' => 'platon',
        'nombre' => $txt['platon'] ?? 'Platón',
        'img_autor' => '../../Assets/Platon.png',
        'img_adepto' => '../../Assets/PlatonPato.png'
    ],
    [
        'id' => 'epicuro',
        'nombre' => $txt['epicuro'] ?? 'Epicuro',
        'img_autor' => '../../Assets/Epicuro.png',
        'img_adepto' => '../../Assets/EpicuroKoala.png'
    ],
    [
        'id' => 'sartre',
        'nombre' => $txt['sartre'] ?? 'Jean-Paul Sartre',
        'img_autor' => '../../Assets/Sartre.png',
        'img_adepto' => '../../Assets/sinAdepto.png'
    ],
    [
        'id' => 'camus',
        'nombre' => $txt['camus'] ?? 'Albert Camus',
        'img_autor' => '../../Assets/Camus.png',
        'img_adepto' => '../../Assets/sinAdepto.png'
    ],
    [
        'id' => 'nietzsche',
        'nombre' => $txt['nietzsche'] ?? 'Friedrich Nietzsche',
        'img_autor' => '../../Assets/Nietzsche.png',
        'img_adepto' => '../../Assets/sinAdepto.png'
    ]
];
?>
<!DOCTYPE html>
<html lang="<?php echo htmlspecialchars($lang ?? 'es', ENT_QUOTES, 'UTF-8'); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>GeRexus - <?php echo htmlspecialchars($txt['filosofos_titulo'] ?? 'Filósofos', ENT_QUOTES, 'UTF-8'); ?></title>
    <link rel="icon" type="image/png" href="../../Assets/Socrates.png">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Michroma&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

    <link rel="stylesheet" href="../CSSs/style.css">
    <link rel="stylesheet" href="../CSSs/filosofos.css">
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
            <h1 class="main-title"><?php echo htmlspecialchars($txt['filosofos_titulo'] ?? 'Filósofos', ENT_QUOTES, 'UTF-8'); ?></h1>
        </section>

        <div class="search-container">
            <span class="material-symbols-outlined search-icon">search</span>
            <input type="text" id="filosofo-search" class="search-input" placeholder="<?php echo htmlspecialchars($txt['buscar_filosofo'] ?? 'Buscar filósofo...', ENT_QUOTES, 'UTF-8'); ?>" autocomplete="off">
        </div>

        <section class="filosofos-scroll-container">
            <div class="filosofos-grid" id="filosofos-grid">
                <?php foreach ($filosofos as $filosofo): ?>
                    <a href="filosofo_detalle.php?id=<?php echo urlencode($filosofo['id']); ?>" class="filosofo-card" data-nombre="<?php echo htmlspecialchars(mb_strtolower($filosofo['nombre'], 'UTF-8')); ?>">
                        <div class="card-avatars">
                            <span class="avatar-img filosofo-img" style="background-image: url('<?php echo htmlspecialchars($filosofo['img_autor'], ENT_QUOTES, 'UTF-8'); ?>');"></span>
                            <span class="avatar-img adepto-img" style="background-image: url('<?php echo htmlspecialchars($filosofo['img_adepto'], ENT_QUOTES, 'UTF-8'); ?>');"></span>
                        </div>
                        <div class="card-info">
                            <h2 class="filosofo-nombre"><?php echo htmlspecialchars($filosofo['nombre'], ENT_QUOTES, 'UTF-8'); ?></h2>
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
        document.getElementById('filosofo-search').addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            const cards = document.querySelectorAll('.filosofo-card');

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