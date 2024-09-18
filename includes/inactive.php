<?php

$inactive = 600; 
if (isset($_SESSION['timeout'])) {
    $session_life = time() - $_SESSION['timeout'];
    if ($session_life > $inactive) {
        session_unset();    
        session_destroy();  
        setcookie(session_name(), '', time() - 3600, '/'); 
        header('Location: login.php');
        exit();
    }
}