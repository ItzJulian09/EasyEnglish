<?php
// Maakt de verbinding met de database.
// Pas hier de gegevens aan als jouw MySQL anders is ingesteld.

class Database
{
    private static $pdo = null;

    public static function connect()
    {
        // Alleen de eerste keer een verbinding maken, daarna dezelfde gebruiken
        if (self::$pdo == null) {
            self::$pdo = new PDO('mysql:host=127.0.0.1;dbname=easyenglish;charset=utf8mb4', 'root', '');
            self::$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }

        return self::$pdo;
    }
}
