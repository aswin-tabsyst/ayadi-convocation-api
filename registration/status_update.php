<?php
// die("hii");
header('Content-Type: application/json');
error_reporting(0);
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require('../config.php');

if (!isset($_REQUEST['memberid'])) {
    $errors[] = "Session time out";
} else {
    $added_by = $_REQUEST["memberid"];
    $updated_by = $_REQUEST["memberid"];

    $added_date = date("Y-m-d H:i:s");
    $updated_date = date("Y-m-d H:i:s");
}
//========================== INCLUDES END ====



if (isset($_REQUEST["id"]) && $_REQUEST["id"] != "") {
    $id = $_REQUEST["id"];
} else {
    $errors[] = "Please Enter student id";
}
if (isset($_REQUEST["status"]) && $_REQUEST["status"] != "") {
    $status = $_REQUEST["status"];
} else {
    $errors[] = "Please Enter status";
}

if (empty($errors)) {

    $sql = "UPDATE `event_registration` SET `evrgn_food_status` = '$status', `evrgn_updated_by` = '$updated_by', `evrgn_food_issued_date` = '$updated_date' WHERE `evrgn_id` = '$id' ";
    $result = $conn->query($sql);


    $sql = "SELECT `evrgn_id`,`evrgn_country_code`,`evrgn_phone`,`evrgn_name`,`evrgn_status`, 
    `es_name`,`ev_name`
     FROM `event_registration` LEFT JOIN `event_sessions` ON `evrgn_es_id` = `es_id` LEFT JOIN `events` ON `evrgn_ev_id` = `ev_id` WHERE `evrgn_id` = '$id'";
    $qry = mysqli_fetch_assoc(mysqli_query($conn, $sql));

    $evrgn_name = $qry['evrgn_name'];

    $session = $qry['es_name'];

    $event = $qry['ev_name'];


}
$evrgn_phone = $qry['evrgn_phone'];
$evrgn_country_code = $qry['evrgn_country_code'];

if (empty($errors)) {

    if ($status == '1') {

        $GET_SQL = "SELECT `evm_food`,SUM(COALESCE(`ef_rgn_food_count`,0) + COALESCE(`ef_rgn_addit_food_count`,0)) as food_count FROM `event_food_registration` LEFT JOIN `event_menu` ON `ef_rgn_food` = `evm_id` WHERE `ef_rgn_rgnid` = '$id' GROUP BY `evm_food`";
        $GET_QRY = mysqli_query($conn, $GET_SQL);
        if ($GET_QRY->num_rows > 0) {
            while ($ROW = mysqli_fetch_assoc($GET_QRY)) {
                if ($ROW['evm_food'] == 'BIRIYANI[CHICKEN]') {
                    $food_details .= "BIRIYANI - " . $ROW['food_count'] . " ";
                } elseif ($ROW['evm_food'] == 'MEALS[VEG]') {
                    $food_details .= "MEALS - " . $ROW['food_count'] . " ";
                }
            }
        }


        $apiUrl = 'https://api.happilee.io/api/v1/sendTemplateMessage';
        $apiKey = 'db582a14985641b79820148bdefa5d4a';
        $phoneNumber = $evrgn_country_code . $evrgn_phone;
        $templateId = '18510';
        $templateParams = [
            ['name' => 'name', 'value' => $evrgn_name],
            ['name' => 'event_name', 'value' => $event],
            ['name' => 'session_name', 'value' => $session],
            ['name' => 'food_details', 'value' => $food_details]

        ];
        $curl_result = sendTemplateMessage($apiUrl, $apiKey, $phoneNumber, $templateId, $templateParams);
        if ($curl_result['error']) {
            $error[] = $curl_result['message'];
        }
        $templateParams = json_encode($templateParams);
        $curl_result = json_encode($curl_result);
        $curl_result = mysqli_real_escape_string($conn, $curl_result);
        $sms_sql = "INSERT INTO `sms_log`( `sms_log_template_id`, `sms_log_type`, `sms_log_request`, `sms_log_response`, `sms_log_phone`, `sms_log_added_date`, `sms_log_added_by`)
                     VALUES ('$templateId','Status Update Approve','$templateParams','$curl_result','$phoneNumber','$added_date','$added_by')";
        $sms_qry = mysqli_query($conn, $sms_sql);
    }
    $conn->commit();
    $response["status"] = "ok";
    $response["message"] = "Data updated";
    $response["id"] = $id;
} else {
    $conn->rollback();
    $response["status"] = "error";
    $response["message"] = "Error : " . implode(" ", $errors);
}



echo json_encode($response);
$conn->close();
function sendTemplateMessage($apiUrl, $apiKey, $phoneNumber, $templateId, $templateParams)
{
    $data = [
        'candidate_details' => [
            'phone_number' => $phoneNumber
        ],
        'template_message_id' => $templateId,
        'template_params' => $templateParams
    ];

    // Initialize cURL
    $ch = curl_init($apiUrl);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'accept: application/json',
        'x-api-key: ' . $apiKey,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Execute cURL and handle the response
    $curl_response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return json_decode($curl_response, true);
}