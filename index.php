<!DOCTYPE html>
<?php
include_once "includes/conn.php";
session_start();
if (isset($_SESSION['user_id'])){
    $user_id = $_SESSION['user_id'];
    $userQuery = "SELECT * FROM user WHERE id = '$user_id'";
    $result = mysqli_query($conn, $userQuery);
    $user = mysqli_fetch_assoc($result);
}else{
    header('Location:login.php');
}
?>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
</body>
</html>
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