<?php
require_once './API.php';

final class Get extends API
{
    public static function _($reqUri, $auth, $body): void
    {
        try {
            switch ($reqUri) {
                case '/dashboardcounts':
                    self::dashBoardCounts($auth, $body);
                    break;
                // case '/statuscodes':
                //     self::StatusCodes($auth, $body);
                //     break;
                default:
                    throw new Exception('API Request Not Found');
            }
        } catch (Exception $e) {
            self::badRequest('get', $reqUri, $body, $e->getMessage());
        }
    }
    private static function dashBoardCounts($auth, $body): void
    {
        if ($auth['usertype'] !== 'admin')
            throw new Exception('Access Denied, Only Admins Can Access Options APIs');

        $responce = [];
        self::success($responce);
    }
}
