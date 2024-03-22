<?php
declare(strict_types=1);

require_once(__DIR__ . '/../config/config.php');
class Posts {
    private $pdo;
    private int $id;
    private string $title;
    private string $body;
    private string $author;
    private string $created_at;
    private string $updated_at;

    public function getAllPosts($limit, $offset): array {
        $this->connectToDatabase();
        $query = "SELECT * FROM posts";  
        
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
    

    public function getPostById(int $id): array|bool {
        $this->connectToDatabase();
        $query = "SELECT * FROM posts WHERE id = :id";
        $statement = $this->pdo->prepare($query);
        $statement->execute(['id' => $id]);
        $post = $statement->fetch(PDO::FETCH_ASSOC);
        return $post;
    }

    //requete de creation 
    public function createPost($postData) {
        
        if (isset($postData->title, $postData->body, $postData->author)) {
            $this->connectToDatabase();

            $title = $postData->title;
            $body = $postData->body;
            $author = $postData->author;

            $query = "INSERT INTO posts (title, body, author, created_at, updated_at) VALUES (:title, :body, :author, NOW(), NOW())";
            $statement = $this->pdo->prepare($query);

            if ($statement->execute(['title' => $title, 'body' => $body, 'author' => $author])) {
                return $this->pdo->lastInsertId(); // Insertion réussie : retourne l'id.
            } else {
                return false; // Échec de l'insertion
            }
        } else {
            return null; // Données incomplètes
        }
    }     

    //requete update
    public function updatePost($id, $postData) {
        $this->connectToDatabase();
        $title = $postData->title;
        $body = $postData->body;
        $author = $postData->author;

        $query = "UPDATE posts SET title = :title, body = :body, author = :author, updated_at = NOW() WHERE id = :id";
        $statement = $this->pdo->prepare($query);
    
        if ($statement->execute(['id' => $id, 'title' => $title, 'body' => $body, 'author' => $author])) {
            return $id; // Mise à jour réussie : retourne l'id.
        } else {
            return false; // Échec de la mise à jour
        }
    }

    //requete delete 
    public function deletePost($id): array|bool {
        $this->connectToDatabase();
        $query = "DELETE FROM posts WHERE id = :id";
        $statement = $this->pdo->prepare($query);
        $statement->execute(['id' => $id]);
        return true;
    }

    // Getters
    public function getId(): int {
        return $this->id;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getBody(): string {
        return $this->body;
    }

    public function getAuthor(): string {
        return $this->author;
    }

    public function getCreatedAt(): string {
        return $this->created_at;
    }

    public function getUpdatedAt(): string {
        return $this->updated_at;
    }

// Setters
    public function setTitle(string $title): void {
        $this->title = $title;
    }

    public function setBody(string $body): void {
        $this->body = $body;
    }

    public function setAuthor(string $author): void {
        $this->author = $author;
    }

    public function setCreatedAt(string $created_at): void {
        $this->created_at = $created_at;
    }

    public function setUpdatedAt(string $updated_at): void {
        $this->updated_at = $updated_at;
    }

// Connection
    private function connectToDatabase(): void {
         if (!$this->pdo) {
             $this->pdo = connect_db();
    }
}
}

