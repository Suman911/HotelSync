<?php

class API
{
    public static function _($reqtUri, $auth, $body): void
    {
    }
    public static function voidAPI($reqMethod, $reqUri, $auth, $body): void
    {
        try {
            if ($auth['usertype'] !== 'admin')
                throw new Exception('Access Denied, Only Admins Can Access Options APIs');
            self::error($reqMethod, $reqUri, $body, 'API is Under Development');
        } catch (Exception $e) {
            self::error($reqMethod, $reqUri, $body, $e->getMessage());
        }
    }
    protected static function success($responce, $statusCode = 200): void
    {
        http_response_code($statusCode);
        $responce = [
            'status' => 'success',
            'result' => $responce
        ];
        echo json_encode($responce);
        exit;
    }
    protected static function unsuccess($responce, $statusCode = 404): void
    {
        http_response_code($statusCode);
        $responce = [
            'status' => 'unsuccess',
            'error' => $responce
        ];
        echo json_encode($responce);
        exit;
    }
    protected static function error($reqMethod, $reqUri, $reqBody, $errorMassage, $statusCode = 400): void
    {
        http_response_code($statusCode);
        $responce = [
            'status' => 'error',
            'request' => [
                'Request Method' => $reqMethod,
                'Request URL' => $reqUri,
                'Request' => $reqBody,
                'Error' => $errorMassage
            ]
        ];
        echo json_encode($responce);
        exit;
    }
}
