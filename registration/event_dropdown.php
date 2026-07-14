<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

// Define the path to the included file
require('../config.php');


$response = array();
$id = $params['id'];
$added_date = date("Y-m-d H:i:s");

// Initialize arrays for data and errors
$data = $error = $condition = array();

// Check if the 'q' parameter is set in the request
if (isset($_REQUEST["q"])) {
    $search_term = $_REQUEST["q"];
    if (!empty($search_term)) {
        $condition[] = " `ev_name` LIKE '%{$search_term}%'";
    }
}
//
$condition[] = " `ev_id` != '1'";

if (!empty($condition)) {
    $where = " WHERE " . implode(" AND ", $condition);
}

// If no errors are present, proceed with database operations
if (empty($error)) {
    // Begin transaction
    $conn->autocommit(false);

    // Create an instance of the 'common' class with database connection and member ID
    $common = new common($conn, $member_ID);

    $GET_SQL = "SELECT `ev_id` as id ,`ev_name` as text FROM `events` 
       ";
    if (!empty($where)) {
        $GET_SQL .= $where;
    }
    $GET_SQL .= " GROUP BY `ev_id` ";
    $GET_QRY = $common->fetch_row_all($GET_SQL);
}
// print_r($GET_QRY);

if (!empty($error)) {
    $response["status"] = "error";
    $response["message"] = $error[0];
    http_response_code(400);
} else {
    $response["status"] = "ok";
    $response["message"] = "success";
    $response["data"] = $GET_QRY;
}

// Encode the result data as JSON format and send it
echo json_encode($response);

// Close the database connection
$conn->close();
