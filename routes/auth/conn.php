<?php

$servername = "localhost";
$username = "root";
$password = "";
$database = "hotelsync";
$dsn = "mysql:host=$servername;dbname=$database";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error connecting to the database: " . $e->getMessage());
}

