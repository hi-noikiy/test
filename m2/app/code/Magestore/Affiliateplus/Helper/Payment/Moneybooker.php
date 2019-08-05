<?php

/**
 * Magestore.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Magestore.com license that is
 * available through the world-wide-web at this URL:
 * http://www.magestore.com/license-agreement.html
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Magestore
 * @package     Magestore_Affiliateplus
 * @copyright   Copyright (c) 2012 Magestore (http://www.magestore.com/)
 * @license     http://www.magestore.com/license-agreement.html
 */
namespace Magestore\Affiliateplus\Helper\Payment;

/**
 * Class Moneybooker
 * @package Magestore\Affiliateplus\Helper\Payment
 */
class Moneybooker extends \Magestore\Affiliateplus\Helper\HelperAbstract
{
    /**
     * @var array
     */
    protected $_errorMsg = array();

    /**
     *
     */
    public function _construct() {
        parent::_construct();
        $this->_errorMsg = [
            'CANNOT_LOGIN'  => __('Moneybooker: Email address and/or API/MQI password are incorrect, please check your  configuration'),
            'LOGIN_INVALID' => __('Moneybooker: Email address and/or password were not provided'),
            'PAYMENT_DENIED' => __('Moneybooker: Check in your account profile that the API is enabled and you are posting your requests from the IP address specified'),
            'BALANCE_NOT_ENOUGH' => __('Moneybooker: Sending amount exceeds account balance.'),
            'LOCK_LEVEL_9'  => __('Moneybooker: This account is currently locked.'),
            'INVALID_EMAIL' => __('Moneybooker: This email is invalid.'),
            'SESSION_EXPIRED' => __('Moneybooker: Transfer failed.'),
        ];
    }

    /**
     * @return array
     */
    public function getErrorMessages(){
        return $this->_errorMsg;
    }

