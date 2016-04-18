<?php

class Way4Pay
{
    const URL = 'https://secure.wayforpay.com/pay';
    const WAYFORPAY_TRANSACTION_APPROVED = 'Approved';
    const WAYFORPAY_TRANSACTION_DECLINED = 'Declined';
    const WAYFORPAY_SIGNATURE_SEPARATOR = ';';
    const WAYFORPAY_ORDER_STATE_PAID = 'paid';
    protected $keysForResponseSignature = array(
        'merchantAccount',
        'orderReference',
        'amount',
        'currency',
        'authCode',
        'cardPan',
        'transactionStatus',
        'reasonCode'
    );
    /** @var array */
    protected $keysForSignature = array(
        'merchantAccount',
        'merchantDomainName',
        'orderReference',
        'orderDate',
        'amount',
        'currency',
        'productName',
        'productCount',
        'productPrice'
    );

    public function allowedCurrency()
    {
        return array('UAH', 'RUB', 'USD', 'EUR');
    }

    /**
     * @param $option
     * @param $keys
     *
     * @return string
     */
    public function getSignature($option, $keys)
    {
        $hash = array();
        foreach ($keys as $dataKey) {
            if (!isset($option[$dataKey])) {
                continue;
            }
            if (is_array($option[$dataKey])) {
                foreach ($option[$dataKey] as $v) {
                    $hash[] = $v;
                }
            } else {
                $hash [] = $option[$dataKey];
            }
        }
        $hash = implode(self::WAYFORPAY_SIGNATURE_SEPARATOR, $hash);

        return hash_hmac('md5', $hash, CSalePaySystemAction::GetParamValue("W4P_SECURE_KEY"));
    }

    /**
     * @param $options
     *
     * @return string
     */
    public function getRequestSignature($options)
    {
        return $this->getSignature($options, $this->keysForSignature);
    }

    /**
     * @param $options
     *
     * @return string
     */
    public function getResponseSignature($options)
    {
        return $this->getSignature($options, $this->keysForResponseSignature);
    }


    /**
     * @param $response
     * @return bool|string
     */
    public function isPaymentValid($response)
    {
        $sign = $this->getResponseSignature($response);
        if ($sign != $response['merchantSignature']) {
            return 'An error has occurred during payment. Signature is not valid.';
        }
        if ($response['transactionStatus'] != self::WAYFORPAY_TRANSACTION_APPROVED) {
            return false;
        }
        return true;
    }

    /**
     * @param $data
     *
     * @return string
     */
    public function getAnswerToGateWay($data)
    {
        $time = time();
        $responseToGateway = array(
            'orderReference' => $data['orderReference'],
            'status'         => 'accept',
            'time'           => $time
        );
        $sign = array();
        foreach ($responseToGateway as $dataKey => $dataValue) {
            $sign [] = $dataValue;
        }
        $sign = implode(self::WAYFORPAY_SIGNATURE_SEPARATOR, $sign);
        $sign = hash_hmac('md5', $sign, $this->secret_key);
        $responseToGateway['signature'] = $sign;

        return json_encode($responseToGateway);
    }

    protected function getRequest()
    {
        return json_decode(file_get_contents("php://input"), true);
    }
}
