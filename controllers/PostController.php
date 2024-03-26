<?php
require_once(__DIR__ . '/BaseController.php');
require_once(__DIR__ . '/../models/Posts.php');
require_once(__DIR__ . '/../models/Users.php');


class PostsController extends BaseController {
    private $postModel;
    private $usersModel;

    public function __construct() {
        $this->postModel = new Posts();
        $this->usersModel = new Users();
    }

//recupereration 
    //tout
    public function getAllPosts() {
        $limit = intval($_GET['limit'] ?? '-1');
        $offset = intval($_GET['offset'] ?? '-1');
        $posts = $this->postModel->getAllPosts($limit, $offset);
        $this->respStandard($posts);
    }

    //par id
    public function getPostById($id) {
        $post = $this->postModel->getPostById($id);

        if(!$post) {
            $this->respCode(404,"Publication introuvable");
        }

        $this->respStandard($post);
    }

//creation
    public function createPost($postData) {
        if (!isset($postData->title, $postData->body)) {
            $this->respCode(400, "Données incomplètes.");
        }

        ['level' => $level, 'user_id' => $userId] = $this->checkAuthorizationAndUserId();

        $user = $this->usersModel->getUserById($userId);
        if (!$user) {
            $this->respCode(404, "Utilisateur introuvable.");
        }

        $title = htmlspecialchars($postData->title);
        $body = htmlspecialchars($postData->body);

        $postData->author = $user['username'];
        $postData->user_id = $userId;

        $result = $this->postModel->createPost($postData);

        $this->handleResult(
            $result, 
            "Publication créée avec succès.", 
            "Échec de la création de la publication."
        );
    }

//update
public function updatePost($postData, $id) {
    $userData = $this->checkAuthorizationAndUserId();
    $level = $userData['level'];
    $userIdFromToken = $userData['user_id'];

    $existingPost = $this->postModel->getPostById($id);
    if (!$existingPost) {
        $this->respCode(404, "Post introuvable");
    }

    if ($level !== 'admin' && intval($userIdFromToken) !==  $existingPost['user_id']) {
        $this->respCode(401, "Non autorisé");
    }

    if (!isset($postData->title, $postData->body, $postData->author)) {
        $this->respCode(400, "Données incomplètes.");
    }

    $title = htmlspecialchars($postData->title);
    $body = htmlspecialchars($postData->body);
    $author = $existingPost['author'];

    if ($title && $body) {
        $updatedPostData = (object) [
            'title' => $title,
            'body' => $body,
            'author' => $author
        ];

        $result = $this->postModel->updatePost($id, $updatedPostData);

        $this->handleResult(
            $result,
            "Mise à jour réussie.",
            "Échec de la mise à jour.",
            "Titre et corps du post ne peuvent pas être vides."
        );
    }
}

//suprimer
    public function deletePost($id) {
        $level = $this->getCheckAuthorization();
        $userIdFromToken = $this->getUserIdFromToken();

        $post = $this->postModel->getPostById($id);
        $author = $post['user_id'] ?? null;

        if ($level === 'admin' || ($level === 'user' && $author === $userIdFromToken)) {
            $result = $this->postModel->deletePost($id);
    
            if(!$result) {
                $this->respCode(500,"Échec de la suppression");
            }
    
            $this->respJson(array("message" => "Suppression réussie.", "id" => intval($id)), 201);
        } else {
            $this->respCode(401, "Suppression non autorisée.");
        }
    }
}
