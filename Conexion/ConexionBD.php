<?php
require_once __DIR__ . '/../config.php';

class ConexionBD {

    private $host = DB_HOST;
    private $db_name = DB_NAME;
    private $username = DB_USER;
    private $password = DB_PASS;
    private $conn;

    public function connect() {
        $this->conn = null;

        try {
            $dsn = "mysql:host={$this->host};dbname={$this->db_name};charset=utf8mb4";
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            throw new Exception("Error al conectar a la base de datos: " . $e->getMessage());
        }

        return $this->conn;
    }

    public function cerrarRecursos() {
        $this->conn = null;
    }
}
?>
