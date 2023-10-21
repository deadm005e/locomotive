<?php
error_reporting(0);
ini_set('display_errors', 0);

include_once dirname(__FILE__).'/config.php';
include_once dirname(__FILE__).'/sources/classes/ThinkClientSync.php';

if(!empty($_POST["authorization"]) && $_POST["authorization"] === $GLOBALS['THINK_CLIENT_CONFIG']['KEY'])
{

    if(isset($_POST['sync_meta']))
    {
        $status = false;
        $data = json_decode($_POST['pages'], true);

        if(!empty($data)) {
            $sync = new ThinkClientSync();
            $status = $sync->write_data($data);
        }
        success_response($status);
    }

    # Check Connection
    else if(isset($_POST['check_connection']))
    {
        $status = file_put_contents($GLOBALS['THINK_CLIENT_CONFIG']['connection_ad'], 'test connection', LOCK_EX);
        success_response($status);
    }
}

# Check Key
if(!empty($_POST["check_key"]) && $_POST["check_key"] === $GLOBALS['THINK_CLIENT_CONFIG']['KEY']) {
    $status = true;
    success_response($status);
} else if(!empty($_POST["check_key"])) {
    $status = false;
    success_response($status);
}

function success_response($status) {
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: Mon, 20 Jan 2000 20:00:00 GMT');
    header('Content-type: application/json');

    echo json_encode(array( 'status' => $status ));
    exit;
}

?>