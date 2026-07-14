<?php
header('Content-Type: application/json');
error_reporting(0);

require('../config.php');

$response = array();
$params = $_REQUEST;

$result = array();
$errors = array();

$mob_number = isset($params["mob_number"]) ? trim($params["mob_number"]) : '';

if (empty($mob_number)) {
    $errors[] = "Mobile number is required";
}

if (empty($errors)) {
    $sql = "SELECT 
                evrgn_id, evrgn_name, evrgn_phone, ev_name , es_name
            FROM event_registration 
            LEFT JOIN events ON evrgn_ev_id = ev_id
            LEFT JOIN event_sessions ON evrgn_es_id = es_id 
            WHERE evrgn_status = 'Approved' AND evrgn_phone LIKE '%$mob_number'";


    // echo $sql;

    $qry = mysqli_query($conn, $sql);

    if ($qry->num_rows != 0) {
        while ($row = mysqli_fetch_assoc($qry)) {

            $result[] = $row;
        }
    } else {
        $errors[] = " Student does not exist ";
    }
}

// Prepare JSON response
if (!empty($errors)) {
    $response["status"] = "error";
    $response["message"] = $errors[0];
    http_response_code(400);
} else {
    $response["status"] = "ok";
    $response["message"] = "success";
    $response["data"] = $result;
}

// Send response as JSON
echo json_encode($response);
?>