
# JWT Authentication with Cookies in PHP and Fetch API

## **1. Server-side (PHP): Setting the JWT in an HTTP-only Cookie**

First, we will create a JWT and set it in an HTTP-only cookie after user authentication. The `firebase/php-jwt` library will be used.

### Install the JWT Library via Composer:
```bash
composer require firebase/php-jwt
```

### PHP Code to Create and Send JWT via Cookie:
```php
<?php
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

// Define your secret key and payload
$secretKey = 'your-256-bit-secret';
$payload = [
    'user_id' => 123,
    'email' => 'user@example.com',
    'exp' => time() + 3600 // Token expiration time (1 hour)
];

// Encode the JWT
$jwt = JWT::encode($payload, $secretKey, 'HS256');

// Set the JWT in an HTTP-only cookie
setcookie('jwt_token', $jwt, time() + 3600, '/', '', false, true); // Secure=false for development, true for production

// Send response back to client (could be user data or a success message)
$response = [
    'status' => 'success',
    'message' => 'Logged in successfully!',
];

header('Content-Type: application/json');
echo json_encode($response);
?>
```

- `setcookie()`: The cookie is set with the JWT, and the `httponly` flag is set to `true` to prevent JavaScript access to the cookie for security.
- Token expiration: The token will expire in 1 hour.

---

## **2. Client-side: GET and POST requests using fetch() and async/await**

For both `GET` and `POST` requests, ensure the browser includes the cookie by setting `credentials: 'include'` in the `fetch` options.

### **Client-side: GET Request with JWT Cookie**
```javascript
// Async function to make GET request
async function fetchData() {
    try {
        const response = await fetch('/api/user', {
            method: 'GET',
            credentials: 'include' // Ensures cookies are sent with the request
        });
        const data = await response.json();
        console.log('User data:', data);
    } catch (error) {
        console.error('Error fetching data:', error);
    }
}

// Call the async function to fetch data
fetchData();
```

- `credentials: 'include'`: This ensures that the browser sends cookies (including the JWT) with the request.

### **Client-side: POST Request with JWT Cookie**
```javascript
// Async function to make POST request
async function postData() {
    const payload = {
        key1: 'value1',
        key2: 'value2'
    };

    try {
        const response = await fetch('/api/submit', {
            method: 'POST',
            credentials: 'include', // Ensures cookies are sent with the request
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload) // Send the payload as JSON
        });
        const data = await response.json();
        console.log('Response from server:', data);
    } catch (error) {
        console.error('Error sending data:', error);
    }
}

// Call the async function to send data
postData();
```

---

### PHP Code to Receive POST Data from Fetch Request Body(json):
To receive the body from a POST request made by the fetch API in PHP, you can use file_get_contents('php://input') to read the raw request body, especially if the request is in JSON format.

Here's an example PHP code that handles a POST request and extracts the data from the body:

```php
<?php
// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Read the raw POST data
    $postData = file_get_contents('php://input');

    // Decode the JSON data (if the request body is in JSON format)
    $data = json_decode($postData, true);

    // Now you can access the data
    if ($data) {
        // Example of accessing data
        $key1 = $data['key1'] ?? null;
        $key2 = $data['key2'] ?? null;

        // Respond back to the client
        $response = [
            'status' => 'success',
            'received_data' => $data,
            'message' => 'Data received successfully!',
        ];
    } else {
        $response = [
            'status' => 'error',
            'message' => 'Invalid JSON format',
        ];
    }

    // Send JSON response back
    header('Content-Type: application/json');
    echo json_encode($response);
}
?>
```

---

## **3. Server-side: Verifying the JWT in PHP**

On the server, you can retrieve the JWT from the cookie, verify it, and handle the request.

### Example PHP Code to Handle API Requests (GET and POST):
```php
<?php
require 'vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secretKey = 'your-256-bit-secret';

// Check if the JWT is present in the cookie
if (isset($_COOKIE['jwt_token'])) {
    $jwt = $_COOKIE['jwt_token'];

    try {
        // Decode the JWT
        $decoded = JWT::decode($jwt, new Key($secretKey, 'HS256'));

        // Access user data from the token
        $userId = $decoded->user_id;

        // Example: Handle GET request
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $response = [
                'status' => 'success',
                'user_id' => $userId,
                'message' => 'User data retrieved successfully!'
            ];
            echo json_encode($response);
        }

        // Example: Handle POST request
        elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postData = json_decode(file_get_contents('php://input'), true);
            
            // Process the POST data (e.g., saving to database)
            // For this example, we'll just echo it back
            $response = [
                'status' => 'success',
                'user_id' => $userId,
                'received_data' => $postData,
                'message' => 'Data saved successfully!'
            ];
            echo json_encode($response);
        }
    } catch (Exception $e) {
        // Invalid token
        http_response_code(401);
        echo json_encode(['message' => 'Unauthorized']);
    }
} else {
    // No JWT token found in the cookie
    http_response_code(401);
    echo json_encode(['message' => 'Unauthorized']);
}
?>
```

---

## **Summary**
- **PHP server**: Sets the JWT in an HTTP-only cookie after user login. This ensures that the JWT is sent automatically with subsequent requests without exposing it to client-side scripts.
- **Client-side (JavaScript)**: Use `fetch()` with `credentials: 'include'` to ensure the cookie containing the JWT is sent with both `GET` and `POST` requests.
- **PHP server**: Extracts and verifies the JWT from the cookie for authentication and processes the request accordingly.

This method keeps the JWT secure while allowing authenticated requests from the client.
