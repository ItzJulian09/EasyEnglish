<?php

namespace App;

/**
 * UserRepository
 *
 * Alles wat met de tabel "users" te maken heeft.
 */
class UserRepository
{
    public function findById(int $id): ?array
    {
        $statement = Database::getConnection()->prepare('SELECT * FROM users WHERE users.id = ?');
        $statement->execute([$id]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    public function findByEmail(string $email): ?array
    {
        $statement = Database::getConnection()->prepare('SELECT * FROM users WHERE users.email = ?');
        $statement->execute([$email]);
        $user = $statement->fetch();

        return $user ?: null;
    }

    /**
     * Maakt een nieuwe gebruiker aan en geeft het nieuwe id terug.
     */
    public function create(string $fullName, string $email, string $password, string $language): int
    {
        $database = Database::getConnection();
        $statement = $database->prepare(
            'INSERT INTO users (full_name, email, password_hash, site_language) VALUES (?, ?, ?, ?)'
        );
        $statement->execute([$fullName, $email, password_hash($password, PASSWORD_DEFAULT), $language]);

        return (int) $database->lastInsertId();
    }

    /**
     * Controleert of een e-mailadres al bij een ándere gebruiker hoort.
     */
    public function emailUsedByOtherUser(string $email, int $userId): bool
    {
        $statement = Database::getConnection()->prepare(
            'SELECT COUNT(*) FROM users WHERE users.email = ? AND users.id != ?'
        );
        $statement->execute([$email, $userId]);

        return (int) $statement->fetchColumn() > 0;
    }

    public function updateSettings(
        int $userId,
        string $fullName,
        string $email,
        string $language,
        string $direction,
        bool $defaultPublic
    ): void {
        $statement = Database::getConnection()->prepare(
            'UPDATE users
             SET full_name = ?, email = ?, site_language = ?, default_direction = ?, default_public = ?
             WHERE users.id = ?'
        );
        $statement->execute([$fullName, $email, $language, $direction, $defaultPublic ? 1 : 0, $userId]);
    }

    public function updateLanguage(int $userId, string $language): void
    {
        $statement = Database::getConnection()->prepare('UPDATE users SET site_language = ? WHERE users.id = ?');
        $statement->execute([$language, $userId]);
    }
}
