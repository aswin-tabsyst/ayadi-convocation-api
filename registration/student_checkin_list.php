<?php
// die("hii");
header('Content-Type: application/json');
error_reporting(0);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require('../config.php');
// print_r($params);


$response = array();



$es_id = $params['id'];
// $status = $params['status'];

if (!empty($es_id) && ($es_id != 'NULL') && ($es_id != '0')) {
    $conditions = " AND `evrgn_es_id` = '$es_id' ";


}

$students_list = [];
$common = new common($conn, $member_ID);

if (empty($error)) {


    $sql = "SELECT `evrgn_id`,`evrgn_name`,`evrgn_phone`,`evrgn_food_issued_date`,ev_name,`es_name` FROM `event_registration` LEFT JOIN `events` ON `evrgn_ev_id` = `ev_id` LEFT JOIN `event_sessions` ON `evrgn_es_id` = `es_id` WHERE  `evrgn_food_status` = '1' AND `evrgn_status` = 'Approved' $conditions ";
    $students_list = $common->fetch_row_all($sql);


}

if (!empty($errors)) {

    $response["status"] = "error";
    $response["message"] = $errors[0];
    http_response_code(400);
} else {

    $response["status"] = "ok";
    $response["message"] = "success";
    $response["master_details"] = $students_list;
}

$conn->close();
echo json_encode($response);
