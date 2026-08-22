<?php
require_once __DIR__ . '/../Scripts/auth_check.php';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$nom = $_SESSION['nom'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Base - Panel de Control</title>
</head>
<body>
    <header>
        <h1>Bienvenido, <?php echo htmlspecialchars($nom, ENT_QUOTES, 'UTF-8'); ?></h1>
        
        <form action="logout.php" method="POST" style="display: inline;">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <button type="submit" class="btn-logout">Cerrar Sesion</button>
        </form>
    </header>

    <main>
        <p>Bienvenido al menu, recordar hacer la pagina despues.</p>
    </main>
</body>
</html>