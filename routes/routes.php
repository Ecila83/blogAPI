<?php
require_once 'controllers/PostsControllers.php';

$routes = [
    '/posts' => fn() => (new PostsControllers())->getAllPosts(),
    '/post/:id' => function($id) { (new PostsControllers())->getPostId($id); },
];

$path = $_SERVER['PATH_INFO'];
$route = $routes[$path] ?? function() { echo '404 Not Found'; };
$route();