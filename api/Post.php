<?php
require_once './API.php';
require_once '../auth/conn.php';
require_once '../auth/JWT.php';

final class POST extends API
{
    public static function _($reqUri, $auth, $body): void
    {
        try {
            switch ($reqUri) {
                case '/login':
                    self::login($auth, $body);
                    break;
                case '/rooms':
                    self::rooms($auth, $body);
                    break;
                case '/bookings':
                    self::bookings($auth, $body);
                    break;
                case '/employees':
                    self::employees($auth, $body);
                    break;
                case '/complaints':
                    self::complaints($auth, $body);
                    break;
                default:
                    throw new Exception('API Request Not Found');
            }
        } catch (Exception $e) {
            self::error('POST', $reqUri, $body, $e->getMessage());
        }
    }
    private static function login($auth, $body): void
    {
        $email = $body['email'] ?? false;
        $password = $body['password'] ?? false;

        if (!$email || !$password) {
            throw new Exception("Error Processing Request: Missing or Invalid Email and Password");
        }

        $pdo = Conn::setConnection();

        $hashedPassword = md5($password);

        try {
            $stmt = $pdo->prepare("SELECT id, usertype, name, email FROM user WHERE username = :email AND password = :password");
            $stmt->execute([':email' => $email, ':password' => $hashedPassword]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($result) {
                $jwt = JWT::encode($result, 86400);
                setcookie('jwt_token', $jwt, time() + 86400, '/', '', false, true);

                $response = $result;
                self::success($response, 202);
            } else {
                throw new Exception("Wrong Email or Password");
            }
        } catch (PDOException $e) {
            throw new Exception("Database Error: " . $e->getMessage());
        }
    }
    private static function rooms($auth, $body): void
    {
        if ($auth['usertype'] !== 'admin') {
            throw new Exception('Access Denied: Only Admins Can Add Rooms');
        }

        $room_type_id = $body['room_type_id'] ?? false;
        $room_no = $body['room_no'] ?? '';
        if (!$room_no) {
            throw new Exception('Room number is required');
        }

        $pdo = Conn::setConnection();

        try {
            $checkQuery = "SELECT COUNT(*) FROM room WHERE room_no = :room_no";
            $stmt = $pdo->prepare($checkQuery);
            $stmt->execute([':room_no' => $room_no]);
            $roomExists = $stmt->fetchColumn();

            if ($roomExists >= 1) {
                self::unsuccess(['message' => 'Room No Already Exists'], 409);
            } else {
                $insertQuery = "INSERT INTO room (room_type_id, room_no) VALUES (:room_type_id, :room_no)";
                $stmt = $pdo->prepare($insertQuery);
                if ($stmt->execute(['room_type_id' => $room_type_id, 'room_no' => $room_no])) {
                    $roomID = $pdo->lastInsertId();
                    $responce = [
                        'message' => 'Successfully Added Room',
                        'room_id' => $roomID
                    ];
                    self::success($responce, 201);
                } else {
                    throw new Exception('Database Error: Could not add room');
                }
            }
        } catch (PDOException $e) {
            throw new Exception("Database Error: " . $e->getMessage());
        }
    }
    private static function bookings($auth, $body): void
    {
        if ($auth['usertype'] !== 'admin') {
            throw new Exception('Access Denied: Only Admins Can Create Bookings');
        }

        $requiredFields = ['room_id', 'check_in', 'check_out', 'total_price', 'name', 'contact_no', 'email', 'id_card_id', 'id_card_no', 'address'];
        foreach ($requiredFields as $field) {
            if (!isset($body[$field])) {
                throw new Exception("Missing Required Field: $field");
            }
        }

        $pdo = Conn::setConnection();

        try {
            $pdo->beginTransaction();

            $customer_sql = "INSERT INTO customer (customer_name, contact_no, email, id_card_type_id, id_card_no, address)
                                VALUES (:name, :contact_no, :email, :id_card_id, :id_card_no, :address)";
            $stmt = $pdo->prepare($customer_sql);
            $params = [
                ':name' => $body['name'],
                ':contact_no' => $body['contact_no'],
                ':email' => $body['email'],
                ':id_card_id' => $body['id_card_id'],
                ':id_card_no' => $body['id_card_no'],
                ':address' => $body['address']
            ];
            $stmt->execute($params);
            $customer_id = $pdo->lastInsertId();

            $booking_sql = "INSERT INTO booking (customer_id, room_id, check_in, check_out, total_price, remaining_price)
                            VALUES (:customer_id, :room_id, :check_in, :check_out, :total_price, :remaining_price)";
            $stmt = $pdo->prepare($booking_sql);
            $params = [
                ':customer_id' => $customer_id,
                ':room_id' => $body['room_id'],
                ':check_in' => $body['check_in'],
                ':check_out' => $body['check_out'],
                ':total_price' => $body['total_price'],
                ':remaining_price' => $body['total_price']
            ];
            $stmt->execute($params);
            $bookingID = $pdo->lastInsertId();

            $room_stats_sql = "UPDATE room SET status = true WHERE room_id = :room_id";
            $stmt = $pdo->prepare($room_stats_sql);
            $stmt->execute([':room_id' => $body['room_id']]);

            $pdo->commit();
            $responce = [
                'message' => 'Room successfully booked',
                'booking_id' => $bookingID
            ];
            self::success($responce, 201);
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw new Exception("Database Error: " . $e->getMessage());
        }
    }
    private static function employees($auth, $body): void
    {
        $requiredFields = ['staff_type', 'shift', 'first_name', 'last_name', 'contact_no', 'id_card_id', 'id_card_no', 'address', 'salary'];
        foreach ($requiredFields as $field) {
            if (!isset($body[$field])) {
                throw new Exception("Missing Required Field: $field");
            }
        }

        $pdo = Conn::setConnection();

        try {
            $pdo->beginTransaction();

            $customer_sql = "INSERT INTO staff (emp_name, staff_type_id, shift_id, id_card_type, id_card_no, address, contact_no, salary) 
                            VALUES (:name, :staff_type, :shift, :id_card_id, :id_card_no, :address, :contact_no, :salary)";
            $stmt = $pdo->prepare($customer_sql);
            $params = [
                ':name' => $body['first_name'] . ' ' . $body['last_name'],
                ':staff_type' => $body['staff_type'],
                ':shift' => $body['shift'],
                ':id_card_id' => $body['id_card_id'],
                ':id_card_no' => $body['id_card_no'],
                ':address' => $body['address'],
                ':contact_no' => $body['contact_no'],
                ':salary' => $body['salary'],
            ];

            if (!$stmt->execute($params)) {
                $pdo->rollBack();
                throw new Exception('Failed to add employee');
            }

            $emp_id = $pdo->lastInsertId();

            $insert_sql = "INSERT INTO emp_history (emp_id, shift_id) VALUES (:emp_id, :shift)";
            $stmt = $pdo->prepare($insert_sql);

            if (!$stmt->execute([':emp_id' => $emp_id, ':shift' => $body['shift']])) {
                $pdo->rollBack();
                throw new Exception('Failed to add employee history');
            }

            $pdo->commit();
            $responce = [
                'message' => 'Room successfully booked',
                'emp_id' => $emp_id
            ];
            self::success($responce, 201);
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw new Exception("Database Error: " . $e->getMessage());
        } catch (Exception $e) {
            $pdo->rollBack();
            throw new Exception($e->getMessage());
        }
    }
    private static function complaints($auth, $body): void
    {
        $requiredFields = ['complainant_name', 'complaint_type', 'complaint'];
        foreach ($requiredFields as $field) {
            if (!isset($body[$field])) {
                throw new Exception("Missing Required Field: $field");
            }
        }

        $pdo = Conn::setConnection();

        try {
            $query = "INSERT INTO complaint (complainant_name, complaint_type, complaint) VALUES (:complainant_name, :complaint_type, :complaint)";
            $stmt = $pdo->prepare($query);
            $params = [
                ':complainant_name' => $body['complainant_name'],
                ':complaint_type' => $body['complaint_type'],
                ':complaint' => $body['complaint']
            ];

            if ($stmt->execute($params)) {
                $complaintID = $pdo->lastInsertId();
                $responce = [
                    'message' => 'Complaint registered successfully',
                    'complaint_id' => $complaintID
                ];
                self::success($responce, 201);
            } else {
                throw new Exception('Failed to create complaint');
            }
        } catch (PDOException $e) {
            throw new Exception("Database Error: " . $e->getMessage());
        }
    }
}
