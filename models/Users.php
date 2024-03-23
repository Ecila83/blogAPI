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

        $users = $statement->fetchAll(PDO::FETCH_ASSOC);

        return $users;
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
    public function createUser($userData) {
        $this->connectToDatabase();

        $username = $userData->username;
        $email = $userData->email;
        $password = $userData->password;

        $query = "INSERT INTO users (username, email, password, created_at, updated_at) VALUES (:username, :email, :password, NOW(), NOW())";
        $statement = $this->pdo->prepare($query);

        if ($statement->execute(['username' => $username, 'email' => $email, 'password' => $password])) {
            return $this->pdo->lastInsertId(); // Insertion réussie : retourne l'id.
        } else {
            return false; // Échec de l'insertion
        }
    }     

    //requete update
    public function updateUser($userData,$id) {
        $this->connectToDatabase();

        $username = $userData->username;
        $email = $userData->email;
        $password = $userData->password;

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

    public function getByUsername($username): ?array{
        $this->connectToDatabase();
        $query = "SELECT * FROM users WHERE username = :username LIMIT 1";
        $statement = $this->pdo->prepare($query);
        $statement->execute(['username' => $username]);
        $user = $statement->fetch(PDO::FETCH_ASSOC);
        return $user !== false ? $user : null;
    }

// Connection
    private function connectToDatabase(): void {
         if (!$this->pdo) {
             $this->pdo = connect_db();
        }
    }

    public function authenticate($username, $password):bool {
       
        $user = $this->getByUsername($username);

        if ($user && password_verify($password, $user['password'])) {
            return true;
        }

        return false;
        }
    }
