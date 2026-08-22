<?php
/**
 * Plantilla de configuracion local (config.example.php).
 * 
 * INSTRUCCIONES:
 * 1. Copia este archivo y renombralo a 'config.php' en esta misma carpeta.
 * 2. Sustituye los valores por defecto por las credenciales de tu entorno local.
 * 3. Asegurate de que 'config.php' este incluido en el archivo .gitignore.
 */

define('PEPPER_SECRET', 'tu_pepper_secret_aqui');
define('RECAPTCHA_SECRET_KEY', 'tu_recaptcha_secret_key_aqui');

define('DB_HOST', 'localhost');
define('DB_NAME', 'Draftoicos');
define('DB_USER', 'root');
define('DB_PASS', '');
?>
