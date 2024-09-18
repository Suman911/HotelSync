
# PHP Security Best Practices

This guide covers the essential security practices to follow when developing a PHP-based website.

## 1. Sanitize User Input
Sanitizing user input helps prevent **SQL injection** and **Cross-Site Scripting (XSS)** attacks.

- **For HTML Output**: Use `htmlspecialchars()` to convert special characters into HTML entities.
```php
$username = htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8');
```

- **For Database Queries**: Use prepared statements for safe database interaction.
```php
$id = intval($_GET['id']); // Convert string to integer to avoid potential injection
```

## 2. CSRF Tokens
A **Cross-Site Request Forgery (CSRF)** token ensures that forms or actions are submitted by legitimate users.

- **Generate a CSRF token** and store it in the session:
```php
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
```

- **Add CSRF token to forms**:
```html
<form action="submit.php" method="POST">
    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
    <!-- Other form fields -->
</form>
```

- **Validate the token upon form submission**:
```php
if ($_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Invalid CSRF token");
}
```

## 3. Secure Sessions
Ensure secure session handling by using the following best practices:

- **Regenerate Session ID** after login or sensitive actions:
```php
session_start();
session_regenerate_id(true);
```

- **Set secure session cookie flags**:
```php
session_set_cookie_params([
    'lifetime' => 0,            // Session expires on browser close
    'path' => '/',
    'domain' => 'yourdomain.com',
    'secure' => true,           // Only send over HTTPS
    'httponly' => true,         // Accessible only via HTTP, not JavaScript
    'samesite' => 'Strict'      // Prevent cross-site cookie sending
]);
session_start();
```

## 4. Use Prepared Statements
Prepared statements ensure that user input is treated as data and not executable code, protecting against **SQL injection**.

- **Example Using PDO**:
```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();
```

## 5. Password Hashing
Use **password hashing** to securely store user passwords.

- **Hashing a Password**:
```php
$hashedPassword = password_hash($password, PASSWORD_BCRYPT);
```

- **Verifying a Password** during login:
```php
if (password_verify($password, $hashedPassword)) {
    // Password is correct
}
```

## 6. Limit User Access and Privileges
Ensure that users only have the permissions they need, and validate file uploads to prevent remote code execution.

- **Restrict file uploads**:
```php
$allowedTypes = ['image/jpeg', 'image/png'];
if (!in_array($_FILES['upload']['type'], $allowedTypes)) {
    die("Invalid file type.");
}
```

## 7. HTTPS
Always use HTTPS to protect data transmitted between the client and server from **Man-in-the-Middle (MITM)** attacks.

## 8. Error Handling
Do not expose detailed error messages in production. Instead, log errors and show user-friendly messages.

- **Example**:
```php
ini_set('display_errors', 0); // Disable error display
ini_set('log_errors', 1);     // Enable error logging
error_log("Error: " . $error_message); // Log the error
```

## Summary:
1. **Sanitize user input** to prevent SQL injection and XSS.
2. **Use CSRF tokens** to protect against CSRF attacks.
3. **Secure sessions** with proper configuration.
4. **Prepared statements** protect against SQL injection.
5. **Hash passwords** before storing them.
6. **Limit access and file uploads**.
7. Always use **HTTPS** for secure data transmission.
8. **Hide error details** in production to avoid leaking sensitive data.

By following these security practices, your PHP website will be more secure and resilient against common web vulnerabilities.
