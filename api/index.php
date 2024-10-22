<?php
require_once '../auth/JWT.php';
require_once '../auth/envLoader.php';

function extBody()
{
    $rawData = file_get_contents('php://input');
    if (!$rawData)
        return $_GET;
    $data = json_decode($rawData, true);
    $data ?? parse_str($rawData, $data);
    return $data;
}
function extAuth($reqUri)
{
    if (isset($_COOKIE['jwt_token']))
        return JWT::decode($_COOKIE['jwt_token']);
    if ($reqUri === '/login')
        return [];
    else
        throw new Exception("Missing Credentials");
}

// $adminjwt = "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoxMjMsInVzZXJuYW1lIjoiam9obmRvZSIsInVzZXJ0eXBlIjoiYWRtaW4iLCJleHAiOjE5MTYyMzkwMjIsImlhdCI6MTcxNjIzOTAyMiwibmJmIjoxNzE2MjM5MDIyfQ.D04T94lEsdJ6HPHH10nmcc4OvQIu01iWn1CUytGdkZQXjobnJHRGidfOdYqaLJnhlBjc67QNK5IXMase6CVXGREuhJ1DchjeDOiABOPUjcore-oEH2Zm8355Ec-G25kswFicz4JpjFnYH6jMcz7ryAg1kW65nQe8bVJ-NB6Arf1C3Rrt2WDm6TYnsYSFYXMMZt-l_2c0Ey63bn4Gz4yb8zDE1VdJCBkAFc6MfzLb--jXKH_FuT9w9erc7CtSf_ckCa742U3ofm-jXcZkdYVXTiJhREtQM5HpCLWdfsfUvlv5dhbSlI9n0zgldjshu0i4bqr2XCbxzeBKM0yUnLx3hw";

// $clientjwt = "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoxMjMsInVzZXJuYW1lIjoiZ2dzdHN4IiwidXNlcnR5cGUiOiJjbGllbnQiLCJleHAiOjE5MTYyMzkwMjIsImlhdCI6MTcxNjIzOTAyMiwibmJmIjoxNzE2MjM5MDIyfQ.UB3-t3THb4bgSFN4tTzwxJmmSH7rn6QudpaCkcghaw9Lvlq2jcvOcMugA8xj6X7g-yMcUicvmrHw65OtnRVNuvS6Jwq2Tzqa7j1nqg3ZGPvzIuq3s-g8qbO8LVPfY8PLfIQjeF1GH7v3ccIDcCvKgdIwM-0R1JMuWExZU3MBEEZd9Q-l1m1eqHxtqa-hDBfq6vvmcv_5s3dgEPG49Y1zzItO5cviW6w3Hssh6o7G4_ZVHMLp9Ag71YaDwYCUEUmJol6pDjSUCjD-bB0YbKAqZ3n3PXaO-BTWdXeGEoTfE_0c7PGm3nsVTNxTKZjlflGB_Fw5pFO6ufc-W_OYTTDEmQ";

// $wrongjwt = "eyJhbGciOiJSUzI1NiIsInR5cCI6IkpXVCJ9.eyJ1c2VyX2lkIjoxMjMsInVzZXJuYW1lIjoiam9obmRvZSIsInVzZXJ0eXBlIjoiYWRtaW4iLCJleHAiOjE5MTYyMzkwMjIsImlhdCI6MTcxNjIzOTAyMiwibmJmIjoxNzE2MjM5MDIyfQ.D04T94lEsdJ6HPHH10nmcc4OvQIu01iWn1CUytGdkZQXjobnJHRGidfOdYqaLJnhlBjc67QNK5IXMase6CVXGREuhJ1DchjeDOiABOPUjcore-oEH2Zm8355Ec-G25kswFicz4Jpjgbes6jMcz7ryAg1kW65nQe8bVJ-NB6Arf1C3Rrt2WDm6TYnsYSFYXMMZt-l_2c0Ey63bn4Gz4yb8zDE1VdJCBkAFc6MfzLb--jXKH_FuT9w9erc7CtSf_ckCa742U3ofm-jXcZkdYVXTiJhREtQM5HpCLWdfsfUvlv5dhbSlI9n0zgldjshu0i4bqr2XCbxzeBKM0yUnLx3hw";

// setcookie('jwt_token', $adminjwt, time() + (86400 * 365 * 10), '/', '', false, true);

header('Content-Type: application/json');
try {
    EnvLoader::load();

    $reqUri = $_SERVER['REQUEST_URI'];
    $reqUri = str_replace($_ENV['API_Root'], '', $reqUri);
    $reqUri = strtok($reqUri, '?');
    $reqUri = rtrim($reqUri, '/');

    $body = extBody();
    $auth = extAuth($reqUri);

    $reqMethod = strtoupper($_SERVER['REQUEST_METHOD']);
    $validMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];

    if (in_array($reqMethod, $validMethods)) {
        require_once "./$reqMethod.php";
        $reqMethod::_($reqUri, $auth, $body);
    } else {
        throw new Exception("Error Processing Request: request method not found.");
    }

} catch (Exception $e) {
    http_response_code(400);
    $responce = [
        'Error' => 'Access Denied, ' . $e->getMessage()
    ];
    echo json_encode($responce);
}
