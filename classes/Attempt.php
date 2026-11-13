<?php


class Attempt
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

   
    public function save($userId, $listId, $goed, $totaal)
    {
        $score = round($goed / $totaal * 100);

        $stmt = $this->db->prepare('INSERT INTO attempts (user_id, list_id, correct_count, total_count, score) VALUES (?, ?, ?, ?, ?)');
        $stmt->execute([$userId, $listId, $goed, $totaal, $score]);
        return $score;
    }


    public function getForUser($userId, $zoek)
    {
        $stmt = $this->db->prepare('
            SELECT attempts.*, word_lists.title
            FROM attempts
            JOIN word_lists ON word_lists.id = attempts.list_id
            WHERE attempts.user_id = ? AND word_lists.title LIKE ?
            ORDER BY attempts.created_at DESC
        ');
        $stmt->execute([$userId, '%' . $zoek . '%']);
        return $stmt->fetchAll();
    }

    
    public function getBest($userId)
    {
        $stmt = $this->db->prepare('
            SELECT attempts.score, word_lists.title
            FROM attempts
            JOIN word_lists ON word_lists.id = attempts.list_id
            WHERE attempts.user_id = ?
            ORDER BY attempts.score DESC
            LIMIT 1
        ');
        $stmt->execute([$userId]);
        return $stmt->fetch();
    }
}
