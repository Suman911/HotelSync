<?php
require_once './API.php';
require_once '../auth/conn.php';
require_once '../auth/JWT.php';
final class Post extends API
{
    public static function _($reqUri, $auth, $body): void
    {
        try {
            switch ($reqUri) {
                case '/login':
                    self::login($auth, $body);
                    break;
                case '/adminlogin':
                    self::adminlogin($auth, $body);
                    break;
                case '/addemployee':
                    self::addemployees($auth, $body);
                    break;
                default:
                    throw new Exception('API Request Not Found');
            }
        } catch (Exception $e) {
            self::badRequest('Post', $reqUri, $auth, $e->getMessage());
        }
    }
    private static function login($auth, $body): void
    {
        $pdo = Conn::setConnection();

        $email = $body['email'] ?? false;
        $password = $body['password'] ?? false;

        if ($email && $password) {
            $stmt = $pdo->prepare("SELECT * FROM user WHERE username = :email");
            $stmt->execute(['email' => $email]);
            $result = $stmt->fetchall(PDO::FETCH_ASSOC);

            if ($result && count($result) === 1) {
                $selectedKeys = ['id', 'usertype', 'name', 'email'];
                $auth = [];
                foreach ($selectedKeys as $key) {
                    $auth[$key] = $result[0][$key];
                }
                $jwt = JWT::encode($auth, 3600);
                setcookie('jwt_token', $jwt, time() + 3600, '/', '', false, true);
            } else {
                throw new Exception("Wrong Email Or Password");
            }
        } else {
            throw new Exception("Error Processing Request");
        }

        $responce = $auth;
        self::success($responce);
    }
    private static function adminlogin($auth, $body): void
    {
        $responce = $body;
        self::success($responce);
    }
    private static function addemployees($auth, $body): void
    {
        if ($auth['usertype'] !== 'admin')
            throw new Exception('Access Denied, Only Admins Can Add Employee');
        $responce = $body;
        self::success($responce);
    }
}

