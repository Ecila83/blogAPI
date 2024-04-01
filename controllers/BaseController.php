<?php
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class BaseController {
    protected function handleResult($result, $successMessage, $failureMessage, $incompleteMessage = "Données incomplètes.") {
        if ($result) {
            $responseData = ['message' => $successMessage, 'id' => intval($result)];
            return new JsonResponse($responseData, Response::HTTP_CREATED);
        } elseif ($result === false) {
            return new JsonResponse(['message' => $failureMessage], Response::HTTP_INTERNAL_SERVER_ERROR);
        } else {
            return new JsonResponse(['message' => $incompleteMessage], Response::HTTP_BAD_REQUEST);
        }
    }

    protected function getTokenContent(Request $request) {
        $matches = array();
        $rawAuthorization = $request->headers->get('Authorization');

        if($rawAuthorization) {
            preg_match('/[Bb]earer (.*)/', $rawAuthorization, $matches);
        }
    
        if ($matches) {
            $encodedToken = $matches[1];
            try {
                $key = $_ENV['JWT_SECRET'];
                $token = JWT::decode($encodedToken, new Key($key, 'HS256'));
    
                $now = time();
                if ($now > $token->valid_until) {
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
            return ['level' => 'anonymous', 'user_id' => -1];
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
