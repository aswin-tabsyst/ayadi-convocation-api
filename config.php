<?php
///
require __DIR__ . '/route_config.php';
$params = $_REQUEST;
include($connectionpath);
$headers_case_sensitive = apache_request_headers();
foreach ($headers_case_sensitive as $key => $value) {
    $headers[strtolower($key)] = $value;
}
$body_Request = "php://input";
if (isset($body_Request)) {
    $body_params = json_decode(file_get_contents($body_Request), true);
}


$params = array();
//$params = [...(array)$_REQUEST, ...(array)$body_params, ...(array)$headers];
if (!empty($_REQUEST)) {

    // $params = (array)[...$params, ...$_REQUEST];
    $params = array_merge($params, $_REQUEST);
}

if (!empty($body_params)) {
    //$params = (array)[...$params, ...$body_params];
    $params = array_merge($params, $body_params);

    //$params = [...(array)$$params = [...$params,...$_REQUEST];, ...(array)$body_params, ...(array)$headers];
}
if (!empty($headers)) {
    // $params = (array)[...$params, ...$headers];
    $params = array_merge($params, $headers);
}
$params["token"] = str_replace('Bearer ', '', $headers["authorization"]);
$device_header = $headers["device"];
$mob_config_device_id = $device_header;

//include($connectionpath);

function check_convocation_token($auth_array)
{


    $result = $GLOBALS["user"]->check_convocation_token($auth_array);

    return $result;
}


$version = $params['currentappversion'];
$platform = $params['platform'];
$con_key = $platform == "android" ? "marketing_app_android_version" : "marketing_app_ios_version";

$sql = "SELECT `con_value` FROM `configuration` WHERE `con_key` = '$con_key' ";
$qry = $conn->query($sql);
$result = mysqli_fetch_assoc($qry);
$ms_latest_version = $result['con_value'] ?? '1.1.1';

$params['emp_id'] = $params['empid'];

// if ($version != $ms_latest_version) {
//     $errors[] = "Incorrect version";
//     $response["message"] = $errors[0];
//     http_response_code(426);
//     die(json_encode($response));
// }
$params['emp_id'] = $params['empid'];

// if (isset($params['token'], $params['emp_id'])) {


//     $auth_array = [
//         "token" => $params['token'],
//         "emp_id" => $params['emp_id'],
//         "device_id" =>NULL,
//     ];




//     $result = check_convocation_token($auth_array);

//     if (!$result["result"]) {
//         $error[] = "Incorrect token";
//         $response["message"] = $error[0];
//         http_response_code(401);
//         die(json_encode($response));
//     } else {
//         $emp_name = $result["emp_name"];
//         $ewr_id = $result["ewr_id"];
//     }
// } else {

//     $response["status"] = "error";
//     $response["message"] = "Couldnt Submit Data";
//     http_response_code(400);
//     echo json_encode($response);
//     //end if submit
// }
