<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alta Usuario</title>
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
            </ul>
        </nav>
        <hr>
    </header>

    <section>
        <h1>Alta Usuario</h1>
        <hr>
        <form action="#" method="POST">
            <label for="nom">Nombre:</label>
            <input type="text" id="nom" name="nom" required>

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required>

            <label for="contra">Contraseña:</label>
            <input type="password" id="contra" name="contra" required>

            <button type="submit">Alta</button>
        </form>
    </section>

    <?php
    require_once (__DIR__ . '/../../DTO/UsuarioDTO.php');
    require_once (__DIR__ . '/../../Logica/LogicaUsuario.php');
    require_once (__DIR__ . '/../../Logica/FachadaLogica.php');

    if (isset($_POST['nom']) && isset($_POST['email']) && isset($_POST['contra'])) {
        $nom = $_POST['nom'];
        $email = $_POST['email'];
        $contra = $_POST['contra'];

        $idUsuario = 0; 
        $partidasGanadas = 0;
        $monedas = 0;
        $esAdmin = false;

        $uDTO = new UsuarioDTO($idUsuario, $nom, $email, $contra, $partidasGanadas, $monedas, $esAdmin);

        $facha = new FachadaLogica();
        $res = $facha->retornoILogicaUsuario()->altaUsuarioL($uDTO);

        if ($res === true) {
            print "<br><strong>Alta dada con éxito.</strong><br>";
        } else {
            print "<br><strong>Error - no se pudo dar de alta.</strong><br>";
        }
    }
    ?>

</body>
</html>