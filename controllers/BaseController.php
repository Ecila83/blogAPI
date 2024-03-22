<?php

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
}
