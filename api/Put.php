<?php
require_once './API.php';

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
                case '/shifts':
                    self::shifts($auth, $body);
                    break;
                default:
                    throw new Exception('API Request Not Found');
            }
        } catch (Exception $e) {
            self::error('put', $reqUri, $body, $e->getMessage());
        }
    }
    private static function employees($auth, $body): void
    {
        if ($auth['usertype'] !== 'admin') {
            throw new Exception('Access Denied: Only Admins Can Update Employee Details');
        }

        $requiredFields = ['emp_id', 'first_name', 'last_name', 'staff_type_id', 'shift_id', 'id_card_no', 'address', 'contact_no', 'joining_date', 'salary'];
        foreach ($requiredFields as $field) {
            if (empty($body[$field])) {
                throw new Exception("Missing Required Field: $field");
            }
        }

        $pdo = Conn::setConnection();

        try {
            $query = "UPDATE staff
                        SET emp_name = :emp_name, staff_type_id = :staff_type_id, shift_id = :shift_id, 
                            id_card_type = :id_card_type, id_card_no = :id_card_no, 
                            address = :address, contact_no = :contact_no, 
                            joining_date = :joining_date, salary = :salary
                        WHERE emp_id = :emp_id";

            $stmt = $pdo->prepare($query);
            $params = [
                ':emp_name' => $body['first_name'] . ' ' . $body['last_name'],
                ':staff_type_id' => $body['staff_type_id'],
                ':shift_id' => $body['shift_id'],
                ':id_card_type' => $body['id_card_type'] ?? null,
                ':id_card_no' => $body['id_card_no'],
                ':address' => $body['address'],
                ':contact_no' => $body['contact_no'],
                ':joining_date' => strtotime($body['joining_date']),
                ':salary' => $body['salary'],
                ':emp_id' => $body['emp_id']
            ];

            if ($stmt->execute($params)) {
                if ($stmt->rowCount() > 0) {
                    self::success(['message' => 'Employee updated successfully']);
                } else {
                    self::unsuccess(['message' => 'No employee found with the provided emp_id']);
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

        $requiredFields = ['room_id', 'room_no'];
        foreach ($requiredFields as $field) {
            if (empty($body[$field])) {
                throw new Exception("Missing Required Field: $field");
            }
        }

        $pdo = Conn::setConnection();

        try {
            $query = "UPDATE room SET room_no = :room_no, room_type_id = :room_type_id WHERE room_id = :room_id";
            $stmt = $pdo->prepare($query);
            $params = [
                ':room_no' => $body['room_no'],
                ':room_type_id' => $body['room_type_id'] ?? null,
                ':room_id' => $body['room_id'],
            ];

            if ($stmt->execute($params)) {
                if ($stmt->rowCount() > 0) {
                    self::success(['message' => 'Room updated successfully']);
                } else {
                    self::unsuccess(['message' => 'No room found with the provided room_id']);
                }
            } else {
                throw new Exception('Error updating room details');
            }
        } catch (PDOException $e) {
            throw new Exception("Database Error: " . $e->getMessage());
        }
    }
    private static function shifts($auth, $body): void
    {
        if ($auth['usertype'] !== 'admin') {
            throw new Exception('Access Denied: Only Admins Can Edit Room Details');
        }
        
        $requiredFields = ['emp_id', 'shift_id'];
        foreach ($requiredFields as $field) {
            if (empty($body[$field])) {
                throw new Exception("Missing Required Field: $field");
            }
        }

        $pdo = Conn::setConnection();

        try {
            $pdo->beginTransaction();

            $query = "UPDATE staff SET shift_id = :shift_id WHERE emp_id = :emp_id";
            $stmt = $pdo->prepare($query);
            $stmt->execute([
                ':shift_id' => $body['shift_id'],
                ':emp_id' => $body['emp_id']
            ]);

            if ($stmt->rowCount() === 0) {
                $pdo->rollBack();
                self::unsuccess(['message' => 'Employee not found or shift not changed'], 404);
            }

            $to_date = date("Y-m-d H:i:s");
            $updateHistory = "UPDATE emp_history SET to_date = :to_date WHERE emp_id = :emp_id AND to_date IS NULL";
            $stmt = $pdo->prepare($updateHistory);
            $stmt->execute([
                ':to_date' => $to_date,
                ':emp_id' => $body['emp_id']
            ]);

            $insertHistory = "INSERT INTO emp_history (emp_id, shift_id) VALUES (:emp_id, :shift_id)";
            $stmt = $pdo->prepare($insertHistory);
            if (
                $stmt->execute([
                    ':emp_id' => $body['emp_id'],
                    ':shift_id' => $body['shift_id']
                ])
            ) {
                $pdo->commit();
                self::success(['message' => 'Shift updated successfully']);
            } else {
                throw new Exception('Failed to insert new shift history');
            }

        } catch (PDOException $e) {
            $pdo->rollBack();
            throw new Exception("Database Error: " . $e->getMessage());
        } catch (Exception $e) {
            $pdo->rollBack();
            throw new Exception($e->getMessage());
        }
    }
}
