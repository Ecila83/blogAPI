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
        $posts = $this->postModel->getAllPosts();
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
        $result = $this->postModel->createPost($postData);

        if ($result) {
            $this->respJson(array("message" => "Publication créée avec succès.", "id" => intval($result)),201);
        } elseif ($result === false) {
            $this->respCode(500,"Échec de la création de la publication.");
        } else {
            $this->respCode(400,"Données incomplètes.");
        }
    }

//update
    public function updatePost($postData, $id) {

    }
//suprimer
    public function deletePost($id) {

    }
}