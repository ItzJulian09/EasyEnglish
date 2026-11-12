<?php

namespace App;

/**
 * WordListRepository
 *
 * Alles wat met woordenlijsten (word_lists), woorden (words) en likes te maken heeft.
 */
class WordListRepository
{
    // Toegestane manieren om de bibliotheek te sorteren
    public const SORT_OPTIONS = [
        'populair' => 'like_count DESC, word_lists.title ASC',
        'nieuwste' => 'word_lists.created_at DESC',
        'naam'     => 'word_lists.title ASC',
    ];

    /**
     * Eén lijst ophalen, inclusief naam van de maker, aantal woorden en aantal likes.
     */
    public function findById(int $listId): ?array
    {
        $statement = Database::getConnection()->prepare(
            'SELECT word_lists.*, users.full_name AS author_name,
                    (SELECT COUNT(*) FROM words WHERE words.list_id = word_lists.id) AS word_count,
                    (SELECT COUNT(*) FROM likes WHERE likes.list_id = word_lists.id) AS like_count
             FROM word_lists
             INNER JOIN users ON users.id = word_lists.user_id
             WHERE word_lists.id = ?'
        );
        $statement->execute([$listId]);
        $list = $statement->fetch();

        return $list ?: null;
    }

    /**
     * Alle eigen lijsten van een gebruiker, met de laatste score en wanneer er laatst is geoefend.
     */
    public function getListsForUser(int $userId): array
    {
        $statement = Database::getConnection()->prepare(
            'SELECT word_lists.*,
                    (SELECT COUNT(*) FROM words WHERE words.list_id = word_lists.id) AS word_count,
                    (SELECT attempts.score FROM attempts
                        WHERE attempts.list_id = word_lists.id AND attempts.user_id = ?
                        ORDER BY attempts.created_at DESC LIMIT 1) AS last_score,
                    (SELECT MAX(attempts.created_at) FROM attempts
                        WHERE attempts.list_id = word_lists.id AND attempts.user_id = ?) AS last_practiced
             FROM word_lists
             WHERE word_lists.user_id = ?
             ORDER BY word_lists.updated_at DESC'
        );
        $statement->execute([$userId, $userId, $userId]);

        return $statement->fetchAll();
    }

    /**
     * Openbare lijsten van andere gebruikers voor de bibliotheek.
     *
     * @param string $search Zoekt in titel, categorie en naam van de maker
     * @param string $sort   Een sleutel uit SORT_OPTIONS
     */
    public function getPublicLists(int $userId, string $search, string $sort): array
    {
        $sql = 'SELECT word_lists.*, users.full_name AS author_name,
                       (SELECT COUNT(*) FROM words WHERE words.list_id = word_lists.id) AS word_count,
                       (SELECT COUNT(*) FROM likes WHERE likes.list_id = word_lists.id) AS like_count
                FROM word_lists
                INNER JOIN users ON users.id = word_lists.user_id
                WHERE word_lists.is_public = 1
                  AND word_lists.is_published = 1
                  AND word_lists.user_id != ?';
        $values = [$userId];

        if ($search !== '') {
            $sql .= ' AND (word_lists.title LIKE ? OR word_lists.category LIKE ? OR users.full_name LIKE ?)';
            $searchText = '%' . $search . '%';
            array_push($values, $searchText, $searchText, $searchText);
        }

        // De sortering komt uit een vaste lijst, dus gebruikersinvoer komt nooit direct in de SQL.
        $orderBy = self::SORT_OPTIONS[$sort] ?? self::SORT_OPTIONS['populair'];
        $sql .= ' ORDER BY ' . $orderBy;

        $statement = Database::getConnection()->prepare($sql);
        $statement->execute($values);

        return $statement->fetchAll();
    }

    /**
     * Woorden van een lijst, op volgorde. Met $limit haal je er maar een paar op.
     */
    public function getWords(int $listId, ?int $limit = null): array
    {
        $sql = 'SELECT * FROM words WHERE words.list_id = ? ORDER BY words.position ASC, words.id ASC';
        if ($limit !== null) {
            $sql .= ' LIMIT ' . (int) $limit;
        }

        $statement = Database::getConnection()->prepare($sql);
        $statement->execute([$listId]);

        return $statement->fetchAll();
    }

    /**
     * Maakt een nieuwe lijst met woorden en geeft het nieuwe id terug.
     *
     * $data bevat: title, description, category, direction, is_public, is_published
     * $words is een array met ['dutch' => ..., 'english' => ...]
     */
    public function create(int $userId, array $data, array $words): int
    {
        $database = Database::getConnection();
        $database->beginTransaction();

        $statement = $database->prepare(
            'INSERT INTO word_lists (user_id, title, description, category, direction, is_public, is_published)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $statement->execute([
            $userId,
            $data['title'],
            $data['description'],
            $data['category'],
            $data['direction'],
            $data['is_public'],
            $data['is_published'],
        ]);
        $listId = (int) $database->lastInsertId();

