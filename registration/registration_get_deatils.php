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

$token = $params['token'];
$evrgn_id = $params['id'];
$session = $params['session'];
$device = $params['device'];
$student_details = [];


if (!empty($session)) {
    $conditions = "`evrgn_phone` LIKE '%$evrgn_id' AND `evrgn_es_id`='$session'";
} else {
    $conditions = "`evrgn_id`='$evrgn_id'";

}

$common = new common($conn, $member_ID);

if (empty($error)) {


    $sql = "SELECT `evrgn_id`, `evrgn_name`, `evrgn_phone`, `evrgn_ev_id`, `evrgn_es_id`, `evrgn_es_amount`, `evrgn_no_of_participants`, `evrgn_no_of_additional_participants`, `evrgn_additional_participants_amount`, `evrgn_total_amount`,`evrgn_food_status`,`ev_name`,`es_name`,`es_additional_perhead_amount`AS per_head_amount
      FROM `event_registration` LEFT JOIN `events` ON `evrgn_ev_id` = `ev_id`
       LEFT JOIN `event_sessions` ON `evrgn_es_id` = `es_id` WHERE `evrgn_status`='Approved' AND $conditions ";
    $student_details = $common->fetch($sql);
    $rgnID = $student_details['evrgn_id'];

    $food_sql = "SELECT `ef_rgn_food`, SUM(`ef_rgn_food_count` + `ef_rgn_addit_food_count`) AS food_count, `evm_food` FROM `event_food_registration` LEFT JOIN `event_menu` ON `ef_rgn_food` = `evm_id` WHERE `ef_rgn_rgnid` = '$rgnID' GROUP BY `ef_rgn_food`, `evm_food`";
    $food_result = mysqli_query($conn, $food_sql);

    // $special_food_query = "SELECT `es_special_food` FROM `event_registration` LEFT JOIN `event_sessions` ON  `evrgn_es_id`=`es_id` WHERE `evrgn_id` = '$rgnID'";
    // $special_food_result = mysqli_query($conn, $special_food_query);
    // $special_food_count = 0;
    // while ($row = mysqli_fetch_assoc($special_food_result)) {
    //     $special_food = $row['es_special_food'];


    //     if ($special_food == 1) {
    //         $special_food_count++;
    //     }

    // }

} else {
    $special_food_count = 0;

}



// die($food_sql);

$food_items = [];
// $food_items[] = [
//     'id' => '1',
//     'count' => "$special_food_count",
//     'name' => 'Special Food',

// ];
while ($food_row = mysqli_fetch_assoc($food_result)) {
    $food_items[] = [
        'id' => $food_row['ef_rgn_food'],
        'count' => $food_row['food_count'],
        'name' => $food_row['evm_food'],

    ];
}



// print_r($food_items);
if (!empty($student_details)) {
    $student_details['food'] = $food_items;

}











if (!empty($errors)) {

    $response["status"] = "error";
    $response["message"] = $errors[0];
    http_response_code(400);
} else {

    $response["status"] = "ok";
    $response["message"] = "success";
    $response["master_details"] = $student_details;
}

$conn->close();
echo json_encode($response);


