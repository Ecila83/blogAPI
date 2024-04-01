<?php

require_once __DIR__ . '/../controllers/UsersController.php';

use Symfony\Component\HttpFoundation\Request;

$data = (object) [
    'username' => $argv[1],
    'email' => $argv[2],
    'password' => $argv[3]
];

(new UsersController())->createUser(new Request(), $data);
