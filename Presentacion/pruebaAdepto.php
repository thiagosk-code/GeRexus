<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alta Adepto</title>
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
        <h1><u>Menú Adepto</u></h1>
        <nav>
            <ul>
                <li><a href="index.php">Inicio</a></li>
                <li><a href="pruebaAdepto.php">Alta Adepto</a></li>
            </ul>
        </nav>
        <hr>
    </header>

    <section>
        <h1>Alta Adepto</h1>
        <hr>
        <form action="#" method="POST">
            <label for="esp">Especie:</label>
            <input type="text" id="esp" name="esp" required>

            <label for="esShiny">Shiny:</label>
            <input type="checkbox" id="esShiny" name="esShiny" value="1">

            <label for="desc">Descripción:</label>
            <input type="text" id="desc" name="desc" required>

            <label for="corr">Corriente:</label>
            <input type="text" id="corr" name="corr" required>

            <button type="submit">Alta</button>
        </form>
    </section>

    <?php
    require_once (__DIR__ . '/../DTO/AdeptoDTO.php');
    require_once (__DIR__ . '/../Logica/LogicaAdepto.php');
    require_once (__DIR__ . '/../Logica/FachadaLogica.php');

    if (isset($_POST['esp']) && isset($_POST['desc']) && isset($_POST['corr'])) {
        $esp = $_POST['esp'];
        $esShiny = isset($_POST['esShiny']);
        $desc = $_POST['desc'];
        $corr = $_POST['corr'];

        $idAdepto = 0;

        $aDTO = new AdeptoDTO($idAdepto, $esp, $esShiny, $desc, $corr);
 
        $facha = new FachadaLogica();
        $res = $facha->retornoILogicaAdepto()->altaAdeptoL($aDTO);

        if ($res === true) {
            print "<br><strong>Alta dada con éxito.</strong><br>";
        } else {
            print "<br><strong>Error - no se pudo dar de alta.</strong><br>";
        }
    }
    ?>

</body>
</html>