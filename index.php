<!DOCTYPE html>
<?php
include_once "./includes/session.php";

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

include_once "./includes/inactive.php";
include_once "./auth/conn.php";

$_SESSION['timeout'] = time();
$user_id = $_SESSION['user_id'];
$stmt = $pdo->prepare("SELECT * FROM user WHERE id = :id");
$stmt->execute(['id' => $user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);
if ($user) {
    var_dump($user);
} else {
    echo "no user";
}

?>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>

<body>


    <?php
    include_once "includes/header.php";
    include_once "includes/sidebar.php";

    $page = key($_GET);

    switch ($page) {
        case 'dashboard':
            include_once 'templates/dashboard.php';
            break;
        case 'reservation':
            include_once 'templates/reservation.php';
            break;
        case 'staff_mang':
            include_once 'templates/staff_mang.php';
            break;
        case 'add_emp':
            include_once 'templates/add_emp.php';
            break;
        case 'complain':
            include_once 'templates/complain.php';
            break;
        case 'statistics':
            include_once 'templates/statistics.php';
            break;
        case 'emp_history':
            include_once 'templates/emp_history.php';
            break;
        case 'room_mang':
        default:
            include_once 'templates/room_mang.php';
            break;
    }

    include_once "includes/footer.php";
    ?>

</body>

</html>