<?php
declare(strict_types=1);
require_once __DIR__ . '/env.php';

final class Database
{
    private static ?PDO $pdo = null;

    private function __construct() {}

    public static function conn(): PDO
    {
        if (self::$pdo === null) {
            $dsn = sprintf('mysql:host=%s;dbname=%s;charset=utf8mb4',
                env('DB_HOST', 'localhost'), env('DB_NAME', 'portfolio_lmc'));
            self::$pdo = new PDO($dsn, env('DB_USER', ''), env('DB_PASS', ''), [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        }
        return self::$pdo;
    }
}
