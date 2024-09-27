<?php

class Conexion {
    private $con;

    public function __construct() {
        $host = "localhost";  
        $dbname = "reto."; 
        $username = "postgres";
        $password = "ana1203";

        try {
            $this->con = new PDO("pgsql:host=$host;port=5432;dbname=$dbname", $username, $password);
            $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            echo "";
        } catch (PDOException $exp) {
            echo "No se puede conectar a la base de datos: " . $exp->getMessage();
        }
    }

    public function getConexion() {
        return $this->con;
    }
}

?>