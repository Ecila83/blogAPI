<?php

require_once(__DIR__ . '/BaseController.php');
require_once(__DIR__ . '/../models/Users.php');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;



class UsersController extends BaseController {
    private Users $usersModel;

    public function __construct() {
        $this->usersModel = new Users();
    }

//recupereration 
    //tout
    public function getAllUsers() {
        $level = $this->getCheckAuthorization();
        
        if ($level === 'admin') {
            $limit = intval($_GET['limit'] ?? '-1');
            $offset = intval($_GET['offset'] ?? '-1');
    
            $users = $this->usersModel->getAllUsers($limit, $offset);
            $this->respStandard($users);
        } elseif ($level === 'user') {      
            $this->respCode(401, "non autorisé");
        }
    }
    
    public function getUserById($id) {
        $userAuthData = $this->checkAuthorizationAndUserId();
        $level = $userAuthData['level'];
        $userIdFromToken = $userAuthData['user_id'];
    
        if ($level !== 'admin' && $level !== 'user') {
            $this->respCode(401, "Non autorisé");
        }

        if ($level === 'admin' || (intval($userIdFromToken) === intval($id))) {
            $user = $this->usersModel->getUserById($id);

            if(!$user) {
                $this->respCode(404,"Utilisateur introuvable");
            }
                $this->respStandard($user);
        } else {
            $this->respCode(401, "non autorisé");
        }
    }
    
//creer
    public function createUser($userData) {
        if(! isset($userData->username, $userData->email, $userData->password) ) {
            $this->respCode(400, "Données incomplètes.");
        }

        $userData->username = htmlspecialchars($userData->username);
        $userData->email = filter_var($userData->email, FILTER_VALIDATE_EMAIL) ;
        $userData->password = password_hash($userData->password, PASSWORD_DEFAULT);

        $result = $this->usersModel->createUser($userData);

        $this->handleResult(
            $result,
            "Utilisateur créé avec succès.",
            "Échec de la création de l'utilisateur."
        );
    }

//update
    public function updateUser($userData, $id) {   
        $userAuthData = $this->checkAuthorizationAndUserId(); 
        $level = $userAuthData['level'];
        $userIdFromToken = $userAuthData['user_id'];

        if ($level === 'admin' || ($level === 'user' && $userIdFromToken === intval($id))) {
            $existingUser = $this->usersModel->getUserById($id);

            if (!$existingUser) {
                $this->respCode(404, "Utilisateur introuvable");
            }

            if (!isset($userData->username, $userData->email, $userData->password)) {
                $this->respCode(400, "Données incomplètes.");
            }

            $userData->username = htmlspecialchars($userData->username);
            $userData->email = filter_var($userData->email, FILTER_SANITIZE_EMAIL);
            $userData->password = password_hash($userData->password, PASSWORD_DEFAULT);

            $result = $this->usersModel->updateUser($userData, $id);

            $this->handleResult(
                $result,
                "Mise à jour réussie.",
                "Échec de la mise à jour.",
            );
        } else {
            $this->respCode(401, "Mise à jour non autorisée");
        }
    }

//suprimer
    public function deleteUser($id) {
        $level = $this->getCheckAuthorization();

        if ($level !== 'admin') {
            $this->respCode(401, "Suppression non autorisée. Seul l'administrateur peut supprimer des utilisateurs.");
        }

        $result = $this->usersModel->deleteUser($id);

        if(!$result) {
            $this->respCode(500,"Echec de la supression ");
        }

        $this->respJson(array("message" => "Supression réussie.", "id" => intval($id)), 201);
    }

//login
    public function loginUser($loginData) {
        if ($this->isValidLoginData($loginData)) {
            $username = $loginData->username;
            $password = $loginData->password;
            $user = $this->authenticateUser($username, $password);

            if ($user) {
                $jwt = $this->generateJWT($user);
                return $this->respJson(array("message" => "login réussi.", "token" => $jwt), 200);
            } else {
                return $this->respCode(401, "Identifiants incorrects !");
            }
        } else {
            return $this->respCode(400, "Données de connexion manquantes !");
        }
    }
    
    protected function isValidLoginData($loginData) {
        return isset($loginData->username) && isset($loginData->password);
    }

    protected function authenticateUser($username, $password) {
        return $this->usersModel->authenticate($username, $password);
    }
}

