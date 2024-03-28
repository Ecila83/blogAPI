<?php
require_once(__DIR__ . '/BaseController.php');
require_once(__DIR__ . '/../models/Users.php');

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;



class UsersController extends BaseController {
    private Users $usersModel;

    public function __construct() {
        $this->usersModel = new Users();
    }

//recupereration 
    //tout
    public function getAllUsers(Request $request) {
        $level = $this->getCheckAuthorization($request);

        if ($level !== 'admin') {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }
        
        $limit = intval($request->query->get('limit', -1));
        $offset = intval($request->query->get('offset', -1));

        $users = $this->usersModel->getAllUsers($limit, $offset);
        return new JsonResponse(['status' => 'success', 'data' => $users], Response::HTTP_OK);
    }
    
    public function getUserById(Request $request, $id) {
        $userAuthData = $this->checkAuthorizationAndUserId($request);
        
        $level = $userAuthData['level'];
        $userIdFromToken = $userAuthData['user_id'];
    
        if ($level !== 'admin' && $level !== 'user') {
            return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
        }

        if ($level === 'admin' || (intval($userIdFromToken) === intval($id))) {
            $user = $this->usersModel->getUserById($id);

            if(!$user) {
                return new JsonResponse(['error' => 'User not found'], Response::HTTP_NOT_FOUND);
            }

            return new JsonResponse(['status' => 'success', 'data' => $user], Response::HTTP_OK);
        } 
        
        return new JsonResponse(['error' => 'Unauthorized'], Response::HTTP_UNAUTHORIZED);
    }
 
    
//creer
    public function createUser(Request $request, $userData) {
        if(! isset($userData->username, $userData->email, $userData->password) ) {
            return new JsonResponse(['error' => 'Incomplete data'], Response::HTTP_BAD_REQUEST);
        }

        $userData->username = htmlspecialchars($userData->username);
        $userData->email = filter_var($userData->email, FILTER_VALIDATE_EMAIL) ;
        $userData->password = password_hash($userData->password, PASSWORD_DEFAULT);

        $result = $this->usersModel->createUser($userData);

        return $this->handleResult(
            $result,
            "Utilisateur créé avec succès.",
            "Échec de la création de l'utilisateur.",
            "Données incomplètes."
        );
    }

//update
    public function updateUser(Request $request, $data, $id) {   
        $userAuthData = $this->checkAuthorizationAndUserId($request); 
        $level = $userAuthData['level'];
        $userIdFromToken = $userAuthData['user_id'];

        if ($level === 'admin' || ($level === 'user' && $userIdFromToken === intval($id))) {
            $existingUser = $this->usersModel->getUserById($id);

            if (!$existingUser) {
                return new JsonResponse(['error' => 'Utilisateur introuvable'], Response::HTTP_NOT_FOUND);
            }

            $userData = json_decode($request->getContent());

            if (!isset($userData->username, $userData->email, $userData->password)) {
                return new JsonResponse(['error' => 'Données incomplètes'], Response::HTTP_BAD_REQUEST);
            }

            $userData->username = htmlspecialchars($userData->username);
            $userData->email = filter_var($userData->email, FILTER_SANITIZE_EMAIL);
            $userData->password = password_hash($userData->password, PASSWORD_DEFAULT);

            $result = $this->usersModel->updateUser($userData, $id);

            return $this->handleResult(
                $result,
                "Mise à jour réussie.",
                "Échec de la mise à jour.",
                "Données incomplètes."
            );
        } else {
            return new JsonResponse(['error' => 'Mise à jour non autorisée'], Response::HTTP_UNAUTHORIZED);
        }
    }

//suprimer
    public function deleteUser(Request $request, $id) {
        $level = $this->getCheckAuthorization($request);

        if ($level !== 'admin') {
            return new JsonResponse(['error' => "Suppression non autorisée. Seul l'administrateur peut supprimer des utilisateurs."], Response::HTTP_UNAUTHORIZED);
        }

        $result = $this->usersModel->deleteUser($id);

        if(!$result) {
            return new JsonResponse(['error' => "Échec de la suppression."], Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(['message' => "Suppression réussie.", 'id' => intval($id)], Response::HTTP_CREATED);
    }

//login
    public function loginUser($request, $loginData) {
        if ($this->isValidLoginData($loginData)) {
            $username = $loginData->username;
            $password = $loginData->password;
            $user = $this->authenticateUser($username, $password);

            if ($user) {
                $jwt = $this->generateJWT($user);
                $responseData = ['message' => 'Authentification réussie.', 'token' => $jwt];
                $jsonResponse = new JsonResponse($responseData, JsonResponse::HTTP_OK);
                $jsonResponse->setEncodingOptions(JSON_UNESCAPED_UNICODE);
                return $jsonResponse;
            } else {
                return new JsonResponse(['error' => 'Identifiants incorrects !'], JsonResponse::HTTP_UNAUTHORIZED);
            }
        } else {
            return new JsonResponse(['error' => 'Données de connexion manquantes !'], JsonResponse::HTTP_BAD_REQUEST);
        }
    }
    
    protected function isValidLoginData($loginData) {
        return isset($loginData->username) && isset($loginData->password);
    }

    protected function authenticateUser($username, $password) {
        return $this->usersModel->authenticate($username, $password);
    }
}

