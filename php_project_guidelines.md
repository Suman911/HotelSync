
# Standard Approach for PHP-based Website (Without Framework)

This guide provides best practices for organizing a PHP-based website without using a framework, while using JavaScript, jQuery, CSS, Bootstrap, and HTML for the frontend.

## 1. Directory Structure
Organize your project files into appropriate directories:

```bash
Hotelsync/
│
├── assets/
│   ├── css/
│   │   └── style.css        # Custom CSS files
│   ├── js/
│   │   └── script.js        # Custom JavaScript/jQuery files
│   └── images/              # Store images
│
├── includes/
│   ├── header.php           # Header template
│   ├── footer.php           # Footer template
│   ├── db.php               # Database connection
│   └── config.php           # Configuration settings
│
├── public
│   ├── index.php            # Main entry point
│   └── other_pages.php      # Other web pages
│
├── templates/
│   ├── home.php             # Main content page
│   └── contact.php          # Example of another page
```

## 2. Routing and URL Handling

Use `.htaccess` to route requests through `index.php` for clean URLs.

### Example `.htaccess`:
```apache
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d

RewriteRule ^(.*)$ index.php?url=$1 [QSA,L]
```

### Example `index.php`:
```php
<?php
$url = isset($_GET['url']) ? $_GET['url'] : 'home';

switch ($url) {
    case 'home':
        include 'templates/home.php';
        break;
    case 'contact':
        include 'templates/contact.php';
        break;
    default:
        include 'templates/404.php'; // Page not found
        break;
}

include 'includes/header.php';
include 'includes/footer.php';
?>
```

## 3. Separation of Concerns

Separate reusable components like headers, footers, and database connections.

### Example `header.php`:
```php
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My PHP Website</title>
    <link rel="stylesheet" href="assets/bootstrap/bootstrap.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <a class="navbar-brand" href="#">My Site</a>
    </nav>
```

### Example `footer.php`:
```php
    <footer>
        <p>&copy; 2024 My Website</p>
    </footer>
    <script src="assets/bootstrap/bootstrap.bundle.min.js"></script>
    <script src="assets/js/script.js"></script>
</body>
</html>
```

## 4. Database Management

Use **PDO** for database interactions to ensure security.

### Example `db.php`:
```php
<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=my_database', 'username', 'password');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error connecting to the database: " . $e->getMessage());
}
?>
```

## 5. Security Best Practices

- **Sanitize User Input**: Always sanitize user input.
- **CSRF Tokens**: Use CSRF tokens for forms.
- **Secure Sessions**: Use secure session handling mechanisms.

### Example of Prepared Statements:
```php
$stmt = $pdo->prepare("SELECT * FROM users WHERE email = :email");
$stmt->execute(['email' => $email]);
$user = $stmt->fetch();
```

## 6. Frontend Structure

Use **Bootstrap** for styling and **jQuery** for interactions.

### Example HTML Template (`home.php`):
```php
<?php include 'includes/header.php'; ?>
<div class="container">
    <h1>Welcome to My PHP Website</h1>
    <p>This is a demo page.</p>
</div>
<?php include 'includes/footer.php'; ?>
```

## 7. CSS and JavaScript

### Custom CSS (`style.css`):
```css
body {
    background-color: #f8f9fa;
}

h1 {
    color: #343a40;
}
```

### Custom JavaScript (`script.js`):
```js
$(document).ready(function() {
    $("button").click(function() {
        alert("Button clicked!");
    });
});
```

## 8. Version Control (Git)

Use **Git** for version control. Example `.gitignore`:

```
/vendor/
node_modules/
.env
```

## 9. Deployment

- Host on a **LAMP** stack (Linux, Apache, MySQL, PHP).
- Use **FileZilla** or **Git** for deployment.

## 10. Testing and Debugging

Use PHP’s built-in server for testing:
```bash
php -S localhost:8000
```
