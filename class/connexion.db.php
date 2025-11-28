<?php
// Shared connection helper for both the local XAMPP stack and the Docker stack.
function connexion(): PDO {
    // Allow overriding via environment variables (set inside Docker).
    $host = getenv('DB_HOST') ?: '127.0.0.1';
    $port = getenv('DB_PORT') ?: 3306;
    $db   = getenv('DB_NAME') ?: 'tpc';
    $user = getenv('DB_USER') ?: 'root';
    $pass = getenv('DB_PASSWORD') !== false ? getenv('DB_PASSWORD') : '';

    $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
    $opts = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    return new PDO($dsn, $user, $pass, $opts);
}
