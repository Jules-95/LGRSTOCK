<?php 

class User {
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    
    /**
    * Recherche un utilisateur par son username 
    * Retourne l'utilisateur ou null si introuvable 
    */
    public function findByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare('
        SELECT id, username, password_hash, role, magasin
        FROM users
        WHERE username = :username
        LIMIT 1
        ');

        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        return $user ?: null;
    }

    /**
     * Verifie le mot de passe contre le hash stocké
     */
    public function verifyPassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }
    
}