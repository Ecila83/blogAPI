<?php
require_once __DIR__ . '/../vendor/autoload.php'; 

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

function connect_db() {
    $dsn = 'mysql:dbname=' . $_ENV['DB_NAME'] . ';host=' . $_ENV['DB_HOST'];
    $user = $_ENV['DB_USER'];
    $password = $_ENV['DB_PASS'];

    try {
        $pdo = new PDO($dsn, $user, $password);
        
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $pdo;
    } catch (PDOException $e) {
        die("Erreur de connexion à la base de données : " . $e->getMessage());
    }
}