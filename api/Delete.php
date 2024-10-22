<?php
require_once './API.php';

final class DELETE extends API
{
    public static function _($reqUri, $auth, $body): void
    {
        try {
            if ($auth['usertype'] !== 'admin')
                throw new Exception('Access Denied, Only Admins Can Access Options APIs');
            switch ($reqUri) {
                case '/logout':
                    self::logout($auth, $body);
                    break;
                case '/employees':
                    self::employees($auth, $body);
                    break;
                default:
                    throw new Exception('API Request Not Found');
            }
        } catch (Exception $e) {
            self::error('delete', $reqUri, $body, $e->getMessage());
        }
    }
    private static function logout($auth, $body): void
    {
        setcookie('jwt_token', '', time() - 3600, '/', '', false, true);
        $responce = ['message' => 'Successfully logged out'];
        self::success($responce);
    }
    private static function employees($auth, $body): void
    {
        if ($auth['usertype'] !== 'admin') {
            throw new Exception('Access Denied: Only Admins Can Delete Employees');
        }

        $emp_ids = $body['empid'] ?? false;
        if (!$emp_ids) {
            throw new Exception('Employee ID(s) are required');
        }
        if (!is_array($emp_ids)) {
            $emp_ids = [$emp_ids];
        }

        $pdo = Conn::setConnection();

        try {
            $placeholders = implode(',', array_fill(0, count($emp_ids), '?'));
            $deleteQuery = "DELETE FROM staff WHERE emp_id IN ($placeholders)";
            $stmt = $pdo->prepare($deleteQuery);

            if ($stmt->execute($emp_ids)) {
                if ($stmt->rowCount() > 0) {
                    self::success(['message' => 'Employee(s) deleted successfully']);
                } else {
                    self::unsuccess(['message' => 'No employee(s) found with the provided emp_id(s)']);
                }
            } else {
                throw new Exception('Error deleting employee(s)');
            }
        } catch (PDOException $e) {
            throw new Exception("Database Error: " . $e->getMessage());
        }
    }
}
