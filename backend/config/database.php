<?php

class Database {
    private $host = "localhost"; 
    private $db_name = "nuestr71_bd"; 
    private $username = "nuestr71_admin";    
    private $password = "Ch0l4pp_333.";        
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