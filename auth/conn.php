<?php
final class Conn
{
    public static function setConnection(): mixed
    {
        $servername = $_ENV['DB_HOST'];
        $username = $_ENV['DB_USERNAME'];
        $password = $_ENV['DB_PASSWORD'];
        $database = $_ENV['DB_DATABASE'];

        $dsn = "mysql:host=$servername;dbname=$database";

        $maxRetries = 3;
        $attempt = 0;
        while ($attempt < $maxRetries) {
            try {
                $pdo = new PDO($dsn, $username, $password);
                $pdo->setAttribute(PDO::ATTR_PERSISTENT, true);
                $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                return $pdo;
            } catch (PDOException $e) {
                if (++$attempt == $maxRetries) {
                    http_response_code(500);
                    throw new Exception("Error connecting to the database after $attempt attempts: " . $e->getMessage());
                }
            }
            sleep($attempt);
        }
        return null;
    }
}