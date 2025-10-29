<?php

$host = '127.0.0.1';
$port = 3307;
$db = 'yourdbname';
$user = 'root';
$pass = '';

echo "Testing connection to MySQL server...\n";

try {
    $pdo = new PDO("mysql:host=$host;port=$port;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_TIMEOUT => 5,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    echo "Connected successfully to MySQL server\n";

    $pdo->exec("USE `$db`");
    echo "Database '$db' selected successfully\n";

} catch (PDOException $e) {
    echo "PDOException: " . $e->getMessage() . "\n";
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
