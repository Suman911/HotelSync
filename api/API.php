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
            self::badRequest($reqMethod, $reqUri, $body, 'API is Under Development');
        } catch (Exception $e) {
            self::badRequest($reqMethod, $reqUri, $body, $e->getMessage());
        }
    }
    protected static function badRequest($reqMethod, $reqUri, $req, $errorMassage, $statusCode = 400): void
    {
        http_response_code($statusCode);
        $responce = [
            'Request Method' => $reqMethod,
            'Request URL' => $reqUri,
            'Request' => $req,
            'Error' => $errorMassage
        ];
        echo json_encode($responce);
        exit;
    }
    protected static function success($responce, $statusCode = 200): void
    {
        http_response_code($statusCode);
        echo json_encode($responce);
        exit;
    }
}
