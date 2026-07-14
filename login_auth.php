<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
require './route_config.php';
header('Content-Type: application/json');
//error_reporting(0);

include("config.php");
include($connectionpath);

function check_convocation_token($auth_array) {


    $result = $GLOBALS["user"]->check_mentor_token($auth_array);
 
   return $result;
}



 $token = $params['token'];
    $device = $params['device'];

    $member_ID = $params['memberid'];
    $auth_array=["token"=>$token,
                "member_id"=>$member_ID,
                "device_id"=>$device
                ];

  $result=check_convocation_token($auth_array);
    
      if (!$result["result"]) {
        $error[] = "Incorrect token";
        $response["message"] = $error[0];
        http_response_code(401);
        die(json_encode($response));
    } else {
        mysqli_select_db($conn, $result["database"]);
        $rmd_id = $result["rmd_id"];

    }