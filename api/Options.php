<?php
require_once './API.php';

final class OPTIONS extends API
{
    public static function _($reqUri, $auth, $body): void
    {
        try {
            switch ($reqUri) {
                case '/help':
                    self::help();
                    break;
                case '/dashboard':
                    self::dashboard();
                    break;
                case '/rooms':
                    self::rooms();
                    break;
                case '/customers':
                    self::customers();
                    break;
                case '/bookings':
                    self::bookings();
                    break;
                case '/authentication':
                    self::authentication();
                    break;
                case '/employees':
                    self::employees();
                    break;
                case '/complaints':
                    self::complaints();
                    break;
                case '/statuscodes':
                    self::statusCodes();
                    break;
                default:
                    throw new Exception('API Request Not Found');
            }
        } catch (Exception $e) {
            self::error('OPTIONS', $reqUri, $body, $e->getMessage());
        }
    }
    private static function help(): void
    {
        $responce = [
            'detailed description' => 'method options, to /api_name',
            'api names' => [
                'dashboard' => [
                    'description' => 'Returns various counts related to rooms, staff, reservations, complaints, and income.',
                ],
                'rooms' => [
                    'description' => 'Handles room-related operations, including retrieving, adding, updating and hiding rooms.',
                ],
                'customers' => [
                    'description' => 'Manages customer-related data, including retrieving customer details and booked room information.',
                ],
                'bookings' => [
                    'description' => 'Handles room booking operations, including creating new bookings and checking in/out rooms.',
                ],
                'authentication' => [
                    'description' => 'Handles user authentication, including login, session refresh and logout functionalities.',
                ],
                'employees' => [
                    'description' => 'Manages employee-related operations, including adding, updating, and deleting employee records.',
                ],
                'complaints' => [
                    'description' => 'Handles complaint management, including creating and resolving complaints.',
                ],
                'statusCodes' => [
                    'description' => 'Returns a comprehensive list of HTTP status codes with descriptions.',
                ]
            ]
        ];
        self::success($responce);
    }
    private static function dashboard(): void
    {
        $responce = [
            'GET' => [
                'dashBoardCounts' => [
                    'params' => [],
                    'result' => [
                        'total_rooms',
                        'available_rooms',
                        'booked_rooms',
                        'checked_in_rooms',
                        'total_reservations',
                        'pending_bookings',
                        'total_staff',
                        'total_complaints',
                        'total_income',
                        'total_due'
                    ]
                ],
            ],
        ];
        self::success($responce);
    }
    private static function rooms(): void
    {
        $responce = [
            'GET' => [
                'rooms' => [
                    'params' => ['room_id'],
                    'result' => [
                        'room_no',
                        'room_type_id'
                    ]
                ],
                'availableRooms' => [
                    'params' => ['room_type_id'],
                    'result' => [
                        'count' => 'Number of rooms',
                        'rooms' => [
                            [
                                'room_id',
                                'room_no'
                            ]
                        ]
                    ]
                ],
            ],
            'POST' => [
                'rooms' => [
                    'params' => ['room_type_id', 'room_no'],
                    'result' => 'Add new Room'
                ],
            ],
            'PUT' => [
                'rooms' => [
                    'params' => ['room_id', 'room_no', 'room_type_id'],
                    'result' => 'Update room details'
                ],
            ],
            'PATCH' => [
                'rooms' => [
                    'params' => ['room_id'],
                    'result' => 'Makes a room hidden or live'
                ],
            ],
        ];
        self::success($responce);
    }
    private static function customers(): void
    {
        $responce = [
            'GET' => [
                'customerDetails' => [
                    'params' => ['room_id'],
                    'result' => [
                        'customer_id',
                        'customer_name',
                        'contact_no',
                        'email',
                        'id_card_no',
                        'id_card_type',
                        'address',
                        'remaining_price'
                    ]
                ],
                'bookedRoomDetails' => [
                    'params' => ['room_id'],
                    'result' => [
                        'booking_id',
                        'name',
                        'room_no',
                        'room_type',
                        'check_in',
                        'check_out',
                        'total_price',
                        'remaining_price'
                    ]
                ],
            ],
        ];
        self::success($responce);
    }
    private static function bookings(): void
    {
        $responce = [
            'POST' => [
                'bookings' => [
                    'params' => ['room_id', 'check_in', 'check_out', 'total_price', 'name', 'contact_no', 'email', 'id_card_id', 'id_card_no', 'address'],
                    'result' => 'Create a new booking for a room'
                ],
            ],
            'PATCH' => [
                'checkInRoom' => [
                    'params' => ['booking_id', 'advance_payment'],
                    'result' => 'Check in a room for a booking with advance payment'
                ],
                'checkOutRoom' => [
                    'params' => ['booking_id', 'remaining_amount'],
                    'result' => 'Check out a room for a booking with the remaining payment amount'
                ],
            ],
        ];
        self::success($responce);
    }
    private static function authentication(): void
    {
        $responce = [
            'POST' => [
                'login' => [
                    'params' => ['email', 'password'],
                    'result' => [
                        'id',
                        'usertype',
                        'name',
                        'email',
                        'jwt_token set using cookies'
                    ]
                ],
            ],
            'PUT' => [
                'refresh' => [
                    'result' => 'Refreshes the session'
                ]
            ],
            'DELETE' => [
                'logout' => [
                    'result' => 'Logout a user by clearing the JWT token'
                ],
            ],
        ];
        self::success($responce);
    }
    private static function employees(): void
    {
        $responce = [
            'POST' => [
                'employees' => [
                    'params' => ['staff_type', 'shift', 'first_name', 'last_name', 'contact_no', 'id_card_id', 'id_card_no', 'address', 'salary'],
                    'result' => 'Add a new employee with their details and history'
                ],
            ],
            'PUT' => [
                'employees' => [
                    'params' => ['emp_id', 'first_name', 'last_name', 'staff_type_id', 'shift_id', 'id_card_no', 'address', 'contact_no', 'joining_date', 'salary'],
                    'result' => 'Update employee details'
                ]
            ],
            'PATCH' =>[
                'shifts' => [
                    'params' => ['emp_id', 'shift_id'],
                    'result' => 'Update employee shift details'
                ],
            ],
            'DELETE' => [
                'employees' => [
                    'params' => ['empid'],
                    'result' => 'Delete one or more employees'
                ],
            ],
        ];
        self::success($responce);
    }
    private static function complaints(): void
    {
        $responce = [
            'POST' => [
                'complaints' => [
                    'params' => ['complainant_name', 'complaint_type', 'complaint'],
                    'result' => 'Create a new complaint'
                ],
            ],
            'GET' => [
                'complaints' => [
                    'result' => 'Retrieve all complaints'
                ],
            ],
            'PUT' => [
                'resolveComplaint' => [
                    'params' => ['complaint_id', 'budget'],
                    'result' => 'Resolve a complaint by updating its budget and marking it as resolved'
                ],
            ],
        ];
        self::success($responce);
    }
    private static function statusCodes(): void
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
