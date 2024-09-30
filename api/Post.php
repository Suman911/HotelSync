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
        $password = md5($password);

        if ($email && $password) {
            $stmt = $pdo->prepare("SELECT id, usertype, name, email FROM user WHERE username = :email AND password = :password");
            $stmt->execute(['email' => $email, 'password' => $password]);
            $result = $stmt->fetchall(PDO::FETCH_ASSOC);

            if ($result && count($result) === 1) {
                $jwt = JWT::encode($result[0], 3600);
                setcookie('jwt_token', $jwt, time() + 3600, '/', '', false, true);
            } else {
                throw new Exception("Wrong Email Or Password");
            }
        } else {
            throw new Exception("Error Processing Request");
        }

        $responce = $result[0];
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

