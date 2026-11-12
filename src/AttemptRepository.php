<?php

namespace App;

/**
 * AttemptRepository
 *
 * Alles wat met de tabel "attempts" (gemaakte toetsen) te maken heeft.
 */
class AttemptRepository
{
    /**
     * Slaat een gemaakte toets op en geeft de score (0-100) terug.
     */
    public function create(int $userId, int $listId, int $correctCount, int $totalCount): int
    {
        // Voorbeeld: 9 van de 10 goed = 90%
        $score = $totalCount > 0 ? (int) round($correctCount / $totalCount * 100) : 0;

        $statement = Database::getConnection()->prepare(
            'INSERT INTO attempts (user_id, list_id, correct_count, total_count, score) VALUES (?, ?, ?, ?, ?)'
        );
        $statement->execute([$userId, $listId, $correctCount, $totalCount, $score]);

        return $score;
    }

    /**
     * Alle pogingen van een gebruiker, nieuwste eerst.
     * Met $search kun je zoeken op de naam van de woordenlijst.
     */
    public function getForUser(int $userId, string $search = ''): array
    {
        $sql = 'SELECT attempts.id, attempts.score, attempts.correct_count, attempts.total_count,
                       attempts.created_at, attempts.list_id, word_lists.title
                FROM attempts
                INNER JOIN word_lists ON word_lists.id = attempts.list_id
                WHERE attempts.user_id = ?';
        $values = [$userId];

        if ($search !== '') {
            $sql .= ' AND word_lists.title LIKE ?';
            $values[] = '%' . $search . '%';
        }

        $sql .= ' ORDER BY attempts.created_at DESC';

        $statement = Database::getConnection()->prepare($sql);
        $statement->execute($values);

        return $statement->fetchAll();
    }

    /**
     * De hoogste score ooit van een gebruiker (of null als er nog niets is gemaakt).
     */
    public function getBestForUser(int $userId): ?array
    {
        $statement = Database::getConnection()->prepare(
            'SELECT attempts.score, word_lists.title
             FROM attempts
             INNER JOIN word_lists ON word_lists.id = attempts.list_id
             WHERE attempts.user_id = ?
             ORDER BY attempts.score DESC, attempts.created_at DESC
             LIMIT 1'
        );
        $statement->execute([$userId]);
        $best = $statement->fetch();

        return $best ?: null;
    }
}
