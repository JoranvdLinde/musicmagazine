<?php
require_once __DIR__ . '/../includes/functions.php';

$host = getEnvValue('DB_HOST');
$dbname = getEnvValue('DB_NAME');
$username = getEnvValue('DB_USER');
$password = getEnvValue('DB_PASS');

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}
