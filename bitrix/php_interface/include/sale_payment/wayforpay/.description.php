<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
include(GetLangFileName(dirname(__FILE__) . "/", "/.description.php"));


$psTitle = "WayForPay";
$psDescription = "<a href=\"http://wayforpay.com\" target=\"_blank\">http://wayforpay.com</a>";

$array = array(
    'wayforpay_merchant',
    'wayforpay_secret_key',
    'wayforpay_price_currency',
    'wayforpay_server_callback_url',
    'wayforpay_response_url',
    'wayforpay_language'
);


$arPSCorrespondence = array(
    "W4P_MERCHANT" => array(
        "NAME" => GetMessage("WAYFORPAY_MERCHANT"),
        "DESCR" => GetMessage("WAYFORPAY_MERCHANT"),
        "VALUE" => "",
        "TYPE" => ""
    ),
    "W4P_SECURE_KEY" => array(
        "NAME" => GetMessage("WAYFORPAY_SECURE_KEY"),
        "DESCR" => GetMessage("WAYFORPAY_SECURE_KEY"),
        "VALUE" => "",
        "TYPE" => ""
    ),
    "W4P_PRICE_CURRENCY" => array(
        "NAME" => GetMessage("WAYFORPAY_PRICE_CURRENCY"),
        "DESCR" => GetMessage("WAYFORPAY_DESC_PRICE_CURRENCY"),
        "VALUE" => "CURRENCY",
        "TYPE" => "ORDER"
    ),
    "W4P_SERVER_CALLBACK_URL" => array(
        "NAME" => GetMessage("WAYFORPAY_SERVER_CALLBACK_URL"),
        "DESCR" => GetMessage("WAYFORPAY_DESC_SERVER_CALLBACK_URL"),
        "VALUE" => "",
        "TYPE" => ""
    ),
    "W4P_RESPONSE_URL" => array(
        "NAME" => GetMessage("WAYFORPAY_RESPONSE_URL"),
        "DESCR" => GetMessage("WAYFORPAY_DESC_RESPONSE_URL"),
        "VALUE" => "",
        "TYPE" => ""
    ),
    "W4P_LANGUAGE" => array(
        "NAME" => GetMessage("WAYFORPAY_LANGUAGE"),
        "DESCR" => GetMessage("WAYFORPAY_DESC_LANGUAGE"),
        "VALUE" => "RU",
        "TYPE" => ""
    ),
);
?>