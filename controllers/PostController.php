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

    public function createPost($postData) {
        $level = $this->getCheckAuthorization();
    
        if ($level === 'admin' || $level === 'user') {
            $userId = $this->getUserIdFromToken();

            if ($userId) {
                $user = $this->usersModel->getUserById($userId);

                if ($user) {
                    $postData->author = $user['username'];
                    $postData->user_id = $userId;

                    if (isset($postData->title, $postData->body)) {
                        $postData->title = htmlspecialchars($postData->title);
                        $postData->body = htmlspecialchars($postData->body);

                        $result = $this->postModel->createPost($postData);
    
                        if ($result) {
                            $this->respJson(array("message" => "Publication créée avec succès.", "id" => intval($result)), 201);
                        } elseif ($result === false) {
                            $this->respCode(500, "Échec de la création de la publication.");
                        } else {
                            $this->respCode(400, "Données incomplètes.");
                        }
                    } else {
                        $this->respCode(400, "Données incomplètes.");
                    }
                } else {
                    $this->respCode(404, "Utilisateur introuvable.");
                }
            } else {
                $this->respCode(401, "Non autorisé");
            }
        }
    }

//update
    public function updatePost($postData, $id) {
        $level = $this->getCheckAuthorization();
        $userIdFromToken = $this->getUserIdFromToken();

        if ($level === 'admin' || ($level === 'user' && intval($userIdFromToken) === $id)) {
            $existingPost = $this->postModel->getPostById($id);

            if (!$existingPost) {
                $this->respCode(404, "Post introuvable");
            }
            if (!isset($postData->title, $postData->body, $postData->author)) {
                $this->respCode(400, "Données incomplètes.");
            }
            
            $title = htmlspecialchars($postData->title);
            $body = htmlspecialchars($postData->body);
            $author = $existingPost['author'];

            if ($title && $body) {
                // Créer un objet avec les données mises à jour
                $updatedPostData = (object) [
                    'title' => $title,
                    'body' => $body,
                    'author' => $author // Utiliser l'auteur du post existant
                ];
                $result = $this->postModel->updatePost($id, $updatedPostData);

                if ($result) {
                    $this->respJson(array("message" => "Mise à jour réussie.", "id" => intval($result)),201);
                } elseif ($result === false) {
                    $this->respCode(500,"Échec de la mise à jour.");
                } else {
                    $this->respCode(400,"Données incomplètes.");
                }
        }else{
            $this->respCode(401,"Non autorisé");
         }   
      }
    }
//suprimer
    public function deletePost($id) {
        $result = $this->postModel->deletePost($id);

        if(!$result) {
            $this->respCode(500,"Echec de la supression ");
        }

        $this->respJson(array("message" => "Supression réussie.", "id" => intval($id)), 201);
    }
}
