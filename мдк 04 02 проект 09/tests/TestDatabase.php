<?php
/**
 * Тестовая база данных — изолированная копия для тестов
 */

class TestDatabase
{
    private static ?PDO $db = null;

    private static function getDbPath(): string
    {
        return __DIR__ . '/test_database.db';
    }

    public static function getDB(): PDO
    {
        if (self::$db === null) {
            $dbPath = self::getDbPath();
            self::$db = new PDO('sqlite:' . $dbPath);
            self::$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::initSchema();
        }
        return self::$db;
    }

    public static function initSchema(): void
    {
        $db = self::$db ?? self::getDB();
        $db->exec("CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            phone TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            secret_question TEXT NOT NULL,
            secret_answer TEXT NOT NULL,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        )");
    }

    public static function cleanup(): void
    {
        if (self::$db !== null) {
            self::$db->exec("DELETE FROM users");
        }
    }

    public static function teardown(): void
    {
        self::$db = null;
        $path = self::getDbPath();
        if (file_exists($path)) {
            @unlink($path);
        }
    }
}
