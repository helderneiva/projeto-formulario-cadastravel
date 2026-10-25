<?php

declare(strict_types=1);

namespace App\Config;

use PDO;

final class Database
{
    /**
     * Abre a conexão com o MySQL usando as variáveis do .env.
     */
    public static function conectar(): PDO
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
            $_ENV['DB_HOST'] ?? 'localhost',
            (int) ($_ENV['DB_PORT'] ?? 3306),
            $_ENV['DB_NAME'] ?? ''
        );

        return new PDO(
            $dsn,
            $_ENV['DB_USER'] ?? '',
            $_ENV['DB_PASS'] ?? '',
            [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]
        );
    }
}