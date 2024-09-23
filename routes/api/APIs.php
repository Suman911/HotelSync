<?php

final class API
{
    public static function options($name, $usertype): void
    {
        try {
            if ($usertype !== 'admin')
                throw new Exception('Access Denied, Only Admins Can Access Options APIs');
            switch ($name) {
                case 'optn_APIs':
                    self::optionAPI();
                    break;
                default:
                    throw new Exception('API Request Not Found');
            }
        } catch (Exception $e) {
            self::badRequest('options', $name, $e->getMessage());
        }
    }
    private static function optionAPI(): void
    {
        $responce = [
            'GET' => [
                'get1' => 'get1 method description',
                'get2' => 'get2 method description'
            ],
            'POST' => [
                'post1' => 'post1 method description',
                'post2' => 'post2 method description',
                'post3' => 'post3 method description',
                'post4' => 'post4 method description'
            ],
            'PUT' => [
                'put1' => 'put1 method description',
                'put2' => 'put2 method description',
                'put3' => 'put3 method description'
            ],
            'PATCH' => [
                'patch1' => 'patch1 method description',
                'patch2' => 'patch2 method description',
                'patch3' => 'patch3 method description',
                'patch4' => 'patch4 method description',
                'patch5' => 'patch5 method description',
                'patch6' => 'patch6 method description'
            ],
            'DELETE' => [
                'delete1' => 'delete1 method description',
                'delete2' => 'delete2 method description',
                'delete3' => 'delete3 method description'
            ],
            'HEAD' => [
                'head1' => 'head1 method description'
            ]
        ];
        echo json_encode($responce);
    }
    public static function voidAPI($reqMethod, $usertype, $body): void
    {
        try {
            if ($usertype !== 'admin')
                throw new Exception('Access Denied, Only Admins Can Access Options APIs');
            self::badRequest($reqMethod, $body, 'API is Under Development');

        } catch (Exception $e) {
            self::badRequest('options', $body, $e->getMessage());
        }
    }
    private static function badRequest($reqMethod, $reqName, $errorMassage): void
    {
        $responce = [
            'Request Method' => $reqMethod,
            'Request' => $reqName,
            'Error' => $errorMassage
        ];
        echo json_encode($responce);
    }

}