    /**
     * @param $url
     * @return mixed
     */
    public function readXml($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);         //set the $url to where your request goes
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //set this flag for results to the variable
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0); //This is required for HTTPS certs if
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0); //you don't have some key/password action

        /* execute the request */
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    /**
     * @param $code
     * @return bool
     */
    public function isError($code){
        if (in_array($code, array_keys($this->_errorMsg))) {
            return true;
        }
        return false;
    }

    /**
     * @param $data
     * @return string
     */
    public function transferMoney($data){
        $sid = $this->getSessionId($data);
        if($this->isError($sid)) {
            return $sid;
        }
        $url = 'https://www.moneybookers.com/app/pay.pl?action=transfer&sid=';
        if($sid){
            $url .= $sid;
        }
        $string = $this->readXml($url);
        $xml = simplexml_load_string($string);
        if($xml) {
            if ($xml->error) {
                if ($xml->error->error_msg){
                    return (string)$xml->error->error_msg;
                }
            } elseif ($xml->transaction) {
                if((string)$xml->transaction->id){
                    return (string)$xml->transaction->id;
                }
            }
        }
        return '';
    }

    /**
     * @param $account
     * @param $amount
     * @param null $storeId
     * @param null $paymentId
     * @return mixed
     */
    public function payoutByApi($account, $amount, $storeId = null, $paymentId = null) {
        if (!$storeId) {
            $stores = $this->_storeManager->getStores();
            foreach ($stores as $store) {
                $storeIds[] = $store->getId();
            }
        } else {
            $storeIds = $storeId;
        }
        $moneyBookerEmail = $account->getMoneybookerEmail();
        $whoPayFees = $this->getConfig('affiliateplus/payment/who_pay_fees',$storeId);
        $baseCurrencyCode = $this->_storeManager->getStore($storeId)->getBaseCurrencyCode();
        $data = array(
            'amount'=>$amount,
            'currency'=>$baseCurrencyCode,
            'email'=>$moneyBookerEmail
        );

        $payment = $this->_getPaymentModel()
            ->load($paymentId)
            ->setId($paymentId)
            ->setPaymentMethod('moneybooker')
            ->setAmount($amount)
            ->setAccountId($account->getId())
            ->setAccountName($account->getName())
            ->setAccountEmail($account->getEmail())
            ->setStoreIds(implode(',', $storeIds))
            ->setIsPayerFee(0);
        if ($account->getData('is_created_by_recurring')) {
            $payment->setData('is_created_by_recurring', 1)
                ->setData('is_recurring', 1);
        }
        $now = new \DateTime();
        if (!$paymentId) {
            $payment->setRequestTime($now)
                ->setStatus(1)
                ->setIsRequest(0);
        }
        if ($this->getConfig('affiliateplus/payment/who_pay_fees',$storeId) == 'payer')
            $payment->setIsPayerFee(1);

        try{
            $tranId = '';
            try {
                $tranId = $this->transferMoney($data);
            } catch (Exception $e){

            }
            if($this->isError($tranId)){
                $payment->setMessageCode($tranId);
                $payment->setErrorMessage($this->_errorMsg[$tranId]);
                $tranId = null;
                $status = 1;
            }else{
                $status = 3;
            }
            $moneyBookerModel = $payment->getPayment();
            $fee = $moneyBookerModel->getEstimateFee($amount, $whoPayFees);
            try{
                $payment->setData('affiliateplus_account', $account);
                $payment->setPaymentMethod('moneybooker')
                    ->setFee($fee)
                    ->setStatus($status) //complete
                    ->save();
                $moneyBookerModel
                    ->setEmail($account->getMoneybookerEmail())
                    ->setTransactionId($tranId)
                    ->savePaymentMethodInfo();
            }  catch (\Exception $e){

            }
        }  catch (\Exception $e){

        }
        return $payment;
    }

    /**
     * @return mixed
     */
    protected function _getPaymentModel(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment');
    }

    /**
     * @return mixed
     */
    protected function _getMoneyBookerPaymentModel(){
        return $this->_objectManager->create('Magestore\Affiliateplus\Model\Payment\MoneyBooker');
    }

    /**
     * @param $data
     * @param null $storeId
     * @return string
     */
    public function getSessionId($data, $storeId = null){
        $mailDefault = $this->getConfig('affiliateplus_payment/moneybooker/user_mechant_email_default',$storeId);
        if($mailDefault){
            $merchant_email = $this->getConfig('moneybookers/settings/moneybookers_email',$storeId);
        }
        $merchant_email = $this->getConfig('affiliateplus_payment/moneybooker/moneybooker_email',$storeId);
        $password = $this->getConfig('affiliateplus_payment/moneybooker/moneybooker_password',$storeId);
        $subject = $this->getConfig('affiliateplus_payment/moneybooker/notification_subject',$storeId);
        $subject = urlencode($subject);
        $note = $this->getConfig('affiliateplus_payment/moneybooker/notification_note',$storeId);
        $note = urlencode($note);
        $whoPayFees = $this->getConfig('affiliateplus/payment/who_pay_fees',$storeId);
        $url = 'https://www.moneybookers.com/app/pay.pl?action=prepare&email='.$merchant_email.'&password='.  md5($password);
        if(isset($data['amount'])){
            $amount = floatval($data['amount']);
            $fee = $this->_getMoneyBookerPaymentModel()->getEstimateFee($amount,$whoPayFees);
            if($whoPayFees == 'recipient'){
                $amount = $amount - $fee;
            }
            $url .= '&amount='.$amount;
        }
        if(isset($data['currency']))
            $url .= '&currency='.$data['currency'];
        if(isset($data['email']))
            $url .= '&bnf_email='.$data['email'];
        if(!$subject)
            $subject = 'Affiliate';
        $url .= '&subject='.$subject;
        if(!$note)
            $note = 'Affiliate';
        $url .= '&note='.$note;


        $data = $this->readXml($url);
        $xml = simplexml_load_string($data);
        if($xml && $xml->error){
            if($xml->error->error_msg){
                return (string)$xml->error->error_msg;
            }
        }
        if($xml->sid){
            return (string)$xml->sid;
        }
        return '';
    }
}