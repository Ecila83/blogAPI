<?php
declare(strict_types=1);

require_once 'config.php';
class Posts{
    private $pdo;
    private int $id;
    private string $title;
    private string $body;
    private string $author;
    private string $created_at;
    private string $updated_at;

    public function __construct(int $id, string $title, string $body, string $author,string $created_at, string $updated_at){
        
        $this->id = $id;
        $this->title = $title;
        $this->body = $body;
        $this->author = $author;
        $this->created_at = $created_at;
        $this->updated_at = $updated_at;

    }
    
// Requete recuperation db (read)
    public function getAllPosts(): array {
        $this->connectToDatabase();
        $query = "SELECT * FROM posts";
        $statement = $this->pdo->query($query);
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPostById(int $id): ?array {
        $this->connectToDatabase();
        $query = "SELECT * FROM posts WHERE id = :id";
        $statement = $this->pdo->prepare($query);
        $statement->execute(['id' => $id]);
        $post = $statement->fetch(PDO::FETCH_ASSOC);
        return $post ? $post : null;
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

