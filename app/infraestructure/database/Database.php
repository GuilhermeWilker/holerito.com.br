<?php

declare(strict_types=1);

namespace Holerito\infraestructure\database;

use PDO;
use PDOException;

final class Database
{
    private static ?PDO $instance = null;

    private function __construct() {}

    public static function connection(): PDO
    {
        if (self::$instance === null) {
            $path = __DIR__ . '/../../../../storage/holerito.sqlite';

            $dir = dirname($path);
            if (!is_dir($dir)) {
                mkdir($dir, 0755, true);
            }

            try {
                self::$instance = new PDO('sqlite:' . $path);
                self::$instance->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                self::$instance->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
                self::$instance->exec('PRAGMA journal_mode=WAL;');
                self::$instance->exec('PRAGMA foreign_keys=ON;');

                self::migrate(self::$instance);
            } catch (PDOException $e) {
                throw new \RuntimeException('Erro ao conectar ao banco de dados: ' . $e->getMessage());
            }
        }

        return self::$instance;
    }

    private static function migrate(PDO $pdo): void
    {
        $pdo->exec("
            CREATE TABLE IF NOT EXISTS financial_settings (
                id              INTEGER PRIMARY KEY,
                comparison_base TEXT    NOT NULL DEFAULT 'vale',
                ideal_limit_pct INTEGER NOT NULL DEFAULT 30,
                created_at      TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at      TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP
            )
        ");

        $pdo->exec("
            CREATE TABLE IF NOT EXISTS credit_card_invoices (
                id           INTEGER PRIMARY KEY AUTOINCREMENT,
                month        TEXT    NOT NULL UNIQUE,
                amount       REAL    NOT NULL,
                closing_day  INTEGER NOT NULL DEFAULT 15,
                payment_base TEXT    NOT NULL DEFAULT 'vale',
                created_at   TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP,
                updated_at   TEXT    NOT NULL DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Garante que sempre existe uma linha de configuração
        $count = $pdo->query('SELECT COUNT(*) FROM financial_settings')->fetchColumn();
        if ((int) $count === 0) {
            $pdo->exec("INSERT INTO financial_settings (comparison_base, ideal_limit_pct) VALUES ('vale', 30)");
        }
    }
}
