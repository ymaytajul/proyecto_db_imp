<?php
// config/db.php

class Database
{
    private $host = "84.247.167.198";
    private $port = "7002"; // Added port from user image
    private $db_name = "postgres";
    private $username = "admin";
    private $password = "elpasswordesesis";
    public $conn;

    public function getConnection()
    {
        $this->conn = null;

        try {
            $dsn = "pgsql:host=" . $this->host . ";port=" . $this->port . ";dbname=" . $this->db_name;
            $this->conn = new PDO($dsn, $this->username, $this->password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // FIX: Set default schema to 'imp'
            $this->conn->exec("SET search_path TO imp, public");

        } catch (PDOException $exception) {
            echo "Error de conexión: " . $exception->getMessage();
        }

        return $this->conn;
    }
}
?>