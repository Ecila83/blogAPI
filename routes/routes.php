<?php

require_once(__DIR__ . '/../controllers/PostController.php');

$routes = [
    'GET' => [
        '/api/v1/posts' => fn() => (new PostsController())->getAllPosts(),
        '/api/v1/post/(.+)' => fn($id) => (new PostsController())->getPostById($id),
    ],
    'POST' => [
        '/api/v1/post' => fn($postData) => (new PostsController())->createPost($postData),
    ],
    'PUT' => [
        '/api/v1/post/(.+)' => fn($postData, $id) => (new PostsController())->updatePost($postData, $id),
    ],
    'DELETE' => [
        '/api/v1/post/(.+)' => fn($id) => (new PostsController())->deletePost($id),
    ]
];

$method = strtoupper($_SERVER['REQUEST_METHOD']);
$path = $_SERVER['PATH_INFO'];

if(isset($routes[$method])) {
    $routesToCheck = $routes[$method];

    foreach($routesToCheck as $re => $func) {
        if(preg_match('/^' . str_replace('/', '\\/', $re) . '$/', $path, $matches)) {
            switch($method) {
                case 'DELETE':
                case 'GET':
                    array_shift($matches);
                break;
                case 'PUT':
                case 'POST':
                    $payload = file_get_contents('php://input');
                    $matches[0] = json_decode($payload);
                break;
            }

            call_user_func_array($func, $matches);
            exit();
        }
    }
}

http_response_code(404);
echo json_encode(array("message" => "Route introuvable."));
