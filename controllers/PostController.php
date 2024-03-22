<?php
require_once(__DIR__ . '/BaseController.php');
require_once(__DIR__ . '/../models/Posts.php');


class PostsController extends BaseController {
    private $postModel;

    public function __construct() {
        $this->postModel = new Posts();
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
        $title = filter_var($postData['title'], FILTER_SANITIZE_STRING);
        $body = filter_var($postData['body'], FILTER_SANITIZE_STRING);
        $author = filter_var($postData['author'], FILTER_SANITIZE_STRING);
        if ($title && $body && $author) {
            $result = $this->postModel->createPost($postData);

            if ($result) {
                $this->respJson(array("message" => "Publication créée avec succès.", "id" => intval($result)),201);
            } elseif ($result === false) {
                $this->respCode(500,"Échec de la création de la publication.");
            } else {
                $this->respCode(400,"Données incomplètes.");
            }
        }
    }

//update
    public function updatePost($postData, $id) {
        $title = filter_var($postData['title'], FILTER_SANITIZE_STRING);
        $body = filter_var($postData['body'], FILTER_SANITIZE_STRING);
        $author = filter_var($postData['author'], FILTER_SANITIZE_STRING);

        if ($title && $body && $author) {
            $result = $this->postModel->updatePost($id, $postData);

            if ($result) {
                $this->respJson(array("message" => "Mise à jour réussie.", "id" => intval($result)),201);
            } elseif ($result === false) {
                $this->respCode(500,"Échec de la mise à jour.");
            } else {
                $this->respCode(400,"Données incomplètes.");
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
