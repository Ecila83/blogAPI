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
        $level = $this->getCheckAuthorization();
        
        if ($level === 'admin') {
            $user = $this->usersModel->getUserById($id);
    
            if(!$user) {
                $this->respCode(404,"Utilisateur introuvable");
            }
    
            $this->respStandard($user);
        } elseif ($level === 'user') {
            $userId = $this->getUserIdFromToken();
            $id = intval($id);
    
            if ($userId === $id) {
                $user = $this->usersModel->getUserById($id);
    
                if(!$user) {
                    $this->respCode(404,"Utilisateur introuvable");
                }
    
                $this->respStandard($user);
            } else {
                $this->respCode(401, "non autorisé");
            }
        } else {
            $this->respCode(401, "non autorisé");
        }
    }
    

    public function createUser($userData) {
        if(! isset($userData->username, $userData->email, $userData->password) ) {
            $this->respCode(400, "Données incomplètes.");
        }

        $userData->username = htmlspecialchars($userData->username);
        $userData->email = filter_var($userData->email, FILTER_VALIDATE_EMAIL) ;
        $userData->password = password_hash($userData->password, PASSWORD_DEFAULT);

        $result = $this->usersModel->createUser($userData);

        if ($result) {
            $this->respJson(array("message" => "Utilisateur créé avec succès.", "id" => intval($result)),201);
        } else {
            $this->respCode(500,"Échec de la création de l'utilisateur.");
        }
    }

//update
public function updateUser($userData, $id) {      
    $level = $this->getCheckAuthorization();

    // Récupérer l'ID de l'utilisateur à partir du jeton JWT
    $userIdFromToken = $this->getUserIdFromToken();

    // Vérifier si l'utilisateur est autorisé à effectuer la mise à jour
    if ($level === 'admin' || ($level === 'user' && intval($userIdFromToken) === intval($id))) {
        // Récupérer l'utilisateur existant
        $existingUser = $this->usersModel->getUserById($id);

        // Vérifier si l'utilisateur existe
        if (!$existingUser) {
            $this->respCode(404, "Utilisateur introuvable");
        }

        // Vérifier si les données de l'utilisateur sont complètes
        if (!isset($userData->username, $userData->email, $userData->password)) {
            $this->respCode(400, "Données incomplètes.");
        }

        // Nettoyer et mettre à jour les données de l'utilisateur
        $userData->username = htmlspecialchars($userData->username);
        $userData->email = filter_var($userData->email, FILTER_SANITIZE_EMAIL);
        $userData->password = password_hash($userData->password, PASSWORD_DEFAULT);

        // Mettre à jour l'utilisateur
        $result = $this->usersModel->updateUser($userData, $id);

        // Vérifier si la mise à jour a réussi
        if ($result) {
            $this->respJson(array("message" => "Mise à jour réussie.", "id" => intval($result)), 201);
        } else {
            $this->respCode(500, "Échec de la mise à jour.");
        }
    } else {
        $this->respCode(401, "Mise à jour non autorisée");
    }
}



//suprimer
    public function deleteUser($id) {
        $level = $this->getCheckAuthorization();

        // Vérifier si l'utilisateur est autorisé à supprimer l'utilisateur
        if ($level !== 'admin') {
            $this->respCode(401, "Suppression non autorisée. Seul l'administrateur peut supprimer des utilisateurs.");
        }

        $result = $this->usersModel->deleteUser($id);

        if(!$result) {
            $this->respCode(500,"Echec de la supression ");
        }

        $this->respJson(array("message" => "Supression réussie.", "id" => intval($id)), 201);
    }

    public function loginUser($loginData){
        if (isset($loginData->username) && isset($loginData->password)) {
            
            $username = $loginData->username;
            $password = $loginData->password;
            $user = $this->usersModel->authenticate($username, $password);

            if ($user) {
                $key = $_ENV['JWT_SECRET'];
                $payload = [
                    'id' => $user->id,
                    'level' => $user->authorization,
                    'valid_until' => time() + 3600
                ];
                
                $jwt = JWT::encode($payload, $key, 'HS256');
                
                return $this->respJson(array("message" => "login réussi.", "token" => $jwt), 200);
            } else {
                return $this->respCode(401, "Identifiants incorrects !");
            }
        } else {
            return $this->respCode(400, "Données de connexion manquantes !");
        }
    }

}

