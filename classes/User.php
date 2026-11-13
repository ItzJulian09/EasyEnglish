<?php
// Alles wat met gebruikers te maken heeft (tabel users)

class User
{
    private $db;

    public function __construct()
    {
        $this->db = Database::connect();
    }

    public function getById($id)
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByEmail($email)
    {
        $stmt = $this->db->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    // Geeft de gebruiker terug als e-mail en wachtwoord kloppen, anders false
    public function login($email, $wachtwoord)
    {
        $user = $this->getByEmail($email);

        if ($user && password_verify($wachtwoord, $user['password_hash'])) {
            return $user;
        }
        return false;
    }

    public function register($naam, $email, $wachtwoord)
    {
        $hash = password_hash($wachtwoord, PASSWORD_DEFAULT);

        $stmt = $this->db->prepare('INSERT INTO users (full_name, email, password_hash) VALUES (?, ?, ?)');
        $stmt->execute([$naam, $email, $hash]);
        return $this->db->lastInsertId();
    }

    public function update($id, $naam, $email, $standaardOpenbaar)
    {
        $stmt = $this->db->prepare('UPDATE users SET full_name = ?, email = ?, default_public = ? WHERE id = ?');
        $stmt->execute([$naam, $email, $standaardOpenbaar, $id]);
    }
}
