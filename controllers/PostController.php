<?php
require_once 'models/Posts.php';

class PostsControllers{
    private $postModel;

    public function __construct(){
        $this->postModel = new Posts();
    }
//recupereration 
    //tout
    public function getAllPosts() {
        $posts = $this->postModel->getAllPosts();

        //retourne au format json 
        header('Content-Type: application/json');
        echo json_encode($posts);
    }

    //par id
    public function getPostId($id) {
        $post = $this->postModel->getPostId($id);

        if(!$post) {
            http_response_code(404);
            echo json_encode(array("message" => "Publication introuvable."));
            return;
        }

        header('Content-Type: application/json');
        echo json_encode($post);
    }


}