<?php
// Woordenlijsten (tabel word_lists) en de woorden erin (tabel words)

class WordList
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    // Eén lijst ophalen, met de naam van de maker en het aantal woorden
    public function getById($id)
    {
        $stmt = $this->db->prepare('
            SELECT word_lists.*, users.full_name,
                (SELECT COUNT(*) FROM words WHERE words.list_id = word_lists.id) AS word_count
            FROM word_lists
            JOIN users ON users.id = word_lists.user_id
            WHERE word_lists.id = ?
        ');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    // Mijn eigen lijsten, met de laatste score en wanneer ik voor het laatst heb geoefend
    public function getMyLists($userId)
    {
        $stmt = $this->db->prepare('
            SELECT word_lists.*,
                (SELECT COUNT(*) FROM words WHERE words.list_id = word_lists.id) AS word_count,
                (SELECT score FROM attempts WHERE attempts.list_id = word_lists.id AND attempts.user_id = ?
                    ORDER BY created_at DESC LIMIT 1) AS last_score,
                (SELECT MAX(created_at) FROM attempts WHERE attempts.list_id = word_lists.id AND attempts.user_id = ?) AS last_date
            FROM word_lists
            WHERE word_lists.user_id = ?
            ORDER BY word_lists.id DESC
        ');
        $stmt->execute([$userId, $userId, $userId]);
        return $stmt->fetchAll();
    }

    // Openbare lijsten voor de bibliotheek. Je kunt zoeken op titel, categorie of maker.
    public function getPublicLists($zoek, $sorteer)
    {
        $sql = '
            SELECT word_lists.*, users.full_name,
                (SELECT COUNT(*) FROM words WHERE words.list_id = word_lists.id) AS word_count
            FROM word_lists
            JOIN users ON users.id = word_lists.user_id
            WHERE word_lists.is_public = 1 AND word_lists.is_published = 1
            AND (word_lists.title LIKE ? OR word_lists.category LIKE ? OR users.full_name LIKE ?)
        ';

        if ($sorteer == 'naam') {
            $sql .= ' ORDER BY word_lists.title ASC';
        } else {
            $sql .= ' ORDER BY word_lists.id DESC';
        }

        $zoekTekst = '%' . $zoek . '%';
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$zoekTekst, $zoekTekst, $zoekTekst]);
        return $stmt->fetchAll();
    }

    // Alle lijsten van iedereen (voor de beheerder)
    public function getAllLists($zoek)
    {
        $stmt = $this->db->prepare('
            SELECT word_lists.*, users.full_name, users.email,
                (SELECT COUNT(*) FROM words WHERE words.list_id = word_lists.id) AS word_count
            FROM word_lists
            JOIN users ON users.id = word_lists.user_id
            WHERE word_lists.title LIKE ? OR users.full_name LIKE ?
            ORDER BY word_lists.id DESC
        ');
        $stmt->execute(['%' . $zoek . '%', '%' . $zoek . '%']);
        return $stmt->fetchAll();
    }

    public function getWords($listId)
    {
        $stmt = $this->db->prepare('SELECT * FROM words WHERE list_id = ? ORDER BY position');
        $stmt->execute([$listId]);
        return $stmt->fetchAll();
    }

    // Nieuwe lijst maken, geeft het id van de nieuwe lijst terug
    public function create($userId, $titel, $omschrijving, $categorie, $richting, $openbaar, $gepubliceerd)
    {
        $stmt = $this->db->prepare('
            INSERT INTO word_lists (user_id, title, description, category, direction, is_public, is_published)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ');
        $stmt->execute([$userId, $titel, $omschrijving, $categorie, $richting, $openbaar, $gepubliceerd]);
        return $this->db->lastInsertId();
    }

    public function update($id, $titel, $omschrijving, $categorie, $richting, $openbaar, $gepubliceerd)
    {
        $stmt = $this->db->prepare('
            UPDATE word_lists
            SET title = ?, description = ?, category = ?, direction = ?, is_public = ?, is_published = ?
            WHERE id = ?
        ');
        $stmt->execute([$titel, $omschrijving, $categorie, $richting, $openbaar, $gepubliceerd, $id]);
    }

    // Eerst alle oude woorden weg, daarna de nieuwe erin
    public function saveWords($listId, $woorden)
    {
        $stmt = $this->db->prepare('DELETE FROM words WHERE list_id = ?');
        $stmt->execute([$listId]);

        $stmt = $this->db->prepare('INSERT INTO words (list_id, dutch, english, position) VALUES (?, ?, ?, ?)');
        $positie = 1;
        foreach ($woorden as $woord) {
            $stmt->execute([$listId, $woord['dutch'], $woord['english'], $positie]);
            $positie++;
        }
    }

    // Verwijderen mag alleen als de lijst van jou is
    public function delete($id, $userId)
    {
        $stmt = $this->db->prepare('DELETE FROM word_lists WHERE id = ? AND user_id = ?');
        $stmt->execute([$id, $userId]);
    }

    // De beheerder mag elke lijst verwijderen
    public function deleteAsAdmin($id)
    {
        $stmt = $this->db->prepare('DELETE FROM word_lists WHERE id = ?');
        $stmt->execute([$id]);
    }

    // Een openbare lijst kopiëren naar mijn eigen lijsten
    public function copy($id, $userId)
    {
        $lijst = $this->getById($id);
        $nieuwId = $this->create($userId, $lijst['title'], $lijst['description'], $lijst['category'], $lijst['direction'], 0, 1);
        $this->saveWords($nieuwId, $this->getWords($id));
        return $nieuwId;
    }
}
