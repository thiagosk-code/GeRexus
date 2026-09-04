<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consultar Partidas Ganadas</title>
    <style>
        body {
            background-color: #121212;
            color: #e0e0e0;
            font-family: sans-serif;
            margin: 20px;
        }
        a {
            color: #bb86fc;
        }
        label {
            display: block;
            margin-top: 10px;
        }
        input, select, button {
            background-color: #1e1e1e;
            color: #ffffff;
            border: 1px solid #333;
            padding: 8px;
            margin-top: 5px;
            display: block;
            width: 250px;
        }
        button {
            cursor: pointer;
            background-color: #bb86fc;
            color: #000;
            font-weight: bold;
            border: none;
            margin-top: 15px;
            width: auto;
        }
    </style>
</head>
<body>

    <header>
        <h1><u>Menú Usuario</u></h1>
        <nav>
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <li><a href="pruebaUsuario.php">Alta Usuario</a></li>
                <li><a href="pruebaConsultarPartidas.php">Consultar Partidas Ganadas</a></li>
            </ul>
        </nav>
        <hr>
    </header>

    <section>
        <h1>Consultar Partidas Ganadas</h1>
        <hr>
        <form action="#" method="POST">
            <label for="idUsuario">ID de Usuario:</label>
            <input type="number" id="idUsuario" name="idUsuario" required min="1">

            <button type="submit">Buscar</button>
        </form>
    </section>

    <?php
    require_once (__DIR__ . '/../DTO/UsuarioDTO.php');
    require_once (__DIR__ . '/../Logica/LogicaUsuario.php');
    require_once (__DIR__ . '/../Logica/FachadaLogica.php');

    if (isset($_POST['idUsuario'])) {
        $idUsuario = (int)$_POST['idUsuario'];

        // Se instancia el DTO pasando el ID a consultar
        $uDTO = new UsuarioDTO($idUsuario, '', '', '', 0, 0, false);

        // Se invoca la capa de Lógica mediante la Fachada
        $facha = new FachadaLogica();
        $partidasGanadas = $facha->retornoILogicaUsuario()->buscarPartidasGanadasL($uDTO);

        if ($partidasGanadas !== null) {
            print "<br><strong>El usuario ID " . htmlspecialchars($idUsuario) . " tiene " . $partidasGanadas . " partidas ganadas.</strong><br>";
        } else {
            print "<br><strong>Error - No se pudo obtener la cantidad de partidas ganadas.</strong><br>";
        }
    }
    ?>

</body>
</html>