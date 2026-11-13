<?php

namespace App\Core;

use PDO;

/** PDO access for parameterized queries. */
final class DB
{
    private static ?PDO $pdo = null;

    public static function connection(): PDO
    {
        if (self::$pdo === null) {
            $host = $_ENV['DB_HOST'] ?? '127.0.0.1';
            $port = $_ENV['DB_PORT'] ?? '3307';
            $name = $_ENV['DB_NAME'] ?? 'classflow';
            $username = $_ENV['DB_USER'] ?? 'root';
            $password = $_ENV['DB_PASSWORD'] ?? '';
            $dsn = "mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4";

            self::$pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        }

        return self::$pdo;
    }

    public static function all(string $sql, array $params = []): array
    {
        $statement = self::connection()->prepare($sql);
        $statement->execute($params);

        return $statement->fetchAll();
    }

    public static function one(string $sql, array $params = []): ?array
    {
        $statement = self::connection()->prepare($sql);
        $statement->execute($params);

        return $statement->fetch() ?: null;
    }

    public static function exec(string $sql, array $params = []): int
    {
        $statement = self::connection()->prepare($sql);
        $statement->execute($params);

        return $statement->rowCount();
    }

    public static function id(): int
    {
        return (int) self::connection()->lastInsertId();
    }
}
