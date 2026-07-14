<?php

header('Content-Type: application/json');
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require('../config.php');

$response = array();
$errors = [];

// Get request data
$member_ID = $_REQUEST['memberid'] ?? null;
$es_id = $_REQUEST['es_id'] ?? null;
$ev_id = $_REQUEST['ev_id'] ?? null;
$added_date = date("Y-m-d");

// if (!$es_id) {
//     $errors[] = "Session ID (es_id) is required.";
// }

$conditions = "";
$conditions1 = "";
$conditions2 = "";
$conditions3 = "";

if (!empty($es_id) && ($es_id != 'NULL') && ($es_id != '0')) {
    $conditions .= " AND `evrgn_es_id`='$es_id' ";
    $conditions1 .= " AND `evrgn_es_id`='$es_id' ";
    $conditions2 .= " AND  `es_id` = '$es_id'";
    $conditions3 .= " AND  `es_id` = '$es_id'";

}
if (!empty($ev_id) && ($ev_id != 'NULL') && ($ev_id != '0')) {
    $conditions .= " AND `evrgn_ev_id`='$ev_id' ";
    $conditions1 .= " AND `evrgn_ev_id`='$ev_id' ";
    $conditions2 .= " AND  `es_ev_id` = '$ev_id'";
    $conditions3 .= " AND  `es_ev_id` = '$ev_id'";

}






