<?php

class Conn
{
    public static function setConnection(): object
    {
        $servername = "localhost";
        $username = "root";
        $password = "";
        $database = "hotelsync";
        $dsn = "mysql:host=$servername;dbname=$database";

        try {
            $pdo = new PDO($dsn, $username, $password);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            http_response_code(500);
            $responce = [
                'Error' => "Error connecting to the database: " . $e->getMessage()
            ];
            die(json_encode($responce));
        }
        return $pdo;
    }
}