<?php
require_once './API.php';
require_once '../auth/conn.php';

final class PATCH extends API
{
    public static function _($reqUri, $auth, $body): void
    {
        try {
            if ($auth['usertype'] !== 'admin')
                throw new Exception('Access Denied, Only Admins Can Access Options APIs');
            switch ($reqUri) {
                case '/checkInRoom':
                    self::checkInRoom($auth, $body);
                    break;
                case '/checkOutRoom':
                    self::checkOutRoom($auth, $body);
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
            self::error('PATCH', $reqUri, $body, $e->getMessage());
        }
    }
    private static function checkInRoom($auth, $body): void
    {
        $booking_id = $body['booking_id'] ?? null;
        $advance_payment = $body['advance_payment'] ?? null;
        if (!$booking_id || !$advance_payment) {
            throw new Exception('Booking ID and advance payment are required');
        }

        $pdo = Conn::setConnection();

        try {
            $query = "SELECT room_id, total_price FROM booking WHERE booking_id = :booking_id";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':booking_id' => $booking_id]);
            $booking_details = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking_details) {
                self::unsuccess(['message' => 'Booking not found']);
            }

            $room_id = $booking_details['room_id'];
            $remaining_price = $booking_details['total_price'] - $advance_payment;

            $updateBooking = "UPDATE booking SET remaining_price = :remaining_price WHERE booking_id = :booking_id";
            $stmt = $pdo->prepare($updateBooking);
            $stmt->execute([':remaining_price' => $remaining_price, ':booking_id' => $booking_id]);

            $updateRoom = "UPDATE room SET check_in_status = 1 WHERE room_id = :room_id";
            $stmt = $pdo->prepare($updateRoom);
            $stmt->execute([':room_id' => $room_id]);

            self::success(['message' => 'Room successfully checked in']);
        } catch (PDOException $e) {
            throw new Exception("Database Error: " . $e->getMessage());
        }
    }
    private static function checkOutRoom($auth, $body): void
    {
        $booking_id = $body['booking_id'] ?? null;
        $remaining_amount = $body['remaining_amount'] ?? null;
        if (!$booking_id || !$remaining_amount) {
            throw new Exception('Booking ID and remaining amount are required');
        }

        $pdo = Conn::setConnection();

        try {
            $query = "SELECT room_id, remaining_price FROM booking WHERE booking_id = :booking_id";
            $stmt = $pdo->prepare($query);
            $stmt->execute([':booking_id' => $booking_id]);
            $booking_details = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$booking_details) {
                self::unsuccess(['message' => 'Booking not found']);
            }

            $room_id = $booking_details['room_id'];
            $remaining_price = $booking_details['remaining_price'];

            if ($remaining_price == $remaining_amount) {
                $updateBooking = "UPDATE booking SET remaining_price = 0, payment_status = 1 WHERE booking_id = :booking_id";
                $stmt = $pdo->prepare($updateBooking);
                $stmt->execute([':booking_id' => $booking_id]);

                $updateRoom = "UPDATE room SET status = NULL, check_in_status = 0, check_out_status = 1 WHERE room_id = :room_id";
                $stmt = $pdo->prepare($updateRoom);
                $stmt->execute([':room_id' => $room_id]);

                self::success(['message' => 'Room successfully checked out']);
            } else {
                self::unsuccess(['message' => "Please enter the full payment amount $remaining_price"]);
            }
        } catch (PDOException $e) {
            throw new Exception("Database Error: " . $e->getMessage());
        }
    }
    private static function rooms($auth, $body): void
    {
        if ($auth['usertype'] !== 'admin') {
            throw new Exception('Access Denied: Only Admins Can Delete Room');
        }

        $requiredFields = ['room_id', 'delete_room'];
        foreach ($requiredFields as $field) {
            if (!isset($body[$field])) {
                throw new Exception("Missing Required Field: $field");
            }
        }

        $pdo = Conn::setConnection();

        try {
            $query = "UPDATE room SET deleteStatus = :deleteStatus WHERE room_id = :room_id AND status IS NULL";
            $stmt = $pdo->prepare($query);
            $params = [
                ':room_id' => $body['room_id'],
                ':deleteStatus' => $body['delete_room']
            ];

            if ($stmt->execute($params)) {
                $statu = $body['delete_room'] ? 'hidden' : 'live';
                if ($stmt->rowCount() > 0) {
                    self::success(['message' => "Room is $statu now"]);
                } else {
                    self::unsuccess(['message' => "Room not found or already $statu"]);
                }
            } else {
                throw new Exception('Error deleting room');
            }
        } catch (PDOException $e) {
            throw new Exception("Database Error: " . $e->getMessage());
        }
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
                self::success(['message' => 'Complaint resolved successfully']);
            } else {
                throw new Exception('Failed to resolve complaint');
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
            if (!isset($body[$field])) {
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
