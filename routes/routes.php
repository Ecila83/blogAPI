<?php 

require_once(__DIR__ . '/../controllers/PostController.php');
require_once(__DIR__ . '/../controllers/usersController.php');

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;


$routes = [
    'GET' => [
        '/api/v1/posts' => fn($request) => (new PostsController())->getAllPosts($request),
        '/api/v1/post/(.+)' => fn($request, $id) => (new PostsController())->getPostById($request, $id),

        '/api/v1/users' => fn($request) => (new UsersController())->getAllUsers($request),
        '/api/v1/user/(.+)' => fn($request, $id) => (new UsersController())->getUserById($request, $id),
    ],
    'POST' => [
        '/api/v1/post' => fn($request, $data) => (new PostsController())->createPost($request, $data),

        '/api/v1/user' => fn($request, $data) => (new UsersController())->createUser($request, $data),
        '/api/v1/user/login' => fn($request, $data) => (new UsersController())->loginUser($request, $data),
    ],
    'PUT' => [
        '/api/v1/post/(.+)' => fn($request, $data, $id) => (new PostsController())->updatePost($request, $data, $id),

        '/api/v1/user/(.+)' => fn($request, $data, $id) => (new UsersController())->updateUser($request, $data, $id),
    ],
    'DELETE' => [
        '/api/v1/post/(.+)' => fn($request, $id) => (new PostsController())->deletePost($request, $id),

        '/api/v1/user/(.+)' => fn($request, $id) => (new UsersController())->deleteUser($request, $id),
    ]
];

$request = Request::createFromGlobals();

$method = $request->getMethod();
$path = $request->getPathInfo();

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
                    $matches[0] = json_decode($request->getContent());
                break;
            }

            array_unshift($matches, $request);

            $response = call_user_func_array($func, $matches);
            $response->setEncodingOptions(JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
            $response->send();
            exit();
        }
    }
}

$response = new JsonResponse(['message' => 'Route introuvable.'], Response::HTTP_NOT_FOUND);
$response->send();
