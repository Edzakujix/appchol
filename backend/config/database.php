<?php
/**
 * Configuración de la Base de Datos
 */

class Database {
    private $host = "localhost";
    private $db_name = "aprendiendo_chol";
    private $username = "root";  // Cambiar según tu configuración
    private $password = "";      // Cambiar según tu configuración
    private $conn;

    public function getConnection() {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password
            );
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch(PDOException $e) {
            error_log("Error de conexión: " . $e->getMessage());
            return null;
        }

        return $this->conn;
    }
}
?>