<?php

namespace App;

use PDO;
use PDOException;

/**
 * Database
 *
 * Maakt één verbinding met MySQL (singleton).
 * Gebruik overal: Database::getConnection()
 */
class Database
{
    private static ?PDO $connection = null;

    public static function getConnection(): PDO
    {
        // Alleen de eerste keer maken we echt een verbinding.
        if (self::$connection !== null) {
            return self::$connection;
        }

        // Staat de MySQL-driver aan in php.ini?
        if (!extension_loaded('pdo_mysql')) {
            self::showError(
                'De PHP-extensie <b>pdo_mysql</b> staat uit.',
                'Open je <code>php.ini</code>, zoek de regel <code>;extension=pdo_mysql</code> en haal de <code>;</code> weg. Start daarna de PHP-server opnieuw.'
            );
        }

        $config = require __DIR__ . '/../config/config.php';

        $dsn = 'mysql:host=' . $config['db_host']
            . ';port=' . $config['db_port']
            . ';dbname=' . $config['db_name']
            . ';charset=utf8mb4';

        try {
            self::$connection = new PDO($dsn, $config['db_user'], $config['db_password'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // fouten worden exceptions
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // rijen als array met kolomnamen
            ]);
        } catch (PDOException $exception) {
            self::showError(
                'Kan geen verbinding maken met de database.',
                'Staat MySQL aan in het XAMPP Control Panel? En heb je <code>database/easyenglish.sql</code> geïmporteerd in phpMyAdmin?<br><br><small>Foutmelding: '
                    . htmlspecialchars($exception->getMessage()) . '</small>'
            );
        }

        return self::$connection;
    }

    /**
     * Toont een duidelijke foutpagina en stopt het script.
     */
    private static function showError(string $title, string $help): never
    {
        http_response_code(500);
        echo '<!doctype html><html lang="nl"><head><meta charset="UTF-8"><title>Databasefout</title></head>'
            . '<body style="font-family:Arial,sans-serif;background:#fff7f7;color:#960000;padding:60px">'
            . '<div style="max-width:560px;margin:auto;background:#fff;border:1px solid #c63a3a;border-radius:16px;padding:32px">'
            . '<h1 style="margin-top:0;font-size:22px">' . $title . '</h1><p style="line-height:1.6">' . $help . '</p>'
            . '</div></body></html>';
        exit;
    }
}
