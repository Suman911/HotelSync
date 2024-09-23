<?php

include_once "includes/session.php";
include_once "includes/conn.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM user WHERE username = :email");
    $stmt->execute(['email' => $email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    // if ($user && password_verify($password, $user['password'])) {

    //     $_SESSION['user_id'] = $user['id'];
    //     $_SESSION['IPaddress'] = $_SERVER['REMOTE_ADDR'];
    //     $_SESSION['userAgent'] = $_SERVER['HTTP_USER_AGENT'];
    //     $_SESSION['timeout'] = time();

    //     header('Location: index.php');
    //     exit();
    // } else {
    //     $error = 'Invalid email or password';
    // }
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Login</title>
</head>

<body>
    <h2>Login</h2>
    <?php if (isset($error)) {
        echo "<p style='color: red;'>$error</p>";
    } ?>
    <form method="POST" action="login.php">
        <label>Email:</label>
        <input name="email" required><br>
        <label>Password:</label>
        <input type="password" name="password" required><br>
        <button type="submit">Login</button>
    </form>
</body>

</html>