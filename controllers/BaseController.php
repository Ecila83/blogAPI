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

    protected function getCheckAuthorization() {
        $key = $_ENV['JWT_SECRET'];

        $encodedToken = null;
        $headers = apache_request_headers();

        if(isset($headers['Authorization'])){
            $matches = array();
            preg_match('/[Bb]earer (.*)/', $headers['Authorization'], $matches);

            if(isset($matches[1])){
                $encodedToken = $matches[1];
            }
        } 

        if($encodedToken) {
            try {
                $token = JWT::decode($encodedToken, new Key($key, 'HS256'));

                if(time() > $token->valid_until){
                    return "expiré";
                }

                return $token->level;
            } catch (Exception $e) {
                return "anonymous";
            }
        }else{
            return "anonymous";
        }
    }
}
