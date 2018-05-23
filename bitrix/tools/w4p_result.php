<?php
#ini_set( "display_errors", true );
#error_reporting( E_ALL );


if ($_SERVER["REQUEST_METHOD"] !== "POST") die();
if (!require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php")) die('prolog_before.php not found!');

if (CModule::IncludeModule('sale')) {
    $data = json_decode(file_get_contents("php://input"), true);

    $ordArray = explode( "_", $data['orderReference']); //order_id 
    $ORDER_ID = $ordArray[1];

    $arOrder = CSaleOrder::GetByID($ORDER_ID);

    $payID = $arOrder['PAY_SYSTEM_ID'];

    $temp = CSalePaySystemAction::GetList(
        array(),
        array("PAY_SYSTEM_ID" => $payID)
    );
    $payData = $temp->Fetch();

    include $_SERVER['DOCUMENT_ROOT'] . $payData['ACTION_FILE'] . "/way4pay.cls.php";


    $wfPayment = new Way4Pay();
    $WfpResult = $wfPayment->isPaymentValid($data);

    if($WfpResult){
        $arFields = array(
            "STATUS_ID" => "P",
            "PAYED" => "Y",
            "PS_STATUS" => "Y",
            "PS_STATUS_CODE" => $data['transactionStatus'],
            "PS_STATUS_DESCRIPTION" => $data['transactionStatus'] . " " . $payID,
            "PS_STATUS_MESSAGE" => " - ",
            "PS_SUM" => $data['amount'],
            "PS_CURRENCY" => $data['currency'],
            "PS_RESPONSE_DATE" => date("d.m.Y H:i:s"),
        );
        CSaleOrder::Update($ORDER_ID, $arFields);
    }

    echo $wfPayment->getResponseSignature($data);
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");