        $this->insertWords($listId, $words);
        $database->commit();

        return $listId;
    }

    /**
     * Past een bestaande lijst aan en vervangt alle woorden.
     */
    public function update(int $listId, array $data, array $words): void
    {
        $database = Database::getConnection();
        $database->beginTransaction();

        $statement = $database->prepare(
            'UPDATE word_lists
             SET title = ?, description = ?, category = ?, direction = ?, is_public = ?, is_published = ?
             WHERE word_lists.id = ?'
        );
        $statement->execute([
            $data['title'],
            $data['description'],
            $data['category'],
            $data['direction'],
            $data['is_public'],
            $data['is_published'],
            $listId,
        ]);

        // Simpel en betrouwbaar: eerst alle oude woorden weg, dan de nieuwe erin.
        $delete = $database->prepare('DELETE FROM words WHERE words.list_id = ?');
        $delete->execute([$listId]);
        $this->insertWords($listId, $words);

        $database->commit();
    }

    /**
     * Verwijdert een lijst, maar alleen als hij van deze gebruiker is.
     */
    public function delete(int $listId, int $userId): void
    {
        $statement = Database::getConnection()->prepare(
            'DELETE FROM word_lists WHERE word_lists.id = ? AND word_lists.user_id = ?'
        );
        $statement->execute([$listId, $userId]);
    }

    /**
     * Verwijdert een lijst van wie dan ook. Alleen voor beheerders!
     * De woorden, pogingen en likes gaan automatisch mee (ON DELETE CASCADE).
     */
    public function deleteAsAdmin(int $listId): void
    {
        $statement = Database::getConnection()->prepare('DELETE FROM word_lists WHERE word_lists.id = ?');
        $statement->execute([$listId]);
    }

    /**
     * Alle lijsten van alle gebruikers, voor de beheerpagina.
     * Met $search zoek je op titel of op naam/e-mail van de maker.
     */
    public function getAllLists(string $search = ''): array
    {
        $sql = 'SELECT word_lists.id, word_lists.title, word_lists.is_public, word_lists.is_published,
                       word_lists.created_at, users.full_name AS author_name, users.email AS author_email,
                       (SELECT COUNT(*) FROM words WHERE words.list_id = word_lists.id) AS word_count
                FROM word_lists
                INNER JOIN users ON users.id = word_lists.user_id';
        $values = [];

        if ($search !== '') {
            $sql .= ' WHERE word_lists.title LIKE ? OR users.full_name LIKE ? OR users.email LIKE ?';
            $searchText = '%' . $search . '%';
            $values = [$searchText, $searchText, $searchText];
        }

        $sql .= ' ORDER BY word_lists.created_at DESC';

        $statement = Database::getConnection()->prepare($sql);
        $statement->execute($values);

        return $statement->fetchAll();
    }

    /**
     * Kopieert een (openbare) lijst naar de eigen lijsten van een gebruiker.
     */
    public function copyToUser(int $listId, int $userId): int
    {
        $list = $this->findById($listId);
        $words = $this->getWords($listId);

        return $this->create($userId, [
            'title'        => $list['title'],
            'description'  => $list['description'],
            'category'     => $list['category'],
            'direction'    => $list['direction'],
            'is_public'    => 0, // een kopie is standaard privé
            'is_published' => 1,
        ], $words);
    }

    public function isLikedBy(int $listId, int $userId): bool
    {
        $statement = Database::getConnection()->prepare(
            'SELECT COUNT(*) FROM likes WHERE likes.list_id = ? AND likes.user_id = ?'
        );
        $statement->execute([$listId, $userId]);

        return (int) $statement->fetchColumn() > 0;
    }

    /**
     * Zet een like aan of uit.
     */
    public function toggleLike(int $listId, int $userId): void
    {
        $database = Database::getConnection();

        if ($this->isLikedBy($listId, $userId)) {
            $statement = $database->prepare('DELETE FROM likes WHERE likes.list_id = ? AND likes.user_id = ?');
        } else {
            $statement = $database->prepare('INSERT INTO likes (list_id, user_id) VALUES (?, ?)');
        }

        $statement->execute([$listId, $userId]);
    }

    private function insertWords(int $listId, array $words): void
    {
        $statement = Database::getConnection()->prepare(
            'INSERT INTO words (list_id, dutch, english, position) VALUES (?, ?, ?, ?)'
        );

        $position = 1;
        foreach ($words as $word) {
            $statement->execute([$listId, $word['dutch'], $word['english'], $position]);
            $position++;
        }
    }
}
