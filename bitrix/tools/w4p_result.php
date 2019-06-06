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

    if($WfpResult === true){
        $arFields = array(
            /*Статус P=payed, если Вы используете другой финальный статус, то замените STATUS_ID на нужный*/
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
        $response = [
            'orderReference' => $data['orderReference'],
            'status'         => 'accept',
            'time'           => time(),
            'signature'      => '',
        ];
        $response['signature'] = $wfPayment->getSignature($response, ['orderReference', 'status', 'time']);

        echo json_encode($response);
    } else {
        print_r($WfpResult);
    }

}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");

