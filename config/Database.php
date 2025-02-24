<?php
class Database
{
    private static $instance = null;
    private $connection;
    private function __construct()
    {
        //$host = "spritns-db-1";

        $host = 'shoprecu-db-1'; //Nombre del contenedor de BD
        $dbname = 'shop'; // Nombre de la base de datos
        $username = 'shop'; // Usuario que se va a conectar
        $password = 'shop'; // Pasword del usuario
        try {
            $this->connection = new
                PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
            $this->connection->setAttribute(
                PDO::ATTR_ERRMODE,
                PDO::ERRMODE_EXCEPTION
            );
        } catch (PDOException $e) {

            if ($e->getMessage()) {
                $error = 'La base de datos no funciona correctamente. Contacte con el administrador';
                redirectError($error);
            }
        }
    }
    public static function getInstance()
    {
        if (self::$instance == null) {
            self::$instance = new Database();
        }
        return self::$instance;
    }
    public function getConnection()
    {
        return $this->connection;
    }
}
