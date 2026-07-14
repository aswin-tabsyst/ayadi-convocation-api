<?php
// Include required files and initialize headers

// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
require './route_config.php';
header('Content-Type: application/json');
// error_reporting(0);
// die($connectionpath);
include($connectionpath);

// Get and process request headers
$headers_mixed = apache_request_headers();
foreach ($headers_mixed as $header => $val) {
    $headers[trim(strtolower($header))] = $val;
}

// Decode and extract login credentials
$login_credentials = explode(":", base64_decode(str_replace('Basic ', '', $headers["authorization"])));
$username = $login_credentials[0];
$password = $login_credentials[1];
$device_id = $headers["device"];


$response = array();
$error = array();

// Validate input parameters
if (!empty($username) && !empty($password) && !empty($device_id)) {


    try {


        $login_return = $user->login_Api($username, $password, $device_id, "Convocation");
    } catch (Exception $ex) {
        $error[] = $ex->getMessage();
    }

    if ($login_return === false) {
        $error[] = 'Wrong username or password or your account has not been activated.';
    }
} else {
    http_response_code(400);
    $response["status"] = "error";
    $response["message"] = "Invalid Parameter";
    die(json_encode($response));
}

// Prepare response
if (!empty($error)) {
    $response["status"] = "error";
    $response["message"] = $error[0];
    http_response_code(401);
} else {
    $response["status"] = "ok";
    $response["message"] = "Login Successful";
    $response["details"] = $login_return;
}
echo json_encode($response);
?>