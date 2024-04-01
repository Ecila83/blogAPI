<?php
require_once(__DIR__ . '/BaseController.php');
require_once(__DIR__ . '/../models/Posts.php');
require_once(__DIR__ . '/../models/Users.php');

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

class PostsController extends BaseController {
    private $postModel;
    private $usersModel;

    public function __construct() {
        $this->postModel = new Posts();
        $this->usersModel = new Users();
    }

//recupereration 
    //tout
    public function getAllPosts(Request $request) {
        $limit = intval($request->query->get('limit', -1));
        $offset = intval($request->query->get('offset', -1));
        
        $posts = $this->postModel->getAllPosts($limit, $offset);
        return new JsonResponse(['status' => 'success', 'posts' => $posts],Response::HTTP_OK);
    }

    //par id
    public function getPostById(Request $request,$id) {
        $post = $this->postModel->getPostById($id);

        if(!$post) {
            return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse(['status' => 'success', 'posts' => $post],Response::HTTP_OK);
    }

//creation
    public function createPost(Request $request, $postData) {
        if (!isset($postData->title, $postData->body)) {
            return new JsonResponse(['error' => 'Incomplete data'], Response::HTTP_BAD_REQUEST);
        }

        ['level' => $level, 'user_id' => $userId] = $this->checkAuthorizationAndUserId($request);

        $user = $this->usersModel->getUserById($userId);
        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur introuvable.'], Response::HTTP_NOT_FOUND);
        }

        $title = htmlspecialchars($postData->title);
        $body = htmlspecialchars($postData->body);

        $postData->title = $title;
        $postData->body = $body;
        $postData->author = $user['username'];
        $postData->user_id = $userId;

        $result = $this->postModel->createPost($postData);

        return $this->handleResult(
            $result, 
            "Publication créée avec succès.", 
            "Échec de la création de la publication."
        );
    }

//update
public function updatePost(Request $request,$postData, $id) {

    $userData = $this->checkAuthorizationAndUserId($request);
    $level = $userData['level'];
    $userIdFromToken = $userData['user_id'];

    $existingPost = $this->postModel->getPostById($id);
    if (!$existingPost) {
        return new JsonResponse(['error' => 'Post introuvable'], Response::HTTP_NOT_FOUND);
    }

    if ($level !== 'admin' && intval($userIdFromToken) !==  $existingPost['user_id']) {
        return new JsonResponse(['error' => 'Non autorisé'], Response::HTTP_UNAUTHORIZED);
    }

    if (!isset($postData->title, $postData->body, $postData->author)) {
        return new JsonResponse(['error' => 'Données incomplètes'], Response::HTTP_BAD_REQUEST);
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

        return $this->handleResult(
            $result,
            "Mise à jour réussie.",
            "Échec de la mise à jour.",
            "Titre et corps du post ne peuvent pas être vides."
        );
    }
}

//suprimer
    public function deletePost(Request $request,$id) {
        $level = $this->getCheckAuthorization($request);
        $userIdFromToken = $this->getUserIdFromToken($request);

        $post = $this->postModel->getPostById($id);
        $author = $post['user_id'] ?? null;

        if ($level === 'admin' || ($level === 'user' && $author === $userIdFromToken)) {
            $result = $this->postModel->deletePost($id);
    
            if(!$result) {
                return new JsonResponse(['error' => 'Échec de la suppression'], Response::HTTP_INTERNAL_SERVER_ERROR);
            }
    
            return new JsonResponse(['message' => 'Suppression réussie.', 'id' => intval($id)], Response::HTTP_OK);
        } else {
            return new JsonResponse(['error' => 'Suppression non autorisée'], Response::HTTP_UNAUTHORIZED);
        }
    }
}
