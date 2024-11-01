<?php
require_once './API.php';
require_once '../auth/conn.php';

final class GET extends API
{
    public static function _($reqUri, $auth, $body): void
    {
        try {
            switch ($reqUri) {
                case '/dashBoardCounts':
                    self::dashBoardCounts($auth, $body);
                    break;
                case '/rooms':
                    self::rooms($auth, $body);
                    break;
                case '/availableRooms':
                    self::availableRooms($auth, $body);
                    break;
                case '/customerDetails':
                    self::customerDetails($auth, $body);
                    break;
                case '/bookedRoomDetails':
                    self::bookedRoomDetails($auth, $body);
                    break;
                case '/complaints':
                    self::complaints($auth, $body);
                    break;
                default:
                    throw new Exception('API Request Not Found');
            }
        } catch (Exception $e) {
            self::error('GET', $reqUri, $body, $e->getMessage());
        }
    }
    private static function dashBoardCounts($auth, $body): void
    {
        $pdo = Conn::setConnection();

        $executeQuery = function ($pdo, $sql, $fetchCount = false) {
            $stmt = $pdo->prepare($sql);
            $stmt->execute();
            if ($fetchCount) {
                return $stmt->rowCount();
            }
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? (int) ($result['income'] ?? 0) : 0;
        };

        try {
            $queries = [
                'total_rooms' => ["SELECT * FROM room", true],
                'available_rooms' => ["SELECT * FROM room WHERE status IS NULL AND deleteStatus = '0'", true],
                'booked_rooms' => ["SELECT * FROM room WHERE status = '1'", true],
                'checked_in_rooms' => ["SELECT * FROM room WHERE check_in_status = '1'", true],
                'total_reservations' => ["SELECT * FROM booking", true],
                'pending_bookings' => ["SELECT * FROM booking WHERE payment_status = '0'", true],
                'total_staff' => ["SELECT * FROM staff", true],
                'total_complaints' => ["SELECT * FROM complaint", true],
                'total_income' => ["SELECT SUM(total_price) AS income FROM booking WHERE payment_status = '1'", false],
                'total_due' => ["SELECT SUM(total_price) AS income FROM booking WHERE payment_status = '0'", false],
            ];

            $response = [];
            foreach ($queries as $key => [$sql, $fetchCount]) {
                $response[$key] = $executeQuery($pdo, $sql, $fetchCount ?? false);
            }

            self::success($response);
        } catch (PDOException $e) {
            throw new Exception("Database Error: " . $e->getMessage());
        }
    }
    private static function rooms($auth, $body): void
    {
        $room_id = $body['room_id'] ?? false;
        if (!$room_id) {
            throw new Exception('Room ID is required');
        }

        $pdo = Conn::setConnection();

        try {
            $stmt = $pdo->prepare("SELECT room_no, room_type_id FROM room WHERE room_id = :room_id");
            $stmt->bindParam(':room_id', $room_id, PDO::PARAM_INT);
            $stmt->execute();
            $room = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($room) {
                $responce = [
                    'room_no' => $room['room_no'],
                    'room_type_id' => $room['room_type_id']
                ];
                self::success($responce);
            } else {
                self::unsuccess(['message' => 'Room not found']);
            }
        } catch (PDOException $e) {
            throw new Exception("Database Error: " . $e->getMessage());
        }
    }
    private static function availableRooms($auth, $body): void
    {
        $room_type_id = $body['room_type_id'] ?? false;
        if (!$room_type_id) {
            throw new Exception('Room Type ID is required');
        }

        $pdo = Conn::setConnection();

        try {
            $query = "SELECT room_id, room_no FROM room WHERE room_type_id = :room_type_id AND status IS NULL AND deleteStatus = '0'";
            $stmt = $pdo->prepare($query);
            $stmt->bindParam(':room_type_id', $room_type_id, PDO::PARAM_INT);
            $stmt->execute();
            $rooms = $stmt->fetchAll(PDO::FETCH_ASSOC);

            if ($rooms) {
                $response = [
                    'count' => count($rooms),
                    'rooms' => $rooms
                ];
                self::success($response);
            } else {
                self::unsuccess(['message' => 'Invalid room type']);
            }
        } catch (PDOException $e) {
            throw new Exception("Database Error: " . $e->getMessage());
        }
    }
    private static function customerDetails($auth, $body): void
    {
        $room_id = $body['room_id'] ?? null;
        if (!$room_id) {
            throw new Exception('Room ID is required');
        }

        $pdo = Conn::setConnection();

        try {
            $sql = "SELECT c.customer_id, c.customer_name, c.contact_no, c.email, c.id_card_no, c.address, 
                    b.remaining_price, c.id_card_type_id, ict.id_card_type
                FROM room r
                NATURAL JOIN room_type rt
                NATURAL JOIN booking b
                NATURAL JOIN customer c
                LEFT JOIN id_card_type ict ON c.id_card_type_id = ict.id_card_type_id
                WHERE r.room_id = :room_id AND b.payment_status = '0'";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([':room_id' => $room_id]);
            $customer_details = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($customer_details) {
                $response = [
                    'customer_id' => $customer_details['customer_id'],
                    'customer_name' => $customer_details['customer_name'],
                    'contact_no' => $customer_details['contact_no'],
                    'email' => $customer_details['email'],
                    'id_card_no' => $customer_details['id_card_no'],
                    'id_card_type' => $customer_details['id_card_type'],
                    'address' => $customer_details['address'],
                    'remaining_price' => $customer_details['remaining_price']
                ];
                self::success($response);
            } else {
                self::unsuccess(['message' => 'No customer details found for the specified room']);
            }
        } catch (PDOException $e) {
            throw new Exception("Database Error: " . $e->getMessage());
        }
    }
    private static function bookedRoomDetails($auth, $body): void
    {
        $room_id = $body['room_id'] ?? null;
        if (!$room_id) {
            throw new Exception('Room ID is required');
        }

        $pdo = Conn::setConnection();

        try {
            $sql = "SELECT b.booking_id, c.customer_name, r.room_no, rt.room_type, b.check_in, b.check_out, 
                            b.total_price, b.remaining_price
                    FROM room r
                    NATURAL JOIN room_type rt
                    NATURAL JOIN booking b
                    NATURAL JOIN customer c
                    WHERE r.room_id = :room_id AND b.payment_status = '0'";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([':room_id' => $room_id]);
            $room = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($room) {
                $response = [
                    'booking_id' => $room['booking_id'],
                    'name' => $room['customer_name'],
                    'room_no' => $room['room_no'],
                    'room_type' => $room['room_type'],
                    'check_in' => date('M j, Y', strtotime($room['check_in'])),
                    'check_out' => date('M j, Y', strtotime($room['check_out'])),
                    'total_price' => $room['total_price'],
                    'remaining_price' => $room['remaining_price']
                ];
                self::success($response);
            } else {
                self::unsuccess(['message' => 'No booking found for the specified room']);
            }
        } catch (PDOException $e) {
            throw new Exception("Database Error: " . $e->getMessage());
        }
    }
    private static function complaints($auth, $body): void
    {
        $pdo = Conn::setConnection();

        try {
            $query = "SELECT * FROM complaint";
            $stmt = $pdo->prepare($query);

            if ($stmt->execute()) {
                $complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if ($complaints) {
                    self::success(['complaints' => $complaints], 200);
                } else {
                    self::unsuccess(['message' => 'No complaints found'], 404);
                }
            } else {
                throw new Exception('Failed to retrieve complaints');
            }
        } catch (PDOException $e) {
            throw new Exception("Database Error: " . $e->getMessage());
        }
    }
}
