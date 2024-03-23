<?php

require_once(__DIR__ . '/BaseController.php');
require_once(__DIR__ . '/../models/Users.php');


class UsersController extends BaseController {
    private $postModel;

    public function __construct() {
        $this->usersModel = new Users();
    }

//recupereration 
    //tout
    public function getAllUsers() {
        $limit = intval($_GET['limit'] ?? '-1');
        $offset = intval($_GET['offset'] ?? '-1');
        $users = $this->usersModel->getAllPosts($limit, $offset);
        $this->respStandard($users);
    }

    //par id
    public function getUserById($id) {
        $user = $this->usersModel->getUserById($id);

        if(!$user) {
            $this->respCode(404,"Utilisateur introuvable");
        }

        $this->respStandard($user);
    }

    public function createUser($userData) {
        $username = filter_var($userData['username'], FILTER_SANITIZE_STRING);
        $email = filter_var($userData['email'], FILTER_SANITIZE_STRING);
        $password = password_hash($userData['password'],PASSWORD_DEFAULT);
        if ($username && $email && $password) {
            $result = $this->usersModel->createUser($userData);

            if ($result) {
                $this->respJson(array("message" => "Utilisateur créé avec succès.", "id" => intval($result)),201);
            } elseif ($result === false) {
                $this->respCode(500,"Échec de la création de l'utilisateur.");
            } else {
                $this->respCode(400,"Données incomplètes.");
            }
        }
    }

//update
    public function updateUser($userData, $id) {
        $username = filter_var($userData['username'], FILTER_SANITIZE_STRING);
        $email = filter_var($userData['email'], FILTER_SANITIZE_STRING);
        $password = password_hash($userData['password'],PASSWORD_DEFAULT);

        if ($username && $email && $password) {
            $result = $this->userModel->updateUser($id, $userData);

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
    public function deleteUser($id) {
        $result = $this->userModel->deleteUser($id);

        if(!$result) {
            $this->respCode(500,"Echec de la supression ");
        }

        $this->respJson(array("message" => "Supression réussie.", "id" => intval($id)), 201);
    }
}