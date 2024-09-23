
# REST API HTTP Methods in PHP

In REST APIs, **HTTP methods** represent the actions that the client can request from the server. In PHP, you can handle these HTTP methods using built-in functionalities such as `$_SERVER['REQUEST_METHOD']`. Below are the primary HTTP methods used in REST APIs and how they can be implemented in PHP.

## 1. GET: Retrieve Data

The GET method is used to request data from a specified resource. It is a safe and idempotent method, meaning multiple identical requests will result in the same response.

### PHP Example (Server-Side):
```php
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Fetch data logic
    $data = array("message" => "This is a GET request");
    echo json_encode($data);
}
```

## 2. POST: Create Data

The POST method is used to send data to the server to create a new resource. It is not idempotent, meaning each request can result in a different outcome.

### PHP Example (Server-Side):
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    // Create data logic
    echo json_encode(array("message" => "Data received", "data" => $input));
}
```

## 3. PUT: Update Data

The PUT method is used to update an existing resource on the server. It is idempotent, meaning multiple identical requests will have the same effect.

### PHP Example (Server-Side):
```php
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $input = json_decode(file_get_contents("php://input"), true);
    // Update data logic
    echo json_encode(array("message" => "Data updated", "data" => $input));
}
```

## 4. DELETE: Delete Data

The DELETE method is used to remove a resource from the server. Like PUT, it is idempotent, meaning multiple identical DELETE requests will have the same effect.

### PHP Example (Server-Side):
```php
if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $input = json_decode(file_get_contents("php://input"), true);
    // Delete data logic
    echo json_encode(array("message" => "Data deleted", "id" => $input['id']));
}
```

## 5. PATCH: Partial Update of Data

The PATCH method is used to apply partial modifications to a resource. Unlike PUT, which replaces the entire resource, PATCH updates only the specified fields.

### PHP Example (Server-Side):
```php
if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    $input = json_decode(file_get_contents("php://input"), true);
    // Partial update logic
    echo json_encode(array("message" => "Data partially updated", "data" => $input));
}
```

## 6. OPTIONS: Fetch Supported Methods

The OPTIONS method is used to describe the communication options for the target resource. It returns the allowed HTTP methods (e.g., GET, POST, etc.).

### PHP Example (Server-Side):
```php
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Allow: GET, POST, PUT, DELETE, OPTIONS");
    exit();
}
```

## 7. HEAD: Retrieve Headers Only

The HEAD method is similar to GET, but it only retrieves the headers of the resource and not the actual body.

### PHP Example (Server-Side):
```php
if ($_SERVER['REQUEST_METHOD'] === 'HEAD') {
    header("X-Custom-Header: MyCustomHeaderValue");
    exit();
}
```

---

### Summary of HTTP Methods in PHP:

1. **GET** – Retrieve data.
2. **POST** – Create a new resource.
3. **PUT** – Update an entire resource.
4. **DELETE** – Delete a resource.
5. **PATCH** – Partially update a resource.
6. **OPTIONS** – List supported HTTP methods.
7. **HEAD** – Retrieve headers only.

These methods form the foundation of RESTful web services in PHP, allowing clients to interact with the API effectively. PHP's `$_SERVER['REQUEST_METHOD']` provides a simple way to detect and handle these methods.