if (empty($errors)) {
    // Total approved registrations for this session
    $get_sql = "SELECT COUNT(evrgn_id) AS registration_count 
                FROM `event_registration` 
                WHERE `evrgn_status`='Approved' AND evrgn_ev_id != '1'  $conditions ";
    $GET_QRY = mysqli_fetch_assoc(mysqli_query($conn, $get_sql));

    // Total participants + additional participants for approved registrations
    $get_sql1 = "SELECT SUM(COALESCE(evrgn_no_of_participants, 0) + COALESCE(evrgn_no_of_additional_participants, 0)) AS participants_count 
                 FROM `event_registration` 
                 WHERE `evrgn_status`='Approved' AND evrgn_ev_id != '1'  $conditions";
    $GET_QRY1 = mysqli_fetch_assoc(mysqli_query($conn, $get_sql1));
    // Total participants + additional participants for approved registrations
    $get_sql5 = "SELECT SUM(COALESCE(evrgn_no_of_participants, 0)) AS main_participants_count 
                 FROM `event_registration` 
                 WHERE `evrgn_status`='Approved' AND evrgn_ev_id != '1'  $conditions";
    $GET_QRY5 = mysqli_fetch_assoc(mysqli_query($conn, $get_sql5));
    // Total participants + additional participants for approved registrations
    $get_sql6 = "SELECT SUM(COALESCE(evrgn_no_of_additional_participants, 0)) AS additional_participants_count 
                 FROM `event_registration` 
                 WHERE `evrgn_status`='Approved' AND evrgn_ev_id != '1'  $conditions";
    $GET_QRY6 = mysqli_fetch_assoc(mysqli_query($conn, $get_sql6));

    // Total food checked-in registrations
    $get_sq2 = "SELECT COUNT(evrgn_id) AS registration_check_count 
                FROM `event_registration` 
                WHERE `evrgn_food_status`='1' AND `evrgn_status`='Approved' AND evrgn_ev_id != '1'  $conditions";
    $GET_QRY2 = mysqli_fetch_assoc(mysqli_query($conn, $get_sq2));

    // Total participants + additional participants for food-checked registrations
    $get_sql3 = "SELECT SUM(COALESCE(evrgn_no_of_participants, 0)) AS main_participants_check_count 
                 FROM `event_registration` 
                 WHERE `evrgn_food_status`='1' AND evrgn_ev_id != '1' AND `evrgn_status`='Approved' $conditions ";
    $GET_QRY3 = mysqli_fetch_assoc(mysqli_query($conn, $get_sql3));

    // Total participants + additional participants for food-checked registrations
    $get_sql4 = "SELECT SUM(COALESCE(evrgn_no_of_additional_participants, 0)) AS additional_participants_check_count 
                 FROM `event_registration` 
                 WHERE `evrgn_food_status`='1' AND evrgn_ev_id != '1' AND `evrgn_status`='Approved' $conditions ";
    $GET_QRY4 = mysqli_fetch_assoc(mysqli_query($conn, $get_sql4));

    // Total participants + additional participants for food-checked registrations
    $get_sql7 = "SELECT SUM(COALESCE(evrgn_no_of_participants, 0) + COALESCE(evrgn_no_of_additional_participants, 0)) AS participants_check_count 
                 FROM `event_registration` 
                 WHERE `evrgn_food_status`='1' AND evrgn_ev_id != '1' AND `evrgn_status`='Approved' $conditions ";
    $GET_QRY7 = mysqli_fetch_assoc(mysqli_query($conn, $get_sql7));

    // Food distribution count
    $food_sql = "SELECT `evm_food`,`es_special_food`, SUM(COALESCE(`ef_rgn_food_count`, 0) + COALESCE(`ef_rgn_addit_food_count`, 0)) AS food_count
   FROM `event_registration`
    LEFT JOIN `event_food_registration` ON `ef_rgn_rgnid` = `evrgn_id`
     LEFT JOIN `event_sessions` ON `es_id`=`evrgn_es_id`
      LEFT JOIN `event_menu` ON `evm_id`=`ef_rgn_food`
      WHERE evrgn_ev_id != '1' 
       $conditions1
       GROUP BY `ef_rgn_rgnid`, `evm_food`;";

    //    die($food_sql);

    $food_result = mysqli_query($conn, $food_sql);


    $data = [
        'biriyani' => 0,
        'meals' => 0,
        'special_food' => 0
    ];
    $biriyani_count = 0;
    $meals_count = 0;
    while ($row = mysqli_fetch_assoc($food_result)) {
        $food_name = $row['evm_food'];
        $food_count = (int) $row['food_count'];

        if (strpos($food_name, 'BIRIYANI[CHICKEN]') !== false) {
            $biriyani_count += $food_count;
        } elseif (strpos($food_name, 'MEALS[VEG]') !== false) {
            $meals_count += $food_count;
        }


    }



    $special_food_query = "SELECT `es_special_food` FROM `event_registration` LEFT JOIN `event_sessions` ON  `evrgn_es_id`=`es_id` WHERE evrgn_ev_id != '1'  $conditions3";
    $special_food_result = mysqli_query($conn, $special_food_query);
    $special_food_count = 0;
    while ($row = mysqli_fetch_assoc($special_food_result)) {
        $special_food = $row['es_special_food'];


        if ($special_food == 1) {
            $special_food_count++;
        }

    }

    $data['biriyani'] = $biriyani_count;
    $data['meals'] = $meals_count;
    $data['special_food'] = $special_food_count;


    // Food distribution count
    $food_sql1 = "SELECT `evm_food`,`es_special_food`, SUM(COALESCE(`ef_rgn_food_count`, 0) + COALESCE(`ef_rgn_addit_food_count`, 0)) AS food_count
  FROM `event_food_registration`
   LEFT JOIN `event_registration` ON `ef_rgn_rgnid` = `evrgn_id`
    LEFT JOIN `event_sessions` ON `es_id`=`evrgn_es_id`
     LEFT JOIN `event_menu` ON `evm_id`=`ef_rgn_food`
     WHERE `evrgn_food_status` = '1' AND evrgn_ev_id != '1'  $conditions
      GROUP BY `ef_rgn_rgnid`, `evm_food`;";

    $food_result1 = mysqli_query($conn, $food_sql1);

    $chekin_data = [
        'biriyani' => 0,
        'meals' => 0,
        'special_food' => 0
    ];
    $biriyani_count = 0;
    $meals_count = 0;
    while ($row1 = mysqli_fetch_assoc($food_result1)) {
        $food_name = $row1['evm_food'];
        $food_count = (int) $row1['food_count'];

        if (strpos($food_name, 'BIRIYANI[CHICKEN]') !== false) {
            $biriyani_count += $food_count;
        } elseif (strpos($food_name, 'MEALS[VEG]') !== false) {
            $meals_count += $food_count;
        }


    }

    $special_food_query1 = "SELECT `es_special_food` FROM `event_registration` LEFT JOIN `event_sessions` ON  `evrgn_es_id`=`es_id` WHERE `evrgn_food_status` = '1' AND evrgn_ev_id != '1'  $conditions2";
    $special_food_result1 = mysqli_query($conn, $special_food_query1);
    $special_food_count = 0;
    while ($row2 = mysqli_fetch_assoc($special_food_result1)) {
        $special_food = $row2['es_special_food'];


        if ($special_food == 1) {
            $special_food_count++;
        }

    }


    $chekin_data['biriyani'] = $biriyani_count;
    $chekin_data['meals'] = $meals_count;
    $chekin_data['special_food'] = $special_food_count;
    // Final response
    $response = [
        "status" => "ok",
        "registration_count" => $GET_QRY["registration_count"] ?? "0",
        "total_participants_count" => $GET_QRY1["participants_count"] ?? "0",
        "main_participants_count" => $GET_QRY5["main_participants_count"] ?? "0",
        "additional_participants_count" => $GET_QRY6["additional_participants_count"] ?? "0",
        "registration_check_count" => $GET_QRY2["registration_check_count"] ?? "0",
        "total_participants_check_count" => $GET_QRY7["participants_check_count"] ?? "0",
        "main_participants_check_count" => $GET_QRY3["main_participants_check_count"] ?? "0",
        "additional_participants_check_count" => $GET_QRY4["additional_participants_check_count"] ?? "0",
        "food_data" => $data,
        "chekin_data" => $chekin_data
    ];
} else {
    // Error response
    $response["status"] = "error";
    $response["message"] = $errors[0];
    http_response_code(400);
}

// Output final JSON
echo json_encode($response);

