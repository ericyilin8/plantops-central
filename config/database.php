<?php
// config/database.php

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables from the project root
//$updir = DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
//error_log(__DIR__ . $updir);
//$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . $updir);
//error_log(dirname(__DIR__));
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
$dotenv->safeLoad(); // safeLoad won't crash if .env is missing in production

//issues with getenv, phpdotenv may not load it depending on config
// Fetch credentials from environment variables (falls back to server env if deployed in production)
// Fetch credentials from the $_ENV superglobal populated by phpdotenv
$host = $_ENV['DB_HOST'];
$db_name = $_ENV['DB_NAME'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db_name;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    http_response_code(500);
    echo json_encode(["success" => false, "error" => "Internal server error."]);
    exit();
}