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
    


    /**
    * Retourne tous les utilisateurs sans les mdp
    */
    public function getAllUser(): array {
        $stmt = $this->pdo->prepare('
        SELECT id, username, role, magasin, created_at
        FROM users
        ORDER BY id ASC');

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Création d'un nouvel utilisateur
     * @throws Exception si données invalides ou username déja pris
     */
    public function createUser(array $data): int
    {
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';
        $role     = $data['role'] ?? '';
        $magasin  = $data['magasin'] ?? '';

        if (empty($username)) {
            throw new Exception("Le nom d'utilisateur est obligatoire");
        }

        if (strlen($password) < 5) {
            throw new Exception ("Le mot de passe doit contenir au moins 5 caractères");
        }

        if (!in_array($role, ['admin', 'employe'])) {
            throw new Exception("Le rôle est invalide");
        }

        if (!in_array($magasin, ['tours_nord', 'tours_centre'])) {
            throw new Exception("Le magasin est invalide");
        }

        // Vérification : Username déja pris
        $existing = $this->findByUsername($username);
        if ($existing) {
            throw new Exception("Ce nom d'utilisateur est déja utilisé");
        }

        $stmt = $this->pdo->prepare('
        INSERT INTO users (username, password_hash, role, magasin)
        VALUES (:username, :password_hash, :role, :magasin)
        ');

        $stmt->execute([
            ':username'      => $username,
            ':password_hash' => password_hash($password, PASSWORD_BCRYPT),
            ':role'          => $role,
            ':magasin'       => $magasin,
        ]);

        return (int) $this->pdo->lastInsertId();

    }

    /**
     * Modification d'un utilisateur existant
     * Le mdp n'est mis à jours que si il est founi
     * @throws Exception si données invalides ou utilisateur introuvable
     */
    public function updateUser(int $id, array $data): void {
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';
        $role     = $data['role'] ?? '';
        $magasin  = $data['magasin'] ?? '';

        if (empty($username)) {
            throw new Exception("Le nom d'utilisateur est obligatoire");
        } 

        if (!in_array($role, ['admin', 'employe'])) {
            throw new Exception("Le rôle est invalide");
        }

        if (!in_array($magasin, ['tours_nord', 'tours_centre'])) {
            throw new Exception("Le magasin est invalide");
        }

        // si un nouveau mdp est fourni, on l'inclut dans la requête
        if (!empty($password)) {
            if (strlen($password) < 5) {
                throw new Exception("Le mot de passe doit contenir au moins 5 caractères");
            }

            $stmt = $this->pdo->prepare('
            UPDATE users
            SET username = :username,
            password_hash = :password_hash,
            role = :role,
            magasin = :magasin
            WHERE id = :id');

            $stmt->execute([
                ':username'      => $username,
                ':password_hash' => password_hash($password, PASSWORD_BCRYPT),
                ':role'          => $role,
                ':magasin'       => $magasin,
                ':id'            => $id,
            ]);

        } else {
            // Si pas de nouveau mdp - hash existant ne bouge pas
            $stmt = $this->pdo->prepare('
            UPDATE users
            SET username = :username,
            role = :role,
            magasin = :magasin
            WHERE id = :id');

            $stmt->execute([
                ':username' => $username,
                ':role'     => $role,
                ':magasin'  => $magasin,
                ':id'       => $id,
            ]);
        }

        if ($stmt->rowCount() === 0) {
            throw new Exception ("Utilisateur introuvable");
        }
    }

    /**
     * Suppression d'un utilisateur
     * Un admin ne peut pas se supprimer lui même
     * @throws Exception si tentative de suppression de son propre compte
     */
    public function deleteUser(int $id, int $currentUserId): void {
        if ($id === $currentUserId) {
            throw new Exception("Impossible de supprimer son propre compte");
        } 
        $stmt = $this->pdo->prepare('
        DELETE FROM users WHERE id = :id
        ');

        $stmt->execute([':id' => $id]);

        if ($stmt->rowCount() === 0) {
            throw new Exception("Utilisateur introuvable");
        }
    }
}