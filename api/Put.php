<?php
require_once './API.php';
require_once '../auth/conn.php';

final class PUT extends API
{
    public static function _($reqUri, $auth, $body): void
    {
        try {
            if ($auth['usertype'] !== 'admin')
                throw new Exception('Access Denied, Only Admins Can Access Options APIs');
            switch ($reqUri) {
                case '/employees':
                    self::employees($auth, $body);
                    break;
                case '/rooms':
                    self::rooms($auth, $body);
                    break;
                case '/refresh':
                    self::refresh($auth, $body);
                    break;
                case '/complaints':
                    self::complaints($auth, $body);
                    break;
                default:
                    throw new Exception('API Request Not Found');
            }
        } catch (Exception $e) {
            self::error('PUT', $reqUri, $body, $e->getMessage());
        }
    }
    private static function employees($auth, $body): void
    {
        if ($auth['usertype'] !== 'admin') {
            throw new Exception('Access Denied: Only Admins Can Update Employee Details');
        }

        $requiredFields = ['emp_id', 'id_card_type', 'id_card_no', 'staff_type_id', 'shift_id', 'address', 'contact_no', 'salary'];
        foreach ($requiredFields as $field) {
            if (!isset($body[$field])) {
                throw new Exception("Missing Required Field: $field");
            }
        }

        $pdo = Conn::setConnection();

        try {
            $query = "UPDATE staff
                        SET id_card_type = :id_card_type, id_card_no = :id_card_no,
                            staff_type_id = :staff_type_id, shift_id = :shift_id,
                            address = :address, contact_no = :contact_no, salary = :salary
                        WHERE emp_id = :emp_id";

            $stmt = $pdo->prepare($query);
            $params = [
                ':id_card_type' => $body['id_card_type'],
                ':id_card_no' => $body['id_card_no'],
                ':staff_type_id' => $body['staff_type_id'],
                ':shift_id' => $body['shift_id'],
                ':address' => $body['address'],
                ':contact_no' => $body['contact_no'],
                ':salary' => $body['salary'],
                ':emp_id' => $body['emp_id']
            ];

            if ($stmt->execute($params)) {
                if ($stmt->rowCount() > 0) {
                    self::success(['message' => 'Employee updated successfully']);
                } else {
                    self::unsuccess(['message' => 'No employee found with the provided emp_id or no updates are made']);
                }
            } else {
                throw new Exception('Error updating employee details');
            }
        } catch (PDOException $e) {
            throw new Exception("Database Error: " . $e->getMessage());
        }
    }
    private static function rooms($auth, $body): void
    {
        if ($auth['usertype'] !== 'admin') {
            throw new Exception('Access Denied: Only Admins Can Edit Room Details');
        }

        $requiredFields = ['room_id', 'room_no', 'room_type_id'];
        foreach ($requiredFields as $field) {
            if (!isset($body[$field])) {
                throw new Exception("Missing Required Field: $field");
            }
        }

        $pdo = Conn::setConnection();

        try {
            $query = "UPDATE room SET room_no = :room_no, room_type_id = :room_type_id WHERE room_id = :room_id";
            $stmt = $pdo->prepare($query);
            $params = [
                ':room_no' => $body['room_no'],
                ':room_type_id' => $body['room_type_id'],
                ':room_id' => $body['room_id'],
            ];

            if ($stmt->execute($params)) {
                if ($stmt->rowCount() > 0) {
                    self::success(['message' => 'Room updated successfully']);
                } else {
                    self::unsuccess(['message' => 'No such room found or you provides the same room_type_id']);
                }
            } else {
                throw new Exception('Error updating room details');
            }
        } catch (PDOException $e) {
            throw new Exception("Database Error: " . $e->getMessage());
        }
    }
    private static function refresh($auth, $body): void
    {
        $jwt = JWT::encode($auth, 86400);
        setcookie('jwt_token', $jwt, time() + 86400, '/', '', false, true);
        self::success(['message' => 'Session refreshed'], 202);
    }
    private static function complaints($auth, $body): void
    {
        $requiredFields = ['complaint_id', 'budget'];
        foreach ($requiredFields as $field) {
            if (!isset($body[$field])) {
                throw new Exception("Missing Required Field: $field");
            }
        }

        $pdo = Conn::setConnection();

        try {
            $query = "UPDATE complaint SET budget = :budget, resolve_status = 1 WHERE id = :complaint_id";
            $stmt = $pdo->prepare($query);
            $params = [
                ':budget' => $body['budget'],
                ':complaint_id' => $body['complaint_id'],
            ];

            if ($stmt->execute($params)) {
                if ($stmt->rowCount() > 0) {
                    self::success(['message' => 'Complaint resolved successfully']);
                } else {
                    throw new Exception('Complaint not fond or alrady resolved');
                }
            } else {
                throw new Exception('Failed to resolve complaint');
            }
        } catch (PDOException $e) {
            throw new Exception("Database Error: " . $e->getMessage());
        }
    }
}
