<?php
require_once './API.php';

final class Options extends API
{
    public static function _($reqUri, $auth, $body): void
    {
        try {
            if ($auth['usertype'] !== 'admin')
                throw new Exception('Access Denied, Only Admins Can Access Options APIs');
            switch ($reqUri) {
                case '/APIs':
                    self::APIs();
                    break;
                case '/statuscodes':
                    self::StatusCodes();
                    break;
                default:
                    throw new Exception('API Request Not Found');
            }
        } catch (Exception $e) {
            self::badRequest('options', 'reqUri', $auth, $e->getMessage());
        }
    }
    private static function APIs(): void
    {
        $responce = [
            'GET' => [
                'get1' => 'get1 API description',
                'get2' => 'get2 API description',
            ],
            'POST' => [
                'post1' => 'post1 API description',
                'post2' => 'post2 API description',
                'post3' => 'post3 API description',
                'post4' => 'post4 API description',
            ],
            'PUT' => [
                'put1' => 'put1 API description',
                'put2' => 'put2 API description',
                'put3' => 'put3 API description',
            ],
            'PATCH' => [
                'patch1' => 'patch1 API description',
                'patch2' => 'patch2 API description',
                'patch3' => 'patch3 API description',
                'patch4' => 'patch4 API description',
                'patch5' => 'patch5 API description',
                'patch6' => 'patch6 API description',
            ],
            'DELETE' => [
                'delete1' => 'delete1 API description',
                'delete2' => 'delete2 API description',
                'delete3' => 'delete3 API description',
            ],
            'HEAD' => [
                'head1' => 'head1 API description',
            ],
            'OPTIONS' => [
                'APIs' => 'Returns all the available APIS',
                'statuscodes' => 'Returns a list of HTTP status codes'
            ]
        ];
        self::success($responce);
    }
    private static function StatusCodes(): void
    {
        $responce = [
            'Informational' => [
                100 => 'Continue',
                101 => 'Switching Protocols',
                102 => 'Processing',
            ],
            'Success' => [
                200 => 'OK',
                201 => 'Created',
                202 => 'Accepted',
                203 => 'Non-Authoritative Information',
                204 => 'No Content',
                205 => 'Reset Content',
                206 => 'Partial Content',
                207 => 'Multi-Status',
                208 => 'Already Reported',
                226 => 'IM Used',
            ],
            'Redirection' => [
                300 => 'Multiple Choices',
                301 => 'Moved Permanently',
                302 => 'Found',
                303 => 'See Other',
                304 => 'Not Modified',
                307 => 'Temporary Redirect',
                308 => 'Permanent Redirect',
            ],
            'Client Error' => [
                400 => 'Bad Request',
                401 => 'Unauthorized',
                402 => 'Payment Required',
                403 => 'Forbidden',
                404 => 'Not Found',
                405 => 'API Not Allowed',
                406 => 'Not Acceptable',
                407 => 'Proxy Authentication Required',
                408 => 'Request Timeout',
                409 => 'Conflict',
                410 => 'Gone',
                411 => 'Length Required',
                412 => 'Precondition Failed',
                413 => 'auth Too Large',
                414 => 'URI Too Long',
                415 => 'Unsupported Media Type',
                416 => 'Range Not Satisfiable',
                417 => 'Expectation Failed',
                418 => 'I am a Teapot',
                421 => 'Misdirected Request',
                422 => 'Unprocessable Entity',
                423 => 'Locked',
                424 => 'Failed Dependency',
                425 => 'Too Early',
                426 => 'Upgrade Required',
                428 => 'Precondition Required',
                429 => 'Too Many Requests',
                431 => 'Request Header Fields Too Large',
                451 => 'Unavailable For Legal Reasons',
            ],
            'Server Error' => [
                500 => 'Internal Server Error',
                501 => 'Not Implemented',
                502 => 'Bad Gateway',
                503 => 'Service Unavailable',
                504 => 'Gateway Timeout',
                505 => 'HTTP Version Not Supported',
                506 => 'Variant Also Negotiates',
                507 => 'Insufficient Storage',
                508 => 'Loop Detected',
                510 => 'Not Extended',
                511 => 'Network Authentication Required',
            ],
        ];
        self::success($responce);
    }
}
