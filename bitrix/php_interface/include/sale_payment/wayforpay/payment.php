<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();
include dirname(__FILE__) . "/way4pay.cls.php";

if (isset($arResult['ORDER_ID'])) {
    $ORDER_ID = $arResult['ORDER_ID'];
} else {
    $ORDER_ID = $_GET['ORDER_ID'];
}

$w4p = new Way4Pay();

$ORDER_ID = filter_var($ORDER_ID, FILTER_SANITIZE_NUMBER_INT);

$arOrder = CSaleOrder::GetByID($ORDER_ID);
$dbBasket = CSaleBasket::GetList(Array("ID" => "ASC"), Array("ORDER_ID" => $ORDER_ID));
$basket = $dbBasket->arResult;
$rsUser = CUser::GetByID($arOrder['USER_ID']);
$arUser = $rsUser->Fetch();
$orderID = "W4P_" . $ORDER_ID . "_" . time();

$proPhone = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"),
    array("ORDER_ID" => $ORDER_ID, "ORDER_PROPS_ID" => 3));
$proPhone = $proPhone->arResult[0]['VALUE_ORIG'];

$proCity = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"),
    array("ORDER_ID" => $ORDER_ID, "ORDER_PROPS_ID" => 5));
$proCity = $proCity->arResult[0]['VALUE_ORIG'];

$proAddress = CSaleOrderPropsValue::GetList(array("SORT" => "ASC"),
    array("ORDER_ID" => $ORDER_ID, "ORDER_PROPS_ID" => 7));
$proAddress = $proAddress->arResult[0]['VALUE_ORIG'];


$formFields = array();

$formFields['merchantAccount'] = CSalePaySystemAction::GetParamValue("W4P_MERCHANT");
$formFields['orderReference'] = $orderID;
//$formFields['orderDate'] = strtotime($arOrder['DATE_INSERT']);
$formFields['orderDate'] = time();
$formFields['merchantAuthType'] = 'simpleSignature';
$formFields['merchantDomainName'] = $_SERVER['HTTP_HOST'];
$formFields['merchantTransactionSecureType'] = 'AUTO';
$formFields['currency'] = CSalePaySystemAction::GetParamValue("W4P_PRICE_CURRENCY");
$formFields['amount'] = round($arOrder['PRICE'], 2);
$productNames = array();
$productQty = array();
$productPrices = array();

foreach ($basket as $item) {
    $productNames[] = addslashes($item['NAME']);
    $productPrices[] = round($item['PRICE'], 2);
    $productQty[] = (int)$item['QUANTITY'];
}
$formFields['productName'] = $productNames;
$formFields['productPrice'] = $productPrices;
$formFields['productCount'] = $productQty;
$formFields['serviceUrl'] = CSalePaySystemAction::GetParamValue("W4P_SERVER_CALLBACK_URL");
$formFields['returnUrl'] = CSalePaySystemAction::GetParamValue("W4P_RESPONSE_URL");
/**
 * Check phone
 */
if ($arUser['PERSONAL_MOBILE'] != '') {
    $phone = $arUser['PERSONAL_MOBILE'];
} else {
    $phone = $proPhone;
}

$phone = str_replace(array('+', ' ', '(', ')', '-'), array(
    '',
    '',
    '',
    '',
    ''
), $phone);
if (strlen($phone) == 10) {
    $phone = '38' . $phone;
} elseif (strlen($phone) == 11) {
    $phone = '3' . $phone;
}

$name = $arOrder['USER_NAME'];
$street = $arUser['PERSONAL_STREET'];
$mailbox = $arUser['PERSONAL_MAILBOX'];
$city = $arUser['PERSONAL_CITY'];
$last_name = $arOrder['USER_LAST_NAME'];

$formFields['clientFirstName'] = isset($name) ? $name : '';
$formFields['clientLastName'] = isset($last_name) ? $last_name : '';
$formFields['clientEmail'] = $arOrder['USER_EMAIL'];
$formFields['clientPhone'] = $phone;

if ($city != '') {
    $formFields['clientCity'] = $city;
} else {
    $formFields['clientCity'] = $proCity;
}

if ($street != '' && $mailbox != '' && $city != '') {
    $formFields['clientAddress'] = $city . ', ' . $street . ', ' . $mailbox;
} else {
    $formFields['clientAddress'] = $proCity . ', ' . $proAddress;
}

$formFields['language'] = CSalePaySystemAction::GetParamValue("W4P_LANGUAGE");

$formFields['merchantSignature'] = $w4p->getRequestSignature($formFields);

echo '	<form action="' . Way4Pay::URL . '" method="post" id="wfp_payment_form" accept-charset="utf-8">';
foreach ($formFields as $name => $field) {
    if (is_array($field)) {
        foreach ($field as $aField) {
            echo '<input type="hidden" name="' . $name . '[]" value="' . $aField . '" />';
        }
    } else {
        echo '<input type="hidden" name="' . $name . '" value="' . $field . '" />';
    }
}
echo '<input type="submit" /></form>';
echo "<script> setTimeout(function() {
     document.getElementById('wfp_payment_form').submit();
     }, 100);
    </script>";
