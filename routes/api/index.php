<?php

include './APIs.php';
require '../auth/JWT.php';

header('Content-Type: application/json');

$adminjwt = "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoxMjMsInVzZXJuYW1lIjoiam9obmRvZSIsInVzZXJ0eXBlIjoiYWRtaW4iLCJleHAiOjE5MTYyMzkwMjIsImlhdCI6MTcxNjIzOTAyMiwibmJmIjoxNzE2MjM5MDIyfQ.D04T94lEsdJ6HPHH10nmcc4OvQIu01iWn1CUytGdkZQXjobnJHRGidfOdYqaLJnhlBjc67QNK5IXMase6CVXGREuhJ1DchjeDOiABOPUjcore-oEH2Zm8355Ec-G25kswFicz4JpjFnYH6jMcz7ryAg1kW65nQe8bVJ-NB6Arf1C3Rrt2WDm6TYnsYSFYXMMZt-l_2c0Ey63bn4Gz4yb8zDE1VdJCBkAFc6MfzLb--jXKH_FuT9w9erc7CtSf_ckCa742U3ofm-jXcZkdYVXTiJhREtQM5HpCLWdfsfUvlv5dhbSlI9n0zgldjshu0i4bqr2XCbxzeBKM0yUnLx3hw";

$clientjwt = "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoxMjMsInVzZXJuYW1lIjoiZ2dzdHN4IiwidXNlcnR5cGUiOiJjbGllbnQiLCJleHAiOjE5MTYyMzkwMjIsImlhdCI6MTcxNjIzOTAyMiwibmJmIjoxNzE2MjM5MDIyfQ.UB3-t3THb4bgSFN4tTzwxJmmSH7rn6QudpaCkcghaw9Lvlq2jcvOcMugA8xj6X7g-yMcUicvmrHw65OtnRVNuvS6Jwq2Tzqa7j1nqg3ZGPvzIuq3s-g8qbO8LVPfY8PLfIQjeF1GH7v3ccIDcCvKgdIwM-0R1JMuWExZU3MBEEZd9Q-l1m1eqHxtqa-hDBfq6vvmcv_5s3dgEPG49Y1zzItO5cviW6w3Hssh6o7G4_ZVHMLp9Ag71YaDwYCUEUmJol6pDjSUCjD-bB0YbKAqZ3n3PXaO-BTWdXeGEoTfE_0c7PGm3nsVTNxTKZjlflGB_Fw5pFO6ufc-W_OYTTDEmQ";

$wrongjwt = "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoxMjMsInVzZXJuYW1lIjoiam9obmRvZSIsInVzZXJ0eXBlIjoiYWRtaW4iLCJleHAiOjE5MTYyMzkwMjIsImlhdCI6MTcxNjIzOTAyMiwibmJmIjoxNzE2MjM5MDIyfQ.D04T94lEsdJ6HPHH10nmcc4OvQIu01iWn1CUytGdkZQXjobnJHRGidfOdYqaLJnhlBjc67QNK5IXMase6CVXGREuhJ1DchjeDOiABOPUjcore-oEH2Zm8355Ec-G25kswFicz4Jpjgbes6jMcz7ryAg1kW65nQe8bVJ-NB6Arf1C3Rrt2WDm6TYnsYSFYXMMZt-l_2c0Ey63bn4Gz4yb8zDE1VdJCBkAFc6MfzLb--jXKH_FuT9w9erc7CtSf_ckCa742U3ofm-jXcZkdYVXTiJhREtQM5HpCLWdfsfUvlv5dhbSlI9n0zgldjshu0i4bqr2XCbxzeBKM0yUnLx3hw";

// setcookie('jwt_token', $adminjwt, time() + (86400 * 365 * 10), '/', '', false, true);

function extBody($post = false)
{
    if ($post)
        return json_decode(file_get_contents('php://input'), true) ?? $_POST;
    return $_GET;
}

try {
    $jwt = $_COOKIE['jwt_token'] ?? throw new Exception("Missing Credentials");
    $payload = JWT::decode($jwt);

    $reqMethod = $_SERVER['REQUEST_METHOD'];

    switch ($reqMethod) {
        case 'GET':
            $body = extBody();
            API::voidAPI($reqMethod, $payload['usertype'], $body);
            break;
        case 'POST':
            $body = extBody(true);
            API::voidAPI($reqMethod, $payload['usertype'], $body);
            break;
        case 'PUT':
            $body = extBody(true);
            API::voidAPI($reqMethod, $payload['usertype'], $body);
            break;
        case 'PATCH':
            $body = extBody(true);
            API::voidAPI($reqMethod, $payload['usertype'], $body);
            break;
        case 'DELETE':
            $body = extBody(true);
            API::voidAPI($reqMethod, $payload['usertype'], $body);
            break;
        case 'HEAD':
            API::voidAPI($reqMethod, $payload['usertype'], $body);
            break;
        case 'OPTIONS':
        default:
            API::options('optn_APIs', $payload['usertype']);
    }
} catch (Exception $e) {
    $responce = [
        'Error' => 'Access Denied, ' . $e->getMessage()
    ];
    echo json_encode($responce);
}