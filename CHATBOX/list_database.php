<?php

$host = 'localhost';
$port = 3307;
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ]);
    $stmt = $pdo->query("SHOW DATABASES");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);

    echo "Databases on MySQL server:\n";
    foreach ($databases as $db) {
        echo "- $db\n";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
