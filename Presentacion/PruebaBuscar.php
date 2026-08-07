<?php
require_once (__DIR__ . '/../DTO/UsuarioDTO.php'); 
require_once (__DIR__ . '/../Conexion/ConexionBD.php'); // Cambia por el nombre real de tu clase DAO / Conexión

$usuariosEncontrados = [];

try {
    // 1. Instanciar e inicianizar conexión usando ConexionBD
    $conexionBD = new ConexionBD();
    $conn = $conexionBD->connect();

    if ($conn !== null) {
        $sql = "SELECT * FROM Usuarios WHERE idUsuario = ?;";
        $stmt = $conn->prepare($sql);

        // 2. Ejecutar la búsqueda basada en el método buscarUsuario (probando del ID 1 al 30)
        for ($id = 1; $id <= 30; $id++) {
            $stmt->execute([$id]);
            $reader = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($reader) {
                // Instanciación usando los atributos del DTO
                $usuario = new UsuarioDTO(
                    (int)$reader['idUsuario'],
                    $reader['Nombre'],
                    $reader['Email'],
                    $reader['Contra'],
                    (int)$reader['PartidasGanadas'],
                    (int)$reader['Monedas'],
                    (bool)$reader['esAdmin']
                );
                $usuario->setPartidasGanadas((int)$reader['Monedas']);

                $usuariosEncontrados[] = $usuario;
            }
        }
        $stmt->closeCursor();
    }
} catch (Exception $e) {
    $errorConexion = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lista de Usuarios - Draftoicos</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f6f9;
            margin: 30px;
        }
        h1 {
            color: #2c3e50;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background-color: #fff;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid #e1e8ed;
        }
        th {
            background-color: #3498db;
            color: white;
            text-transform: uppercase;
            font-size: 13px;
        }
        tr:hover {
            background-color: #f1f5f9;
        }
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            color: white;
        }
        .admin { background-color: #2ecc71; }
        .user { background-color: #95a5a6; }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            padding: 15px;
            border-radius: 6px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>

    <h1>Listado de Usuarios (BD: Draftoicos)</h1>

    <?php if (isset($errorConexion)): ?>
        <div class="error">
            <strong>Error:</strong> <?= htmlspecialchars($errorConexion) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($usuariosEncontrados)): ?>
        <table>
            <thead>
                <tr>
                    <th>ID Usuario</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Contraseña</th>
                    <th>Monedas / Partidas</th>
                    <th>Rol</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($usuariosEncontrados as $usr): ?>
                    <tr>
                        <td><?= htmlspecialchars($usr->getIdUsuario()) ?></td>
                        <td><?= htmlspecialchars($usr->getNombre()) ?></td>
                        <td><?= htmlspecialchars($usr->getEmail()) ?></td>
                        <td><code><?= htmlspecialchars($usr->getPassword()) ?></code></td>
                        <td><?= htmlspecialchars($usr->getPartidasGanadas()) ?></td>
                        <td>
                            <?php if ($usr->getEsAdmin()): ?>
                                <span class="badge admin">Admin</span>
                            <?php else: ?>
                                <span class="badge user">Usuario</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No se encontraron usuarios en la base de datos.</p>
    <?php endif; ?>

</body>
</html>