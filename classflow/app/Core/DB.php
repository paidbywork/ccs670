<?php
namespace App\Core;
use PDO;
final class DB {
  private static ?PDO $pdo = null;
  public static function connection(): PDO {
    if (self::$pdo === null) {
      $host = $_ENV['DB_HOST'] ?? '127.0.0.1'; $port = $_ENV['DB_PORT'] ?? '3306';
      $name = $_ENV['DB_NAME'] ?? 'classflow';
      self::$pdo = new PDO("mysql:host={$host};port={$port};dbname={$name};charset=utf8mb4", $_ENV['DB_USER'] ?? 'root', $_ENV['DB_PASSWORD'] ?? '', [PDO::ATTR_ERRMODE=>PDO::ERRMODE_EXCEPTION,PDO::ATTR_DEFAULT_FETCH_MODE=>PDO::FETCH_ASSOC,PDO::ATTR_EMULATE_PREPARES=>false]);
    }
    return self::$pdo;
  }
  public static function all(string $sql,array $params=[]): array { $s=self::connection()->prepare($sql);$s->execute($params);return $s->fetchAll(); }
  public static function one(string $sql,array $params=[]): ?array { $s=self::connection()->prepare($sql);$s->execute($params);return $s->fetch() ?: null; }
  public static function exec(string $sql,array $params=[]): int { $s=self::connection()->prepare($sql);$s->execute($params);return $s->rowCount(); }
  public static function id(): int {return (int)self::connection()->lastInsertId();}
}
