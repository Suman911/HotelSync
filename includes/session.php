<?php

session_set_cookie_params([
    'lifetime' => 1800,
    'secure' => true,
    'httponly' => true,
    'samesite' => 'Strict'
]);

session_start();