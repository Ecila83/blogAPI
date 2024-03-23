<?php
declare(strict_types=1);

require_once(__DIR__ . '/../config/config.php');
class Users {
    private $pdo;
    private int $id;
    private string $username;
    private string $email;
    private string $password;
    private string $created_at;
    private string $updated_at;

    public function getAllUsers($limit, $offset): array {
        $this->connectToDatabase();
        $query = "SELECT * FROM users";  
        
        if($limit > -1 && $offset > -1) {
            $query .= " LIMIT :limit OFFSET :offset";
            $statement = $this->pdo->prepare($query);
            $statement->bindParam(':limit', $limit, PDO::PARAM_INT);
            $statement->bindParam(':offset', $offset, PDO::PARAM_INT);
            $statement->execute();
        } else {
            $statement = $this->pdo->query($query);
        }
        $posts = $statement->fetchAll(PDO::FETCH_ASSOC);
        return $posts;
    }
    

    public function getUserById(int $id): array|bool {
        $this->connectToDatabase();
        $query = "SELECT * FROM users WHERE id = :id";
        $statement = $this->pdo->prepare($query);
        $statement->execute(['id' => $id]);
        $post = $statement->fetch(PDO::FETCH_ASSOC);
        return $post;
    }

    //requete de creation 
    public function createUser($postData) {
        
        if (isset($postData->title, $postData->body, $postData->author)) {
            $this->connectToDatabase();

            $username = $postData->username;
            $email = $postData->email;
            $password = $postData->password;

            $query = "INSERT INTO users (username, email, password, created_at, updated_at) VALUES (:username, :email, :password, NOW(), NOW())";
            $statement = $this->pdo->prepare($query);

            if ($statement->execute(['username,' => $username, 'email' => $email, 'password' => $password])) {
                return $this->pdo->lastInsertId(); // Insertion réussie : retourne l'id.
            } else {
                return false; // Échec de l'insertion
            }
        } else {
            return null; // Données incomplètes
        }
    }     

    //requete update
    public function updateUser($id, $postData) {
        $this->connectToDatabase();

        $username = $postData->username;
        $email = $postData->email;
        $password = $postData->password;

        $query = "UPDATE users SET username = :username, email = :email, password = :password, updated_at = NOW() WHERE id = :id";
        $statement = $this->pdo->prepare($query);
    
        if ($statement->execute(['id' => $id, 'username' => $username, 'email' => $email, 'password' => $password])) {
            return $id; // Mise à jour réussie : retourne l'id.
        } else {
            return false; // Échec de la mise à jour
        }
    }

    //requete delete 
    public function deleteUser($id): array|bool {
        $this->connectToDatabase();
        $query = "DELETE FROM users WHERE id = :id";
        $statement = $this->pdo->prepare($query);
        $statement->execute(['id' => $id]);
        return true;
    }

// Connection
    private function connectToDatabase(): void {
         if (!$this->pdo) {
             $this->pdo = connect_db();
    }
}
}
