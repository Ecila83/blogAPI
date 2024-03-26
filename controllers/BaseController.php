<?php
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
class BaseController {
    protected function respJson($data,$code = 200) {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit();
    }

    protected function respCode($code,$message){
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(array("message" => $message),JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        exit();
    }

    protected function respStandard($data) {
        $result = [
            "status" => 200,
            "message" => "OK",
            "data" => $data
        ];

        $this->respJson($result);
    }

    protected function handleResult($result, $successMessage, $failureMessage, $incompleteMessage = "Données incomplètes.") {
        if ($result) {
            $this->respJson(array("message" => $successMessage, "id" => intval($result)), 201);
        } elseif ($result === false) {
            $this->respCode(500, $failureMessage);
        } else {
            $this->respCode(400, $incompleteMessage);
        }
    }

    protected function getTokenContent($tokenKey = null) {
        $encodedToken = null;
        $headers = apache_request_headers();
    
        if (isset($headers['Authorization'])) {
            $matches = array();
            preg_match('/[Bb]earer (.*)/', $headers['Authorization'], $matches);
    
            if (isset($matches[1])) {
                $encodedToken = $matches[1];
            }
        } 
    
        if ($encodedToken) {
            try {

                $key = $tokenKey ?? $_ENV['JWT_SECRET'];
                $token = JWT::decode($encodedToken, new Key($key, 'HS256'));
    
                if (time() > $token->valid_until) {
                    $this->respCode(401, "Token expiré");
                }
    
                return $token;
            } catch (Exception $e) {
                return null; 
            }
        } else {
            return null; 
        }
    }
    
    protected function getCheckAuthorization() {
        $tokenContent = $this->getTokenContent();
    
        if (!$tokenContent) {
            return "anonymous"; 
        }
        return $tokenContent->level;
    }

    protected function getUserIdFromToken() {
        $tokenContent = $this->getTokenContent();
    
        if (!$tokenContent) {
            return null; 
        }
        return $tokenContent->id;
    }

    public function checkAuthorizationAndUserId() {
        $level = $this->getCheckAuthorization();
        $userId = $this->getUserIdFromToken();
    
        if ($level !== 'admin' && $level !== 'user' || !$userId) {
            $this->respCode(401, "Non autorisé");
        }
    
        return array('level' => $level, 'user_id' => $userId);
    }

    protected function generateJWT($user) {
        $key = $_ENV['JWT_SECRET'];
        $payload = [
            'id' => $user->id,
            'level' => $user->authorization,
            'valid_until' => time() + 3600
        ];
        return JWT::encode($payload, $key, 'HS256');
    }
}
