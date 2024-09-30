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

        $maxRetries = 3;
        $retryDelay = 1;

        $pdo = null;
        $attempt = 0;
        while ($attempt < $maxRetries) {
            try {
                $pdo = new PDO($dsn, $username, $password);
                $pdo->setAttribute(PDO::ATTR_PERSISTENT, true);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                break;
            } catch (PDOException $e) {
                if ($attempt + 1 >= $maxRetries) {
                    http_response_code(500);
                    $response = [
                        'Error' => "Error connecting to the database after $maxRetries attempts: " . $e->getMessage()
                    ];
                    die(json_encode($response));
                }
                $attempt++;
                sleep($retryDelay);
            }
        }
        return $pdo;
    }
}