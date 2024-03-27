<?php
namespace BlogApi\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
class BaseController {
    protected function respJson($data, $code = 200){
        return $this->json($data, $code, [], ['json_encode_options' => JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE]);
    }

    protected function respCode($code, $message) {
        return $this->json(['message' => $message], $code,[],['json_encode_options' => JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE]);
    }

    protected function respStandard($data) {
        return $this->json(['status' => 200, 'message' => 'OK', 'data' => $data]);
    }

    protected function handleResult($result, $successMessage, $failureMessage, $incompleteMessage = "Données incomplètes."){
        if ($result) {
            return $this->json(['message' => $successMessage, 'id' => intval($result)], Response::HTTP_CREATED);
        } elseif ($result === false) {
            return $this->respCode(Response::HTTP_INTERNAL_SERVER_ERROR, $failureMessage);
        } else {
            return $this->respCode(Response::HTTP_BAD_REQUEST, $incompleteMessage);
        }
    }

    protected function getTokenContent(Request $request, $tokenKey = null) {
        $encodedToken = $request->headers->get('Authorization');
    
        if ($encodedToken) {
            try {
                $key = $tokenKey ?? $_ENV['JWT_SECRET'];
                $token = JWT::decode($encodedToken, new Key($key, 'HS256'));
    
                if (time() > $token->valid_until) {
                    return null;
                }
    
                return $token;
            } catch (\Exception $e) {
                return null;
            }
        } else {
            return null;
        }
    }
    
    protected function getCheckAuthorization(Request $request) {
        $tokenContent = $this->getTokenContent($request);
    
        if (!$tokenContent) {
            return "anonymous";
        }
        return $tokenContent->level;
    }

    protected function getUserIdFromToken(Request $request) {
        $tokenContent = $this->getTokenContent($request);
    
        if (!$tokenContent) {
            return null; 
        }
        return $tokenContent->id;
    }

    public function checkAuthorizationAndUserId(Request $request) {
        $level = $this->getCheckAuthorization($request);
        $userId = $this->getUserIdFromToken($request);
    
        if ($level !== 'admin' && $level !== 'user' || !$userId) {
            return $this->respCode(Response::HTTP_UNAUTHORIZED, "Non autorisé");
        }
        
        return ['level' => $level, 'user_id' => $userId];
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
